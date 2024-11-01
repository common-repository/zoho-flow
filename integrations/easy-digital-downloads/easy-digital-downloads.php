<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow                  2.6.0
 * @since easy-digital-downloads    3.3.2
 */
class Zoho_Flow_Easy_Digital_Downloads extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array( "order_created", "download_purchased", "order_status_updated", "refund_created", "customer_created_updated" );
    
    /**
     * List latest 30 customers
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error Array of latest 30 rcustomer | WP_Error object with error details.
     */
    public function get_customers( $request ){
        return rest_ensure_response( edd_get_customers() );
    }
    
    /**
     * Fetch orders based on the field
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * Request param  Mandatory.
     * @type string  @fetch_field   Field to fetch
     * @type string  @fetch_value   Field value to fetch
     * 
     * @return WP_REST_Response|WP_Error Details of an order | WP_Error object with error details.
     */
    public function fetch_order( $request ){
        $fetch_field = $request['fetch_field'];
        $fetch_value = $request['fetch_value'];
        $available_fetch_fields = array ( "id", "order_number", "user_id", "customer_id", "email", "ip", "payment_key", "uuid" );
        if( isset( $fetch_field ) && isset( $fetch_value ) && in_array( $fetch_field, $available_fetch_fields ) ){
            $order_obj = edd_get_order_by( $fetch_field, $fetch_value );
            if( $order_obj ){
                $order_details = $this->get_order( $order_obj->__get('id') );
                if( $order_details ){
                    return rest_ensure_response( $order_details );
                }
            }
            return new WP_Error( 'rest_bad_request', "Order does not exist!", array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', "The filter criteria are not well constructed", array( 'status' => 404 ) );
    }
    
    /**
     * Fetch customer based on the field
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * Request param  Mandatory.
     * @type string  @fetch_field   Field to fetch
     * @type string  @fetch_value   Field value to fetch
     *
     * @return WP_REST_Response|WP_Error Details of a customer | WP_Error object with error details.
     */
    public function fetch_customer( $request ){
        $fetch_field = $request['fetch_field'];
        $fetch_value = $request['fetch_value'];
        $available_fetch_fields = array ( "id", "user_id", "email", "name", "uuid" );
        if( isset( $fetch_field ) && isset( $fetch_value ) && in_array( $fetch_field, $available_fetch_fields ) ){
            $customer_obj = edd_get_customer_by( $fetch_field, $fetch_value );
            if( $customer_obj ){
                $customer_details = $this->get_customer( $customer_obj->__get('id') );
                if( $customer_details ){
                    return rest_ensure_response( $customer_details );
                }
            }
            return new WP_Error( 'rest_bad_request', "Customer does not exist!", array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', "The filter criteria are not well constructed", array( 'status' => 404 ) );
    }
    
    /**
     * Fetch download based on the field
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * Request param  Mandatory.
     * @type string  @fetch_field   Field to fetch
     * @type string  @fetch_value   Field value to fetch
     *
     * @return WP_REST_Response|WP_Error Details of a download | WP_Error object with error details.
     */
    public function fetch_download( $request ){
        $fetch_field = $request['fetch_field'];
        $fetch_value = $request['fetch_value'];
        $available_fetch_fields = array ( "id", "sku", "slug", "name" );
        if( isset( $fetch_field ) && isset( $fetch_value ) && in_array( $fetch_field, $available_fetch_fields ) ){
            $download_obj = edd_get_download_by( $fetch_field, $fetch_value );
            if( $download_obj ){
                $download_details = json_decode( json_encode( $download_obj ), true );
                $edd_download = new EDD_Download($download_obj->ID);
                if( $edd_download ){
                    $download_details['name'] = $edd_download->get_name();
                    $download_details['price'] = $edd_download->get_price();
                    $download_details['prices'] = $edd_download->get_prices();
                    $download_details['file_download_limit'] = $edd_download->get_file_download_limit();
                    $download_details['type'] = $edd_download->get_type();
                    $download_details['is_bundled_download'] = $edd_download->is_bundled_download();
                    $download_details['sku'] = $edd_download->get_sku();
                    $download_details['sales_count'] = $edd_download->get_sales();
                    $download_details['total_earnings'] = $edd_download->get_earnings();
                    $files = $edd_download->get_files();
                    $file_array = array();
                    foreach ( $files as $index => $file ){
                        array_push( $file_array, $file );
                    }
                    $download_details['files'] = $file_array;
                    $bundle_downloads = $edd_download->get_bundled_downloads();
                    $bundle_array = array();
                    foreach ( $bundle_downloads as $index => $download_id ){
                        array_push( $bundle_array, $download_id );
                    }
                    $download_details['bundle_downloads'] = $bundle_array;
                    return rest_ensure_response( $download_details );
                }
            }
            return new WP_Error( 'rest_bad_request', "Download does not exist!", array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', "The filter criteria are not well constructed", array( 'status' => 404 ) );
    }
    
    /**
     * Add notes to a customer
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * Request param  Mandatory.
     * @type string  @customer_id       ID of the customer, the note should be added
     * @type string  @note_content      Note content
     *
     * @return WP_REST_Response|WP_Error Details of a note | WP_Error object with error details.
     */
    public function add_customer_note( $request ){
        $customer_id = $request[ 'customer_id' ];
        $note = $request[ 'note_content' ];
        $customer_obj = edd_get_customer( $customer_id );
        if( $customer_obj ){
            $add_note = $customer_obj->add_note( $note );
            if( $add_note ){
                return rest_ensure_response( array( 
                    "customer_id" => $customer_id,
                    "note_content" => $add_note
                ) );
            }
            return new WP_Error( 'rest_bad_request', "Unable to add note.", array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', "Customer does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * Add notes to a order
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * Request param  Mandatory.
     * @type string  @order_id          ID of the ordder, the note should be added
     * @type string  @note_content      Note content
     *
     * @return WP_REST_Response|WP_Error Details of a note | WP_Error object with error details.
     */
    public function add_order_note( $request ){
        $order_id = $request[ 'order_id' ];
        $note = $request[ 'note_content' ];
        $order_obj = edd_get_order( $order_id );
        if( $order_obj ){
            $add_note = edd_add_note( array(
                "object_id" => $order_id,
                "object_type" => "order",
                "content" => $note
            ));
            if( $add_note ){
                return rest_ensure_response( array(
                    "order_id" => $order_id,
                    "note_content" => $note,
                    "id" => $add_note
                ) );
            }
            return new WP_Error( 'rest_bad_request', "Unable to add note.", array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', "Order does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * Send a receipt of an order to customer
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * Request param  Mandatory.
     * @type string  @order_id          ID of the ordder
     *
     * @return WP_REST_Response|WP_Error Success message array| WP_Error object with error details.
     */
    public function send_receipt( $request ){
        $order_id = $request[ 'order_id' ];
        if( edd_get_order( $order_id ) ){
            $order_receipt = new EDD\Emails\Types\OrderReceipt( edd_get_order( $order_id ) );
            $send_status = $order_receipt->send();
            if( $send_status ){
                return rest_ensure_response( array( "status" => "Success") );
            }
            return new WP_Error( 'rest_bad_request', "Receipt could not be sent!", array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', "Order does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * Get order details
     * 
     * @param   int     $order_id   ID of the order
     * 
     * @return  array|boolean    Order details array | false if order doesnot exists
     */
    private function get_order( $order_id ){
        if( isset( $order_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}edd_orders WHERE id = %d ORDER BY date_created DESC LIMIT 1",
                    $order_id
                )
                );
            if( !empty( $results[0] ) ){
                
                $order_details = json_decode( json_encode( $results[0] ), true );
                $order_items = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}edd_order_items WHERE order_id = %d ORDER BY cart_index ASC LIMIT 500",
                        $order_id
                    )
                    );
                $items = array();
                foreach ( $order_items as $order_item ){
                    array_push( $items, $order_item );
                }
                $order_details[ 'order_items' ] = $items;
                $order_details[ 'customer' ] = new EDD_Customer( $order_details['customer_id'] );
                $order_address = $this->get_order_address( $order_id );
                if( $order_address ){
                    $order_details[ 'address' ] = $order_address;
                }
                return $order_details;
            }
            
        }
        return false;
    }
    
    /**
     * Get order address details
     *
     * @param   int     $order_id   ID of the order
     * 
     * @return  array|boolean    address details array | false if order address doesnot exists
     */
    private function get_order_address( $order_id ){
        if( isset( $order_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}edd_order_addresses WHERE order_id = %d ORDER BY date_created DESC LIMIT 1",
                    $order_id
                )
                );
            if( !empty( $results[0] ) ){
                return json_decode( json_encode( $results[0] ), true );
            }
            
        }
        return false;
    }
    
    /**
     * Get order item details
     *
     * @param   int     $order_item_uuid   UUID of the order item
     * 
     * @return  array|boolean    Order item details array | false if UUID doesnot exists
     */
    private function get_order_item( $order_item_uuid ){
        if( isset( $order_item_uuid ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}edd_order_items WHERE uuid = %d ORDER BY date_created DESC LIMIT 1",
                    $order_item_uuid
                )
                );
            if( !empty( $results[0] ) ){
                return json_decode( json_encode( $results[0] ), true );
            }
            
        }
        return false;
    }
    
    /**
     * Get customer details
     *
     * @param   int     $customer_id   ID of the customer
     *
     * @return  array|boolean    customer details array | false if id doesnot exists
     */
    private function get_customer( $customer_id ){
        if( isset( $customer_id ) ){
            $customer = new EDD_Customer( $customer_id );
            if( $customer->id ){
                $customer_details = json_decode( json_encode( $customer ), true );
                $customer_details[ 'order_ids' ] = $customer->get_order_ids();
                $customer_details[ 'emails' ] = $customer->get_emails();
                $customer_details[ 'primary_address' ] = $this->get_customer_primary_address( $customer_id );
                $customer_details[ 'addresses' ] = $this->get_customer_addresses( $customer_id );
                return $customer_details;
            }
        }
        return false;
        
    }
    
    /**
     * Get customer primary adddress details
     *
     * @param   int     $customer_id   ID of the customer
     *
     * @return  array|boolean    customer primary address details array | false if id doesnot exists
     */
    private function get_customer_primary_address( $customer_id ){
        if( isset( $customer_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}edd_customer_addresses WHERE customer_id = %d AND is_primary = 1 ORDER BY date_created ASC LIMIT 1",
                    $customer_id
                )
                );
            if( !empty( $results ) ){
                return json_decode( json_encode( $results[0] ), true );
            }
            
        }
        return array();
    }
    
    /**
     * Get customer addresses
     *
     * @param   int     $customer_id   ID of the customer
     *
     * @return  array|boolean    Array of customer addresses | false if id doesnot exists
     */
    private function get_customer_addresses( $customer_id ){
        if( isset( $customer_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}edd_customer_addresses WHERE customer_id = %d ORDER BY date_created DESC LIMIT 100",
                    $customer_id
                )
                );
            if( !empty( $results ) ){
                $addresses = array();
                foreach ( $results as $address_obj ){
                    array_push( $addresses, json_decode( json_encode( $address_obj ), true ));
                }
                return $addresses;
            }
            
        }
        return array();
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
            $post_name = "Easy Digital Downloads ";
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
     * Fires once the order is created or order is marked as completed
     * 
     * @param int          $order_id Payment ID.
     * @param EDD_Payment  $payment    EDD_Payment object containing all payment data.
     * @param EDD_Customer $customer   EDD_Customer object containing all customer data.
     */
    public function payload_order_created( $order_id, $payment, $customer ){
        $args = array(
            'event' => 'order_created'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $order_details = $this->get_order( $order_id );
            if( $order_details ){
                $event_data = array(
                    'event' => 'order_created',
                    'data' => $order_details
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the download is purchased. Triggers just before order creation
     * 
     * @param int       $item_id        ID of the Item
     * @param int       $order_id       ID of the order
     * @param string    $download_type  Download type
     * @param array     $cart_details   Cart details array
     * @param int       $cart_index     Index position of the item in the order
     */
    public function payload_download_purchased( $item_id, $order_id, $download_type, $cart_details, $cart_index ){
        $args = array(
            'event' => 'download_purchased'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $cart_details['order_id'] = $order_id;
            $cart_details['download_type'] = $download_type;
            $cart_details['cart_index'] = $cart_index;
            $event_data = array(
                'event' => 'download_purchased',
                'data' => $cart_details
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires once the status of the order updated
     * 
     * @param int       $payment_id     ID of the order
     * @param string    $new_status     New status
     * @param string    $old_status     Old status
     */
    public function payload_payment_status_changed( $payment_id, $new_status, $old_status ){
        if( 'new' !== $old_status ){
            $args = array(
                'event' => 'order_status_updated'
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $order_details = $this->get_order( $payment_id );
                if( $order_details ){
                    $order_details['old_status'] = $old_status;
                    $event_data = array(
                        'event' => 'order_status_updated',
                        'data' => $order_details
                    );
                    foreach( $webhooks as $webhook ){
                        $url = $webhook->url;
                        zoho_flow_execute_webhook( $url, $event_data, array() );
                    }
                }
            }
        }
    }
    
    /**
     * Fires once a refund order is created
     * 
     * @param int  $order_id     Order ID of the original order.
     * @param int  $refund_id    ID of the new refund object.
     * @param bool $all_refunded Whether or not the entire order was refunded.
     */
    public function payload_payment_refund( $order_id, $refund_id, $all_refunded ){
        $args = array(
            'event' => 'refund_created'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $order_details = $this->get_order( $refund_id );
            $order_details[ 'all_refunded' ] = $all_refunded;
            if( $order_details ){
                $event_data = array(
                    'event' => 'refund_created',
                    'data' => $order_details
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the customer is updated
     * 
     * @param boolean   $updated        Whether or not the customer is updated.
     * @param int       $customer_id    ID of the customer
     * @param array     $data           Array of data attributes for a customer
     */
    public function payload_customer_updated( $updated, $customer_id, $data ){
        $args = array(
            'event' => 'customer_created_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $customer_details = $this->get_customer( $customer_id );
            if( $customer_details ){
                $event_data = array(
                    'event' => 'customer_created_updated',
                    'data' => $customer_details
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the email address is added to the customer.
     * 
     * @param int   $customer_id    ID of the customer
     * @param array $data           Array of arguments: nonce, customer id, and email address
     */
    public function payload_customer_email_added( $customer_id, $data ){
        $args = array(
            'event' => 'customer_created_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $customer_details = $this->get_customer( $customer_id );
            if( $customer_details ){
                $event_data = array(
                    'event' => 'customer_created_updated',
                    'data' => $customer_details
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/easy-digital-downloads/easy-digital-downloads.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['easy_digital_downloads'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}