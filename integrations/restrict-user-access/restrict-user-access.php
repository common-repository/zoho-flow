<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow              2.10.0
 * @since restrict-user-access  2.7
 */
class Zoho_Flow_Restrict_User_Access extends Zoho_Flow_Service{
    
    /**
     *  @var array Webhook events supported.
     */
    public static $supported_events = array(
        "user_level_added",
        "user_level_removed",
        "user_level_extended"
    );
    
    /**
     * List levels
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Optional. Arguments for querying levels.
     * @type int     limit        Number of levels to query for. Default: 200.
     * @type string  $order_by    Level list order by the field. Default: post_modified.
     * @type string  $order       Level list order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of form details | WP_Error object with error details.
     */
    public function list_levels( $request ){
        $args = array(
            "post_type" => "restriction",
            "numberposts" => ($request['limit']) ? $request['limit'] : '200',
            "orderby" => ($request['order_by']) ? $request['order_by'] : 'post_modified',
            "order" => ($request['order']) ? $request['order'] : 'DESC',
        );
        $level_list = get_posts( $args );
        $level_return_list = array();
        foreach ( $level_list as $form ){
            $level_return_list[] = array(
                "ID" => $form->ID,
                "post_title" => $form->post_title,
                "post_status" => $form->post_status,
                "post_author" => $form->post_author,
                "post_date" => $form->post_date,
                "post_modified" => $form->post_modified
            );
        }
        return rest_ensure_response( $level_return_list );
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
            $wp_user_obj = get_user_by( $fetch_field, $fetch_value );
            if( $wp_user_obj ){
                $user_details = $this->get_user_details( $wp_user_obj->ID );
                if( $user_details ){
                    return rest_ensure_response( $user_details );
                }
            }
            return new WP_Error( 'rest_bad_request', 'User does not exist!', array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', 'Mandatory fields not found', array( 'status' => 400 ) );
    }
    
    /**
     * Add level to user
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response user details array | WP_Error object with error details
     *
     */
    public function add_user_level( $request ){
        $user_id = $request->get_url_params()['user_id'];
        $level_id = $request->get_url_params()['level_id'];
        $wp_user_obj = get_user_by( 'ID', $user_id );
        if( $wp_user_obj ){
            if( $this->is_valid_level( $level_id ) ){
                $rua_user = rua_get_user( $user_id );
                $rua_user->add_level( $level_id );
                return rest_ensure_response( $this->get_user_details( $user_id ) );
            }
            return new WP_Error( 'rest_bad_request', 'Access level does not exist!', array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', 'User does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Remove level from user
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response user details array | WP_Error object with error details
     *
     */
    public function remove_user_level( $request ){
        $user_id = $request->get_url_params()['user_id'];
        $level_id = $request->get_url_params()['level_id'];
        $wp_user_obj = get_user_by( 'ID', $user_id );
        if( $wp_user_obj ){
            if( $this->is_valid_level( $level_id ) ){
                $rua_user = rua_get_user( $user_id );
                $rua_user->remove_level( $level_id );
                return rest_ensure_response( $this->get_user_details( $user_id ) );
            }
            return new WP_Error( 'rest_bad_request', 'Access level does not exist!', array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', 'User does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Get user details from ID.
     *
     * @param int $user_id  User ID.
     * @return array|boolean  User details array if the user exists | false for others.
     */
    private function get_user_details( $user_id ){
        if( isset( $user_id ) && is_numeric( $user_id ) ){
            $wp_user_obj = get_user_by( 'ID', $user_id );
            if( $wp_user_obj ){
                $user_details = json_decode( json_encode( $wp_user_obj->data ), true );
                unset( 
                    $user_details['user_pass'],
                    $user_details['user_activation_key']
                    );
                $rua_user = rua_get_user( $user_id );
                $level_ids = $rua_user->get_level_ids();
                foreach( $level_ids as $level_id ){
                    $level_obj = get_post( $level_id, 'ARRAY_A' );
                    $start_time = $rua_user->get_level_start( $level_id );
                    $expiry_time = $rua_user->get_level_expiry( $level_id );
                    $user_details['levels'][] = array(
                        'id' => $level_id,
                        'name' => $level_obj['post_title'],
                        'start_time' => 0 == $start_time ? null : date('Y-m-d H:i:s', $start_time),
                        'expiry_time' => 0 == $expiry_time ? null : date('Y-m-d H:i:s', $expiry_time)
                    );
                }
                return $user_details;
            }
        }
        return false;
    }
    
    /**
     * Check whether the Level ID is valid or not.
     *
     * @param int $level_id  Level ID.
     * @return boolean  true if the level exists | false for others.
     */
    private function is_valid_level( $level_id ){
        if( isset( $level_id ) ){
            if( "restriction" === get_post_type( $level_id ) ){
                return true;
            }
        }
        return false;
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
        if( ( !isset( $entry->level_id ) ) || !$this->is_valid_level( $entry->level_id ) ){
            return new WP_Error( 'rest_bad_request', "Level does not exist!", array( 'status' => 400 ) );
        }
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event,
                'level_id' => $entry->level_id
            );
            $post_name = "Restrict User Access ";
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
     * Fires once user added to the level.
     *
     * @param RUA_User  $rua_user_object    $rua_user_object.
     * @param int       $level_id           The id of the level.
     */
    public function payload_user_level_added( $rua_user_object, $level_id ){
        $args = array(
            'event' => 'user_level_added',
            'level_id' => $level_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = $this->get_user_details( $rua_user_object->get_id() );
            if( $user_data ){
                $event_data = array(
                    'event' => 'user_level_added',
                    'data' => $user_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once user removed from the level.
     *
     * @param RUA_User  $rua_user_object    $rua_user_object.
     * @param int       $level_id           The id of the level.
     */
    public function payload_user_level_removed( $rua_user_object, $level_id ){
        $args = array(
            'event' => 'user_level_removed',
            'level_id' => $level_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = $this->get_user_details( $rua_user_object->get_id() );
            if( $user_data ){
                $event_data = array(
                    'event' => 'user_level_removed',
                    'data' => $user_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once user level extended.
     *
     * @param RUA_User  $rua_user_object    $rua_user_object.
     * @param int       $level_id           The id of the level.
     */
    public function payload_user_level_extended( $rua_user_object, $level_id ){
        $args = array(
            'event' => 'user_level_extended',
            'level_id' => $level_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = $this->get_user_details( $rua_user_object->get_id() );
            if( $user_data ){
                $event_data = array(
                    'event' => 'user_level_extended',
                    'data' => $user_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/restrict-user-access/restrict-user-access.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['restrict_user_access'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}