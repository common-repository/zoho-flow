<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow          2.6.0
 * @since fluent-booking    1.4.3
 */
class Zoho_Flow_Fluent_Booking extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array( "booking_created", "booking_rescheduled", "booking_cancelled", "booking_rejected", "booking_completed", "booking_pending", "booking_no_show", "payment_paid" );
    
    /**
     * List calendars
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $limit        Number of results. Default: 200.
     * @type string  $order_by    Calendars list order by the field. Default: created_at.
     * @type string  $order       Calendars list order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response    WP_REST_Response array with calendar details
     */
    public function list_calendars( $request ){
        global $wpdb;
        $order_by_allowed = array('id', 'title', 'created_at', 'updated_at');
        $order_allowed = array('ASC', 'DESC');
        $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'created_at';
        $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'DESC';
        $limit = ($request['limit']) ? $request['limit'] : '200';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}fcal_calendars ORDER BY $order_by $order LIMIT %d",
                $limit
            )
            );
        return rest_ensure_response( $results );
    }
    
    /**
     * List calendar events
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $calendar_id   ID of the calendar.
     * @type int     $limit         Number of results. Default: 200.
     * @type string  $order_by      Calendars list order by the field. Default: created_at.
     * @type string  $order         Calendars list order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with calendar event details | WP_Error object with error details.
     */
    public function list_calendar_events( $request ){
        global $wpdb;
        $calendar_id = $request['calendar_id'];
        if( $this->is_valid_calendar( $calendar_id ) ){
            $order_by_allowed = array('id', 'title', 'created_at', 'updated_at');
            $order_allowed = array('ASC', 'DESC');
            $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'created_at';
            $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'DESC';
            $limit = ($request['limit']) ? $request['limit'] : '200';
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}fcal_calendar_events WHERE calendar_id = %d ORDER BY $order_by $order LIMIT %d",
                    $calendar_id,
                    $limit
                )
                );
            $events_return_list = array();
            foreach ( $results as $event ){
                $event_array = array();
                foreach ( $event as $key => $value ){
                    $event_array[ $key ] = maybe_unserialize( $value );
                }
                $events_return_list[] = $event_array;
            }
            return rest_ensure_response( $events_return_list );
        }
        return new WP_Error( 'rest_bad_request', "Calendar does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * List event fields
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $calendar_id   ID of the calendar.
     * @type int     $limit         Number of results. Default: 200.
     * @type string  $order_by      Calendars list order by the field. Default: created_at.
     * @type string  $order         Calendars list order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with calendar event details | WP_Error object with error details.
     */
    public function list_event_fields( $request ){
        global $wpdb;
        $event_id = $request['event_id'];
        if( $this->is_valid_event( $event_id ) ){
            $object_type = 'calendar_event';
            $meta_key = 'booking_fields';
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}fcal_meta WHERE object_id = %d AND object_type = %s AND `key` = %s LIMIT 1",
                    $event_id,
                    $object_type,
                    $meta_key
                )
                );
            if( !empty( $results ) ){
                return rest_ensure_response(  unserialize( $results[0]->value ) );
            }
            return rest_ensure_response( array() );
        }
        return new WP_Error( 'rest_bad_request', "Event does not exist!", array( 'status' => 404 ) );
    }
    
    public function fetch_booking( $request ){
        $event_id = $request['event_id'];
        $booking_id = $request['booking_id'];
        if( $this->is_valid_event( $event_id ) ){
            $booking = $this->is_valid_booking( $booking_id );
            if( ( $booking ) && ($event_id ===  $booking['event_id']) ){
                return rest_ensure_response( $booking );
            }
            return new WP_Error( 'rest_bad_request', "Booking does not exist!", array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', "Event does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * Check whether the Calendar ID is valid or not.
     *
     * @param   int     $calendar_id  Calendar ID.
     * @return  mixed   calendar array if the calendar exists | false for others.
     */
    private function is_valid_calendar( $calendar_id ){
        if( isset( $calendar_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}fcal_calendars WHERE id = %d ORDER BY updated_at DESC LIMIT 1",
                    $calendar_id
                )
                );
            if( !empty( $results ) ){
                return $results;
            }
            
        }
        return false;
    }
    
    /**
     * Check whether the Event ID is valid or not.
     *
     * @param   int     $event_id  Event ID.
     * @return  mixed   event array if the event exists | false for others.
     */
    private function is_valid_event( $event_id ){
        if( isset( $event_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}fcal_calendar_events WHERE id = %d ORDER BY updated_at DESC LIMIT 1",
                    $event_id
                )
                );
            if( !empty( $results ) ){
                return $results;
            }
            
        }
        return false;
    }
    
    /**
     * Check whether the Booking ID is valid or not.
     *
     * @param   int     $booking_id  Booking ID.
     * @return  mixed   booking array if the booking exists | false for others.
     */
    private function is_valid_booking( $booking_id ){
        if( isset( $booking_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}fcal_bookings WHERE id = %d ORDER BY updated_at DESC LIMIT 1",
                    $booking_id
                )
                );
            if( !empty( $results ) ){
                $booking_data_return = array();
                foreach ( $results[0] as $key => $value ){
                    $booking_data_return[ $key ] = maybe_unserialize( $value );
                }
                $meta_results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}fcal_booking_meta WHERE booking_id = %d ORDER BY created_at DESC LIMIT 200",
                        $booking_id
                    )
                    );
                if( $meta_results ){
                    $meta = array();
                    foreach ( $meta_results as $meta_row ){
                        $meta[ $meta_row->meta_key ] = maybe_unserialize( $meta_row->value );
                    }
                    $booking_data_return[ 'meta' ] = $meta;
                }
                return $booking_data_return;
            }
            
        }
        return false;
    }
    
    /**
     * Fetch the order and payment details of a booking.
     *
     * @param   int     $booking_id  Booking ID.
     * @return  mixed   order array if the order exists | false for others.
     */
    private function fetch_booking_order_payment( $booking_id ){
        if( isset( $booking_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}fcal_orders WHERE parent_id = %d ORDER BY created_at DESC LIMIT 1",
                    $booking_id
                )
                );
            if( !empty( $results ) ){
                $order_data_return = array();
                foreach ( $results[0] as $key => $value ){
                    $order_data_return[ $key ] = maybe_unserialize( $value );
                }
                $transaction_results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}fcal_transactions WHERE object_id = %d AND object_type = %s ORDER BY created_at DESC LIMIT 1",
                        $order_data_return['id'],
                        'order'
                    )
                    );
                if( $transaction_results ){
                    $transaction = array();
                    foreach ( $transaction_results[0] as $key => $value ){
                        $transaction[ $key ] = maybe_unserialize( $value );
                    }
                    $order_data_return[ 'transaction' ] = $transaction;
                }
                return $order_data_return;
            }
            
        }
        return false;
    }
    
    /**
     * Creates a webhook entry
     * The events available in $supported_events array only accepted
     *
     * @param WP_REST_Request $request WP_REST_Request onject.
     *
     * @return array|WP_Error Array with Webhook ID | WP_Error object with error details.
     */
    public function create_webhook( $request ){
        $entry = json_decode( $request->get_body() );
        if( ( !isset( $entry->event_id ) ) || !$this->is_valid_event( $entry->event_id ) ){
            return new WP_Error( 'rest_bad_request', "Event does not exist!", array( 'status' => 400 ) );
        }
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event,
                'event_id' => $entry->event_id
            );
            $post_name = "Fluent Booking ";
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
     * @param WP_REST_Request $request WP_REST_Request onject.
     *
     * @return array|WP_Error Array with success message | WP_Error object with error details.
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
     * Fires after booking is created.
     *
     * @param object   $booking        FluentBooking\App\Models\Booking object.
     * @param object   $calendarslot   FluentBooking\App\Models\CalendarSlot object
     */
    public function payload_booking_created( $booking, $calendarslot){
        $args = array(
            'event' => 'booking_created',
            'event_id' => $booking->__get( 'event_id' )
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'booking_created',
                'data' => array(
                    'booking' => $this->is_valid_booking( $booking->__get( 'id' ) )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after booking is cancelled.
     *
     * @param object   $booking        FluentBooking\App\Models\Booking object.
     * @param object   $calendarslot   FluentBooking\App\Models\CalendarSlot object
     */
    public function payload_booking_cancelled( $booking, $calendarslot){
        $args = array(
            'event' => 'booking_cancelled',
            'event_id' => $booking->__get( 'event_id' )
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'booking_cancelled',
                'data' => array(
                    'booking' => $this->is_valid_booking( $booking->__get( 'id' ) )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after booking is rejected.
     *
     * @param object   $booking        FluentBooking\App\Models\Booking object.
     * @param object   $calendarslot   FluentBooking\App\Models\CalendarSlot object
     */
    public function payload_booking_rejected( $booking, $calendarslot){
        $args = array(
            'event' => 'booking_rejected',
            'event_id' => $booking->__get( 'event_id' )
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'booking_rejected',
                'data' => array(
                    'booking' => $this->is_valid_booking( $booking->__get( 'id' ) )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after booking is rescheduled.
     *
     * @param object   $booking        FluentBooking\App\Models\Booking object.
     * @param object   $calendarslot   FluentBooking\App\Models\CalendarSlot object
     */
    public function payload_booking_rescheduled( $booking, $calendarslot){
        $args = array(
            'event' => 'booking_rescheduled',
            'event_id' => $booking->__get( 'event_id' )
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'booking_rescheduled',
                'data' => array(
                    'booking' => $this->is_valid_booking( $booking->__get( 'id' ) )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after booking is rejected.
     *
     * @param object   $booking        FluentBooking\App\Models\Booking object.
     * @param object   $calendarslot   FluentBooking\App\Models\CalendarSlot object
     */
    public function payload_booking_completed( $booking, $calendarslot){
        $args = array(
            'event' => 'booking_completed',
            'event_id' => $booking->__get( 'event_id' )
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'booking_completed',
                'data' => array(
                    'booking' => $this->is_valid_booking( $booking->__get( 'id' ) )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after pending booking is created
     *
     * @param object   $booking        FluentBooking\App\Models\Booking object.
     * @param object   $calendarslot   FluentBooking\App\Models\CalendarSlot object
     */
    public function payload_booking_pending( $booking, $calendarslot){
        $args = array(
            'event' => 'booking_pending',
            'event_id' => $booking->__get( 'event_id' )
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'booking_pending',
                'data' => array(
                    'booking' => $this->is_valid_booking( $booking->__get( 'id' ) )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after booking is marker as no show.
     *
     * @param object   $booking        FluentBooking\App\Models\Booking object.
     * @param object   $calendarslot   FluentBooking\App\Models\CalendarSlot object
     */
    public function payload_booking_no_show( $booking, $calendarslot){
        $args = array(
            'event' => 'booking_no_show',
            'event_id' => $booking->__get( 'event_id' )
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'booking_no_show',
                'data' => array(
                    'booking' => $this->is_valid_booking( $booking->__get( 'id' ) )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after booking is paid.
     *
     * @param object   $booking        FluentBooking\App\Models\Booking object.
     */
    public function payload_payment_paid( $booking ){
        $args = array(
            'event' => 'payment_paid',
            'event_id' => $booking->__get( 'event_id' )
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'payment_paid',
                'data' => array(
                    'booking' => $this->is_valid_booking( $booking->__get( 'id' ) ),
                    'order' =>$this->fetch_booking_order_payment( $booking->__get( 'id' ) )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
        
    }
    
    /**
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/fluent-booking-pro/fluent-booking-pro.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['fluent_booking'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    } 
}