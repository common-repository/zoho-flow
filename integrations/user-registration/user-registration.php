<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//support from
//zohoflow 1.5.0
//user-registration 3.0.3
class Zoho_Flow_User_Registration extends Zoho_Flow_Service{

  //webhook events supported
  public static $supported_events = array("user_registered");

	//To get forms list
  public function get_forms($request){
    if (!class_exists('UR_Form_Handler')) {
      require_once ABSPATH . 'wp-content/plugins/user-registration/includes/class-ur-form-handler.php';
    }
    $UR_forms_object = new UR_Form_Handler();
    $forms_list = $UR_forms_object->get_form();
    return rest_ensure_response( $forms_list );
  }

	//To get form fields
  public function get_form_fields($request){
    $form_id = $request['form_id'];
    if($this->is_valid_form($form_id)){
      if (!class_exists('UR_Form_Handler')) {
        require_once ABSPATH . 'wp-content/plugins/user-registration/includes/class-ur-form-handler.php';
      }
      $UR_forms_object = new UR_Form_Handler();
      $form_fields_list = $UR_forms_object->get_form($form_id);
      $form_data = json_decode($form_fields_list->post_content);
      $fields_dict = array();
      foreach ( $form_data as $sec ) {
				foreach ( $sec as $fields ) {
					foreach ( $fields as $field ) {
            //password fields are not restricted here, will be handled in flow integration end
            array_push($fields_dict,$field);
					}
				}
			}
      return rest_ensure_response( $fields_dict );
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Invalid form ID', array( 'status' => 400 ) );
    }
  }

  //utilities
  private function is_valid_form($form_id){
    if((empty($form_id)) || (!is_numeric($form_id))){
			return false;
		}
    if (!class_exists('UR_Form_Handler')) {
      require_once ABSPATH . 'wp-content/plugins/user-registration/includes/class-ur-form-handler.php';
    }
    $UR_form_handler = New UR_Form_Handler;
    $form_details = $UR_form_handler->get_form($form_id);
    if(!empty($form_details->ID)){
      return true;
    }
    else{
      return false;
    }
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
      $post_name = "User Registration ";
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




	//Webhook payload handling
	//For new user form registration
	public function payload_after_register_user_action($valid_form_data, $form_id, $user_id){
	  $return_array = array();
	  foreach ($valid_form_data as $key => $value) {
	    //due to security risk we will not handle passwords in flow
	    if($value->field_type != 'password'){
	      $return_array[$key] = $value->value;
	    }
	  }
	  $return_array['form_id'] = $form_id;
	  $return_array['user_id'] = $user_id;
	  $args = array(
	    'event' => 'user_registered',
	    'form_id' => $form_id
	  );
	  $webhooks = $this->get_webhook_posts($args);
	  $event_data = array(
	    'event' => 'user_registered',
	    'data' => $return_array
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/user-registration/user-registration.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['user_registration_plugin'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
