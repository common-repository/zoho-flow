<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow  2.9.0
 * @since bookit    2.5.0
 */
class Zoho_Flow_Bookit extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array(
        "appointment_created",
        "appointment_updated",
        "appointment_status_changed",
        "customer_created_or_updated"
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
     * @return WP_REST_Response    WP_REST_Response array with service details
     */
    public function list_services( $request ){
        global $wpdb;
        $order_by_allowed = array(
            'id',
            'title',
            'category_id'
        );
        $order_allowed = array('ASC', 'DESC');
        $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'id';
        $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'DESC';
        $limit = ($request['limit']) ? $request['limit'] : '200';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT s.*, c.name AS category_name
                 FROM {$wpdb->prefix}bookit_services s
                 LEFT JOIN {$wpdb->prefix}bookit_categories c
                 ON s.category_id = c.id
                 ORDER BY $order_by $order
                 LIMIT %d",
                 $limit
                 ), 'ARRAY_A'
                );
        return rest_ensure_response( $results );
    }
    
    /**
     * Check whether the service ID is valid or not.
     *
     * @param int $service_id  ID of the service.
     * @return array|bool Service array if exists | false for others.
     */
    private function is_valid_service( $service_id ){
        if( isset( $service_id ) && is_numeric( $service_id )){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                "SELECT s.*, c.name AS category_name
                 FROM {$wpdb->prefix}bookit_services s
                 LEFT JOIN {$wpdb->prefix}bookit_categories c
                 ON s.category_id = c.id
                 WHERE s.id = %d
                 LIMIT 1",
                 $service_id
                ), 'ARRAY_A'
               );
            if( !empty( $results ) ){
                return $results[0];
            }
        }
        return false;
    }
    
    /**
     * Check whether the customer ID is valid or not.
     *
     * @param int $customer_id  ID of the customer.
     * @return array|bool Customer array if exists | false for others.
     */
    private function is_valid_customer( $customer_id ){
        if( isset( $customer_id ) && is_numeric( $customer_id )){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}bookit_customers WHERE id = %d LIMIT 1",
                    $customer_id
                ), 'ARRAY_A'
                    );
            if( !empty( $results ) ){
                return $results[0];
            }
        }
        return false;
    }
    
    /**
     * Check whether the staff ID is valid or not.
     *
     * @param int $staff_id  ID of the staff.
     * @return array|bool Staff array if exists | false for others.
     */
    private function is_valid_staff( $staff_id ){
        if( isset( $staff_id ) && is_numeric( $staff_id )){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}bookit_staff WHERE id = %d LIMIT 1",
                    $staff_id
                ), 'ARRAY_A'
                    );
            if( !empty( $results ) ){
                return $results[0];
            }
        }
        return false;
    }
    
    /**
     * Check whether the customer ID is valid or not.
     *
     * @param int $customer_id  ID of the customer.
     * @return array|bool Customer array if exists | false for others.
     */
    private function is_valid_appointment( $appointment_id ){
        if( isset( $appointment_id ) && is_numeric( $appointment_id )){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}bookit_appointments WHERE id = %d LIMIT 1",
                    $appointment_id
                ), 'ARRAY_A'
                    );
            if( !empty( $results ) ){
                $results[0]['notes'] = maybe_unserialize( $results[0]['notes'] );
                $service = $this->is_valid_service( $results[0]['service_id'] );
                if( $service ){
                    $results[0]['service'] = $service;
                }
                $staff = $this->is_valid_staff( $results[0]['staff_id'] );
                if( $staff ){
                    $results[0]['staff'] = $staff;
                }
                $customer = $this->is_valid_customer( $results[0]['customer_id'] );
                if( $customer ){
                    $results[0]['customer'] = $customer;
                }
                return $results[0];
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
            if( isset( $entry->service_id ) && ( ( 'appointment_created' === $entry->event ) || ( 'appointment_updated' === $entry->event )  || ( 'appointment_status_changed' === $entry->event ) ) ){
                if(  !$this->is_valid_service( $entry->service_id ) ){
                    return new WP_Error( 'rest_bad_request', 'Service does not exist!', array( 'status' => 404 ) );
                }
                $args['service_id'] = $entry->service_id;
            }
            $post_name = "Bookit ";
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
     * Fires once the appointment is created.
     *
     * @param int   $appointment_id    Appointment ID.
     */
    public function payload_appointment_created( $appointment_id ){
        $args = array(
            'event' => 'appointment_created',
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $appointment = $this->is_valid_appointment( $appointment_id );
            $event_data = array(
                'event' => 'appointment_created',
                'data' => $appointment
            );
            foreach( $webhooks as $webhook ){
                $service_id = get_post_meta( $webhook->ID, 'service_id', true );
                if( empty( $service_id ) || ( $service_id == $appointment['service_id'] ) ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the appointment is updated.
     *
     * @param int   $appointment_id    Appointment ID.
     */
    public function payload_appointment_updated( $appointment_id ){
        $args = array(
            'event' => 'appointment_updated',
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $appointment = $this->is_valid_appointment( $appointment_id );
            $event_data = array(
                'event' => 'appointment_updated',
                'data' => $appointment
            );
            foreach( $webhooks as $webhook ){
                $service_id = get_post_meta( $webhook->ID, 'service_id', true );
                if( empty( $service_id ) || ( $service_id == $appointment['service_id'] ) ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the appointment status is changed.
     *
     * @param int   $appointment_id    Appointment ID.
     */
    public function payload_appointment_status_changed( $appointment_id ){
        $args = array(
            'event' => 'appointment_status_changed',
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $appointment = $this->is_valid_appointment( $appointment_id );
            $event_data = array(
                'event' => 'appointment_status_changed',
                'data' => $appointment
            );
            foreach( $webhooks as $webhook ){
                $service_id = get_post_meta( $webhook->ID, 'service_id', true );
                if( empty( $service_id ) || ( $service_id == $appointment['service_id'] ) ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the customer is saved
     *
     * @param int   $customer_id Customer ID
     */
    public function payload_customer_created_or_updated( $customer_id ){
        $args = array(
            'event' => 'customer_created_or_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $customer_data = $this->is_valid_customer( $customer_id );
            if( $customer_data ){
                $event_data = array(
                    'event' => 'customer_created_or_updated',
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/bookit/bookit.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['bookit'] = @$plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}