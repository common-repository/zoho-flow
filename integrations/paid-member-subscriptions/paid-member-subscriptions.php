<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow                  2.10.0
 * @since paid-member-subscriptions 2.12.9
 */
class Zoho_Flow_Paid_Member_Subscriptions extends Zoho_Flow_Service{
    
    /**
     *  @var array Webhook events supported.
     */
    public static $supported_events = array(
        "member_subscription_added",
        "member_subscription_updated",
        "payment_added",
        "payment_updated"
    );
    
    /**
     * List member subscriptions
     * 
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of subscriptions | WP_Error object with error details     
     * 
     */
    public function list_member_subscriptions( $request ){
        $subscriptions = pms_get_member_subscriptions( array(
            'number' => 20
        ) );
        return rest_ensure_response( $subscriptions );
    }
    
    /**
     * Get user details from ID.
     *
     * @param int $user_id  User ID.
     * @return array|boolean  User details array if the user exists | false for others.
     */
    private function get_user_details( $user_id ){
        if( isset( $user_id ) && is_numeric( $user_id ) ){
            $user_obj = pms_get_member( $user_id );
            if( !empty($user_obj->username ) ){
                $user_details = $user_obj->to_array();
                $billing_obj = new PMS_Billing_Details();
                $user_details['billing'] = $billing_obj->get_billing_fields_data( $user_id );
                return $user_details;
            }
        }
        return false;
    }
    
    /**
     * Get subscription basic details from ID.
     *
     * @param int $subscription_id  Subscription ID.
     * @return array|boolean  Subscription details array if the subscription exists | false for others.
     */
    private function get_subscription_basic_details( $subscription_id ){
        if( isset( $subscription_id ) && is_numeric( $subscription_id ) ){
            $subscription_obj = pms_get_member_subscription( $subscription_id );
            if( $subscription_obj ){
                $subscription_details = $subscription_obj->to_array();
                return $subscription_details;
            }
        }
        return false;
    }
    
    /**
     * Get subscription details from ID.
     *
     * @param int $subscription_id  Subscription ID.
     * @return array|boolean  Subscription details array if the subscription exists | false for others.
     */
    private function get_subscription_details( $subscription_id ){
        if( isset( $subscription_id ) && is_numeric( $subscription_id ) ){
            $subscription_obj = pms_get_member_subscription( $subscription_id );
            if( $subscription_obj ){
                $subscription_details = $subscription_obj->to_array();
                $subscription_details['user'] = $this->get_user_details( $subscription_details['user_id'] );
                $billing_obj = new PMS_Billing_Details();
                $subscription_details['billing'] = $billing_obj->get_billing_fields_data( $subscription_details['user_id'] );
                $subscription_details['payment_method'] = pms_get_member_subscription_payment_method_details( $subscription_id );
                $payments_array = pms_get_payments_by_subscription_id( $subscription_id );
                if( is_array( $payments_array ) ){
                    foreach ( $payments_array as $payment ){
                        $subscription_details['payments'][] = $this->get_payment_basic_details($payment[ 'payment_id' ] );
                    }
                }
                if( isset( $subscription_details['subscription_plan_id'] ) ){
                    $subscription_details['subscription_plan'] = pms_get_subscription_plan( $subscription_details['subscription_plan_id'] );
                }
                return $subscription_details;
            }
        }
        return false;
    }
    
    /**
     * Get payment basic details from ID.
     *
     * @param int $payment_id  Payment ID.
     * @return array|boolean  Payment details array if the payment exists | false for others.
     */
    private function get_payment_basic_details( $payment_id ){
        if( isset( $payment_id ) && is_numeric( $payment_id ) ){
            $payment_obj = pms_get_payment( $payment_id );
            if( 0 !== $payment_obj->id ){
                $payment_details = $payment_obj->to_array();
                $payment_details['subscription_plan'] = pms_get_subscription_plan( $payment_details['subscription_id'] );
                return $payment_details;
            }
        }
        return false;
    }
    
    /**
     * Get payment details from ID.
     *
     * @param int $payment_id  Payment ID.
     * @return array|boolean  Payment details array if the payment exists | false for others.
     */
    private function get_payment_details( $payment_id ){
        if( isset( $payment_id ) && is_numeric( $payment_id ) ){
            $payment_obj = pms_get_payment( $payment_id );
            if( 0 !== $payment_obj->id ){
                $payment_details = $payment_obj->to_array();
                $payment_details['user'] = $this->get_user_details( $payment_details['user_id'] );
                $payment_details['subscription'] = $this->get_subscription_basic_details( $payment_details['member_subscription_id'] );
                $payment_details['subscription_plan'] = pms_get_subscription_plan( $payment_details['subscription_id'] );
                return $payment_details;
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
            $post_name = "Paid Membership Subscriptions ";
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
     * Fires after membership subscription is added.
     *
     * @param int 	$subscription_id     The id of the subscription that has been updated
     * @param array $data                The array of values to be updated for the subscription
     */
    public function payload_member_subscription_added( $subscription_id, $data ){
        $args = array(
            'event' => 'member_subscription_added'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'member_subscription_added',
                'data' => $this->get_subscription_details( $subscription_id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after membership subscription is updated.
     *
     * @param int 	$subscription_id     The id of the subscription that has been updated
	 * @param array $data                The array of values to be updated for the subscription
	 * @param array $old_data 	         The array of values representing the subscription before the update
     */
    public function payload_member_subscription_updated( $subscription_id, $data, $old_data ){
        $args = array(
            'event' => 'member_subscription_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'member_subscription_updated',
                'data' => $this->get_subscription_details( $subscription_id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after payment entry is added.
     *
     * @param int 	$payment_id  The id of the new payment
     * @param array $data        The provided data for the current payment
     */
    public function payload_payment_added( $payment_id, $data ){
        $args = array(
            'event' => 'payment_added'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'payment_added',
                'data' => $this->get_payment_details( $payment_id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after payment entry is updated.
     *
     * @param int 	$payment_id  The id of the payment that was updated
     * @param array $data        The provided data to be changed for the payment
     * @param array $old_data    The array of values representing the payment before the update
     */
    public function payload_payment_updated( $payment_id, $data, $old_data ){
        $args = array(
            'event' => 'payment_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'payment_updated',
                'data' => $this->get_payment_details( $payment_id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Default API
     * Get user and system info.
     *
     * @return array|WP_Error System and logged in user details | WP_Error object with error details.
     */
    public function get_system_info(){
        $system_info = parent::get_system_info();
        if( ! function_exists( 'get_plugin_data' ) ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/paid-member-subscriptions/index.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['paid_member_subscriptions'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}