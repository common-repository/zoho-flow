<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow  2.5.0
 * @since ws_form   1.9.199
 */
class Zoho_Flow_WS_Form extends Zoho_Flow_Service{

  /**
   * webhook events supported
   */
  public static $supported_events = array( "submission_added", "submission_status_changed" );

  /**
   * list modules
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * request params  Optional. Arguments for querying forms.
   * @type string  $order_by    List order type with the order.Eg. date_added DESC. Accepts label, date_added, date_updated, id. Default id ASC.
   * @type int     limit        Number of forms to query for. Default all.
   * @type int     offset       Index of query. Default 0.
   *
   * @return array|WP_Error Array of form details | WP_Error object with error details.
   */
  public function list_forms( $request ){
    $form_obj = new WS_Form_Form();
    $forms_list = $form_obj->db_read_all(
      '',
      '',
      isset($request['order_by']) ? $request['order_by'] : '',
      isset($request['limit']) ? $request['limit'] : '',
      isset($request['offset']) ? $request['offset'] : '',
      true,
      false,
      ''
    );
    if( is_wp_error( $forms_list ) ){
      return new WP_Error( 'rest_bad_request', $forms_list->get_error_messages()[0], array( 'status' => 400 ) );
    }
    elseif( false === $forms_list ){
      return new WP_Error( 'rest_bad_request', 'Unable to list forms', array( 'status' => 400 ) );
    }
    return rest_ensure_response( $forms_list );
  }

  /**
   * list form fields
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * request path param  Mandatory.
   * @type int  form_id   Form ID to retrive the fields for.
   *
   * @return stdClass|WP_Error stdClass object with form details, groups, sections and the fields | WP_Error object with error details.
   */
  public function list_form_fields( $request ){
    $form_obj = new WS_Form_Form();
    $form_obj->id = $request['form_id'];
    if( empty( $form_obj->db_get_label() ) ){
      return new WP_Error( 'rest_bad_request', 'Invalid form ID', array( 'status' => 404 ) );
    }
    $form_detail = $form_obj->db_read( false, true );
    return rest_ensure_response( $form_detail );
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
      $form_obj = new WS_Form_Form();
      if( is_wp_error( $form_obj ) ){
        return false;
      }
      $form_obj->id = $form_id;
      if( empty( $form_obj->db_get_label() ) ){
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
     $post_name = "WS Form ";
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
  * Fires after saving submission to db.
  *
  * @param WS_Form_Submit   $form_submit_obj   Details of an submission data.
  *
  * @return array|null Webhook payload. event, submission details, field details included | null if criteria not match.
  */
 public function payload_submission_added($form_submit_obj){
   $args = array(
     'event' => 'submission_added',
     'form_id' => $form_submit_obj->form_id
   );
   $webhooks = $this->get_webhook_posts( $args );
   if( !empty( $webhooks ) ){
     //$form_submit_obj->db_read();
     $entry_array = json_decode( json_encode( $form_submit_obj ), true );
     unset(
       $entry_array['hash'],
       $entry_array['token'],
       $entry_array['token_validated'],
       $entry_array['actions'],
       $entry_array['form_object'],
       $entry_array['encrypted'],
       $entry_array['table_name'],
       $entry_array['table_name_meta'],
       $entry_array['bypass_required_array'],
       $entry_array['field_types'],
       $entry_array['submit_fields'],
       $entry_array['return_hash']
     );
     $event_data = array(
       'event' => 'submission_added',
       'data' => $entry_array
     );
     foreach( $webhooks as $webhook ){
       $url = $webhook->url;
       zoho_flow_execute_webhook( $url, $event_data, array() );
     }
   }
 }

 /**
  * Fires when status of the submission updated.
  *
  * @param int      $submission_id    ID of submission.
  * @param string   $status           Status of submission
  *
  * @return array|null Webhook payload. event, submission details, field details included | null if criteria not match.
  */
 public function payload_submission_status_changed( $submission_id, $status ){
   $form_submit_obj = New WS_Form_Submit();
   $form_submit_obj->id = $submission_id;
   $form_submit_obj->db_read();
   $args = array(
     'event' => 'submission_status_changed',
     'form_id' => $form_submit_obj->form_id
   );
   $webhooks = $this->get_webhook_posts( $args );
   if( !empty( $webhooks ) ){
     $entry_array = json_decode( json_encode( $form_submit_obj ), true );
     unset(
       $entry_array['hash'],
       $entry_array['token'],
       $entry_array['token_validated'],
       $entry_array['actions'],
       $entry_array['form_object'],
       $entry_array['encrypted'],
       $entry_array['table_name'],
       $entry_array['table_name_meta'],
       $entry_array['bypass_required_array'],
       $entry_array['field_types'],
       $entry_array['submit_fields'],
       $entry_array['return_hash']
     );
     $event_data = array(
       'event' => 'submission_status_changed',
       'data' => $entry_array
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/ws-form/ws-form.php';
    if(file_exists( $plugin_dir ) ){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['ws_form'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
