<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow    2.5.0
 * @since weforms     1.6.23
 */
class Zoho_Flow_WeForms extends Zoho_Flow_Service{

  /**
   * webhook events supported
   */
  public static $supported_events = array( "submission_added" );

  /**
   * list forms
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * request params  Optional. Arguments for querying forms.
   * @type int        per_page   Number of results per call. Default 10.
   * @type int        page       Index of page. Default 1.
   * @type string     order      Sort order. DESC | ASC. Default DESC.
   * @type string     orderby    Sort field. Default post_date.
   * @type string     status     Status of the form. Default any.
   * @type string     search     search query string.
   *
   * @return array Array of form details.
   */
  public function list_forms( $request ){
    $form_controller = New Weforms_Forms_Controller();
		$forms = $form_controller->get_items( $request );
    return rest_ensure_response( $forms );
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
    $form_obj = New WeForms_Form( $request['form_id'] );
    if( 0 === $form_obj->get_id() ){
      return new WP_Error( 'rest_bad_request', 'Invalid form ID', array( 'status' => 404 ) );
    }
    return rest_ensure_response( $form_obj->get_fields() );
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
      $form_obj = New WeForms_Form( $form_id );
      if( ( 0 === $form_obj->get_id() ) || ( 'wpuf_contact_form' != $form_obj->data->post_type ) ){
        return false;
      }
      return true;
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
   * @param int   $entry_id    Form entry ID.
   * @param int   $form_id     Form ID.
   * @param int   $page_id     Page ID.
   *
   * @return array|null Webhook payload. event, and submission details | null if criteria not match.
   */
  public function payload_submission_added( $entry_id, $form_id, $page_id, $form_settings ){
    $args = array(
      'event' => 'submission_added',
      'form_id' => $form_id
    );
    $webhooks = $this->get_webhook_posts( $args );
    if( !empty( $webhooks ) ){
      $form = New WeForms_Form($form_id);
  		$form_entry = New WeForms_Form_Entry($entry_id, $form);
      $form_fields = $form_entry->get_fields();
      $form_field_details = array();
      foreach ($form_fields as $field_obj) {
        $field_key = $field_obj['name'].'_'.$field_obj['id'];
        $form_field_details[$field_key] = $field_obj['value'];
      }
      $entry_details = $form_entry->get_metadata();
      $entry_details['page_id'] = $page_id;
      $event_data = array(
        'event' => 'submission_added',
        'data' => array(
           'entry_data' => $entry_details,
           'field_data' => $form_field_details
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
   *
   * @return array|WP_Error System and logged in user details | WP_Error object with error details.
	 */
  public function get_system_info(){
    $system_info = parent::get_system_info();
    if( ! function_exists( 'get_plugin_data' ) ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_dir = ABSPATH . 'wp-content/plugins/weforms/weforms.php';
    if(file_exists( $plugin_dir ) ){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['weforms'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
