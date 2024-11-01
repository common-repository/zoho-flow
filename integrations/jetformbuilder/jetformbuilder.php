<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow          2.5.0
 * @since jetformbuilder    3.3.2
 */
class Zoho_Flow_JetFormBuilder extends Zoho_Flow_Service{

  /**
   * webhook events supported
   */
  public static $supported_events = array( "record_added" );

  /**
   * list forms
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * @return array Array of form details.
   */
  public function list_forms( $request ){
    $forms = \Jet_Form_Builder\Classes\Tools::get_forms_list_for_js();
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
    if( $this->is_valid_form_id( $request['form_id'] ) ){
      $form    = get_post( $request['form_id'] );
      jet_fb_context()->set_parsers(
  			\Jet_Form_Builder\Blocks\Block_Helper::get_blocks_by_post( $form )
  		);
      foreach ( jet_fb_context()->iterate_parsers() as $name => $parser ) {
  			if ( $parser->is_secure() ) {
  				continue;
  			}
  			$fields[] = array(
  				'value' => $name,
  				'label' => $parser->get_label(),
  				'type'  => $parser->get_type(),
  			);
  		}
      return $fields;
    }
    else{
      return new WP_Error( 'rest_bad_request', "Form does not exist!", array( 'status' => 400 ) );
    }
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
      $form = get_post( $form_id );
      if( ( $form ) && ( 'jet-form-builder' === $form->post_type ) ){
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
      $post_name = "JetFormBuilder ";
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
   * Fires once a record is succesfully stored in DB.
   *
   * @param Form_Handler    $form_handler     Formhandler object.
   * @param bool            $is_success       success notification.
   *
   * @return array|null Webhook payload. event, and record details | null if criteria not match.
   */
  public function payload_record_added( $form_handler, $is_success ){
    $args = array(
      'event' => 'record_added',
      'form_id' => $form_handler->form_id
    );
    $webhooks = $this->get_webhook_posts( $args );
    if( !empty( $webhooks ) ){
      $record_ids = $form_handler->action_handler->context['save_record'];
      $record_array = array();
      foreach ($record_ids as $record_id) {
        $record = array(
          'record_id' => $record_id,
          'is_success' => $is_success,
          'record_fields' => \JFB_Modules\Form_Record\Query_Views\Record_Fields_View::get_request( $record_id )
        );
        array_push( $record_array, $record );
      }
      $event_data = array(
        'event' => 'record_added',
        'data' => array(
           'form_id' => $form_handler->form_id,
           'record_data' => $record_array
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/jetformbuilder/jet-form-builder.php';
    if(file_exists( $plugin_dir ) ){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['jetformbuilder'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
