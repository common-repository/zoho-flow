<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Zoho_Flow_Contact_Form_7 extends Zoho_Flow_Service
{
    public function get_forms( $request ) {
        $args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1);
        $posts = get_posts( $args );

        $data = array();

        if ( empty( $posts ) ) {
            return rest_ensure_response( $data );
        }

        foreach ( $posts as $post ) {

            $post_data = array();

            $schema = $this->get_form_schema();

            if ( isset( $schema['properties']['id'] ) ) {
                $post_data['id'] = (int) $post->ID;
            }

            if ( isset( $schema['properties']['title'] ) ) {
                $post_data['title'] = $post->post_title;
            }

            $response = rest_ensure_response( $post_data );
            array_push($data, $post_data);
        }

        return rest_ensure_response( $data );
    }

    public function get_form_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'form',
            'type'                 => 'form',
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'ID of the Contact Form 7 form.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'title' => array(
                    'description'  => esc_html__( 'The title of the Contact Form 7 form.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit'),
                ),
            ),
        );

        return $schema;
    }


    public function get_fields( $request ) {
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $contact_form = WPCF7_ContactForm::get_instance( $form_id );

        if(!$contact_form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $form_tags = $contact_form->scan_form_tags();

        $fields = array();

        if ( empty( $form_tags ) ) {
            return rest_ensure_response( $fields );
        }

        foreach ( $form_tags as $form_tag ) {
            $basetype = $form_tag->basetype;
            if($basetype != 'text' && $basetype != 'email' && $basetype != 'url' && $basetype != 'tel' && $basetype != 'textarea' && $basetype != 'number' && $basetype != 'date' && $basetype != 'select' && $basetype != 'checkbox' && $basetype != 'radio' && $basetype != 'acceptance'&& $basetype != 'file' && $basetype != 'hidden' && $basetype != 'dynamictext' && $basetype != 'dynamichidden'){
                continue;
            }

            $type = $form_tag->type;
            $name = $form_tag->name;
            $name = $this->convert_field_name($name);
            $is_required = ( $form_tag->is_required() || 'radio' == $form_tag->type );

            $data = array(
                'name' => $name,
                'type' => $basetype,
                'is_required' => $is_required
            );

            if ( wpcf7_form_tag_supports( $form_tag->type, 'selectable-values' ) ) {
                $options = array();
                foreach ( $form_tag->values as $value ) {
                    $option = array('key' => $value, 'value' => $value);
                    array_push($options, $option);
                }
                $data['options'] = $options;
            }
            array_push($fields, $data);
        }

        return rest_ensure_response( $fields );
    }

    public function get_field_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'field',
            'type'                 => 'field',
            'properties'           => array(
                'name' => array(
                    'description'  => esc_html__( 'Unique name of the field.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'label' => array(
                    'description'  => esc_html__( 'Label of the field.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit')
                ),
                'type' => array(
                    'description'  => esc_html__( 'Type of the field.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit')
                ),
                'options' => array(
                    'description'  => esc_html__( 'Options of a dropdown/multiselect/checkbox/radio field.', 'zoho-flow' ),
                    'type'         => 'array',
                    'context'      => array( 'view', 'edit')
                ),
                'is_required' => array(
                    'description'  => esc_html__( 'Whether the field is mandatory.', 'zoho-flow' ),
                    'type'         => 'boolean',
                    'context'      => array( 'view', 'edit')
                ),
            ),
        );

        return $schema;
    }



    public function get_webhooks( $request ) {
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $contact_form = WPCF7_ContactForm::get_instance( $form_id );

        if(!$contact_form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $args = array(
            'form_id' => $contact_form->id()
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
                'form_id' => $contact_form->id(),
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }
        return rest_ensure_response( $data );
    }

    public function create_webhook_old( $request ) {
        $form_id = $request['form_id'];
        $url = esc_url_raw($request['url']);
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $contact_form = WPCF7_ContactForm::get_instance( $form_id );

        if(!$contact_form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $form_title = $contact_form->title();

        $post_id = $this->create_webhook_post($form_title, array(
            'form_id' => $contact_form->id(),
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'form_id' => $contact_form->id(),
            'url' => $url
        ) );
    }

    public function delete_webhook_old( $request ) {
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $contact_form = WPCF7_ContactForm::get_instance( $form_id );

        if(!$contact_form){
            return new WP_Error( 'not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $webhook_id = $request['webhook_id'];
        if(!ctype_digit($webhook_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The webhook ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
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

    public function get_file( $request ){
        $filename = sanitize_file_name($request['filename']);

        if(validate_file($filename) > 0){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The requested file name is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $dest_dir = $this->upload_dir();
        $file_path = $dest_dir . '/' . $filename;
        if(!file_exists($file_path)){
            return new WP_Error( 'rest_not_found', esc_html__( 'The requested file could not be found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $this->download_file($file_path);

    }

	public function process_form_submission($contact_form){
	    $id = $contact_form->id();

        $args = array(
            'form_id' => $contact_form->id()
        );
        $webhooks = $this->get_webhook_posts($args);
        $data = array();

        if ( !empty( $webhooks ) ) {
            $submission = WPCF7_Submission::get_instance();
            $posted_data = $submission->get_posted_data();
            foreach ($posted_data as $name => $value) {
                $data[$this->convert_field_name($name)] = $value;
            }
        	foreach ( $webhooks as $webhook ) {
        		$url = $webhook->url;
		        zoho_flow_execute_webhook($url, $data, array());
        	}
        }



	}

    private function convert_field_name($name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_]+/', ' ', $name);
        $name = trim($name);
        $name = str_replace(" ", "_", $name);
        $name = strtolower($name);

        return $name;
    }


		/**
	   * webhook events supported
		 * @since 2.5.0
	   */
	  public static $supported_events = array( "form_entry_added" );

	  /**
	   * list forms
	   * @since 2.5.0
		 *
	   * @param WP_REST_Request $request WP_REST_Request onject.
	   *
	   * request params  Optional. Arguments for querying forms.
	   * @type int        per_page   Number of results per call. Default 200.
	   * @type int        offset     Index of page. Default 0.
	   * @type string     order      Sort order. DESC | ASC. Default DESC.
	   * @type string     orderby    Sort field. Default ID.
	   *
	   * @return array Array of form details.
	   */
	  public function list_forms( $request ){
			$args = array(
				'post_status' => 'any',
				'posts_per_page' => isset($request['per_page']) ? $request['per_page'] : 200,
				'offset' => isset($request['offset']) ? $request['offset'] : 0,
				'orderby' => isset($request['orderby']) ? $request['orderby'] : 'ID',
				'order' => isset($request['order']) ? $request['order'] : 'DESC',
			);
			$items = WPCF7_ContactForm::find( $args );
			$forms = array();
			foreach ( $items as $item ) {
				$forms[] = array(
					'id' => $item->id(),
					'slug' => $item->name(),
					'title' => $item->title(),
					'locale' => $item->locale(),
				);
			}
	    return rest_ensure_response( $forms );
	  }

	  /**
	   * list form fields
		 * @since 2.5.0
	   *
	   * @param WP_REST_Request $request WP_REST_Request onject.
	   *
	   * request path param  Mandatory.
	   * @type int  form_id   Form ID to retrive the fields for.
	   *
	   * @return array|WP_Error array of form field details | WP_Error object with error details.
	   */
	  public function list_form_fields( $request ){
			$contact_form = WPCF7_ContactForm::get_instance( $request['form_id'] );
			if( $contact_form ){
				$items = $contact_form->scan_form_tags();
				$fields = array();
				foreach ( $items as $item ) {
					$fields[] = array(
						'type' => $item->type,
						'basetype' => $item->basetype,
						'raw_name' => $item->raw_name,
						'name' => $item->name,
						'raw_values' => $item->raw_values,
						'values' => $item->values,
						'labels' => $item->labels,
					);
				}
		    return rest_ensure_response( $fields );
     	}
		 	return new WP_Error( 'rest_bad_request', 'Form does not exist!', array( 'status' => 404 ) );
	  }

	  /**
	   * Check whether the Form ID is valid or not.
	   * @since 2.5.0
	   *
	   * @param int   $form_id    Form ID.
	   *
	   * @return bool true if the form exists | false for others.
	   */
	  private function is_valid_form_id( $form_id ){
	    if( isset( $form_id ) ){
	      $contact_form = WPCF7_ContactForm::get_instance( $form_id );
	      if( $contact_form ){
	        return true;
	      }
	      return false;
	    }
	    else{
	      return false;
	    }
	  }

	  /**
	   * Creates a webhook entry
	   * The events available in $supported_events array only accepted
	   * @since 2.5.0
	   *
	   * @param WP_REST_Request $request WP_REST_Request onject.
	   *
	   * @return array|WP_Error Array with Webhook ID | WP_Error object with error details.
	   */
	  public function create_webhook( $request ){
	    $entry = json_decode( $request->get_body() );
	    if( ( !isset( $entry->form_id ) ) || !$this->is_valid_form_id( $entry->form_id ) ){
	      return new WP_Error( 'rest_bad_request', "Form does not exist!", array( 'status' => 400 ) );
	    }
	    if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
	      $args = array(
	        'name' => $entry->name,
	        'url' => $entry->url,
	        'event' => $entry->event,
	        'form' => $entry->form_id
	      );
	      $post_name = "Contact Form 7 ";
	      $post_id = $this->create_webhook_post( $post_name, $args );
	      if( is_wp_error( $post_id ) ){
	        $errors = $post_id->get_error_messages();
	        return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
	      }
	      return rest_ensure_response(
	        array(
	          'webhook_id' => $post_id
	        )
	      );
	    }
	    else{
	      return new WP_Error( 'rest_bad_request', 'Data validation failed', array( 'status' => 400 ) );
	    }
	  }

	 /**
	   * Deletes a webhook entry
	   * Webhook ID returned from webhook create event should be used. Use minimum user scope.
	   * @since 2.5.0
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
	   * Fires once a submission is about to send email.
	   * @since 2.5.0
	   *
	   * @param WPCF7_ContactForm   $contact_form    WPCF7_ContactForm obj.
	   *
	   * @return array|null Webhook payload. event, and submission details | null if criteria not match.
	   */
	  public function payload_form_entry_added( $contact_form ){
			$form_id = $contact_form->id();
	    $args = array(
	      'event' => 'form_entry_added',
	      'form' => $form_id
	    );
	    $webhooks = $this->get_webhook_posts( $args );
	    if( !empty( $webhooks ) ){
				$submission = WPCF7_Submission::get_instance();
				$uploaded_file_urls = $submission->uploaded_files();
				$uploaded_file = array();
				foreach ( $uploaded_file_urls as $field_key => $file_url_array ) {
					foreach ( $file_url_array as $file_url ) {
						$uploaded_file[$field_key][] = array(
							'file_path' => $file_url,
							'file_name' => @basename( $file_url ),
							'file_size' => @filesize( $file_url ),
							'file_type' => @mime_content_type( $file_url ),
						);
					}
				}
				$submission_meta = array(
					'timestamp' => $submission->get_meta( 'timestamp' ),
					'remote_ip' => $submission->get_meta( 'remote_ip' ),
					'remote_port' => $submission->get_meta( 'remote_port' ),
					'user_agent' => $submission->get_meta( 'user_agent' ),
					'url' => $submission->get_meta( 'url' ),
					'unit_tag' => $submission->get_meta( 'unit_tag' ),
					'container_post_id' => $submission->get_meta( 'container_post_id' ),
					'date' => @wp_date( get_option( 'date_format' ), $submission->get_meta( 'timestamp' ) ),
					'time' => @wp_date( get_option( 'time_format' ), $submission->get_meta( 'timestamp' ) ),
					'serial_number' => @wpcf7_flamingo_serial_number( '', '_serial_number', false, new WPCF7_MailTag( sprintf( '[%s]', 'serial_number' ), 'serial_number', '' ) ),
					'post_id' => @wpcf7_post_related_smt( '', '_post_id', false, new WPCF7_MailTag( sprintf( '[%s]', 'post_id' ), 'post_id', '' ) ),
					'post_name' => @wpcf7_post_related_smt( '', '_post_name', false, new WPCF7_MailTag( sprintf( '[%s]', 'post_name' ), 'post_name', '' ) ),
					'post_title' => @wpcf7_post_related_smt( '', '_post_title', false, new WPCF7_MailTag( sprintf( '[%s]', 'post_title' ), 'post_title', '' ) ),
					'post_url' => @wpcf7_post_related_smt( '', '_post_url', false, new WPCF7_MailTag( sprintf( '[%s]', 'post_url' ), 'post_url', '' ) ),
					'post_author' => @wpcf7_post_related_smt( '', '_post_author', false, new WPCF7_MailTag( sprintf( '[%s]', 'post_author' ), 'post_author', '' ) ),
					'post_author_email' => @wpcf7_post_related_smt( '', '_post_author_email', false, new WPCF7_MailTag( sprintf( '[%s]', 'post_author_email' ), 'post_author_email', '' ) ),
					'site_title' => @wpcf7_site_related_smt( '', '_site_title', false, new WPCF7_MailTag( sprintf( '[%s]', 'site_title' ), 'site_title', '' ) ),
					'site_description' => @wpcf7_site_related_smt( '', '_site_description', false, new WPCF7_MailTag( sprintf( '[%s]', 'site_description' ), 'site_description', '' ) ),
					'site_url' => @wpcf7_site_related_smt( '', '_site_url', false, new WPCF7_MailTag( sprintf( '[%s]', 'site_url' ), 'site_url', '' ) ),
					'site_admin_email' => @wpcf7_site_related_smt( '', '_site_admin_email', false, new WPCF7_MailTag( sprintf( '[%s]', 'site_admin_email' ), 'site_admin_email', '' ) ),
					'user_login' => @wpcf7_user_related_smt( '', '_user_login', false, new WPCF7_MailTag( sprintf( '[%s]', 'user_login' ), 'user_login', '' ) ),
					'user_email' => @wpcf7_user_related_smt( '', '_user_email', false, new WPCF7_MailTag( sprintf( '[%s]', 'user_email' ), 'user_email', '' ) ),
					'user_display_name' => @wpcf7_user_related_smt( '', '_user_display_name', false, new WPCF7_MailTag( sprintf( '[%s]', 'user_display_name' ), 'user_display_name', '' ) )
				);
				if( class_exists( 'Cf7_Visited_Pages_Url_Tracking_Public' ) ){
					$submission_meta['cf7vput_last_visited_pages'] = @json_decode( stripslashes( sanitize_text_field( $_COOKIE[ 'cf7vput_last_visited_pages' ] ) ) );
				}
	      $event_data = array(
	        'event' => 'form_entry_added',
	        'data' => array(
	           'field_data' => $submission->get_posted_data(),
						 'uploaded_files' => $uploaded_file,
						 'submission_meta' => $submission_meta
	         )
	      );
	      foreach( $webhooks as $webhook ){
	        $url = $webhook->url;
	        zoho_flow_execute_webhook( $url, $event_data, array() );
	      }
	    }
	  }

	  /**
    	* default API
    	* Get user and system info.
    	* @since 2.5.0
    	*
	    * @return array|WP_Error System and logged in user details | WP_Error object with error details.
		*/
	  public function get_system_info(){
	    $system_info = parent::get_system_info();
	    if( ! function_exists( 'get_plugin_data' ) ){
	      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	    }
	    $plugin_dir = ABSPATH . 'wp-content/plugins/contact-form-7/wp-contact-form-7.php';
	    if(file_exists( $plugin_dir ) ){
	      $plugin_data = get_plugin_data( $plugin_dir );
	      $system_info['contact_form_7'] = $plugin_data['Version'];
	    }
			return rest_ensure_response( $system_info );
	  }

}
