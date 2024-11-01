<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow      2.5.0
 * @since jetpack_crm   6.4.2
 */
class Zoho_Flow_WP_Booking_Calendar extends Zoho_Flow_Service{
    
    /**
     * 
     * @var array Webhook events supported.
     */
    public static $supported_events = array( "booking_added", "booking_approved", "booking_moved_to_trash", "booking_pending" );
    
    /**
     * List booking fields
     * 
     * @param WP_REST_Request   $request    WP_REST_Request object.
     * 
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of field details | WP_Error object with error details.
     */
    public function list_fields( $request ){
        if ( class_exists( 'WPBC_Page_SettingsFormFieldsFree' ) ) {
            $form_free = New WPBC_Page_SettingsFormFieldsFree();
            $form_fields = $form_free->get_booking_form_structure_for_visual();
            return rest_ensure_response( $form_fields );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Something went wrong. Contact Flow support.', array( 'status' => 400 ) );
        }
        
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
            $post_name = "WP Booking Calendar ";
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
     * Fires once the booking is added
     *
     * @param array $params booking params
     */
    public function payload_booking_added( $params ){
        $args = array(
            'event' => 'booking_added'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $booking_details = wpbc_api_get_booking_by_id( $params['booking_id'] );
            $event_data = array(
                'event' => 'booking_added',
                'data' => $booking_details
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires once the booking is approved
     *
     * @param array $params         booking params
     * @param array $action_result  result of action
     */
    public function payload_booking_approved( $params, $action_result ){
        $args = array(
            'event' => 'booking_approved'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $booking_details = wpbc_api_get_booking_by_id( $params['booking_id'] );
            $booking_details[ 'resource_id'] = $params[ 'selected_resource_id' ];
            $booking_details[ 'reason_of_action'] = $params[ 'reason_of_action' ];
            $booking_details[ 'remark'] = $params[ 'remark' ];
            $booking_details[ 'payment_status'] = $params[ 'selected_payment_status' ];
            $booking_details[ 'booking_cost'] = $params[ 'booking_cost' ];
            $booking_details[ 'feedback_note'] = $params[ 'feedback__note' ];
            $booking_details[ 'feedback_stars'] = $params[ 'feedback_stars' ];
            $event_data = array(
                'event' => 'booking_approved',
                'data' => $booking_details
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires once the booking is marked as pending
     *
     * @param array $params         booking params
     * @param array $action_result  result of action
     */
    public function payload_booking_pending( $params, $action_result ){
        $args = array(
            'event' => 'booking_pending'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $booking_details = wpbc_api_get_booking_by_id( $params['booking_id'] );
            $booking_details[ 'resource_id'] = $params[ 'selected_resource_id' ];
            $booking_details[ 'reason_of_action'] = $params[ 'reason_of_action' ];
            $booking_details[ 'remark'] = $params[ 'remark' ];
            $booking_details[ 'payment_status'] = $params[ 'selected_payment_status' ];
            $booking_details[ 'booking_cost'] = $params[ 'booking_cost' ];
            $booking_details[ 'feedback_note'] = $params[ 'feedback__note' ];
            $booking_details[ 'feedback_stars'] = $params[ 'feedback_stars' ];
            $event_data = array(
                'event' => 'booking_pending',
                'data' => $booking_details
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires once the booking is moved to trash
     *
     * @param array $params         booking params
     * @param array $action_result  result of action
     */
    public function payload_booking_moved_to_trash( $params, $action_result ){
        $args = array(
            'event' => 'booking_moved_to_trash'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $booking_details = wpbc_api_get_booking_by_id( $params['booking_id'] );
            $booking_details[ 'resource_id'] = $params[ 'selected_resource_id' ];
            $booking_details[ 'reason_of_action'] = $params[ 'reason_of_action' ];
            $booking_details[ 'remark'] = $params[ 'remark' ];
            $booking_details[ 'payment_status'] = $params[ 'selected_payment_status' ];
            $booking_details[ 'booking_cost'] = $params[ 'booking_cost' ];
            $booking_details[ 'feedback_note'] = $params[ 'feedback__note' ];
            $booking_details[ 'feedback_stars'] = $params[ 'feedback_stars' ];
            $event_data = array(
                'event' => 'booking_moved_to_trash',
                'data' => $booking_details
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/booking/wpdev-booking.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['wp_booking_calendar'] = @$plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}