<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//support from
//zohoflow 2.0.0
//fluent-form 5.0.8
class Zoho_Flow_Fluent_Forms extends Zoho_Flow_Service{

  //webhook events supported
  public static $supported_events = array("submission_inserted");

	//To get forms list
  public function get_all_forms($request){
    $form_obj = fluentFormApi('forms');
    $forms_list = $form_obj->forms(
      array(
        'per_page' => 500,
        'page' => 1,
        'sort_column' => 'id',
        'sort_by' => 'DESC'
      )
    );
    return rest_ensure_response($forms_list);
  }

	//To get fields list
  public function get_all_form_fields($request){
    $form_id = $request['form_id'];
    if(!empty($form_id) && is_numeric($form_id) && $this->is_valid_form($form_id)){
      $form_obj = fluentFormApi('forms');
      $form = fluentFormApi('forms')->form($form_id);
      $form_field_list = $form->fields();
      return rest_ensure_response($form_field_list);
    }
    return new WP_Error( 'rest_bad_request', 'Invalid form ID', array( 'status' => 404 ) );
  }



  //utilities
  public function is_valid_form($form_id){
    if(!empty($form_id) && is_numeric($form_id)){
      $form_obj = fluentFormApi('forms');
      if(is_object($form_obj->find($form_id))){
        return true;
      }
      return false;
    }
    return false;
  }

  //webhooks
  public function create_webhook($request){
    $entry = json_decode($request->get_body());
    $name = $entry->name;
    $url = $entry->url;
    $event = $entry->event;
    $supported_events = self::$supported_events;
    $form_id = $entry->form_id;
    if((!empty($name)) && (!empty($url)) && (!empty($event)) && (in_array($event, self::$supported_events)) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url)) && $this->is_valid_form($form_id)){
      $args = array(
        'name' => $name,
        'url' => $url,
        'event' => $event,
        'form_id' => $form_id
      );
      $post_name = "Fluent Forms ";
      $post_id = $this->create_webhook_post($post_name, $args);
      if(is_wp_error($post_id)){
        $errors = $post_id->get_error_messages();
        return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
      }
      return rest_ensure_response( array(
          'webhook_id' => $post_id
      ) );
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Data validation failed', array( 'status' => 400 ) );
    }
  }

  public function delete_webhook($request){
    $webhook_id = $request['webhook_id'];
    if(is_numeric($webhook_id)){
      $webhook_post = $this->get_webhook_post($webhook_id);
      if(!empty($webhook_post[0]->ID)){
        $delete_webhook = $this->delete_webhook_post($webhook_id);
        if(is_wp_error($delete_webhook)){
          $errors = $delete_webhook->get_error_messages();
          return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
        }
        else{
          return rest_ensure_response(array('message' => 'Success'));
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

	// Payload
	// For new form entries
  public function payload_submission_inserted($insertId, $formData, $form){
    $payload_array = array(
      'submission_data' => $formData,
      'submission_id' => $insertId,
      'form_data' => array(
        'id' => $form->id,
        'title' => $form->title,
        'created_by' => $form->created_by,
        'created_at' => $form->created_at,
        'updated_at' => $form->updated_at,
      )
    );
    $args = array(
      'event' => 'submission_inserted',
      'form_id' => $form->id
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'submission_inserted',
      'data' => $payload_array
    );
    foreach($webhooks as $webhook){
      $url = $webhook->url;
      zoho_flow_execute_webhook($url, $event_data,array());
    }
  }

  //default API
  public function get_system_info(){
    $system_info = parent::get_system_info();
    if( ! function_exists('get_plugin_data') ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_dir = ABSPATH . 'wp-content/plugins/fluentform/fluentform.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['fluent_forms'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
