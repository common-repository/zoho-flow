<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow          2.10.0
 * @since new-user-approve  2.6.1
 */
class Zoho_Flow_New_User_Approve extends Zoho_Flow_Service{
    
    /**
     *  @var array Webhook events supported.
     */
    public static $supported_events = array(
        "user_approved",
        "user_denied",
        "user_invited"
    );
    
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
            $post_name = "New User Approve ";
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
     * Fires after user approved.
     *
     * @param WP_User   $user   WP_User object.
     */
    public function payload_user_approved( $user ){
        $args = array(
            'event' => 'user_approved'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = $user->data;
            unset(
                $user_data->user_pass, $user_data->user_activation_key,
                );
            $event_data = array(
                'event' => 'user_approved',
                'data' => $user_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after user denied.
     *
     * @param WP_User   $user   WP_User object.
     */
    public function payload_user_denied( $user ){
        $args = array(
            'event' => 'user_denied'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = $user->data;
            unset(
                $user_data->user_pass, $user_data->user_activation_key,
                );
            $event_data = array(
                'event' => 'user_denied',
                'data' => $user_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after user is invited.
     *
     * @param int       $user_id            The id of the user.
     * @param string    $invitation_code    Invitation code
     */
    public function payload_user_invited( $user_id, $invitation_code ){
        $args = array(
            'event' => 'user_invited'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user = get_user_by( 'ID', $user_id );
            if( $user ){
                $user_data = $user->data;
                $user_data = json_decode( json_encode( $user->data ), true);
                unset(
                    $user_data['user_pass'], $user_data['user_activation_key'],
                    );
                $user_data['invitation_code'] = $invitation_code;
                $event_data = array(
                    'event' => 'user_invited',
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/new-user-approve/new-user-approve.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['new_user_approve'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}