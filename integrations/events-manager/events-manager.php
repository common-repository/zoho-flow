<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow          2.9.0
 * @since events-manager    6.5.2
 */
class Zoho_Flow_Events_Manager extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array(
        "booking_added",
        "booking_status_updated",
        "booking_rsvp_status_updated"
    );
    
    /**
     * List events
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  non-mandatory.
     * @type int     $limit         Number of results. Default: 200.
     * @type string  $order_by      Sort by the field. Default: event_date_modified.
     * @type string  $order         Sord order. Values: ASC|DESC. Default: DESC.
     * @type int     $event_rsvp    Check whether the event has RSVP enabled or not. Default all events will be returned. Values: 1|0
     *
     * @return WP_REST_Response    WP_REST_Response Array of events
     */
    public function list_events( $request ){
        global $wpdb;
        $order_by_allowed = array(
            'event_id',
            'post_id',
            'event_name',
            'event_start',
            'event_end',
            'event_rsvp_date',
            'event_date_created',
            'event_date_modified'
        );
        $order_allowed = array('ASC', 'DESC');
        $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'event_date_modified';
        $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'DESC';
        $limit = ($request['limit']) ? $request['limit'] : '200';
        $query = "SELECT * FROM {$wpdb->prefix}em_events";
        if ( isset( $request['event_rsvp'] ) && !empty( $request['event_rsvp'] ) ) {
            $query .= $wpdb->prepare(" WHERE event_rsvp = %d",  $request['event_rsvp'] );
        }
        $query .= $wpdb->prepare(
            " ORDER BY $order_by $order LIMIT %d",
            $limit
            );
        
        $results = $wpdb->get_results( $query, 'ARRAY_A');
        return rest_ensure_response( $results );
    }
    
    /**
     * List tickets
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $event_id         ID of the event.
     *
     * @return WP_REST_Response    WP_REST_Response Array of tickets
     */
    public function list_tickets( $request ){
        global $wpdb;
        $event_id = $request->get_url_params()['event_id'];
        $results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}em_tickets WHERE event_id = %d ORDER BY ticket_order ASC LIMIT 500",
                        $event_id
                    ), 'ARRAY_A'
                );
        return rest_ensure_response( $results );
    }
    
    /**
     * List booking status
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * @return WP_REST_Response    WP_REST_Response arrayof statuses
     */
    public function list_booking_status( $request ){
        $booking = new EM_Booking();
        $status_array = array();
        foreach ( $booking->status_array as $id => $name ){
            $status_array[] = array(
                'id' => $id,
                'name' => $name
            );
        }
        return rest_ensure_response( $status_array );
    }
    
    /**
     * Fetch booking
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Mandatory.
     * @type int    booking_id      ID of booking.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response booking details array | WP_Error object with error details.
     */
    public function fetch_booking( $request ){
        $booking_id = $request['booking_id'];
        $booking = new EM_Booking( $booking_id );
        if( !empty( $booking->booking_id ) ){
            return rest_ensure_response( $this->get_booking_data( $booking_id ) );
        }
        return new WP_Error( 'rest_bad_request', 'Booking does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Update booking status
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Mandatory.
     * @type int    booking_id      ID of booking.
     * @type int    status          ID of status.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response booking details array | WP_Error object with error details.
     */
    public function update_booking_status( $request ){
        $booking_id = $request['booking_id'];
        $status = $request['status'];
        $booking = new EM_Booking( $booking_id );
        if( !empty( $booking->booking_id ) ){
            if( $booking->set_status( $status ) ){
                return rest_ensure_response( $this->get_booking_data( $booking_id ) );
            }
            return new WP_Error( 'rest_bad_request', 'Status not updated', array( 'status' => 400 ) );
        }
        return new WP_Error( 'rest_bad_request', 'Booking does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Add booking note
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Mandatory.
     * @type int    booking_id      ID of booking.
     * @type string note_text       Text.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response booking details array | WP_Error object with error details.
     */
    public function add_booking_note( $request ){
        $booking_id = $request['booking_id'];
        $note_text = $request['note_text'];
        $booking = new EM_Booking( $booking_id );
        if( !empty( $booking->booking_id ) ){
            if( $booking->add_note( $note_text ) ){
                return rest_ensure_response(
                    array(
                        "status" => "Success",
                        "message" => "Note added"
                    )
                    );
            }
            return new WP_Error('rest_bad_request', 'Note not added', array( 'status' => 400 ) );
        }
        return new WP_Error('rest_bad_request', 'Booking does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Check whether the booking ID is valid or not.
     *
     * @param int $booking_id  Booking ID.
     * @return array|bool  Booking details array | false for others.
     */
    private function get_booking_data( $booking_id ){
        if( isset( $booking_id ) && is_numeric( $booking_id ) ){
            $booking = em_get_booking( $booking_id );
            $booking_data = $booking->to_api();
            $booking_data['currency'] = $booking->get_currency();
            $booking_data['total_paid'] = $booking->get_total_paid();
            $booking_data['status_text'] = $booking->get_status();
            $booking_data['rsvp_status_text'] = $booking->get_rsvp_status(true);
            $booking_data['price_summary'] = $booking->get_price_summary_array();
            return $booking_data;
        }
        return false;
    }
    
    /**
     * Check whether the event ID is valid or not.
     *
     * @param int $event_id  Event ID.
     * @return bool  true if event found | false for others.
     */
    private function is_valid_event( $event_id ){
        if( isset( $event_id ) && is_numeric( $event_id ) ){
            $event = new EM_Event( $event_id );
            if( !empty( $event->event_id ) ){
                return true;
            }
            return false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Check whether the event and ticket are valid or not.
     *
     * @param int $event_id     Event ID.
     * @param int $ticket_id    Ticket ID.
     * @return bool  true if event and ticket are found | false for others.
     */
    private function is_valid_event_ticket( $event_id, $ticket_id ){
        if( isset( $event_id ) && is_numeric( $event_id ) && isset( $ticket_id ) && is_numeric( $ticket_id ) ){
            $ticket = new EM_Ticket( $ticket_id );
            if( $event_id == $ticket->event_id ){
                return true;
            }
            return false;
        }
        else{
            return false;
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
        if( !isset($entry->event_id) || !$this->is_valid_event( $entry->event_id ) ){
            return new WP_Error( 'rest_bad_request', 'Event does not exist!', array( 'status' => 404 ) );
        }
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event,
                'event_id' => $entry->event_id
            );
            if( ( 'booking_added' === $entry->event ) or ( 'booking_status_updated' === $entry->event ) ){
                if( isset( $entry->ticket_id ) && $this->is_valid_event( $entry->event_id, $entry->ticket_id ) ){
                    $args['ticket_id'] = $entry->ticket_id;
                }
            }
            $post_name = "Events Manager ";
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
     * @param EM_Booking $booking  Booking object.
     */
    public function payload_booking_added( $booking ){
        $args = array(
            'event' => 'booking_added',
            'event_id' => $booking->event_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $booking_data = $this->get_booking_data($booking->booking_id);
            $event_data = array(
                'event' => 'booking_added',
                'data' => $booking_data
            );
            foreach( $webhooks as $webhook ){
                $ticket_id = get_post_meta( $webhook->ID, 'ticket_id', true );
                if( empty( $ticket_id ) || array_key_exists( $ticket_id, $booking_data['tickets'] ) ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the booking status is updated
     *
     * @param EM_Booking    $booking        Booking object.
     * @param array         $extra_data     Status update details array.
     */
    public function payload_booking_status_updated( $booking, $extra_data ){
        $args = array(
            'event' => 'booking_status_updated',
            'event_id' => $booking->event_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $booking_data = $this->get_booking_data($booking->booking_id);
            $event_data = array(
                'event' => 'booking_status_updated',
                'data' => $booking_data
            );
            foreach( $webhooks as $webhook ){
                $ticket_id = get_post_meta( $webhook->ID, 'ticket_id', true );
                if( empty( $ticket_id ) || array_key_exists( $ticket_id, $booking_data['tickets'] ) ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the booking RSVP status is updated
     *
     * @param EM_Booking    $booking        Booking object.
     * @param int           $status         New status ID.
     * @param array         $extra_data     Status update details array.
     */
    public function payload_booking_rsvp_status_updated( $booking, $status, $extra_data ){
        $args = array(
            'event' => 'booking_rsvp_status_updated',
            'event_id' => $booking->event_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'booking_rsvp_status_updated',
                'data' => $this->get_booking_data($booking->booking_id)
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/events-manager/events-manager.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['events_manager'] = @$plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}