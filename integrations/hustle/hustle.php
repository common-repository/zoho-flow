<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow  2.5.0
 * @since hustle    7.8.4
 */
class Zoho_Flow_Hustle extends Zoho_Flow_Service{

  /**
   * webhook events supported
   */
  public static $supported_events = array( "entry_submitted" );

  /**
   * list modules
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * request params  Optional. Arguments for querying modules.
   * @type string  $module_type Module type. Accepts popup, slidein, embedded, social_sharing. Default All.
   * @type int     limit        Number of module to query for. Default -1.
   *
   * @return array|WP_Error Array of Hustle_Module_Model | WP_Error object with error details.
   */
  public function list_modules( $request ){
    $module_collection_obj = new Hustle_Module_Collection();
    if( is_wp_error( $module_collection_obj ) ){
      return new WP_Error( 'rest_bad_request', $module_collection_obj->get_error_messages()[0], array( 'status' => 400 ) );
    }
    return rest_ensure_response( $module_collection_obj->get_all(
      true,
      isset( $request['module_type'] ) ? array( 'module_type' => $request['module_type'] ) : array(),
      isset( $request['limit'] ) ? $request['limit'] : -1
    ) );
  }

  /**
   * list module fields
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * request path param  Mandatory.
   * @type int  module_id Module ID to retrive the fields for.
   *
   * @return array|WP_Error Array of field arrays | WP_Error object with error details.
   */
  public function list_fields( $request ){
    $module_model_obj = new Hustle_Module_Model( $request['module_id'] );
    if( is_wp_error( $module_model_obj ) ){
      return new WP_Error( 'rest_bad_request', $module_model_obj->get_error_messages()[0], array( 'status' => 400 ) );
    }
    else if( $module_model_obj->active ){
      return rest_ensure_response( $module_model_obj->get_form_fields() );
    }
    else{
      return new WP_Error( 'rest_bad_request', "Module does not exist!", array( 'status' => 400 ) );
    }
  }

  /**
   * Check whether the Module ID is valid or not.
   *
   * @param int   $module_id    Module ID.
   *
   * @return bool true if the module is active | false for others.
   */
  private function is_valid_module_id( $module_id ){
    if( isset( $module_id ) ){
      $module_model_obj = new Hustle_Module_Model( $module_id );
      if( is_wp_error( $module_model_obj ) ){
        return false;
      }
      else if( $module_model_obj->active ){
        return true;
      }
      else{
        return false;
      }
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
   if( ( !isset( $entry->module_id ) ) || !$this->is_valid_module_id( $entry->module_id ) ){
     return new WP_Error( 'rest_bad_request', "Module does not exist!", array( 'status' => 400 ) );
   }
   if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
     $args = array(
       'name' => $entry->name,
       'url' => $entry->url,
       'event' => $entry->event,
       'module_id' => $entry->module_id
     );
     $post_name = "Hustle ";
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
  * Fires before saving entry to db.
  *
  * @param Hustle_Entry_Model $entry_data   Details of an entry.
  * @param int                $module_id    Module ID.
  * @param array              $field_data   Submitted data array.
  *
  * @return array|null Webhook payload. event, entry details, field details included | null if criteria not match.
  */
 public function payload_entry_created( $entry_data, $module_id, $field_data ){
  $args = array(
      'event' => 'entry_submitted',
      'module_id' => $module_id
    );
   $webhooks = $this->get_webhook_posts( $args );
   if( !empty( $webhooks ) ){
     $event_data = array(
       'event' => 'entry_submitted',
       'data' => array(
          'entry_data' => $entry_data,
          'field_data' => $field_data
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/wordpress-popup/popover.php';
    if(file_exists( $plugin_dir ) ){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['hustle'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
