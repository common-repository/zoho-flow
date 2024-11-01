<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//support from
//zohoflow 2.2.0
//wp_mail_smtp 1.2.3.24
class Zoho_Flow_UsersWP extends Zoho_Flow_Service{

  //webhook events supported
  public static $supported_events = array("login_success","login_failure","registration_success","forgot_password");

	//webhooks
  public function create_webhook($request){
    $entry = json_decode($request->get_body());
    $name = $entry->name;
    $url = $entry->url;
    $event = $entry->event;
    if((!empty($name)) && (!empty($url)) && (!empty($event)) && (in_array($event, self::$supported_events)) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url))){
      $args = array(
        'name' => $name,
        'url' => $url,
        'event' => $event
      );
      $post_name = "UsersWP ";
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

  function payload_login_success($data){
		$args = array(
       'event' => 'login_success'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			unset($data['password']);
      $event_data = array(
        'event' => 'login_success',
        'data' => $data
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

	function payload_login_failed($username){
		$args = array(
       'event' => 'login_failure'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
      $event_data = array(
        'event' => 'login_failure',
        'data' => array(
					'username' => $username
				)
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  function payload_register_success($data,$userid){
		$args = array(
       'event' => 'registration_success'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			$data['userid'] = $userid;
			unset($data['password']);
      $event_data = array(
        'event' => 'registration_success',
        'data' => $data
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  function payload_forgot_password($data){
		$args = array(
       'event' => 'forgot_password'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
      $event_data = array(
        'event' => 'forgot_password',
        'data' => $data
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  //default API
  public function get_system_info(){
    $system_info = parent::get_system_info();
    if( ! function_exists('get_plugin_data') ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_dir = ABSPATH . 'wp-content/plugins/userswp/userswp.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['userswp'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
