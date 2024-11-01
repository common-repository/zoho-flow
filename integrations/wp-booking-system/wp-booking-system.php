<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zoho-flow             2.9.0
 * @since wp-booking-system     2.0.19.10
 */
class Zoho_Flow_WP_Booking_System extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array(
        "event_added",
        "event_updated",
        "event_added_or_updated",
        "booking_added",
        "booking_updated",
        "booking_added_or_updated"
    );
    
    /**
     * List calendars
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  non-mandatory.
     * @type int     $number        Number of results. Default: -1.
     * @type int     $offset        Index of results. Default: 0.
     * @type string  $order_by      List order by the field. Default: id.
     * @type string  $order         List order Values: ASC|DESC. Default: ASC.
     * @type string  $search        Search string
     *
     * @return WP_REST_Response    WP_REST_Response array of calendar details
     */
    public function list_calendars( $request ){
        $args = array();
        if( $request['number'] ){
            $args['number'] = $request['number'];
        }
        if( $request['offset'] ){
            $args['offset'] = $request['offset'];
        }
        if( $request['orderby'] ){
            $args['orderby'] = $request['orderby'];
        }
        if( $request['order'] ){
            $args['order'] = $request['order'];
        }
        if( $request['search'] ){
            $args['search'] = $request['search'];
        }
        
        if( ! class_exists( 'WPBS_Object_DB_Calendars' ) ) {
            require_once( ABSPATH . 'wp-content/plugins/wp-booking-system/includes/base/calendar/class-object-db-calendars.php' );
        }
        $calendar_obj = new WPBS_Object_DB_Calendars();
        $calendar_list =  $calendar_obj->get_calendars( $args );
        $calendar_array = array();
        foreach ( $calendar_list as $calendar ){
            $calendar_array[] = $calendar_obj->get( $calendar->get('id') );
        }
        return rest_ensure_response( $calendar_array );
    }
    
    /**
     * List forms
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  non-mandatory.
     * @type int     $number        Number of results. Default: -1.
     * @type int     $offset        Index of results. Default: 0.
     * @type string  $order_by      List order by the field. Default: id.
     * @type string  $order         List order Values: ASC|DESC. Default: ASC.
     * @type string  $search        Search string
     *
     * @return WP_REST_Response    WP_REST_Response array of form details
     */
    public function list_forms( $request ){
        $args = array();
        if( $request['number'] ){
            $args['number'] = $request['number'];
        }
        if( $request['offset'] ){
            $args['offset'] = $request['offset'];
        }
        if( $request['orderby'] ){
            $args['orderby'] = $request['orderby'];
        }
        if( $request['order'] ){
            $args['order'] = $request['order'];
        }
        if( $request['search'] ){
            $args['search'] = $request['search'];
        }
        
        if( ! class_exists( 'WPBS_Object_DB_Forms' ) ) {
            require_once( ABSPATH . 'wp-content/plugins/wp-booking-system/includes/base/form/class-object-db-forms.php' );
        }
        $form_obj = new WPBS_Object_DB_Forms();
        $form_list =  $form_obj->get_forms( $args );
        $form_array = array();
        foreach ( $form_list as $calendar ){
            $form = $form_obj->get( $calendar->get('id') );
            unset( $form->fields );
            $form_array[] = $form;
        }
        return rest_ensure_response( $form_array );
    }
    
    /**
     * List legend items
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  non-mandatory.
     * @type int     $number        Number of results. Default: -1.
     * @type int     $offset        Index of results. Default: 0.
     * @type string  $order_by      List order by the field. Default: id.
     * @type string  $order         List order Values: ASC|DESC. Default: ASC.
     *
     * @return WP_REST_Response    WP_REST_Response array of legend item details
     */
    public function list_legend_items( $request ){
        $args = array();
        if( $request['calendar_id'] && $this->get_calendar_data( $request['calendar_id'] ) ){
            $args['calendar_id'] = $request['calendar_id'];
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Calendar does not exist!', array( 'status' => 400 ) );
        }
        if( $request['number'] ){
            $args['number'] = $request['number'];
        }
        if( $request['offset'] ){
            $args['offset'] = $request['offset'];
        }
        if( $request['orderby'] ){
            $args['orderby'] = $request['orderby'];
        }
        if( $request['order'] ){
            $args['order'] = $request['order'];
        }
        if( ! class_exists( 'WPBS_Object_DB_Legend_Items' ) ) {
            require_once( ABSPATH . 'wp-content/plugins/wp-booking-system/includes/base/legend/class-object-db-legend-items.php' );
        }
        $legend_obj = new WPBS_Object_DB_Legend_Items();
        $legend_list =  $legend_obj->get_legend_items( $args );
        $legend_array = array();
        foreach ( $legend_list as $legend ){
            $legend_array[] = $legend_obj->get( $legend->get('id') );
        }
        return rest_ensure_response( $legend_array );
    }
    
    /**
     * Fetch event
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $calendar_id   ID of the calendar.
     *
     * @return WP_REST_Response    WP_REST_Response Array of event details.
     */
    public function fetch_event( $request ){
        $calendar_id = $request['calendar_id'];
        $fetch_field = $request['fetch_field'];
        $fetch_value = $request['fetch_value'];
        $allowed_fetch_feilds = array(
            'id',
            'event_date'
        );
        if( isset( $fetch_field ) && isset( $fetch_value ) && in_array( $fetch_field, $allowed_fetch_feilds) ){
            if ( 'id' === $fetch_field ){
                $event = $this->get_event_data( $fetch_value );
                if( $event && ( $calendar_id == $event['calendar_id'] ) ){
                    return rest_ensure_response( $event );
                }
            }
            elseif ( 'event_date' === $fetch_field && strtotime( $fetch_value ) ){
                if( ! class_exists( 'WPBS_Object_DB_Events' ) ) {
                    require_once( ABSPATH . 'wp-content/plugins/wp-booking-system/includes/base/event/class-object-db-events.php' );
                }
                $event_obj = new WPBS_Object_DB_Events();
                $date = new DateTime( $fetch_value );
                $events = $event_obj->get_events( array(
                    'calendar_id' => $calendar_id,
                    'date_year' => $date->format('Y'),
                    'date_month'=> $date->format('m'),
                    'date_day' => $date->format('d')
                ) );
                if( !empty( $events ) ){
                    $event = $this->get_event_data( $events[0]->get('id') );
                    return rest_ensure_response( $event );
                }
            }
        }
        return new WP_Error( 'rest_bad_request', 'Event does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Fetch form
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $form_id   ID of the form.
     *
     * @return WP_REST_Response    WP_REST_Response Array of form details.
     */
    public function fetch_form( $request ){
        $form_id = $request->get_url_params()['form_id'];
        $form_data = $this->get_form_data( $form_id );
        if( $form_data ){
            return rest_ensure_response( $form_data );
        }
        return new WP_Error( 'rest_bad_request', 'Form does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Fetch booking
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $form_id        ID of the form.
     * @type int    $booking_id     ID of the booking.
     *
     * @return WP_REST_Response    WP_REST_Response Array of booking details.
     */
    public function fetch_booking( $request ){
        $form_id = $request->get_url_params()['form_id'];
        $booking_id = $request->get_url_params()['booking_id'];
        $booking_data = $this->get_booking_data( $booking_id );
        if( $booking_data &&  ( $form_id == $booking_data['form_id'] ) ){
            return rest_ensure_response( $booking_data );
        }
        return new WP_Error( 'rest_bad_request', 'Booking does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Add or update event based on date
     * @param   int     $calendar_id    ID of the calendar.
     * @param   string  $event_date     Date of the event. 
     * @param   int     $legend_item_id ID of the legend.
     * @param   string  $description    Description.
     * 
     * @return WP_REST_Response|WP_Error    Event details array | Error details
     */
    public function addd_or_update_event( $request ){
        $data = array();
        if( isset( $request['calendar_id'] ) && $this->get_calendar_data( $request['calendar_id'] ) ){
            $data['calendar_id'] = $request['calendar_id'];
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Calendar does not exist!', array( 'status' => 400 ) );
        }
        if( isset( $request['event_date'] ) && strtotime( $request['event_date'] ) ){
            $date = new DateTime( $request['event_date'] );
            $data['date_year'] = $date->format('Y');
            $data['date_month']= $date->format('m');
            $data['date_day'] = $date->format('d');
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Invalid date', array( 'status' => 404 ) );
        }
        if( isset( $request['legend_item_id'] ) && $this->get_legend_item_data( $request['legend_item_id'] ) ){
            $data['legend_item_id'] = $request['legend_item_id'];
        }
        if( isset( $request['description'] ) ){
            $data['description'] = $request['description'];
        }
        
        if( ! class_exists( 'WPBS_Object_DB_Events' ) ) {
            require_once( ABSPATH . 'wp-content/plugins/wp-booking-system/includes/base/event/class-object-db-events.php' );
        }
        $event_obj = new WPBS_Object_DB_Events();
        $events = $event_obj->get_events( array(
            'calendar_id' => $data['calendar_id'],
            'date_year' => $data['date_year'],
            'date_month'=> $data['date_month'],
            'date_day' => $data['date_day']
        ) );
        if( !empty( $events ) ){
            $event_id = $event_obj->update( $events[0]->get('id'), $data );
            if( $event_id ){
                return rest_ensure_response( $this->get_event_data( $events[0]->get('id') ) );
            }
        }
        else{
            $event_id = $event_obj->insert( $data );
            if( $event_id ){
                return rest_ensure_response( $this->get_event_data( $event_id ) );
            }
        }
        return new WP_Error( 'rest_bad_request', 'Event not added!', array( 'status' => 400 ) );
    }
    
    /**
     * Get calendar details
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $calendar_id   ID of the calendar.
     *
     * @return WP_REST_Response|bool    WP_REST_Response Array of calendar details | false if it does not exists.
     */
    private function get_calendar_data( $calendar_id ){
        if( isset( $calendar_id ) && is_numeric( $calendar_id ) ){
            if( ! class_exists( 'WPBS_Object_DB_Calendars' ) ) {
                require_once( ABSPATH . 'wp-content/plugins/wp-booking-system/includes/base/calendar/class-object-db-calendars.php' );
            }
            $calendar_obj = new WPBS_Object_DB_Calendars();
            $calendar = $calendar_obj->get( $calendar_id );
            if( !empty( $calendar ) ){
                return $calendar;
            }
        }
        return false;
    }
    
    /**
     * Get calendar details
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $form_id   ID of the form.
     *
     * @return WP_REST_Response|bool    WP_REST_Response Array of form details | false if it does not exists.
     */
    private function get_form_data( $form_id ){
        if( isset( $form_id ) && is_numeric( $form_id ) ){
            if( ! class_exists( 'WPBS_Object_DB_Forms' ) ) {
                require_once( ABSPATH . 'wp-content/plugins/wp-booking-system/includes/base/form/class-object-db-forms.php' );
            }
            $form_obj = new WPBS_Object_DB_Forms();
            $form = $form_obj->get( $form_id );
            if( !empty( $form ) ){
                return $form;
            }
        }
        return false;
    }
    
    /**
     * Get calendar details
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $form_id   ID of the form.
     *
     * @return WP_REST_Response|bool    WP_REST_Response Array of form details | false if it does not exists.
     */
    private function get_booking_data( $booking_id ){
        if( isset( $booking_id ) && is_numeric( $booking_id ) ){
            if( ! class_exists( 'WPBS_Object_DB_Bookings' ) ) {
                require_once( ABSPATH . 'wp-content/plugins/wp-booking-system/includes/base/booking/class-object-db-bookings.php' );
            }
            $form_obj = new WPBS_Object_DB_Bookings();
            $form = $form_obj->get( $booking_id );
            if( !empty( $form ) ){
                return  json_decode(json_encode( $form ),true );
            }
        }
        return false;
    }
    
    /**
     * Get event details
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $event_id   ID of the event.
     *
     * @return WP_REST_Response|bool    WP_REST_Response Array of event details | false if it does not exists.
     */
    private function get_event_data( $event_id ){
        if( isset( $event_id ) && is_numeric( $event_id ) ){
            if( ! class_exists( 'WPBS_Object_DB_Events' ) ) {
                require_once( ABSPATH . 'wp-content/plugins/wp-booking-system/includes/base/event/class-object-db-events.php' );
            }
            $event_obj = new WPBS_Object_DB_Events();
            $event = $event_obj->get( $event_id );
            if( !empty( $event ) ){
                $event_array = json_decode(json_encode( $event ), true);
                $date = new DateTime();
                $date->setDate($event_array['date_year'], $event_array['date_month'], $event_array['date_day']);
                $event_array['event_date'] = $date->format('Y-m-d');
                $legend_item = $this->get_legend_item_data( $event_array['legend_item_id'] );
                if( $legend_item ){
                    $event_array['legend_item'] = $legend_item;
                }
                return $event_array;
            }
        }
        return false;
    }
    
    /**
     * Get legend details
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $legend_item_id   ID of the legend.
     *
     * @return WP_REST_Response|bool    WP_REST_Response Array of legend details | false if it does not exists.
     */
    private function get_legend_item_data( $legend_item_id ){
        if( isset( $legend_item_id ) && is_numeric( $legend_item_id ) ){
            if( ! class_exists( 'WPSBC_Object_DB_Legend_Items' ) ) {
                require_once( ABSPATH . 'wp-content/plugins/wp-booking-system/includes/base/legend/class-object-db-legend-items.php' );
            }
            $legend_obj = new WPBS_Object_DB_Legend_Items();
            $legend = $legend_obj->get( $legend_item_id );
            if( !empty( $legend ) ){
                return $legend;
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
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event
            );
            if( ( 'event_added' === $entry->event ) or ( 'event_updated' === $entry->event ) or ( 'event_added_or_updated' === $entry->event ) ){
                if( isset( $entry->calendar_id ) && $this->get_calendar_data( $entry->calendar_id ) ){
                    $args['calendar_id'] = $entry->calendar_id;
                }
                else{
                    return new WP_Error( 'rest_bad_request', 'Calendar does not exist!', array( 'status' => 404 ) );
                }
            }
            elseif( ( 'booking_added' === $entry->event ) or ( 'booking_updated' === $entry->event ) or ( 'booking_added_or_updated' === $entry->event ) ){
                if( isset( $entry->form_id ) && $this->get_form_data( $entry->form_id ) ){
                    $args['form_id'] = $entry->form_id;
                }
                else{
                    return new WP_Error( 'rest_bad_request', 'Form does not exist!', array( 'status' => 404 ) );
                }
            }
            $post_name = "WP Booking System ";
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
     * Fires once the event is added.
     *
     * @param   int     $event_id   ID of the event.
     * @param   array   $data       event data.
     */
    public function payload_event_added( $event_id, $data ){
        $event = $this->get_event_data( $event_id );
        if( $event ){
            $args = array(
                'event' => 'event_added',
                'calendar_id' => $event['calendar_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'event_added',
                    'data' => $event
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the event is updated.
     *
     * @param   int     $event_id   ID of the event.
     * @param   array   $data       event data.
     */
    public function payload_event_updated( $event_id, $data ){
        $event = $this->get_event_data( $event_id );
        if( $event ){
            $args = array(
                'event' => 'event_updated',
                'calendar_id' => $event['calendar_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'event_updated',
                    'data' => $event
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the event is added or updated.
     *
     * @param   int     $event_id   ID of the event.
     * @param   array   $data       event data.
     */
    public function payload_event_added_or_updated( $event_id, $data ){
        $event = $this->get_event_data( $event_id );
        if( $event ){
            $args = array(
                'event' => 'event_added_or_updated',
                'calendar_id' => $event['calendar_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'event_added_or_updated',
                    'data' => $event
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the booking is added.
     *
     * @param   int     $booking_id ID of the Booking.
     * @param   array   $data       event data.
     */
    public function payload_booking_added( $booking_id, $data ){
        $event = $this->get_booking_data( $booking_id );
        if( $event ){
            $args = array(
                'event' => 'booking_added',
                'form_id' => $event['form_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'booking_added',
                    'data' => $event
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the booking is updated.
     *
     * @param   int     $booking_id ID of the Booking.
     * @param   array   $data       event data.
     */
    public function payload_booking_updated( $booking_id, $data ){
        $event = $this->get_booking_data( $booking_id );
        if( $event ){
            $args = array(
                'event' => 'booking_updated',
                'form_id' => $event['form_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'booking_updated',
                    'data' => $event
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the booking is added or updated.
     *
     * @param   int     $booking_id ID of the Booking.
     * @param   array   $data       event data.
     */
    public function payload_booking_added_or_updated( $booking_id, $data ){
        $event = $this->get_booking_data( $booking_id );
        if( $event ){
            $args = array(
                'event' => 'booking_added_or_updated',
                'form_id' => $event['form_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'booking_added_or_updated',
                    'data' => $event
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/wp-booking-system/wp-booking-system.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['wp_booking_system'] = @$plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}