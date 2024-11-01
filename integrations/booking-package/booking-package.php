<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow          2.9.0
 * @since booking-package   1.6.62
 */
class Zoho_Flow_Booking_Package extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array(
        "booking_completed",
        "booking_status_changed"
    );
    
    /**
     * List calendars
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $limit         Number of results. Default: 200.
     * @type string  $order_by      List order by the field. Default: created.
     * @type string  $order         List order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response    WP_REST_Response array with calendar details
     */
    public function list_calendar_account_list( $request ){
        global $wpdb;
        $order_by_allowed = array(
            'created',
            'uploadDate',
            'name'
        );
        $order_allowed = array('ASC', 'DESC');
        $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'created';
        $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'DESC';
        $limit = ($request['limit']) ? $request['limit'] : '200';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}booking_package_calendar_accounts ORDER BY $order_by $order LIMIT %d",
                $limit
            ), 'ARRAY_A'
                );
        foreach ($results as $index => $row ){
            unset( $results[$index]['icalToken'], 
                $results[$index]['customizeLabels'], 
                $results[$index]['customizeLayoutsBool'], 
                $results[$index]['customizeLayouts'], 
                $results[$index]['customizeButtonsBool'], 
                $results[$index]['customizeLabelsBool'],
                $results[$index]['customizeButtons'] 
                );
        }
        return rest_ensure_response( $results );
    }
    
    /**
     * List custom fields
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $limit         Number of results. Default: 200.
     * @type string  $order_by      List order by the field. Default: ordering.
     * @type string  $order         List order Values: ASC|DESC. Default: ASC.
     *
     * @return WP_REST_Response    WP_REST_Response array with custom field details
     */
    public function list_form_fields( $request ){
        global $wpdb;
        $calendar_id = $request->get_url_params()['calendar_id'];
        $results = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}booking_package_form WHERE `key` = %d",
                $calendar_id
            ), 'ARRAY_A'
                );
        if( $results ){
            return rest_ensure_response( json_decode( $results['data'],true ) );
        }
        return new WP_Error( 'rest_bad_request', 'Calendar form does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Check whether the calendar ID is valid or not.
     *
     * @param int $calendar_id  ID of the calendar.
     * @return array|bool Staff array if exists | false for others.
     */
    private function is_valid_calendar( $calendar_id ){
        if( isset( $calendar_id ) && is_numeric( $calendar_id )){
            global $wpdb;
            $results = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}booking_package_calendar_accounts WHERE `key` = %d",
                    $calendar_id
                ), 'ARRAY_A'
                    );
            if( !empty( $results ) ){
                return $results;
            }
        }
        return false;
    }
    
    /**
     * Check whether the customer ID is valid or not.
     *
     * @param int $booking_id  ID of the booking.
     * @return array|bool customer array if exists | false for others.
     */
    private function is_valid_customer( $booking_id ){
        if( isset( $booking_id ) && is_numeric( $booking_id )){
            global $wpdb;
            $results = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}booking_package_booked_customers WHERE `key` = %d",
                    $booking_id
                ), 'ARRAY_A'
                    );
            if( !empty( $results ) ){
                $results['customer_key'] = $results['key'];
                $results['praivateData'] = $results['praivateData'] ? json_decode( $results['praivateData'],true ) : '';
                $results['emails'] = $results['emails'] ? json_decode( $results['emails'],true ) : '';
                $results['accommodationDetails'] = $results['accommodationDetails'] ? json_decode( $results['accommodationDetails'],true ) : '';
                $results['preparation'] = $results['preparation'] ? json_decode( $results['preparation'],true ) : '';
                return $results;
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
        if( !isset($entry->calendar_id) || !$this->is_valid_calendar( $entry->calendar_id ) ){
            return new WP_Error( 'rest_bad_request', 'Calendar does not exist!', array( 'status' => 404 ) );
        }
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event,
                'calendar_id' => $entry->calendar_id
            );
            $post_name = "Booking Package ";
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
     * Fires once the booking is created.
     *
     * @param int   $booking_id    Booking ID.
     */
    public function payload_booking_completed( $booking_id ){
        $customer_data = $this->is_valid_customer( $booking_id );
        if( $customer_data ){
            $args = array(
                'event' => 'booking_completed',
                'calendar_id'=>$customer_data['accountKey']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'booking_completed',
                    'data' => $customer_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the booking status is changed.
     *
     * @param array   $booking_data    Booking details array.
     */
    public function payload_booking_status_changed( $booking_data ){
        $customer_data = $this->is_valid_customer( $booking_data['id'] );
        if( $customer_data ){
            $args = array(
                'event' => 'booking_status_changed',
                'calendar_id'=>$customer_data['accountKey']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'booking_status_changed',
                    'data' => $customer_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/booking-package/index.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['booking_package'] = @$plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}