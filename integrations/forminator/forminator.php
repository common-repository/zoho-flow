<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//support from
//zohoflow 1.5.0
//forminator 1.24.6
class Zoho_Flow_Forminator extends Zoho_Flow_Service
{
	//webhook events supported
  public static $supported_events = array("form_entry_added","poll_added","quiz_added");

	//To get forms list
  public function get_forms($request){
    $forms_list = Forminator_API::get_forms();
    return rest_ensure_response($forms_list);
  }

	//to get form fields
  public function get_form_fields($request){
    $form_id = $request['form_id'];
    if((empty($form_id)) || (!is_numeric($form_id))){
      return new WP_Error( 'rest_bad_request', 'Invalid form ID', array( 'status' => 400 ) );
    }
    $forms_field_list = Forminator_API::get_form_wrappers($form_id);
    if(is_wp_error($forms_field_list)){
      return new WP_Error( 'rest_bad_request', 'Invalid form ID', array( 'status' => 400 ) );
    }
    return rest_ensure_response($forms_field_list);
  }

	//To get polls list
  public function get_polls($request){
    $polls_list = Forminator_API::get_polls();
    return rest_ensure_response($polls_list);
  }

	//To get quizzes list
  public function get_quizzes($request){
    $quiz_list = Forminator_API::get_quizzes();
    return rest_ensure_response($quiz_list);
  }

	//To get quiz fields
  public function get_quiz_fields($request){
    $quiz_id = $request['quiz_id'];
    if((empty($quiz_id)) || (!is_numeric($quiz_id))){
      return new WP_Error( 'rest_bad_request', 'Invalid quiz ID', array( 'status' => 400 ) );
    }
    $quiz_field_list = Forminator_API::get_quiz($quiz_id);
    if(is_wp_error($quiz_field_list)){
      return new WP_Error( 'rest_bad_request', 'Invalid quiz ID', array( 'status' => 400 ) );
    }
    return rest_ensure_response($quiz_field_list);
  }



  //webhooks
  public function create_webhook($request){
    $entry = json_decode($request->get_body());
    $name = $entry->name;
    $url = $entry->url;
    $event = $entry->event;
    $form_id = $entry->form_id;
    $poll_id = $entry->poll_id;
    $quiz_id = $entry->quiz_id;
    $resource_type = '';
    $resource_id = '';
    if((!empty($form_id)) && (is_numeric($form_id)) && ($event == 'form_entry_added')){
      $form_detail = Forminator_API::get_form($form_id);
      if(is_wp_error($form_detail)){
        return new WP_Error( 'rest_bad_request', 'Invalid form ID', array( 'status' => 400 ) );
      }
      $resource_type = 'form';
      $resource_id = $form_id;
    }
    if((!empty($poll_id)) && (is_numeric($poll_id)) && ($event == 'poll_added')){
      $poll_detail = Forminator_API::get_poll($poll_id);
      if(is_wp_error($poll_detail)){
        return new WP_Error( 'rest_bad_request', 'Invalid poll ID', array( 'status' => 400 ) );
      }
      $resource_type = 'poll';
      $resource_id = $poll_id;
    }
    if((!empty($quiz_id)) && (is_numeric($quiz_id)) && ($event == 'quiz_added')){
      $quiz_detail = Forminator_API::get_quiz($quiz_id);
      if(is_wp_error($quiz_detail)){
        return new WP_Error( 'rest_bad_request', 'Invalid quiz ID', array( 'status' => 400 ) );
      }
      $resource_type = 'quiz';
      $resource_id = $quiz_id;
    }
    if((empty($resource_type)) || (empty($resource_id))){
      return new WP_Error( 'rest_bad_request', 'Invalid resource', array( 'status' => 400 ) );
    }
    $supported_events = self::$supported_events;
    if((!empty($name)) && (!empty($url)) && (!empty($event)) && (in_array($event, self::$supported_events)) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url))){
      $args = array(
        'name' => $name,
        'url' => $url,
        'event' => $event,
        'resource_id' => $resource_id,
        'resource_type' => $resource_type
      );
      $post_name = "Forminator ";
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
	//For form entry
  public function payload_form_entry_added($fron_mail, $custom_form, $data, $entry){
    foreach ($entry as $key => $value) {
      if($key != 'meta_data'){
        $data[$key] = $value;
      }
    }
    foreach ($entry->meta_data as $key => $value) {
      $data[$key] = $value['value'];
    }

    $args = array(
      'event' => 'form_entry_added',
      'resource_type' => 'form',
      'resource_id' => $data['form_id']
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'form_entry_added',
      'data' => $data
    );
    foreach($webhooks as $webhook){
      $url = $webhook->url;
      zoho_flow_execute_webhook($url, $event_data,array());
    }
  }

	//For poll entry
  public function payload_poll_added($current_poll, $poll, $data, $entry){
    foreach ($entry as $key => $value) {
      if($key != 'meta_data'){
        $data[$key] = $value;
      }
    }
    foreach ($entry->meta_data as $key => $value) {
      $data[$key] = $value['value'];
    }
    $data['answer'] = $data[$data[$data['form_id']]];
    $args = array(
      'event' => 'poll_added',
      'resource_type' => 'poll',
      'resource_id' => $data['form_id']
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'poll_added',
      'data' => $data
    );
    foreach($webhooks as $webhook){
      $url = $webhook->url;
      zoho_flow_execute_webhook($url, $event_data,array());
    }
  }

	//For quiz entry
  public function payload_quiz_added($current_quiz, $quiz, $data, $entry){
    foreach ($entry as $key => $value) {
      if($key != 'meta_data'){
        $data[$key] = $value;
      }
    }
    foreach ($entry->meta_data as $key => $value) {
      $data[$key] = $value['value'];
    }
    foreach ($quiz->questions as $key => $value) {
      $data[$value['slug']] = $value['answers'][$data['answers'][$value['slug']]]['title'];
      if(empty($value['answers'][$data['answers'][$value['slug']]]['toggle'])){
        $data[$value['slug'].'_is_correct'] = false;
      }
      else{
        $data[$value['slug'].'_is_correct'] = true;
      }
    }
    $args = array(
      'event' => 'quiz_added',
      'resource_type' => 'quiz',
      'resource_id' => $data['form_id']
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'quiz_added',
      'data' => $data
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
		$plugin_dir = ABSPATH . 'wp-content/plugins/forminator/forminator.php';
		if(file_exists($plugin_dir)){
			$plugin_data = get_plugin_data( $plugin_dir );
			$system_info['forminator_plugin'] = $plugin_data['Version'];
		}
		return rest_ensure_response( $system_info );
	}
}
