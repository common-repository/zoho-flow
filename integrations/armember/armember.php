<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow      2.10.0
 * @since armember      4.0.45
 */
class Zoho_Flow_ARMember extends Zoho_Flow_Service{
    
    /**
     *  @var array Webhook events supported.
     */
    public static $supported_events = array(
        "member_added_or_updated",
        "member_added",
        "member_updated",
        "form_entry_submitted",
        "transaction_added",
        "subscription_cancelled"
    );
    
    /**
     * List all form fields
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of fields | WP_Error object with error details
     *
     */
    public function get_all_fields( $request ){
        if( class_exists( 'ARM_member_forms' ) ){
            $member_forms_obj = new ARM_member_forms();
            return rest_ensure_response( $member_forms_obj->arm_get_all_form_fields() );
        }
        elseif ( class_exists( 'ARM_member_forms_lite' ) ){
            $member_forms_obj = new ARM_member_forms_lite();
            return rest_ensure_response( $member_forms_obj->arm_get_all_form_fields() );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Something went wrong!', array( 'status' => 400 ) );
        }
    }
    
    /**
     * List all forms
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of forms | WP_Error object with error details
     *
     */
    public function get_all_forms( $request ){
        if( class_exists( 'ARM_member_forms' ) ){
            $member_forms_obj = new ARM_member_forms();
            return rest_ensure_response( $member_forms_obj->arm_get_all_member_forms() );
        }
        elseif ( class_exists( 'ARM_member_forms_lite' ) ){
            $member_forms_obj = new ARM_member_forms_lite();
            return rest_ensure_response( $member_forms_obj->arm_get_all_member_forms() );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Something went wrong!', array( 'status' => 400 ) );
        }
    }
    
    /**
     * List form fields
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of fields | WP_Error object with error details
     *
     */
    public function get_form_fields( $request ){
        $form_id = $request->get_url_params()['form_id'];
        if( class_exists( 'ARM_Form' ) ){
            $member_form_obj = new ARM_Form('form_id', $form_id);
            if( $member_form_obj->exists( ) ){
                $member_form = array(
                    'fields' => $member_form_obj->fields
                );
                if( isset( $member_form_obj->settings['hidden_fields'] ) ){
                    $member_form['hidden_fields'] = $member_form_obj->settings['hidden_fields'];
                }
                return rest_ensure_response( $member_form );
            }
            return new WP_Error( 'rest_bad_request', "Form does not exist!", array( 'status' => 404 ) );
        }
        elseif ( class_exists( 'ARM_Form_Lite' ) ){
            $member_form_obj = new ARM_Form_Lite('form_id', $form_id);
            if( $member_form_obj->exists( ) ){
                $member_form = array(
                    'fields' => $member_form_obj->fields
                );
                if( isset( $member_form_obj->settings['hidden_fields'] ) ){
                    $member_form['hidden_fields'] = $member_form_obj->settings['hidden_fields'];
                }
                return rest_ensure_response( $member_form );
            }
            return new WP_Error( 'rest_bad_request', "Form does not exist!", array( 'status' => 404 ) );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Something went wrong!', array( 'status' => 400 ) );
        }
    }
    
    /**
     * List all subscription plans
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of subscription plans | WP_Error object with error details
     *
     */
    public function get_all_plans( $request ){
        if( class_exists( 'ARM_subscription_plans' ) ){
            $plan_obj = new ARM_subscription_plans();
            return rest_ensure_response( $plan_obj->arm_get_all_subscription_plans() );
        }
        elseif ( class_exists( 'ARM_subscription_plans_Lite' ) ){
            $plan_obj = new ARM_subscription_plans_Lite();
            return rest_ensure_response( $plan_obj->arm_get_all_subscription_plans() );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Something went wrong!', array( 'status' => 400 ) );
        }
    }
    
    /**
     * List recently created members.
     * 
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of members | WP_Error object with error details     
     * 
     */
    public function list_all_members( $request ){
        if( class_exists( 'ARM_members' ) ){
            $member_obj = new ARM_members();
            return rest_ensure_response( $member_obj->arm_get_all_members( 0, 0, 1 ) );
        }
        elseif ( class_exists( 'ARM_members_Lite' ) ){
            $member_obj = new ARM_members_Lite();
            return rest_ensure_response( $member_obj->arm_get_all_members( 0, 0 ) );
        }
    }
    
