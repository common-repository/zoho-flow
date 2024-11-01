<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow          2.10.0
 * @since registrationmagic 6.0.2.2
 */
class Zoho_Flow_RegistrationMagic extends Zoho_Flow_Service{
    
    /**
     *  @var array Webhook events supported.
     */
    public static $supported_events = array(
        "submission_completed",
        "newsletter_subscribed",
        "user_registered",
        "user_activated",
        "user_deactivated",
        "user_signon"
    );
    
    /**
     * List forms
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int     $limit         Number of results. Default: 200.
     * @type string  $order_by      List order by the field. Default: created_on.
     * @type string  $order         List order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response    WP_REST_Response array of form details
     */
    public function list_forms( $request ){
        global $wpdb;
        $order_by_allowed = array(
            'id',
            'title',
            'position'
        );
        $order_allowed = array('ASC', 'DESC');
        $order_by = ($request['order_by'] && (in_array($request['order_by'], $order_by_allowed))) ? $request['order_by'] : 'created_on';
        $order = ($request['order'] && (in_array($request['order'], $order_allowed))) ? $request['order'] : 'DESC';
        $limit = ($request['limit']) ? $request['limit'] : '200';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}rm_forms ORDER BY $order_by $order LIMIT %d",
                $limit
            ), 'ARRAY_A'
            );
        foreach ($results as $index => $row ){
            unset( $results[$index]['form_options'] );
            $results[$index]['form_user_role'] = maybe_unserialize( $results[$index]['form_user_role'] );
            $results[$index]['published_pages'] = maybe_unserialize( $results[$index]['published_pages'] );
        }
        return rest_ensure_response( $results );
    }

    /**
     * List form fields
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int    form_id    ID of the form.
     *
     * @return WP_REST_Response    WP_REST_Response array of field details
     */
    public function list_form_fields( $request ){
        $form_id = $request->get_url_params()['form_id'];
        if( isset( $form_id ) && $this->is_valid_form( $form_id ) ){
            global $wpdb;
            
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}rm_fields WHERE form_id=%d LIMIT 500",
                    $form_id
                ), 'ARRAY_A'
                    );
            foreach ($results as $index => $row ){
                unset( $results[$index]['field_options'] );
                $results[$index]['field_value'] = maybe_unserialize( $results[$index]['field_value'] );
            }
            return rest_ensure_response( $results );
        }
        
    }
    
    /**
     * Check the given form id is valid or not
     *
     * @param int $form_id   Form ID
     * @return  boolean true if form exists | false for others
     */
    private function is_valid_form( $form_id ){
        if( isset( $form_id ) && is_numeric( $form_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}rm_forms WHERE form_id = %d LIMIT 1",
                    $form_id
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
        
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event,
            );
            if( ( 'submission_completed' === $entry->event ) || ( 'newsletter_subscribed' === $entry->event ) ){
                if( ( isset( $entry->form_id ) ) && $this->is_valid_form( $entry->form_id ) ){
                    $args['form_id'] = $entry->form_id;
                }
                else{
                    return new WP_Error( 'rest_bad_request', "Form does not exist!", array( 'status' => 400 ) );
                }
            }
            $post_name = "RegistrationMagic ";
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
     * Fires after user is registered.
     *
     * @param int   $user_id    The id of the user.
     */
    public function payload_user_registered( $user_id ){
        $args = array(
            'event' => 'user_registered'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user = get_user_by( 'ID', $user_id );
            if( $user ){
                $user_data = $user->data;
                unset(
                    $user_data->user_pass, $user_data->user_activation_key,
                    );
                $event_data = array(
                    'event' => 'user_registered',
                    'data' => $user_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires after user is activated.
     *
     * @param int   $user_id    The id of the user.
     */
    public function payload_user_activated( $user_id ){
        $args = array(
            'event' => 'user_activated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user = get_user_by( 'ID', $user_id );
            if( $user ){
                $user_data = $user->data;
                unset(
                    $user_data->user_pass, $user_data->user_activation_key,
                    );
                $event_data = array(
                    'event' => 'user_activated',
                    'data' => $user_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires after user is deactivated.
     *
     * @param int   $user_id    The id of the user.
     */
    public function payload_user_deactivated( $user_id ){
        $args = array(
            'event' => 'user_deactivated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user = get_user_by( 'ID', $user_id );
            if( $user ){
                $user_data = $user->data;
                unset(
                    $user_data->user_pass, $user_data->user_activation_key,
                    );
                $event_data = array(
                    'event' => 'user_deactivated',
                    'data' => $user_data
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires after user sig-in.
     *
     * @param WP_User   $user   WP_User object.
     */
    public function payload_user_signon( $user ){
        $args = array(
            'event' => 'user_signon'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $user_data = $user->data;
            unset(
                $user_data->user_pass, $user_data->user_activation_key,
                );
            $event_data = array(
                'event' => 'user_signon',
                'data' => $user_data
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after form submission.
     *
     *@param int   $form_id    The id of the form.
     * @param array $submission Submission details.
     */
    public function payload_submission_completed( $form_id, $user_id, $submission ){
        $args = array(
            'event' => 'submission_completed',
            'form_id' => $form_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'submission_completed',
                'data' => array(
                    'form_id' => $form_id,
                    'user_id' => $user_id,
                    'submission' => $submission
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires user subscribed to the newsletter via selected form.
     *
     * @param int   $form_id    The id of the form.
     * @param array $submission Submission details.
     */
    public function payload_newsletter_subscribed( $form_id, $submission ){
        $args = array(
            'event' => 'newsletter_subscribed',
            'form_id' => $form_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            unset( $submission['pwd'], $submission['password_confirmation'] );
            $event_data = array(
                'event' => 'newsletter_subscribed',
                'data' => $submission
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/custom-registration-form-builder-with-submission-manager/registration_magic.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['registrationmagic'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}