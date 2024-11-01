<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow          2.6.0
 * @since super-socializer  7.13.65
 */
class Zoho_Flow_Super_Socializer extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array( "user_created", "user_login" );
    
    /**
     * Current user details
     * 
     * @param   WP_REST_Request $request WP_REST_Request object.
     * @return  WP_REST_Response|WP_Error Array of current use details | WP_Error object with error details.
     */
    public function get_curren_user( $request ){
        $user_details = wp_get_current_user();
        unset( $user_details->data->user_pass );
        return rest_ensure_response( $user_details );
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
                'event' => $entry->event
            );
            $post_name = "Super Socializer ";
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
     * Fired after user login using social media
     *
     * @param int       $user_id        WordPress user ID
     * @param array     $profile_data   Download package details
     * @param int       $social_id      Social ID
     * @param bool      $update         Is this login updates user data
     */
    public function payload_user_login( $user_id, $profile_data, $social_id, $update ){
        $args = array(
            'event' => 'user_login'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $profile_data[ 'wp_user_id' ] = $user_id;
            $profile_data[ 'updated' ] = $update;
            $event_data = array(
                'event' => 'user_login',
                'data' => $profile_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fired after user signed up using social media
     *
     * @param int       $user_id        WordPress user ID
     * @param array     $user_data      WordPress user data
     * @param array     $profile_data   Download package details
     */
    public function payload_user_created( $user_id, $user_data, $profile_data ){
        $args = array(
            'event' => 'user_created'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            unset( $user_data[ 'user_pass' ] );
            $profile_data[ 'wp_user_id' ] = $user_id;
            $profile_data[ 'wp' ] = $user_data;
            $event_data = array(
                'event' => 'user_created',
                'data' => $profile_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /*
     * Get user and system info.
     * Default API
     *
     * @return WP_REST_Response|WP_Error  WP_REST_Response system and logged in user details | WP_Error object with error details.
     */
    public function get_system_info(){
        $system_info = parent::get_system_info();
        if( ! function_exists( 'get_plugin_data' ) ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/super-socializer/super_socializer.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['super_socializer'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}