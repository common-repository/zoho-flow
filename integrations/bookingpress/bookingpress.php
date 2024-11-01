<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow          2.6.0
 * @since bookingpress      1.1.6
 */
class Zoho_Flow_BookingPress extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array( "appointment_created", "appointment_updated", "appointment_status_changed", "customer_created", "customer_updated" );
    
    /**
     * List categories
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $limit        Number of results. Default: 200.
     * @type string  $order_by    Calendars list order by the field. Default: bookingpress_categorydate_created.
     * @type string  $order       Calendars list order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response    WP_REST_Response array with category details
     */
    public function list_categories( $request ){
        global $wpdb;
        $order_by_allowed = array('bookingpress_category_id', 'bookingpress_category_name', 'bookingpress_category_position', 'bookingpress_categorydate_created');
        $order_allowed = array('ASC', 'DESC');
        $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'bookingpress_categorydate_created';
        $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'DESC';
        $limit = ($request['limit']) ? $request['limit'] : '200';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}bookingpress_categories ORDER BY $order_by $order LIMIT %d",
                $limit
            )
            );
        return rest_ensure_response( $results );
    }
    
    /**
     * List services
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $limit        Number of results. Default: 200.
     * @type string  $order_by    Calendars list order by the field. Default: bookingpress_servicedate_created.
     * @type string  $order       Calendars list order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response    WP_REST_Response array with service details
     */
    public function list_services( $request ){
        global $wpdb;
        $order_by_allowed = array('bookingpress_service_id', 'bookingpress_service_name', 'bookingpress_service_position', 'bookingpress_servicedate_created');
        $order_allowed = array('ASC', 'DESC');
        $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'bookingpress_servicedate_created';
        $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'DESC';
        $limit = ($request['limit']) ? $request['limit'] : '200';
        $category_id = $request['category_id'];
        $query = "SELECT * FROM {$wpdb->prefix}bookingpress_services";
        if (!empty($category_id)) {
            $query .= $wpdb->prepare(" WHERE bookingpress_category_id = %d", $category_id);
        }
        $query .= $wpdb->prepare(
            " ORDER BY $order_by $order LIMIT %d",
            $limit
            );
        $results = $wpdb->get_results($query);
        return rest_ensure_response( $results );
    }
    
    /**
     * List form fields
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * 
     * @return WP_REST_Response    WP_REST_Response array with form field details
     */
    public function list_form_fields( $request ){
        global $wpdb;
        $order_by_allowed = array('bookingpress_form_field_id', 'bookingpress_form_field_name', 'bookingpress_field_label', 'bookingpress_field_position', 'bookingpress_created_at');
        $order_allowed = array('ASC', 'DESC');
        $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'bookingpress_field_position';
        $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'ASC';
        $limit = ($request['limit']) ? $request['limit'] : '200';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}bookingpress_form_fields ORDER BY $order_by $order LIMIT %d",
                $limit
            )
            );
        return rest_ensure_response( $results );
    }
    
    /**
     * Fetch customer details
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * 
     * Request param  Mandatory.
     * @type string  @fetch_field   Field to fetch
     * @type string  @fetch_value   Field value to fetch
     * 
     * @return WP_REST_Response|WP_Error Array of customer details | WP_Error object with error details.
     */
    public function fetch_customer( $request ){
        $fetch_field = $request['fetch_field'];
        $fetch_value = $request['fetch_value'];
        $available_fetch_fields = array ( "bookingpress_customer_id", "bookingpress_wpuser_id", "bookingpress_user_login", "bookingpress_user_name", "bookingpress_user_firstname", "bookingpress_user_lastname", "bookingpress_customer_full_name", "bookingpress_user_email", "bookingpress_user_phone" );
        if( isset( $fetch_field ) && isset( $fetch_value ) && in_array( $fetch_field, $available_fetch_fields ) ){
            global $wpdb;
            $query = "SELECT * FROM {$wpdb->prefix}bookingpress_customers";
            $query .= $wpdb->prepare(" WHERE  %s = %s ORDER BY bookingpress_user_created DESC LIMIT 100", $fetch_field, $fetch_value);
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}bookingpress_customers WHERE $fetch_field = %s ORDER BY bookingpress_user_created DESC LIMIT 100",
                    $fetch_value
                )
                );
            if( !empty( $results ) ){
                $customer_details_array = array();
                foreach ( $results as $customer_obj){
                    $customer_details = json_decode( json_encode( $customer_obj ), true );
                    $customer_meta = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}bookingpress_customers_meta WHERE bookingpress_customer_id = %d ORDER BY bookingpress_customersmeta_created_date DESC LIMIT 200",
                            $customer_details['bookingpress_customer_id']
                        )
                        );
                    $meta = array();
                    foreach ( $customer_meta as $meta_row_value ){
                        $meta[ $meta_row_value->bookingpress_customersmeta_key ] = maybe_unserialize( $meta_row_value->bookingpress_customersmeta_value );
                    }
                    $customer_details[ 'meta' ] = $meta;
                    array_push($customer_details_array, $customer_details);
                }
                return rest_ensure_response( $customer_details_array );
            }  
            return new WP_Error( 'rest_bad_request', "Customer does not exist!", array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', "The filter criteria are not well constructed", array( 'status' => 404 ) );
    }
    
    /**
     * Fetch appointment details
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request param  Mandatory.
     * @type string  @fetch_field   Field to fetch
     * @type string  @fetch_value   Field value to fetch
     *
     * @return WP_REST_Response|WP_Error Array of appointment details | WP_Error object with error details.
     */
    public function fetch_appointment( $request ){
        $fetch_field = $request['fetch_field'];
        $fetch_value = $request['fetch_value'];
        $available_fetch_fields = array ( "bookingpress_appointment_booking_id", "bookingpress_booking_id", "bookingpress_entry_id", "bookingpress_payment_id", "bookingpress_customer_id", "bookingpress_customer_name", "bookingpress_username", "bookingpress_customer_phone", "bookingpress_customer_email" );
        if( isset( $fetch_field ) && isset( $fetch_value ) && in_array( $fetch_field, $available_fetch_fields ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}bookingpress_appointment_bookings WHERE $fetch_field = %s ORDER BY bookingpress_created_at DESC LIMIT 100",
                    $fetch_value
                )
                );
            if( !empty( $results ) ){
                $appointment_details_array = array();
                foreach ( $results as $appointment_obj){
                    $appointment_details = json_decode( json_encode( $appointment_obj ), true );
                    $appointment_meta = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}bookingpress_appointment_meta WHERE bookingpress_appointment_id = %d ORDER BY bookingpress_appointment_meta_created_date DESC LIMIT 200",
                            $appointment_details['bookingpress_appointment_booking_id']
                        )
                        );
                    $meta = array();
                    foreach ( $appointment_meta as $meta_row_value ){
                        $meta[ $meta_row_value->bookingpress_appointment_meta_key ] = json_decode( $meta_row_value->bookingpress_appointment_meta_value );
                    }
                    $appointment_details[ 'meta' ] = $meta;
                    array_push($appointment_details_array, $appointment_details);
                }
                return rest_ensure_response( $appointment_details_array );
            }
            return new WP_Error( 'rest_bad_request', "Appointment does not exist!", array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', "The filter criteria are not well constructed", array( 'status' => 404 ) );
    }
    
    /**
     * Check whether the Service ID is valid or not.
     *
     * @param   int     $service_id  Service ID.
     * @return  array|boolean   service array if the service exists | false for others.
     */
    private function is_valid_service( $service_id ){
        if( isset( $service_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}bookingpress_services WHERE bookingpress_service_id = %d ORDER BY bookingpress_servicedate_created DESC LIMIT 1",
                    $service_id
                )
                );
            if( !empty( $results ) ){
                return $results;
            }
            
        }
        return false;
    }
    
    /**
     * Check whether the appointment ID is valid or not.
     *
     * @param   int     $appointment_id  Appointment ID.
     * @return  array|boolean   appointment array if the appointment exists | false for others.
     */
    private function is_valid_appointment( $appointment_id ){
        if( isset( $appointment_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}bookingpress_appointment_bookings WHERE bookingpress_appointment_booking_id = %d ORDER BY bookingpress_created_at DESC LIMIT 1",
                    $appointment_id
                )
                );
            if( !empty( $results[0] ) ){
                $appointment_details = json_decode( json_encode( $results[0] ), true );
                $appointment_meta = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}bookingpress_appointment_meta WHERE bookingpress_entry_id = %d ORDER BY bookingpress_appointment_meta_created_date DESC LIMIT 200",
                        $appointment_details['bookingpress_entry_id']
                    )
                    );
                $meta = array();
                foreach ( $appointment_meta as $meta_row_value ){
                    $meta[ $meta_row_value->bookingpress_appointment_meta_key ] = json_decode( $meta_row_value->bookingpress_appointment_meta_value );
                }
                $appointment_details[ 'meta' ] = $meta;
                return $appointment_details;
            }
            
        }
        return false;
    }
    
    /**
     * Check whether the customer ID is valid or not.
     *
     * @param   int     $customer_id  Customer ID.
     * @return  array|boolean   customer array if the customer exists | false for others.
     */
    private function is_valid_customer( $customer_id ){
        if( isset( $customer_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}bookingpress_customers WHERE bookingpress_customer_id = %d ORDER BY bookingpress_created_at DESC LIMIT 1",
                    $customer_id
                )
                );
            if( !empty( $results[0] ) ){
                $customer_details = json_decode( json_encode( $results[0] ), true );
                $customer_meta = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}bookingpress_customers_meta WHERE bookingpress_customer_id = %d ORDER BY bookingpress_customersmeta_created_date DESC LIMIT 200",
                        $customer_details['bookingpress_customer_id']
                    )
                    );
                $meta = array();
                foreach ( $customer_meta as $meta_row_value ){
                    $meta[ $meta_row_value->bookingpress_customersmeta_key ] = maybe_unserialize( $meta_row_value->bookingpress_customersmeta_value );
                }
                $customer_details[ 'meta' ] = $meta;
                return $customer_details;
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
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event
            );
            if( ( $entry->event == 'appointment_created' ) || ( $entry->event == 'appointment_updated' ) || ( $entry->event == 'appointment_status_changed' ) ){
                if( ( !isset( $entry->service_id ) ) || !$this->is_valid_service( $entry->service_id ) ){
                    return new WP_Error( 'rest_bad_request', "Service does not exist!", array( 'status' => 400 ) );
                }
                $args['service_id'] = $entry->service_id;
            }
            $post_name = "BookingPress ";
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
     * Fires after Appointment is created.
     *
     * @param object   $appointment_id  Appointment ID
     */
    public function payload_appointment_created( $appointment_id){
        $appointment = $this->is_valid_appointment( $appointment_id );
        $args = array(
            'event' => 'appointment_created',
            'service_id' => $appointment['bookingpress_service_id']
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'appointment_created',
                'data' => $appointment
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after Appointment is updated.
     *
     * @param object   $appointment_id  Appointment ID
     */
    public function payload_appointment_updated( $appointment_id){
        $appointment = $this->is_valid_appointment( $appointment_id );
        $args = array(
            'event' => 'appointment_updated',
            'service_id' => $appointment['bookingpress_service_id']
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'appointment_updated',
                'data' => $appointment
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after Appointment status is updated.
     *
     * @param object   $appointment_id  Appointment ID
     */
    public function payload_appointment_status_changed( $appointment_id){
        $appointment = $this->is_valid_appointment( $appointment_id );
        $args = array(
            'event' => 'appointment_status_changed',
            'service_id' => $appointment['bookingpress_service_id']
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'appointment_status_changed',
                'data' => $appointment
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after customer is created.
     *
     * @param int   $customer_id  Customer ID
     */
    public function payload_customer_created( $customer_id){
        $customer = $this->is_valid_customer( $customer_id );
        $args = array(
            'event' => 'customer_created'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'customer_created',
                'data' => $customer
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after customer is updated.
     *
     * @param int   $customer_id  Customer ID
     */
    public function payload_customer_updated( $customer_id){
        $customer = $this->is_valid_customer( $customer_id );
        $args = array(
            'event' => 'customer_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'customer_updated',
                'data' => $customer
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/bookingpress-appointment-booking/bookingpress-appointment-booking.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['bookingpress'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    } 
}