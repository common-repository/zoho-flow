<?php
class Zoho_Flow_Ultimate_Member extends Zoho_Flow_Service
{
    //Deprecated in version 2.9.1
    public function get_forms( $request ) {
        $args = array('post_type' => 'um_form', 'posts_per_page' => -1);
        $forms = get_posts( $args );

        $data = array();
        if ( empty( $forms ) ) {
            return rest_ensure_response( $data );
        }
        $schema = $this->get_form_schema( $request );

        foreach ( $forms as $form ) {

            if ( isset( $schema['properties']['id'] ) ) {
                $post_data['id'] = $form->ID;
            }

            if ( isset( $schema['properties']['title'] ) ) {
                $post_data['title'] = $form->post_title;
            }
            if ( isset( $schema['properties']['created_at'] ) ) {
                $post_data['created_at'] = $form->post_date;
            }
            array_push($data, $post_data);
         }

        return rest_ensure_response( $data );
    }

    //Deprecated in version 2.9.1
    public function get_form_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'form',
            'type'                 => 'form',
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'ID of the Ultimate Member Form.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'title' => array(
                    'description'  => esc_html__( 'The title of the Ultimate Member Form.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit'),
                ),
                'created_at' => array(
                    'description' => esc_html__("Created Date of the Ultimate Member Form", "zoho-flow"),
                    'type'        => 'string',
                    'context'     => array('view'),
                    'readonly'    => true,
                ),
            ),
        );

        return $schema;
    }

    //Deprecated in version 2.9.1
    public function get_fields( $request ) {
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $form_fields = UM()->query()->get_attr( 'custom_fields', $form_id);

        if($this->check_form_exists($request['form_id'])){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $fields = array();
        foreach( $form_fields as $field ){
            $type = $field['type'];
            if($type!=='row'){
                $data = array(
                    'label' => $field['label'],
                    'metakey'=> $field['metakey'],
                    'type'=> $type,
                    'required' => $field['required'],
                );
                array_push($fields, $data);
            }
        }
        return rest_ensure_response( $fields );
    }

    //Deprecated in version 2.9.1
    public function get_webhooks($request){
        $form_id = $request['form_id'];

        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        if($this->check_form_exists($form_id)){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $args = array(
            'form_id' => $form_id
        );
        $webhooks = $this->get_webhook_posts($args);

        if ( empty( $webhooks ) ) {
            return rest_ensure_response( $webhooks );
        }

        $data = array();

        foreach ( $webhooks as $webhook ) {
            $webhook = array(
                'plugin_service' => $this->get_service_name(),
                'id' => $webhook->ID,
                'form_id' => $form_id,
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }

        return rest_ensure_response( $data );
    }

    //Deprecated in version 2.9.1
    public function create_webhook_old( $request ) {
        $form_id = $request['form_id'];
        $url = esc_url_raw($request['url']);
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $forms = UM()->query()->forms();

        if($this->check_form_exists($form_id)){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $form_title = $forms[$form_id];

        $post_id = $this->create_webhook_post($form_title, array(
            'form_id' => $form_id,
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'form_id' => $form_id,
            'url' => $url
        ) );
    }

    //Deprecated in version 2.9.1
    public function delete_webhook_old( $request ) {
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        if($this->check_form_exists($form_id)){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $webhook_id = $request['webhook_id'];
        if(!ctype_digit($webhook_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Webhook ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $result = $this->delete_webhook_post($webhook_id);
        if(is_wp_error($result)){
            return $result;
        }
        return rest_ensure_response(array(
            'plugin_service' => $this->get_service_name(),
            'id' => $result->ID
        ));
        return rest_ensure_response($result);
    }


    //Deprecated in version 2.9.1
    public function get_form_webhook_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'webhook',
            'type'                 => 'webhook',
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'Unique id of the webhook.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'form_id' => array(
                    'description'  => esc_html__( 'Unique id of the form.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'url' => array(
                    'description'  => esc_html__( 'The webhook URL.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit')
                ),
            ),
        );

        return $schema;
    }

    //Deprecated in version 2.9.1
    public function process_form_submission($user_id, $submitted)
    {
        $form_id = $submitted['form_id'];
        $data = $this->convert_field_data($form_id, $submitted);
        $args = array(
            'form_id' => $form_id
        );
        $webhooks = $this->get_webhook_posts($args);
        $files = array();
    	foreach ( $webhooks as $webhook ) {
    		$url = $webhook->url;
	        zoho_flow_execute_webhook($url, $data, $files);
    	}
    }

    //Deprecated in version 2.9.1
    public function um_user_updated($id, $args, $userinfo){

        $form_id = $args['form_id'];
        $data = $this->convert_field_data($form_id, $userinfo);

        $args = array(
            'form_id' => $form_id
        );
        $webhooks = $this->get_webhook_posts($args);
        $files = array();
        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            zoho_flow_execute_webhook($url, $data, $files);
        }
    }

    //Deprecated in version 2.9.1
    private function convert_field_data($form_id, $submitted){
        $form_fields = UM()->query()->get_attr( 'custom_fields', $form_id);

        $data = array();
        foreach ($form_fields as $formfield ) {
            $type = $formfield['type'];
            $value='';
            if( $type == "password" or $type == "row" ){
                continue;
            }
            else {
                $key = $formfield['metakey'];
                if(isset($submitted[$key])){
                    switch($type){
                        case 'checkbox':
                            $options = $submitted[$key];
                            $data[$key] = $options;
                        case 'select' :
                            if($key==='role_select'){
                                $key='role';
                                $value = $submitted[$key];
                            }else{
                                $value = $submitted[$key];
                            }
                            break;
                        case 'radio':
                            if($key==='gender'){
                                $arr = $submitted[$key];
                                $value = $arr[0];
                            }else if($key==='role_radio'){
                                $key='role';
                                $value = $submitted[$key];
                            }
                            break;
                        case 'select':
                            $values = $submitted[$key];
                            $data[$key] = $values;
                            break;
                        default:
                            $value = $submitted[$key];
                            break;
                    }
                    if($type != 'select' or $type != 'checkbox'){
                        $data[$key] = $value;
                    }
                }
            }
        }
        return $data;
    }

    //Deprecated in version 2.9.1
    private function check_form_exists($form_id){
        $forms = UM()->query()->forms();
        if(!array_key_exists($form_id, $forms)){
            return true;
        }
        return false;
    }

    //Deprecated in version 2.9.1
    private function convert_field_name($name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_]+/', ' ', $name);
        $name = trim($name);
        $name = str_replace(" ", "_", $name);
        $name = strtolower($name);

        return $name;
    }

    /**
     *  @since  2.9.1
     *  
     *  @var array Webhook events supported.
     */
    public static $supported_events = array(
        "user_registered",
        "user_status_changed",
        "member_role_changed",
        "user_profile_updated",
        "user_account_updated"
    );

    /**
     * List forms
     * 
     * @since 2.9.1
     * 
     * @param   WP_REST_Request $request WP_REST_Request object.
     * @return  WP_REST_Response|WP_Error   array of forms details | WP_Error object with valid error details
     */
    public function list_all_forms( $request ){
      $allowed_orderby = array(
            "date",
            "ID",
            "post_title",
            "post_date",
            "post_date_gmt",
            "post_modified",
            "post_modified_gmt"
        );
        $allowed_order = array(
            "ASC",
            "DESC"
        );
        $args = array(
            'post_type' => 'um_form',
            'numberposts' => isset($request['limit']) ? $request['limit'] : '200',
            'orderby' => ( isset( $request['order_by'] ) && ( in_array( $request['order_by'], $allowed_orderby ) ) ) ? $request['order_by'] : 'date',
            'order' => ( isset( $request['order'] ) && ( in_array( $request['order'], $allowed_order ) ) ) ? $request['order'] : 'DESC',
            'post_status' => array('publish'),
            'meta_query'  => array(
              array(
                'key' => '_um_mode',
				'value' => 'register',
				'compare' => '='
              )
            )
        );
        $post_list = get_posts( $args );
        return rest_ensure_response( $post_list );
    }

    /**
     * List fields (Global)
     *
     * @since 2.9.1
     *
     * @param   WP_REST_Request $request WP_REST_Request object.
     * @return  WP_REST_Response|WP_Error   array of fields details | WP_Error object with valid error details
     */
    public function list_all_fields( $request ){
      return rest_ensure_response ( UM()->builtin() );
    }
    
    /**
     * Fetch user
     *
     * @since 2.9.1
     *
     * @param   WP_REST_Request $request WP_REST_Request object.
     * 
     * @return  WP_REST_Response|WP_Error   Array of user details | WP_Error object with valid error details
     */
    public function fetch_user( $request ){
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
                   $um_user = $this->get_user( $user->ID );
                   if( $um_user ){
                       return rest_ensure_response( $um_user );
                   }
               }
               return new WP_Error( 'rest_bad_request', 'User does not exist!', array( 'status' => 404 ) );
           }
           else{
               return new WP_Error( 'rest_bad_request', 'Invalid field reference', array( 'status' => 400 ) );
           }
       }
       return new WP_Error( 'rest_bad_request', 'Invalid input', array( 'status' => 400 ) );
    }
    
    /**
     * Updates user status
     *
     * @since 2.9.1
     *
     * @param   WP_REST_Request $request WP_REST_Request object.
     *
     * @return  WP_REST_Response|WP_Error   Array of user details | WP_Error object with valid error details
     */
    public function update_user_status( $request ){
        $user_id = $request->get_url_params()['user_id'];
        $status = $request->get_url_params()['status'];
        $user_obj = UM()->user();
        if( isset( $user_id ) && $user_obj->user_exists_by_id( $user_id ) ){
            $allowed_statuses = array(
                'approved',
                'awaiting_email_confirmation',
                'awaiting_admin_review',
                'rejected',
                'inactive'
            );
            if( isset( $status ) && in_array( $status, $allowed_statuses ) ){
                $user_obj->set( $user_id );
                $user_obj->set_status( $status );
                return rest_ensure_response( $this->get_user( $user_id ) );
            }
            return new WP_Error( 'rest_bad_request', 'Invalid status', array( 'status' => 400 ) );
        }
        return new WP_Error( 'rest_bad_request', 'User does not exist!', array( 'status' => 400 ) );
    }
    
    /**
     * Get user details
     * 
     * @since 2.9.1
     * @param number $user_id   User ID
     * @return array|boolean User details array | false if user does not exists.
     */
    private function get_user( $user_id = 0 ){
      $user_obj = UM()->user();
      if( $user_obj->user_exists_by_id( $user_id ) ){
        $user_obj->set( $user_id );
        $user = $user_obj->profile;
        unset( $user['user_pass'] );
        $user['um_member_directory_data'] = isset( $user['um_member_directory_data'] ) ? maybe_unserialize(  $user['um_member_directory_data'] ) : '';
        $user['submitted'] = isset( $user['submitted'] ) ? maybe_unserialize(  $user['submitted'] ) : '';
        $user['um_account_secure_fields'] = isset( $user['um_account_secure_fields'] ) ? maybe_unserialize(  $user['um_account_secure_fields'] ) : '';
        return $user;
      }
      return false;
    }

    /**
     * Get form details
     *
     * @since 2.9.1
     * @param number $form_id   User ID
     * @return array|boolean    Form details array | false if user does not exists.
     */
    private function get_form( $form_id = 0 ){
      $form_obj = UM()->Query();
      $form = $form_obj->post_data( $form_id );
      if( 'register' === $form['mode'] ){
        return $form;
      }
      return false;
    }

    /**
     * Creates a webhook entry
     * The events available in $supported_events array only accepted
     * 
     * @since 2.9.1
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
            if( isset( $entry->form_id ) && ( 'user_registered' === $entry->event ) ){
                if(  !$this->get_form( $entry->form_id ) ){
                    return new WP_Error( 'rest_bad_request', 'Form does not exist!', array( 'status' => 404 ) );
                }
                $args['form_id'] = $entry->form_id;
            }
            $post_name = "Ultimate Member ";
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
     * @since 2.9.1
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
     * Fires once the profile is updated.
     * 
     * @since 2.9.1
     *
     * @param array     $to_update      User details.
     * @param number    $user_id        User ID.
     * @param array     $args           User arguments.
     */
    public function payload_user_profile_updated( $to_update, $user_id, $args ){
        $user = $this->get_user( $user_id );
        if( $user ){
            $args = array(
                'event' => 'user_profile_updated'
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'user_profile_updated',
                    'data' => $user
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the account is updated.
     * 
     * @since 2.9.1
     *
     * @param number    $user_id        User ID.
     * @param array     $args           array of details updated from the UI.
     */
    public function payload_user_account_updated( $user_id, $changes ){
        $user = $this->get_user( $user_id );
        if( $user ){
            $args = array(
                'event' => 'user_account_updated'
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'user_account_updated',
                    'data' => $user
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the user role is updated.
     * 
     * @since 2.9.1
     *
     * @param array     $new_roles      New roles array.
     * @param array     $old_roles      Old roles array.
     * @param number    $user_id        User ID.
     */
    public function payload_member_role_changed( $new_roles, $old_roles, $user_id ){
        if( !empty( array_diff($new_roles,$old_roles) ) ){
            $user = $this->get_user( $user_id );
            if( $user ){
                $args = array(
                    'event' => 'member_role_changed'
                );
                $webhooks = $this->get_webhook_posts( $args );
                if( !empty( $webhooks ) ){
                    $user['old_roles'] = $old_roles;
                    $event_data = array(
                        'event' => 'member_role_changed',
                        'data' => $user
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
     * Fires once the user status is updated.
     * 
     * @since 2.9.1
     *
     * @param string    $status         New status.
     * @param number    $user_id        User ID.
     */
    public function payload_user_status_changed( $status, $user_id ){
        $user = $this->get_user( $user_id );
        if( $user ){
            $args = array(
                'event' => 'user_status_changed'
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'user_status_changed',
                    'data' => $user
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the user registered.
     * 
     * @since 2.9.1
     *
     * @param number    $user_id        User ID.
     * @param array     $submitted      Array of submitted data.
     * @param array     $form_data      Form data from UI submit request.
     */
    public function payload_user_registered( $user_id, $submitted, $form_data ){
        $user = $this->get_user( $user_id );
        if( $user ){
            $args = array(
                'event' => 'user_registered'
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'user_registered',
                    'data' => array_merge( $user, $submitted )
                );
                foreach( $webhooks as $webhook ){
                    $form_id = get_post_meta( $webhook->ID, 'form_id', true );
                    if( empty( $form_id ) || ( $form_id == $submitted['form_id'] ) ){
                        $url = $webhook->url;
                        zoho_flow_execute_webhook( $url, $event_data, array() );
                    }
                }
            }
        }
    }

    /**
     * Get user and system info.
     * Default API
     *
     *  @since 2.9.1
     *
     * @return WP_REST_Response|WP_Error  WP_REST_Response system and logged in user details | WP_Error object with error details.
     */
    public function get_system_info(){
        $system_info = parent::get_system_info();
        if( ! function_exists( 'get_plugin_data' ) ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/ultimate-member/ultimate-member.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['ultimate_member'] = @$plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}
