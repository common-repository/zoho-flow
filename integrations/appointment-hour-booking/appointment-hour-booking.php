<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow                  2.9.0
 * @since appointment-hour-booking  1.4.83
 */
class Zoho_Flow_Appointment_Hour_Booking extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array(
        "appointment_booked",
        "appointment_status_updated"
    );
    
    /**
     * List calendar forms
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $limit         Number of results. Default: 200.
     * @type string  $order_by      List order by the field. Default: id.
     * @type string  $order         List order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response    WP_REST_Response array with form details
     */
    public function list_calendar_forms( $request ){
        global $wpdb;
        $order_by_allowed = array(
            'id',
            'form_name'
        );
        $order_allowed = array('ASC', 'DESC');
        $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'id';
        $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'DESC';
        $limit = ($request['limit']) ? $request['limit'] : '200';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cpappbk_forms ORDER BY $order_by $order LIMIT %d",
                $limit
            ), 'ARRAY_A'
                );
        foreach ( $results as $index => $form ){
            unset($results[ $index ]['form_structure']
          );
        }
        return rest_ensure_response( $results );
    }
    
    /**
     * Fetch calendar form
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $calendar_form_id   ID of the form.
     *
     * @return WP_REST_Response    WP_REST_Response Array of form details
     */
    public function fetch_calendar_form( $request ){
        global $wpdb;
        $calendar_form_id = $request->get_url_params()['calendar_form_id'];
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cpappbk_forms WHERE id = %d LIMIT 1",
                $calendar_form_id
            ), 'ARRAY_A'
                );
        if( !empty( $results ) ){
            $results = $results[0];
            $results['form_structure'] = json_decode( maybe_unserialize( $results['form_structure'], true ) );
            return rest_ensure_response( $results );
        }
        return new WP_Error( 'rest_bad_request', 'Form does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * List status
     * 
     * @return WP_REST_Response      WP_REST_Response Array of statuses
     */
    public function list_statuses( $request ){
        $app_booking = new CP_AppBookingPlugin();
        return rest_ensure_response( $app_booking->get_status_list() );
    }
    
    /**
     * Fetch appointment
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $appointment_id   ID of the appointment.
     *
     * @return WP_REST_Response    WP_REST_Response Array of appointment details.
     */
    public function fetch_appointment( $request ){
        $appointment_id = $request->get_url_params()['appointment_id'];
        $appointment = $this->get_appointment_data( $appointment_id );
        if( $appointment ){
            return rest_ensure_response( $this->get_appointment_data( $appointment_id ) );
        }
        return new WP_Error( 'rest_bad_request', 'Appointment does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Update appointment status
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $appointment_id   ID of the appointment.
     *
     * @return WP_REST_Response    WP_REST_Response Array of appointment details.
     */
    public function update_appointment_status( $request ){
        $appointment_id = $request->get_url_params()['appointment_id'];
        $appointment_status = $request['status'];
        $appointment = $this->get_appointment_data( $appointment_id );
        if( $appointment ){
            $app_booking = new CP_AppBookingPlugin();
            if( isset( $appointment_status ) && !empty( $appointment_status ) && in_array( $appointment_status , $app_booking->get_status_list() ) ){
                $app_booking->update_status( $appointment_id, $appointment_status );
                return rest_ensure_response( $this->get_appointment_data( $appointment_id ) );
            }
            return new WP_Error( 'rest_bad_request', 'Status does not exist!', array( 'status' => 400 ) );
        }
        return new WP_Error( 'rest_bad_request', 'Appointment does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Check whether the form ID is valid or not.
     *
     * @param int $calendar_form_id  ID of the form.
     * @return bool  true if form exists | false for others.
     */
    private function is_valid_calendar_form( $calendar_form_id ){
        if( isset( $calendar_form_id ) && is_numeric( $calendar_form_id )){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}cpappbk_forms WHERE id = %d LIMIT 1",
                    $calendar_form_id
                ), 'ARRAY_A'
                    );
            if( !empty( $results ) ){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get appointment details
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $appointment_id   ID of the appointment.
     *
     * @return WP_REST_Response|bool    WP_REST_Response Array of appointment details | false if it does not exists.
     */
    private function get_appointment_data( $appointment_id ){
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cpappbk_messages WHERE id = %d LIMIT 1",
                $appointment_id
            ), 'ARRAY_A'
                );
        if( !empty( $results ) ){
            $results = $results[0];
            $results['posted_data'] = maybe_unserialize( $results['posted_data'] );
            return $results;
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
        if( !isset($entry->calendar_form_id) || !$this->is_valid_calendar_form( $entry->calendar_form_id ) ){
            return new WP_Error( 'rest_bad_request', 'Form does not exist!', array( 'status' => 404 ) );
        }
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event,
                'calendar_form_id' => $entry->calendar_form_id
            );
            $post_name = "Appointment Hour Booking ";
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
     * Fires once the appointment is booked
     *
     * @param   array   $appointment_data      Appointment details array.
     */
    public function payload_appointment_booked( $appointment_data ){
        $appointment = $this->get_appointment_data($appointment_data['itemnumber']);
        if( $appointment ){
            $args = array(
                'event' => 'appointment_booked',
                'calendar_form_id' => $appointment['formid']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array( 
                    'event' => 'appointment_booked',
                    'data' => $appointment
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the appointment status is updated
     *
     * @param   int     $appointment_id     ID of the appointment.
     * @param   string  $status             New status.
     */
    public function payload_appointment_status_updated( $appointment_id, $status ){
        $appointment = $this->get_appointment_data($appointment_id);
        if( $appointment ){
            $args = array(
                'event' => 'appointment_status_updated',
                'calendar_form_id' => $appointment['formid']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'appointment_status_updated',
                    'data' => $appointment
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/appointment-hour-booking/app-booking-plugin.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['appointment_hour_booking'] = @$plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}