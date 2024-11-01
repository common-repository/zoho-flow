<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow          2.10.0
 * @since wp-user-manager   2.9.11
 */
class Zoho_Flow_WP_User_Manager extends Zoho_Flow_Service{
    
    /**
     *  @var array Webhook events supported.
     */
    public static $supported_events = array(
        "user_registered",
        "user_profile_updated"
    );
    
    /**
     * List registration forms
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * @return WP_REST_Response    WP_REST_Response Array of form details
     */
    public function list_registration_forms( $request ){
        global $wpdb;
        $results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}wpum_registration_forms ORDER BY id DESC LIMIT 500"
                    ), 'ARRAY_A'
                );
        return rest_ensure_response( $results );
    }
    
    /**
     * List all fields
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * @return WP_REST_Response    WP_REST_Response Array of form details
     */
    public function list_all_fields( $request ){
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare("
        SELECT
            f.id,
            f.group_id,
            f.field_order,
            f.type,
            f.name,
            f.description,
            fm.meta_key,
            fm.meta_value
        FROM {$wpdb->prefix}wpum_fields f
        LEFT JOIN {$wpdb->prefix}wpum_fieldmeta fm ON f.id = fm.wpum_field_id
    "),
    'ARRAY_A'
            );
        $final_results = [];
        foreach ($results as $row) {
            $field_id = $row['id'];
            
            if (!isset($final_results[$field_id])) {
                $final_results[$field_id] = [
                    'id' => $row['id'],
                    'group_id' => $row['group_id'],
                    'field_order' => $row['field_order'],
                    'type' => $row['type'],
                    'name' => $row['name'],
                    'description' => $row['description']
                ];
            }
            
            $final_results[$field_id][$row['meta_key']] = $row['meta_value'];
        }
        
        $final_results = array_values($final_results);
        
        return rest_ensure_response( $final_results );
    }
    
    /**
     * List registration form fields
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * @return WP_REST_Response    WP_REST_Response Array of form field details
     */
    public function list_registration_form_fields( $request ){
        $form_id = $request->get_url_params()['form_id'];
        if( isset( $form_id ) && $this->is_valid_form( $form_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}wpum_registration_formmeta
                    WHERE wpum_registration_form_id = %d
                    AND meta_key = %s
                    LIMIT 1",
                    $form_id, 'fields'
                        ), 'ARRAY_A'
                            );
            if( !empty( $results ) ){
                $fields = maybe_unserialize( $results[0]['meta_value']);
                
                $fields_placeholders = implode(',', array_fill(0, count($fields), '%d')); // Prepare placeholders for the IN clause
                
                $query = $wpdb->prepare(
                    "
                    SELECT *
                    FROM {$wpdb->prefix}wpum_fields
                    WHERE id IN ($fields_placeholders)
                    ",
                    ...$fields // Spread the $fields array to match the placeholders
                                );
                
                $fields_results = $wpdb->get_results($query, 'ARRAY_A');
                
                
                return rest_ensure_response( $fields_results );
            }
            return new WP_Error( 'rest_bad_request', 'Fiields not found!', array( 'status' => 404 ) );
        }
        return new WP_Error( 'rest_bad_request', 'Form does not exist!', array( 'status' => 404 ) );
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
                    "SELECT * FROM {$wpdb->prefix}wpum_registration_forms WHERE id = %d LIMIT 1",
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
            if( 'user_registered' === $entry->event ){
                if( ( isset( $entry->form_id ) ) && $this->is_valid_form( $entry->form_id ) ){
                    $args['form_id'] = $entry->form_id;
                }
                else{
                    return new WP_Error( 'rest_bad_request', "Form does not exist!", array( 'status' => 400 ) );
                }
            }
            $post_name = "WP User Manager ";
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
     * 
     * @param int               $user_id            The id of the user.
     * @param array             $values             User data to be added.
     * @param WPUM_Form_Profile $form               WPUM_Form_Profile object
     */
    public function payload_user_registered( $user_id, $values, $form ){
        $args = array(
            'event' => 'user_registered',
            'form_id' => $form->__get('id')
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $values['ID'] = $user_id;
            unset(
                $values['register']['user_password'],
                );
            $event_data = array(
                'event' => 'user_registered',
                'data' => $values
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after user profile is updated.
     *
     * @param WPUM_Form_Profile $form               WPUM_Form_Profile object
     * @param array             $values             User data to be added.
     * @param int               $user_id            The id of the user.
     */
    public function payload_user_profile_updated( $form, $values, $user_id ){
        $args = array(
            'event' => 'user_profile_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $values['ID'] = $user_id;
            unset(
                $values['register']['user_password'],
                );
            $event_data = array(
                'event' => 'user_profile_updated',
                'data' => $values
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/wp-user-manager/wp-user-manager.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['wp_user_manager'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}