<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow                          2.9.0
 * @since simply-schedule-appointments      1.6.7.46
 */
class Zoho_Flow_Simply_Schedule_Appointments extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array(
        "appointment_booked",
        "appointment_pending",
        "appointment_edited",
        "appointment_rescheduled",
        "appointment_canceled",
        "appointment_abandoned",
        "appointment_customer_information_edited"
    );
    
    /**
     * List services
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $limit         Number of results. Default: 200.
     * @type string  $order_by      List order by the field. Default: id.
     * @type string  $order         List order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response    WP_REST_Response array with appointment type details
     */
    public function list_appointment_types( $request ){
        global $wpdb;
        $order_by_allowed = array(
            'id',
            'title',
            'booking_start_date',
            'booking_end_date',
            'availability_start_date',
            'availability_end_date',
            'date_created',
            'date_modified'
        );
        $order_allowed = array('ASC', 'DESC');
        $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'date_modified';
        $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'DESC';
        $limit = ($request['limit']) ? $request['limit'] : '200';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ssa_appointment_types ORDER BY $order_by $order LIMIT %d",
                $limit
            ), 'ARRAY_A'
                );
        foreach ( $results as $index => $appointment_type ){
            $results[ $index ]['payments'] = json_decode( maybe_unserialize( $appointment_type['payments'], true ) );
            $results[ $index ]['staff'] = json_decode( maybe_unserialize( $appointment_type['staff'], true ) );
            $results[ $index ]['google_calendars_availability'] = json_decode( maybe_unserialize( $appointment_type['google_calendars_availability'], true ) );
            $results[ $index ]['google_calendar_booking'] = json_decode( maybe_unserialize( $appointment_type['google_calendar_booking'], true ) );
            $results[ $index ]['web_meetings'] = json_decode( maybe_unserialize( $appointment_type['web_meetings'], true ) );
            $results[ $index ]['mailchimp'] = json_decode( maybe_unserialize( $appointment_type['mailchimp'], true ) );
            unset($results[ $index ]['availability'],
                $results[ $index ]['customer_information'],
                $results[ $index ]['custom_customer_information'],
                $results[ $index ]['notifications'],
                $results[ $index ]['resources_settings'],
                $results[ $index ]['booking_flow_settings'],
                );
        }
        return rest_ensure_response( $results );
    }
    
    /**
     * Fetch appointment type
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    $appointment_type_id   ID of the appointment type.
     *
     * @return WP_REST_Response    WP_REST_Response Array of appointment type details
     */
    public function fetch_appointment_type( $request ){
        global $wpdb;
        $appointment_type_id = $request->get_url_params()['appointment_type_id'];
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ssa_appointment_types WHERE id = %d ORDER BY date_modified DESC LIMIT 1",
                $appointment_type_id
            ), 'ARRAY_A'
                );
        if( !empty( $results ) ){
            $results = $results[0];
            $results['availability'] = json_decode( maybe_unserialize( $results['availability'], true ) );
            $results['customer_information'] = json_decode( maybe_unserialize( $results['customer_information'], true ) );
            $results['custom_customer_information'] = json_decode( maybe_unserialize( $results['custom_customer_information'], true ) );
            $results['notifications'] = json_decode( maybe_unserialize( $results['notifications'], true ) );
            $results['payments'] = json_decode( maybe_unserialize( $results['payments'], true ) );
            $results['staff'] = json_decode( maybe_unserialize( $results['staff'], true ) );
            $results['google_calendars_availability'] = json_decode( maybe_unserialize( $results['google_calendars_availability'], true ) );
            $results['google_calendar_booking'] = json_decode( maybe_unserialize( $results['google_calendar_booking'], true ) );
            $results['web_meetings'] = json_decode( maybe_unserialize( $results['web_meetings'], true ) );
            $results['mailchimp'] = json_decode( maybe_unserialize( $results['mailchimp'], true ) );
            $results['resources_settings'] = json_decode( maybe_unserialize( $results['resources_settings'], true ) );
            $results['booking_flow_settings'] = json_decode( maybe_unserialize( $results['booking_flow_settings'], true ) );
            return rest_ensure_response( $results );
        }
        return new WP_Error( 'rest_bad_request', 'Appointment type does not exist!', array( 'status' => 404 ) );
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
        if( $this->is_valid_appointment( $appointment_id ) ){
            $appointment = $this->get_appointment_data( $appointment_id );
            if( $appointment ){
                return rest_ensure_response( $this->get_appointment_data( $appointment_id ) );
            }
        }
        return new WP_Error( 'rest_bad_request', 'Appointment does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Check whether the attendee ID is valid or not.
     *
     * @param int $attendee_id  Attendee ID.
     * @return array|bool  Attendee array if the attendee exists | false for others.
     */
    private function get_appointment_data( $appointment_id ){
        if( $this->is_valid_appointment( $appointment_id ) ){
            $appointment_obj = new SSA_Appointment_Object( $appointment_id );
            $appointment = $appointment_obj->get_data();
            $appointment['staff_ids'] = $appointment_obj->get_staff_members();
            $appointment['public_edit_url'] = $appointment_obj->get_public_edit_url();
            $appointment['admin_edit_url'] = $appointment_obj->get_admin_edit_url();
            return $appointment;
        }
        return false;
    }
    
    /**
     * Check whether the appointment type ID is valid or not.
     *
     * @param int $appointment_type_id  ID of the appointment type.
     * @return bool  true if appointment type exists | false for others.
     */
    private function is_valid_appointment_type( $appointment_type_id ){
        if( isset( $appointment_type_id ) && is_numeric( $appointment_type_id )){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}ssa_appointment_types WHERE id = %d LIMIT 1",
                    $appointment_type_id
                ), 'ARRAY_A'
                    );
            if( !empty( $results ) ){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check whether the appointment ID is valid or not.
     *
     * @param int $appointment_id  ID of the appointment.
     * @return bool  true if appointment exists | false for others.
     */
    private function is_valid_appointment( $appointment_id ){
        if( isset( $appointment_id ) && is_numeric( $appointment_id )){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}ssa_appointments WHERE id = %d LIMIT 1",
                    $appointment_id
                ), 'ARRAY_A'
                    );
            if( !empty( $results ) ){
                return true;
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
        if( !isset($entry->appointment_type_id) || !$this->is_valid_appointment_type( $entry->appointment_type_id ) ){
            return new WP_Error( 'rest_bad_request', 'Appointment type does not exist!', array( 'status' => 404 ) );
        }
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event,
                'appointment_type_id' => $entry->appointment_type_id
            );
            $post_name = "Simply Schedule Appointments ";
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
     * @param   int     $appointment_id  ID of the appointment.
     * @param   array   $data_after      Appointment array after the event.
     * @param   array   $data_before     Appointment array before the event.
     */
    public function payload_appointment_booked( $appointment_id, $data_after, $data_before, $response ){
        $appointment = $this->get_appointment_data($appointment_id);
        if( $appointment ){
            $args = array(
                'event' => 'appointment_booked',
                'appointment_type_id' => $appointment['appointment_type_id']
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
     * Fires once the pending appointment is created.
     *
     * @param   int     $appointment_id  ID of the appointment.
     * @param   array   $data_after      Appointment array after the event.
     * @param   array   $data_before     Appointment array before the event.
     */
    public function payload_appointment_pending( $appointment_id, $data_after, $data_before, $response ){
        $appointment = $this->get_appointment_data($appointment_id);
        if( $appointment ){
            $args = array(
                'event' => 'appointment_pending',
                'appointment_type_id' => $appointment['appointment_type_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'appointment_pending',
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
     * Fires once the appointment is updated
     *
     * @param   int     $appointment_id  ID of the appointment.
     * @param   array   $data_after      Appointment array after the event.
     * @param   array   $data_before     Appointment array before the event.
     */
    public function payload_appointment_edited( $appointment_id, $data_after, $data_before, $response ){
        $appointment = $this->get_appointment_data($appointment_id);
        if( $appointment ){
            $args = array(
                'event' => 'appointment_edited',
                'appointment_type_id' => $appointment['appointment_type_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'appointment_edited',
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
     * Fires once the appointment is rescheduled.
     *
     * @param   int     $appointment_id  ID of the appointment.
     * @param   array   $data_after      Appointment array after the event.
     * @param   array   $data_before     Appointment array before the event.
     */
    public function payload_appointment_rescheduled( $appointment_id, $data_after, $data_before, $response ){
        $appointment = $this->get_appointment_data($appointment_id);
        if( $appointment ){
            $args = array(
                'event' => 'appointment_rescheduled',
                'appointment_type_id' => $appointment['appointment_type_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'appointment_rescheduled',
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
     * Fires once the appointment is canceled
     *
     * @param   int     $appointment_id  ID of the appointment.
     * @param   array   $data_after      Appointment array after the event.
     * @param   array   $data_before     Appointment array before the event.
     */
    public function payload_appointment_canceled( $appointment_id, $data_after, $data_before, $response ){
        $appointment = $this->get_appointment_data($appointment_id);
        if( $appointment ){
            $args = array(
                'event' => 'appointment_canceled',
                'appointment_type_id' => $appointment['appointment_type_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'appointment_canceled',
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
     * Fires once the appointment is marked as abandoned.
     *
     * @param   int     $appointment_id  ID of the appointment.
     * @param   array   $data_after      Appointment array after the event.
     * @param   array   $data_before     Appointment array before the event.
     */
    public function payload_appointment_abandoned( $appointment_id, $data_after, $data_before, $response ){
        $appointment = $this->get_appointment_data($appointment_id);
        if( $appointment ){
            $args = array(
                'event' => 'appointment_abandoned',
                'appointment_type_id' => $appointment['appointment_type_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'appointment_abandoned',
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
     * Fires once the customer information in appointment is booked
     *
     * @param   int     $appointment_id  ID of the appointment.
     * @param   array   $data_after      Appointment array after the event.
     * @param   array   $data_before     Appointment array before the event.
     */
    public function payload_appointment_customer_information_edited( $appointment_id, $data_after, $data_before, $response ){
        $appointment = $this->get_appointment_data($appointment_id);
        if( $appointment ){
            $args = array(
                'event' => 'appointment_customer_information_edited',
                'appointment_type_id' => $appointment['appointment_type_id']
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'appointment_customer_information_edited',
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/simply-schedule-appointments/simply-schedule-appointments.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['simply_schedule_appointments'] = @$plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}