    /**
     * Update member status
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Responsemember details array | WP_Error object with error details
     *
     */
    public function update_member_status( $request ){
        $user_id = $request->get_url_params()['user_id'];
        $primary_status = $request['primary_status'];
        $secondary_status = $request['secondary_status'];
        $allowed_p_statuses = array( 
            "1",
            "2",
            "3"
        );
        $allowed_s_statuses = array(
            "0",
            "1",
            "2",
            "3",
            "4",
            "5",
            "6"
        );
        if( isset( $user_id) && $this->is_valid_user( $user_id ) ){
            if( isset( $primary_status ) && in_array( $primary_status, $allowed_p_statuses ) ){
                if( !isset( $secondary_status ) || !in_array( $secondary_status, $allowed_s_statuses ) ){
                    $secondary_status = 0;
                }
                arm_set_member_status( $user_id, $primary_status, $secondary_status );
                return rest_ensure_response( $this->get_member_details( $user_id ) );
            }
            else{
                return new WP_Error( 'rest_bad_request', "Invalid primary status", array( 'status' => 400 ) );
            }
        }
        return new WP_Error( 'rest_bad_request', "Member does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * Update member subscription plan
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Responsemember details array | WP_Error object with error details
     *
     */
    public function update_member_subscription_plan( $request ){
        $user_id = $request->get_url_params()['user_id'];
        $plan_id = $request->get_url_params()['plan_id'];
        if( isset( $user_id) && $this->is_valid_user( $user_id ) ){
            if( isset( $plan_id) && $this->is_valid_plan( $plan_id ) ){
                if( class_exists( 'ARM_subscription_plans' ) ){
                    $subscription_plan_obj = new ARM_subscription_plans();
                    $subscription_plan_obj->arm_update_user_subscription( $user_id, $plan_id );
                }
                elseif( class_exists( 'ARM_subscription_plans_Lite' ) ){
                    $subscription_plan_obj = new ARM_subscription_plans_Lite();
                    $subscription_plan_obj->arm_update_user_subscription( $user_id, $plan_id );
                }
                return rest_ensure_response( $this->get_member_details( $user_id ) );
            }
            return new WP_Error( 'rest_bad_request', "Plan does not exist!", array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', "Member does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * Fetch member
     *
     * @param   WP_REST_Request $request WP_REST_Request object.
     *
     * @return  WP_REST_Response|WP_Error   Array of member details | WP_Error object with valid error details
     */
    public function fetch_member( $request ){
        if( isset( $request['fetch_field'] ) && ( $request['fetch_value'] ) ){
            $allowed_fetch_fields = array(
                'id',
                'ID',
                'slug',
                'email',
                'login'
            );
            if( in_array( $request['fetch_field'] , $allowed_fetch_fields ) ){
                $user = get_user_by( $request['fetch_field'], $request['fetch_value'] );
                if( $user ){
                    $arm_member = $this->get_member_details( $user->ID );
                    if( $arm_member ){
                        return rest_ensure_response( $arm_member );
                    }
                }
                return new WP_Error( 'rest_bad_request', 'Member does not exist!', array( 'status' => 404 ) );
            }
            else{
                return new WP_Error( 'rest_bad_request', 'Invalid field reference', array( 'status' => 400 ) );
            }
        }
        return new WP_Error( 'rest_bad_request', 'Invalid input', array( 'status' => 400 ) );
    }
    
    /**
     * Get member details from ID.
     *
     * @param int $user_id  User ID.
     * @return array|boolean  Member details array if the member exists | false for others.
     */
    private function get_member_details( $user_id ){
        if( isset( $user_id ) && is_numeric( $user_id ) ){
            if( class_exists( 'ARM_members' ) ){
                $member_obj = new ARM_members();
                $user = $member_obj->arm_get_member_detail( $user_id );
                if( $user ){
                    $user_meta = $user->data->user_meta;
                    $user_data = $user->data;
                    $user_meta['id'] = $user_data->ID;
                    unset(
                        $user_data->user_pass,
                        $user_data->user_activation_key,
                        $user_data->user_meta
                        );
                    if( isset( $user_meta['arm_user_last_plan'] ) ){
                        $membership_obj = new ARM_subscription_plans();
                        $user_meta['user_membership_details'] = $membership_obj->arm_get_user_membership_detail( $user_id, $user_meta['arm_user_last_plan'] );
                    }
                    return array_merge( json_decode( json_encode( $user_data ), true), $user_meta );
                }
            }
            elseif ( class_exists( 'ARM_members_Lite' ) ){
                $member_obj = new ARM_members_Lite();
                $user = $member_obj->arm_get_member_detail( $user_id );
                if( $user ){
                    $user_meta = $user->data->user_meta;
                    $user_data = $user->data;
                    $user_meta['id'] = $user_data->ID;
                    unset(
                        $user_data->user_pass,
                        $user_data->user_activation_key,
                        $user_data->user_meta
                        );
                    if( isset( $user_meta['arm_user_last_plan'] ) ){
                        $membership_obj = new ARM_subscription_plans_Lite();
                        $user_meta['user_membership_details'] = $membership_obj->arm_get_user_membership_detail( $user_id, $user_meta['arm_user_last_plan'] );
                    }
                    return array_merge( json_decode( json_encode( $user_data ), true), $user_meta );
                }
            }
        }
        return false;
    }
    
    /**
     * Get plan details from ID.
     *
     * @param int $plan_id  Plan ID.
     * @return array|boolean  Plan details array if the plan exists | false for others.
     */
    private function get_plan_details( $plan_id ){
        if( isset( $plan_id ) && is_numeric( $plan_id ) ){
            if( class_exists( 'ARM_subscription_plans' ) ){
                $plam_obj = new ARM_subscription_plans();
                return $plam_obj->arm_get_subscription_plan( $plan_id );
            }
            elseif ( class_exists( 'ARM_subscription_plans_Lite' ) ){
                $plam_obj = new ARM_subscription_plans_Lite();
                return $plam_obj->arm_get_subscription_plan( $plan_id );
            }
        }
        return false;
    }
    
     /**
     * Check whether the user ID is valid or not.
     *
     * @param int $user_id  User ID.
     * @return boolean  true if the user exists | false for others.
     */
    private function is_valid_user( $user_id ){
        if( isset( $user_id ) && is_numeric( $user_id ) ){
            if( class_exists( 'ARM_members' ) ){
                $member_obj = new ARM_members();
                $user = $member_obj->arm_get_member_detail( $user_id );
                if( $user ){
                    return true;
                }
            }
            elseif ( class_exists( 'ARM_members_Lite' ) ){
                $member_obj = new ARM_members_Lite();
                $user = $member_obj->arm_get_member_detail( $user_id );
                if( $user ){
                    return true;
                }
            }
        }
        return false;
    }
    
     /**
     * Check whether the plan ID is valid or not.
     *
     * @param int $plan_id  Plan ID.
     * @return boolean  true if the plan exists | false for others.
     */
    private function is_valid_plan( $plan_id ){
        if( isset( $plan_id ) && is_numeric( $plan_id ) ){
            if( class_exists( 'ARM_subscription_plans' ) ){
                $plan_obj = new ARM_subscription_plans();
                if( $plan_obj->arm_get_subscription_plan( $plan_id ) ){
                    return true;
                }
            }
            elseif ( class_exists( 'ARM_subscription_plans_Lite' ) ){
                $plan_obj = new ARM_subscription_plans_Lite();
                if( $plan_obj->arm_get_subscription_plan( $plan_id ) ){
                    return true;
                }
            }
        }
        return false;
    }
    
     /**
     * Check whether the Form ID is valid or not.
     *
     * @param int $form_id  Form ID.
     * @return boolean  true if the form exists | false for others.
     */
    private function is_valid_form( $form_id ){
        if( isset( $form_id ) && is_numeric( $form_id ) ){
            if( class_exists( 'ARM_Form' ) ){
                $member_form_obj = new ARM_Form('form_id', $form_id);
                if( $member_form_obj->exists( ) ){
                    return true;
                }
            }
            elseif ( class_exists( 'ARM_Form_Lite' ) ){
                $member_form_obj = new ARM_Form_Lite('form_id', $form_id);
                if( $member_form_obj->exists( ) ){
                    return true;
                }
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
                'event' => $entry->event,
            );
            if( 'form_entry_submitted' === $entry->event ){
                if( ( isset( $entry->form_id ) ) && $this->is_valid_form( $entry->form_id ) ){
                    $args['form_id'] = $entry->form_id;
                }
                else{
                    return new WP_Error( 'rest_bad_request', "Form does not exist!", array( 'status' => 400 ) );
                }
            }
            $post_name = "ARMember ";
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
     * Fires after member is added or updated.
     *
     * @param int   $user_id            The id of the user
     * @param array $member_data        Member data
     * @param bool  $admin_save_flag 	1 for Admin updates | 0 for user updates
     */
    public function payload_member_added_or_updated( $user_id, $member_data, $admin_save_flag ){
        $args = array(
            'event' => 'member_added_or_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = $this->get_member_details( $user_id );
            if( $user_data ){
                $member_data = array_merge( $user_data, $member_data );
            }
            $member_data['admin_save_flag'] = $admin_save_flag;
            unset(
                $member_data['id'], //During member addition id sets to 0 be default.
                $member_data['user_pass'],
                $member_data['repeat_pass'],
                $member_data['arm_wp_nonce']
                );
            $event_data = array(
                'event' => 'member_added_or_updated',
                'data' => $member_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after member is added.
     *
     * @param int   $user_id            The id of the user
     * @param array $member_data        Member data
     */
    public function payload_member_added( $user_id, $member_data ){
        $args = array(
            'event' => 'member_added'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = $this->get_member_details( $user_id );
            if( $user_data ){
                $member_data = array_merge( $user_data, $member_data );
            }
            unset(
                $member_data['id'], //During member addition id sets to 0 be default.
                $member_data['user_pass'],
                $member_data['repeat_pass'],
                $member_data['arm_wp_nonce']
                );
            $event_data = array(
                'event' => 'member_added',
                'data' => $member_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after member is added or updated.
     *
     * @param int   $user_id            The id of the user
     * @param array $member_data        Member data
     */
    public function payload_member_updated( $user_id, $member_data ){
        $args = array(
            'event' => 'member_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = $this->get_member_details( $user_id );
            if( $user_data ){
                $member_data = array_merge( $user_data, $member_data );
            }
            unset(
                $member_data['user_pass'],
                $member_data['repeat_pass'],
                $member_data['arm_wp_nonce']
                );
            $event_data = array(
                'event' => 'member_updated',
                'data' => $member_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after form is submitted.
     *
     * @param ARM_Form  $arm_form         Form object.
     * @param array     $post_data        Submitted data.
     */
    public function payload_form_entry_submitted( $arm_form, $post_data ){
        $args = array(
            'event' => 'form_entry_submitted',
            'form_id' => $arm_form->ID
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $form_fields = $arm_form->fields;
            foreach ( $form_fields as $field_id => $field_details ){
                if( 'password' === $field_details['arm_form_field_option']['type'] ){
                    unset( $post_data[$field_details[ 'arm_form_field_slug' ] ] );
                }
            }
            $event_data = array(
                'event' => 'form_entry_submitted',
                'data' => $post_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after transaction is made.
     *
     * @param array $payment_data        Payment data
     */
    public function payload_transaction_added( $payment_data ){
        $args = array(
            'event' => 'transaction_added'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            if( isset( $payment_data['arm_extra_vars'] ) ){
                $payment_data['arm_extra_vars'] = maybe_unserialize( $payment_data['arm_extra_vars'] );
            }
            $event_data = array(
                'event' => 'transaction_added',
                'data' => $payment_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after membership is cancelled.
     *
     * @param int   $user_id            The id of the user.
     * @param int   $plan_id            ID of the plan cancelled.
     */
    public function payload_subscription_cancelled( $user_id, $plan_id ){
        $args = array(
            'event' => 'subscription_cancelled'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = $this->get_member_details( $user_id );
            $user_data['cancelled_plan_details'] = $this->get_plan_details( $plan_id );
            unset(
                $user_data['user_pass'],
                $user_data['repeat_pass'],
                $user_data['arm_wp_nonce']
                );
            $event_data = array(
                'event' => 'subscription_cancelled',
                'data' => $user_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Default API.
     * Get user and system info.
     *
     * @return array|WP_Error System and logged in user details | WP_Error object with error details.
     */
    public function get_system_info(){
        $system_info = parent::get_system_info();
        if( ! function_exists( 'get_plugin_data' ) ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/armember-membership/armember-membership.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['armember'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}