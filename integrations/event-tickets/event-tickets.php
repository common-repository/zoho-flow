<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow          2.9.0
 * @since event-tickets     5.13.2
 */
class Zoho_Flow_Event_Tickets extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array( 
        "rsvp_attendee_added", 
        "tc_attendee_added", 
        "attendee_added",
        "tc_order_created",
        "rsvp_checkin",
        "tc_checkin",
        "attendee_checkin",
        "attendee_uncheckin"
    );
    
    /**
     * List RSVP tickets
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     *  request params  Optional. Arguments for querying tickets.
     *  @type int     limit        Number of tickets to query. Default: 200.
     *  @type string  $order_by    Course list order by the field. Default: date.
     *  @type string  $order       Course list order Values: ASC|DESC. Default: DESC.
     *   
     *  @return WP_REST_Response|WP_Error    WP_REST_Response array of RSVP tickets | WP_Error object with error details.
     */
    public function list_rsvp_tickets( $request ){
        $allowed_orderby = array(
            "date",
            "ID",
            "post_title",
            "post_date",
            "post_date_gmt",
            "post_modified",
            "post_modified_gmt"
        );
        $allowed_order = array(
            "ASC",
            "DESC"
        );
        $args = array(
            'post_type' => 'tribe_rsvp_tickets',
            'numberposts' => isset($request['limit']) ? $request['limit'] : '200',
            'orderby' => ( isset( $request['order_by'] ) && ( in_array( $request['order_by'], $allowed_orderby ) ) ) ? $request['order_by'] : 'date',
            'order' => ( isset( $request['order'] ) && ( in_array( $request['order'], $allowed_order ) ) ) ? $request['order'] : 'DESC',
        );
        $post_list = get_posts( $args );
        return rest_ensure_response( $post_list );
    }
    
    /**
     * List commerce tickets
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     *  request params  Optional. Arguments for querying tickets.
     *  @type int     limit        Number of tickets to query. Default: 200.
     *  @type string  $order_by    Course list order by the field. Default: date.
     *  @type string  $order       Course list order Values: ASC|DESC. Default: DESC.
     *
     *  @return WP_REST_Response|WP_Error    WP_REST_Response array of commerce tickets | WP_Error object with error details.
     */
    public function list_commerce_tickets( $request ){
        $allowed_orderby = array(
            "date",
            "ID",
            "post_title",
            "post_date",
            "post_date_gmt",
            "post_modified",
            "post_modified_gmt"
        );
        $allowed_order = array(
            "ASC",
            "DESC"
        );
        $args = array(
            'post_type' => 'tec_tc_ticket',
            'numberposts' => isset($request['limit']) ? $request['limit'] : '200',
            'orderby' => ( isset( $request['order_by'] ) && ( in_array( $request['order_by'], $allowed_orderby ) ) ) ? $request['order_by'] : 'date',
            'order' => ( isset( $request['order'] ) && ( in_array( $request['order'], $allowed_order ) ) ) ? $request['order'] : 'DESC',
        );
        $post_list = get_posts( $args );
        return rest_ensure_response( $post_list );
    }
    
    /**
     * Fetch attendee by ID
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * 
     * request params  Mandatory.
     * @type int    attendee_id     Attendee ID.
     * 
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of attendee details | WP_Error object with error details.
     */
    public function fetch_attendee( $request ){
        $attendee_id = $request->get_url_params()['attendee_id'];
        $attendee_data = $this->get_attendee_data( $attendee_id );
        if( $attendee_data ){
            return rest_ensure_response( $attendee_data );
        }
        return new WP_Error( 'rest_bad_request', 'Attendee does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Fetch ticket commerce order by ID
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Mandatory.
     * @type int    order_id      Ticket commerce order ID.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of order details | WP_Error object with error details.
     */
    public function fetch_order( $request ){
        $order_id = $request->get_url_params()['order_id'];
        $order_data = tec_tc_get_order( $order_id );
        if( $order_data ){
            return rest_ensure_response( $order_data );
        }
        return new WP_Error( 'rest_bad_request', 'Order does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Check whether the attendee ID is valid or not.
     *
     * @param int $attendee_id  Attendee ID.
     * @return array|bool  Attendee array if the attendee exists | false for others.
     */
    private function get_attendee_data( $attendee_id ){
        if( isset( $attendee_id ) ){
            $data_api_obj = new Tribe__Tickets__Data_API();
            $attendees = $data_api_obj->get_attendees_by_id( $attendee_id );
            if(!empty($attendees)){
                return $attendees[0];
            }
        }
        return false;
    }
    
    /**
     * Check whether the RSVP ticket ID is valid or not.
     *
     * @param int $ticket_id  RSVP ticket ID.
     * @return array|bool  Ticket array if the RSVP ticket exists | false for others.
     */
    private function is_valid_rsvp_ticket( $ticket_id ){
        if( isset( $ticket_id ) ){
            if( "tribe_rsvp_tickets" === get_post_type( $ticket_id ) ){
                $post = get_post( $ticket_id, 'ARRAY_A' );
                unset( $post["post_password"] );
                return $post;
            }
            return false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Check whether the ticket commerce ticket ID is valid or not.
     *
     * @param int $ticket_id  TC ticket ID.
     * @return array|bool  Ticket array if the TC ticket exists | false for others.
     */
    private function is_valid_tc_ticket( $ticket_id ){
        if( isset( $ticket_id ) ){
            if( "tec_tc_ticket" === get_post_type( $ticket_id ) ){
                $post = get_post( $ticket_id, 'ARRAY_A' );
                unset( $post["post_password"] );
                return $post;
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
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event
            );
            if( ( 'rsvp_attendee_added' === $entry->event ) or ( 'rsvp_checkin' === $entry->event ) ){
                if( !isset( $entry->product_id ) || !$this->is_valid_rsvp_ticket( $entry->product_id ) ){
                    return new WP_Error( 'rest_bad_request', 'RSVP ticket does not exist!', array( 'status' => 404 ) );
                }
                $args['product_id'] = $entry->product_id;
            }
            else if( ( 'tc_attendee_added' === $entry->event ) or ( 'tc_checkin' === $entry->event ) ){
                if( !isset( $entry->product_id ) || !$this->is_valid_tc_ticket( $entry->product_id ) ){
                    return new WP_Error( 'rest_bad_request', 'Ticket does not exist!', array( 'status' => 404 ) );
                }
                $args['product_id'] = $entry->product_id;
            }
            $post_name = "Event Tickets ";
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
     * Fires once the RSVP attendee is added
     *
     * @param int $attendee_id  Attendee ID
     * @param int $post_id      Event ID
     * @param int $order_id     Order ID
     * @param int $product_id   Product ID
     */
    public function payload_rsvp_attendee_added( $attendee_id, $post_id, $order_id, $product_id ){
        $args = array(
            'event' => 'rsvp_attendee_added',
            'product_id' => $product_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $attendee_data = $this->get_attendee_data( $attendee_id );
            if( $attendee_data ){
                $event_data = array(
                    'event' => 'rsvp_attendee_added',
                    'data' => $attendee_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the RSVP attendee is added
     *
     * @param int $attendee_id  Attendee ID
     * @param int $post_id      Event ID
     * @param int $order_id     Order ID
     * @param int $product_id   Product ID
     */
    public function payload_attendee_added_rsvp( $attendee_id, $post_id, $order_id, $product_id ){
        $args = array(
            'event' => 'attendee_added'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $attendee_data = $this->get_attendee_data( $attendee_id );
            if( $attendee_data ){
                $event_data = array(
                    'event' => 'attendee_added',
                    'data' => $attendee_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the TC attendee is added
     *
     * @param WP_Post                       $attendee       Attendee post object
     * @param WP_Post                       $order          Order post object
     * @param Tribe__Tickets__Ticket_Object $ticket         Ticket object
     * @param array                         $attendee_args  Attendee extra arguments
     */
    public function payload_tc_attendee_added( $attendee, $order, $ticket, $attendee_args ){
        $args = array(
            'event' => 'tc_attendee_added',
            'product_id' => $ticket->ID
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $attendee_data = $this->get_attendee_data( $attendee->ID );
            if( $attendee_data ){
                $event_data = array(
                    'event' => 'tc_attendee_added',
                    'data' => array_merge( $attendee_data, $attendee_args )
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the TC attendee is added
     *
     * @param WP_Post                       $attendee       Attendee post object
     * @param WP_Post                       $order          Order post object
     * @param Tribe__Tickets__Ticket_Object $ticket         Ticket object
     * @param array                         $attendee_args  Attendee extra arguments
     */
    public function payload_attendee_added_tc( $attendee, $order, $ticket, $attendee_args ){
        $args = array(
            'event' => 'attendee_added'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $attendee_data = $this->get_attendee_data( $attendee->ID );
            if( $attendee_data ){
                $event_data = array(
                    'event' => 'attendee_added',
                    'data' => array_merge( $attendee_data, $attendee_args )
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the order is completed
     *
     * @param TEC\Tickets\Commerce\Status\Completed $new_status     Status object
     * @param TEC\Tickets\Commerce\Status\Pending   $old_status     Status object
     * @param WP_Post                               $post           TC Order object
     */
    public function payload_tc_order_completed( $new_status, $old_status, $post ){
        $args = array(
            'event' => 'tc_order_created'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $order_data = tec_tc_get_order( $post->ID );
            if( $order_data ){
                $event_data = array(
                    'event' => 'tc_order_created',
                    'data' => $order_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the RSVP attendee is checked in
     *
     * @param int       $attendee_id The post ID of the attendee that's being checked-in.
     * @param bool|null $qr          Whether the check-in is from a QR code.
     */
    public function payload_rsvp_checkin( $attendee_id, $qr ){
        $attendee_data = $this->get_attendee_data( $attendee_id );
        $args = array(
            'event' => 'rsvp_checkin',
            'product_id' => $attendee_data['product_id']
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            if( $attendee_data ){
                $event_data = array(
                    'event' => 'rsvp_checkin',
                    'data' => $attendee_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the TC attendee is checked in
     *
     * @param int       $attendee_id The post ID of the attendee that's being checked-in.
     * @param bool|null $qr          Whether the check-in is from a QR code.
     * @param int|null  $event_id    The ID of the ticket-able post the Attendee is being checked into.
     */
    public function payload_tc_checkin( $attendee_id, $qr, $event_id ){
        $attendee_data = $this->get_attendee_data( $attendee_id );
        $args = array(
            'event' => 'tc_checkin',
            'product_id' => $attendee_data['product_id']
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            if( $attendee_data ){
                $event_data = array(
                    'event' => 'tc_checkin',
                    'data' => $attendee_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the attendee is checked in
     *
     * @param int       $attendee_id The post ID of the attendee that's being checked-in.
     * @param bool|null $qr          Whether the check-in is from a QR code.
     */
    public function payload_checkin( $attendee_id, $qr ){
        $attendee_data = $this->get_attendee_data( $attendee_id );
        $args = array(
            'event' => 'attendee_checkin',
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            if( $attendee_data ){
                $event_data = array(
                    'event' => 'attendee_checkin',
                    'data' => $attendee_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the attendee is unchecked
     *
     * @param int   $attendee_id The post ID of the attendee.
     */
    public function payload_uncheckin( $attendee_id ){
        $attendee_data = $this->get_attendee_data( $attendee_id );
        $args = array(
            'event' => 'attendee_uncheckin',
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            if( $attendee_data ){
                $event_data = array(
                    'event' => 'attendee_uncheckin',
                    'data' => $attendee_data
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/event-tickets/event-tickets.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['event_tickets'] = @$plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}