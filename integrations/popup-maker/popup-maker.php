<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow      2.6.0
 * @since popup-maker   1.19.0
 */
class Zoho_Flow_Popup_Maker extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array( "form_entry_added" );
    
    /**
     * List all popups
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error Array of all popups | WP_Error object with error details.
     */
    public function list_popups( $request ){
        $popups = pum_get_all_popups();
        $popup_array = array();
        foreach ( $popups as $popup ){
            $popup_array[] = array( 
                'id' => $popup->ID,
                'name' => $popup->post_title
            );
        }
        return rest_ensure_response( $popup_array );
    }
    
    /**
     * Update popup status
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function update_popup_status( $request ){
        $popup_id = $request['popup_id'];
        $enabled = $request['enabled'];
        $popup = pum_get_popup($popup_id);
        if( isset( $popup_id ) && (pum_get_popup($popup_id)->ID == $popup_id ) && ( null !== $popup->get_meta( 'enabled' ) ) ){
            if( in_array( $enabled, [ 0 , 1 ] ) ){
                $popup->update_meta( 'enabled', $enabled );
                return rest_ensure_response( array( 
                    'popup_id' => $popup_id,
                    'enabled' => $enabled
                ) );
                return new WP_Error( 'rest_bad_request', 'Something went wrong, please try again.', array( 'status' => 400 ) );
            }
            return new WP_Error( 'rest_bad_request', 'Invalid enable state provided.', array( 'status' => 400 ) );
        }
        return new WP_Error( 'rest_bad_request', 'Invalid popup ID provided.', array( 'status' => 400 ) );
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
        if( !isset( $entry->popup_id ) || ( pum_get_popup( $entry->popup_id )->ID != $entry->popup_id ) ){
            return new WP_Error( 'rest_bad_request', 'Invalid popup ID provided.', array( 'status' => 400 ) );
        }
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event,
                'popup_id' => $entry->popup_id
            );
            $post_name = "Popup Maker ";
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
     * Fires after the dafult form used in the popup is submitted.
     *
     * @param array     $values     Form entry details
     */
    public function payload_form_entry_added( $values ){
        $popup_id = $values['popup_id'];
        $args = array(
            'event' => 'form_entry_added',
            'popup_id' => $popup_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'form_entry_added',
                'data' => $values
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/popup-maker/popup-maker.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['popup_maker'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}