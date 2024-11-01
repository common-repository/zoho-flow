<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MailPoet\API\MP\v1;
use MailPoet\API\MP\v1\APIException;

//support from
//zohoflow 2.0.0
//mailpoet 4.30.0
class Zoho_Flow_MailPoet extends Zoho_Flow_Service{

	//webhook events supported
  public static $supported_events = array("subscriber_added","subscriber_updated","subscriber_status_changed");

  //To get contact list
	public function get_lists($request){
		try{
  		$mailpoet_api = \MailPoet\API\API::MP('v1');
  		$lists = $mailpoet_api->getLists();
  		return rest_ensure_response($lists);
		}catch(Exception $e) {
			return new WP_Error( 'rest_bad_request', $e->getMessage(), array( 'status' => 400,'code' => $e->getCode()) );
		}
  }

  //To get field meta list
  public function get_fields($request){
	   try{
  		   $mailpoet_api = \MailPoet\API\API::MP('v1');
  		   $fields = $mailpoet_api->GetSubscriberFields();
  		   return rest_ensure_response($fields);
		  }catch(Exception $e) {
			   return new WP_Error( 'rest_bad_request', $e->getMessage(), array( 'status' => 400,'code' => $e->getCode()) );
		   }
  }

  //To get subscribers list
  public function get_subscribers($request){
	   try{
       $mailpoet_api = \MailPoet\API\API::MP('v1');
       $filter = array(
         "status" => $request['status'],
         "listId" => $request['listId'],
         "minUpdatedAt" => $request['minUpdatedAt'],
       );
       $limit = ((!empty($request['limit']))? $request['limit'] : 200);
       $offset = ((!empty($request['offset']))? $request['offset'] : 0);
  		 $subscribers = $mailpoet_api->GetSubscribers($filter, $limit, $offset);
  		 return rest_ensure_response($subscribers);
		 }catch(Exception $e) {
			 return new WP_Error( 'rest_bad_request', $e->getMessage(), array( 'status' => 400,'code' => $e->getCode()) );
		}
  }

  //To fetch subscriber by id or email
  public function get_subscriber($request){
    try {
  		$mailpoet_api = \MailPoet\API\API::MP('v1');
  		$subscriber = $mailpoet_api->GetSubscriber($request['subscriber_email_or_id']);
  		return rest_ensure_response($subscriber);
    }catch (Exception $e) {
      return new WP_Error( 'rest_bad_request', $e->getMessage(), array( 'status' => (($e->getCode() == 4)?404:400),'code' => $e->getCode()) );
    }
  }

  //To create contact list
  public function create_list($request){
    try{
    	$mailpoet_api = \MailPoet\API\API::MP('v1');
      $list = array(
        "name" => $request['name'],
        "description" => $request['description'],
      );
  		$list_added = $mailpoet_api->AddList($list);
      return rest_ensure_response($list_added);
    }catch(Exception $e) {
      return new WP_Error( 'rest_bad_request', $e->getMessage(), array( 'status' => 400,'code' => $e->getCode()) );
    }
  }

  //To add subscriber
  public function create_subscriber($request){
    try{
      $mailpoet_api = \MailPoet\API\API::MP('v1');
      $request_body = (array)json_decode($request->get_body());
      $subscriber = $mailpoet_api->addSubscriber((array)$request_body['subscriber'],(array)$request_body['list_ids'],(array)$request_body['options']);
      return rest_ensure_response($subscriber);
    }catch(Exception $e) {
      return new WP_Error( 'rest_bad_request', $e->getMessage(), array( 'status' => 400,'code' => $e->getCode()) );
    }
  }

  //To unsubscribe globally
  public function unsubscribe_subscriber($request){
    try {
      $mailpoet_api = \MailPoet\API\API::MP('v1');
      $subscriber = $mailpoet_api->unsubscribe($request['subscriber_id']);
      return rest_ensure_response($subscriber);
    }catch (Exception $e) {
      return new WP_Error( 'rest_bad_request', $e->getMessage(), array( 'status' => 400,'code' => $e->getCode()) );
    }
  }

  //To subscriber to lists
  public function subscriber_subscribetolists($request){
    try {
      $mailpoet_api = \MailPoet\API\API::MP('v1');
      $request_body = (array)json_decode($request->get_body());
      $subscriber = $mailpoet_api->subscribeToLists($request['subscriber_id'],(array)$request_body['list_ids'],(array)$request_body['options']);
      return rest_ensure_response($subscriber);
    }catch (Exception $e) {
      return new WP_Error( 'rest_bad_request', $e->getMessage(), array( 'status' => 400,'code' => $e->getCode()) );
    }
  }

  //To unsubscribe from lists
  public function subscriber_unsubscribefromlists($request){
    try {
      $mailpoet_api = \MailPoet\API\API::MP('v1');
      $request_body = (array)json_decode($request->get_body());
      $subscriber = $mailpoet_api->unsubscribeFromLists($request['subscriber_id'],(array)$request_body['list_ids']);
      return rest_ensure_response($subscriber);
    }catch (Exception $e) {
      return new WP_Error( 'rest_bad_request', $e->getMessage(), array( 'status' => 400,'code' => $e->getCode()) );
    }
  }

  //webhooks
  public function create_webhook($request){
    $entry = json_decode($request->get_body());
    $name = $entry->name;
    $url = $entry->url;
    $event = $entry->event;
    $supported_events = self::$supported_events;
    if((!empty($name)) && (!empty($url)) && (!empty($event)) && (in_array($event, self::$supported_events)) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url))){
      $args = array(
        'name' => $name,
        'url' => $url,
        'event' => $event
      );
      $post_name = "MailPoet ";
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



  //For subscriber added
  public function payload_subscriber_created($subscriber_id){
    try{
      $args = array(
         'event' => 'subscriber_added'
       );
      $webhooks = $this->get_webhook_posts($args);
    	$mailpoet_api = \MailPoet\API\API::MP('v1');
      $event_data = array(
        'event' => 'subscriber_added',
        'data' => $mailpoet_api->GetSubscriber($subscriber_id)
      );
      foreach($webhooks as $webhook){
    		$url = $webhook->url;
    		zoho_flow_execute_webhook($url, $event_data,array());
      }
    }catch (Exception $e) {}
  }

  //For subscriber updated
  public function payload_subscriber_updated($subscriber_id){
    try{
      $args = array(
		     'event' => 'subscriber_updated'
		   );
	    $webhooks = $this->get_webhook_posts($args);
			$mailpoet_api = \MailPoet\API\API::MP('v1');
	    $event_data = array(
	      'event' => 'subscriber_updated',
	      'data' => $mailpoet_api->GetSubscriber($subscriber_id)
	    );
	    foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
    }catch (Exception $e) {}
	}

  //For subscriber status changed
  public function payload_subscriber_status_changed($subscriber_id){
    try{
      $args = array(
        'event' => 'subscriber_status_changed'
      );
      $webhooks = $this->get_webhook_posts($args);
  		$mailpoet_api = \MailPoet\API\API::MP('v1');
      $event_data = array(
        'event' => 'subscriber_status_changed',
        'data' => $mailpoet_api->GetSubscriber($subscriber_id)
      );
      foreach($webhooks as $webhook){
  			$url = $webhook->url;
  			zoho_flow_execute_webhook($url, $event_data,array());
		  }
    }catch (Exception $e) {}
	}

  //default API
  public function get_system_info(){
    $system_info = parent::get_system_info();
    if( ! function_exists('get_plugin_data') ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_dir = ABSPATH . 'wp-content/plugins/mailpoet/mailpoet.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['mailpoet'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
