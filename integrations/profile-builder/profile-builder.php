<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow              2.10.0
 * @since profile-builder       3.12.4
 */
class Zoho_Flow_Profile_Builder extends Zoho_Flow_Service{
    
    /**
     *  @var array Webhook events supported.
     */
    public static $supported_events = array(
        "user_registered",
        "user_profile_updated",
    );
    
    /**
     * List all form fields
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of fields | WP_Error object with error details
     *
     */
    public function get_all_fields( $request ){
        $fields = get_option('wppb_manage_fields');
        return rest_ensure_response( $fields );
    }
    
    /**
     * Fetch user details
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response user details array | WP_Error object with error details
     *
     */
    public function fetch_user( $request ){
        $fetch_field = $request['fetch_field'];
        $fetch_value = $request['fetch_value'];
        if(isset( $fetch_field ) && isset( $fetch_value ) ){
            $user_details = $this->get_user_details( $fetch_field, $fetch_value );
            if( $user_details ){
                return rest_ensure_response( $user_details );
            }
            return new WP_Error( 'rest_bad_request', 'User does not exist!', array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', 'Mandatory fields not found', array( 'status' => 400 ) );
    }
    
    /**
     * Get user details.
     * 
     * @param string $fetch_field  Field to fetch.
     * @param string $fetch_value  Value of the field to fetch.
     * 
     * @return array|boolean  User details array if the user exists | false for others.
     */
    private function get_user_details( $fetch_field, $fetch_value ){
        $allowed_fetch_feilds = array(
            "id",
            "ID",
            "slug",
            "email",
            "login"
        );
        if( isset( $fetch_field ) && in_array( $fetch_field, $allowed_fetch_feilds ) && isset( $fetch_value ) ){
            $user = get_user_by( $fetch_field, $fetch_value );
            if( $user ){
                $user_details = json_decode( json_encode( $user->data ), true );
                unset( $user_details['user_pass'] );
                $meta = $this->get_user_meta_details( $user_details['ID'] );
                if( $meta ){
                    $user_details = array_merge($user_details, $meta );
                }
                return $user_details;
            }
        }
        return false;
    }
    
    /**
     * Get user's meta details.
     * 
     * @param int $user_id      ID of the user
     * 
     * @return array|boolean  User meta array if the user exists | false for others.
     */
    private function get_user_meta_details( $user_id ){
        $meta = get_user_meta( $user_id );
        if( $meta ){
            $meta_details = array();
            foreach ( $meta as $meta_key => $meta_value ){
                if( 1 >= sizeof( $meta_value ) ){
                    $meta_details[$meta_key] = maybe_unserialize( $meta_value[0] );
                }
                elseif (1 < sizeof( $meta_value ) ){
                    foreach ($meta_value as $value) {
                        $meta_details[$meta_key][] = maybe_unserialize( $value );
                    }
                }
            }
            return $meta_details;
        }
        return $meta;
    
    }
    
    /**
     * Creates a webhook entry
     * The events available in $supported_events array only accepted
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with Webhook ID | WP_Error object with error details.
     */
    public function create_webhook( $request ){
        $entry = json_decode( $request->get_body() );
        
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event,
            );
            $post_name = "Profile Builder ";
            $post_id = $this->create_webhook_post( $post_name, $args );
            if( is_wp_error( $post_id ) ){
                $errors = $post_id->get_error_messages();
                return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
            }
            return rest_ensure_response(
                array(
                    'webhook_id' => $post_id
                ) );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Data validation failed', array( 'status' => 400 ) );
        }
    }
    
    /**
     * Deletes a webhook entry
     * Webhook ID returned from webhook create event should be used. Use minimum user scope.
     *
     * @param WP_REST_Request   $request    WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with success message | WP_Error object with error details.
     */
    public function delete_webhook( $request ){
        $webhook_id = $request['webhook_id'];
        if( is_numeric( $webhook_id ) ){
            $webhook_post = $this->get_webhook_post( $webhook_id );
            if( !empty( $webhook_post[0]->ID ) ){
                $delete_webhook = $this->delete_webhook_post( $webhook_id );
                if( is_wp_error( $delete_webhook ) ){
                    $errors = $delete_webhook->get_error_messages();
                    return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
                }
                else{
                    return rest_ensure_response( array( 'message' => 'Success' ) );
                }
            }
            else{
                return new WP_Error( 'rest_bad_request', 'Invalid webhook ID', array( 'status' => 400 ) );
            }
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Invalid webhook ID', array( 'status' => 400 ) );
        }
    }
    
    /**
     * Fires after user is registered.
     * 
     * @param array     $user_data          User data to be added.
     * @param string    $form_name          Name of the form.
     * @param int       $user_id            The id of the user.
     */
    public function payload_user_registered( $user_data, $form_name, $user_id ){
        $args = array(
            'event' => 'user_registered'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_details = $this->get_user_details( 'ID', $user_id );
            if( $user_details ){
                $user_data = array_merge( $user_details, $user_data );
            }
            unset(
                $user_data['passw1'],
                $user_data['passw2']
                );
            $event_data = array(
                'event' => 'user_registered',
                'data' => $user_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after user profile is updated.
     * 
     * @param array     $user_data          User data to be updated.
     * @param string    $form_name          Name of the form.
     * @param int       $user_id            The id of the user.
     */
    public function payload_user_profile_updated( $user_data, $form_name, $user_id ){
        $args = array(
            'event' => 'user_profile_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_details = $this->get_user_details( 'ID', $user_id );
            if( $user_details ){
                $user_data = array_merge( $user_details, $user_data );
            }
            unset(
                $user_data['passw1'],
                $user_data['passw2']
                );
            $event_data = array(
                'event' => 'user_profile_updated',
                'data' => $user_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Default API.
     * Get user and system info.
     *
     * @return array|WP_Error System and logged in user details | WP_Error object with error details.
     */
    public function get_system_info(){
        $system_info = parent::get_system_info();
        if( ! function_exists( 'get_plugin_data' ) ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/profile-builder/index.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['profile_builder'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}