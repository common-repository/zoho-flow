<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow      2.5.0
 * @since happyforms    1.25.11
 */
class Zoho_Flow_Happyforms extends Zoho_Flow_Service{

  /**
   * webhook events supported
   */
  public static $supported_events = array( "submission_added" );

  /**
   * list forms
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * @return array|WP_Error Array of form details | WP_Error object with error details.
   */
  public function list_forms( $request ){
    $form_controller_inst_obj = happyforms_get_form_controller();
		$forms = $form_controller_inst_obj->do_get();
    if( $forms ){
      $forms_list = array();
      foreach ( $forms as $form_obj ) {
        $form_array = array();
        $form_array['ID'] = $form_obj['ID'];
        $form_array['post_author'] = $form_obj['post_author'];
        $form_array['post_title'] = $form_obj['post_title'];
        $form_array['post_status'] = $form_obj['post_status'];
        $form_array['post_date'] = $form_obj['post_date'];
        $form_array['post_date_gmt'] = $form_obj['post_date_gmt'];
        $form_array['post_modified'] = $form_obj['post_modified'];
        $form_array['post_modified_gmt'] = $form_obj['post_modified_gmt'];
        array_push( $forms_list, $form_array );
      }
      return rest_ensure_response( $forms_list );
    }
    return new WP_Error( 'rest_bad_request', 'Unable to list forms', array( 'status' => 400 ) );
  }

  /**
   * list form fields
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * request path param  Mandatory.
   * @type int  form_id   Form ID to retrive the fields for.
   *
   * @return array|WP_Error array of form field details | WP_Error object with error details.
   */
  public function list_form_fields( $request ){
    $form_controller_inst_obj = happyforms_get_form_controller();
    $form_detail = $form_controller_inst_obj->do_get( $request['form_id'], false );
    if( $form_detail ){
      return rest_ensure_response( $form_detail['parts'] );
    }
    return new WP_Error( 'rest_bad_request', 'Invalid form ID', array( 'status' => 404 ) );
  }

  /**
   * Check whether the Form ID is valid or not.
   *
   * @param int   $form_id    Form ID.
   *
   * @return bool true if the form exists | false for others.
   */
  private function is_valid_form_id( $form_id ){
    if( isset( $form_id ) ){
      $form_controller_inst_obj = happyforms_get_form_controller();
      $form_detail = $form_controller_inst_obj->do_get( $form_id, false );
      if( $form_detail ){
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
        'form_id' => $entry->form_id
      );
      $post_name = "Happyforms ";
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
   * Fires once a message is succesfully submitted.
   *
   * @param array   $submission_data    Submission data.
   * @param array   $form_data          Current form data.
   *
   * @return array|null Webhook payload. event, and submission details | null if criteria not match.
   */
  public function payload_submission_added( $submission_data, $form_data, $args ){
    if( isset( $form_data['ID'] ) ){
      $args = array(
        'event' => 'submission_added',
        'form_id' => $form_data['ID']
      );
      $webhooks = $this->get_webhook_posts( $args );
      if( !empty( $webhooks ) ){
        $event_data = array(
          'event' => 'submission_added',
          'data' => $submission_data
        );
        foreach( $webhooks as $webhook ){
          $url = $webhook->url;
          zoho_flow_execute_webhook( $url, $event_data, array() );
        }
      }
    }
  }

  /**
	 * default API
	 * Get user and system info.
   *
   * @return array|WP_Error System and logged in user details | WP_Error object with error details.
	 */
  public function get_system_info(){
    $system_info = parent::get_system_info();
    if( ! function_exists( 'get_plugin_data' ) ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_dir = ABSPATH . 'wp-content/plugins/happyforms/happyforms.php';
    if(file_exists( $plugin_dir ) ){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['happyforms'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
