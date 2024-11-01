<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow      2.10.0
 * @since wp-members    3.4.9.5
 */
class Zoho_Flow_WP_Members extends Zoho_Flow_Service{
    
    /**
     *  @var array Webhook events supported.
     */
    public static $supported_events = array(
        "user_registered",
        "user_profile_updated",
        "user_activated",
        "user_deactivated"
    );
    
    /**
     * List all form fields
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of fields | WP_Error object with error details
     *
     */
    public function get_all_fields( $request ){
        if( function_exists( 'wpmem_fields' ) ){
            return rest_ensure_response( wpmem_fields() );
        }
        return new WP_Error( 'rest_bad_request', 'Internal error', array( 'status' => 400 ) );
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
                $user_details = wpmem_user_data( $user->ID );
                $user_details['ID'] = $user->ID;
                unset(
                    $user_details['password'],
                    $user_details['confirm_password']
                    );
                return rest_ensure_response( $user_details );
            }
            return new WP_Error( 'rest_bad_request', 'User does not exist!', array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', 'Mandatory fields not found', array( 'status' => 400 ) );
    }
    
    /**
     * Activate user
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response success array | WP_Error object with error details
     *
     */
    public function activate_user( $request ){
        $user_id = $request['user_id'];
        $notify = isset( $request['notify'] ) && ( false == $request['notify'] ) ? false : true ;
        if( wpmem_is_user( $user_id ) ){
            wpmem_activate_user( $user_id, $notify );
            if( wpmem_is_user_activated( $user_id ) ){
                return rest_ensure_response( array(
                    'status' => 'Success',
                    'message' => 'User activated'
                ) );
            }
            return new WP_Error( 'rest_bad_request', 'User activation unsuccessful.', array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', 'User does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Deactivate user
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response success array | WP_Error object with error details
     *
     */
    public function deactivate_user( $request ){
        $user_id = $request['user_id'];
        if( wpmem_is_user( $user_id ) ){
            wpmem_deactivate_user( $user_id );
            if( !wpmem_is_user_activated( $user_id ) ){
                return rest_ensure_response( array(
                    'status' => 'Success',
                    'message' => 'User deactivated'
                ) );
            }
            return new WP_Error( 'rest_bad_request', 'User deactivation unsuccessful.', array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', 'User does not exist!', array( 'status' => 404 ) );
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
            $post_name = "WP-Members ";
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
     * @param int       $user_id            The id of the user.
     */
    public function payload_user_registered( $user_data ){
        $args = array(
            'event' => 'user_registered'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = array_merge( $user_data, wpmem_user_data( $user_data['ID'] ) );
            unset(
                $user_data['password'],
                $user_data['confirm_password']
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
     * @param array     $user_data          User data to be added.
     * @param int       $user_id            The id of the user.
     * @param array     $user_data          User data to be added.
     */
    public function payload_user_profile_updated( $user_data, $user_id, $pre_user_data ){
        $args = array(
            'event' => 'user_profile_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = array_merge( $user_data, wpmem_user_data( $user_id ) );
            unset(
                $user_data['password'],
                $user_data['confirm_password']
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
     * Fires after user is activated.
     *
     * @param int       $user_id            The id of the user.
     */
    public function payload_user_activated( $user_id ){
        $args = array(
            'event' => 'user_activated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) && wpmem_is_user( $user_id ) ){
            $user_data = wpmem_user_data( $user_id );
            $user_data['ID'] = $user_id;
            unset(
                $user_data['password'],
                $user_data['confirm_password']
                );
            $event_data = array(
                'event' => 'user_activated',
                'data' => $user_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after user is deactivated.
     *
     * @param int       $user_id            The id of the user.
     */
    public function payload_user_deactivated( $user_id ){
        $args = array(
            'event' => 'user_deactivated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) && wpmem_is_user( $user_id ) ){
            $user_data = wpmem_user_data( $user_id );
            $user_data['ID'] = $user_id;
            unset(
                $user_data['password'],
                $user_data['confirm_password']
                );
            $event_data = array(
                'event' => 'user_deactivated',
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/wp-members/wp-members.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['wp_members'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}