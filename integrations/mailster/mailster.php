<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//support from
//zohoflow 1.5.0
//mailster 3.3.7
class Zoho_Flow_Mailster extends Zoho_Flow_Service
{
	//webhook events supported
  public static $supported_events = array("subscriber_added","subscriber_updated","subscriber_tag","subscriber_list_added");

	//APIs
	//to get list of campaigns
  public function get_campaigns(){
    $args = $defaults = array(
			'post_type'              => 'newsletter',
      'posts_per_page'         => 500,
      'orderby'                => 'modified',
			'order'                  => 'DESC',
		);
    $campaigns_query = new WP_Query( $args );
    return rest_ensure_response($campaigns_query->{'posts'});
  }

	//to get list of custom fields
  public function get_custom_fields(){
    if (!class_exists('mailster')) {
      require_once ABSPATH . 'wp-content/plugins/mailster/classes/mailster.class.php';
    }
    return rest_ensure_response(mailster()->get_custom_fields());
  }

	//to get subscriber list
  public function get_lists(){
    if (!class_exists('MailsterLists')) {
      require_once ABSPATH . 'wp-content/plugins/mailster/classes/lists.class.php';
    }
    $mailster_lists = new MailsterLists();

    return rest_ensure_response($mailster_lists->get());
  }

	//to get supported list of status
  public function get_statuses(){
    if (!class_exists('MailsterSubscribers')) {
      require_once ABSPATH . 'wp-content/plugins/mailster/classes/subscribers.class.php';
    }
    $mailster_subscribers = new MailsterSubscribers();
    $status_list = $mailster_subscribers->get_status();
    $status_array = array();
    foreach ($status_list as $index=>$status) {
      $status_obj = array(
        'id' => $index,
        'name' => $status
      );
      array_push($status_array,$status_obj);
    }
    return rest_ensure_response($status_array);
  }

	//to get subscriber details by id. Used in most of the action's output and payload handling functions
  private function get_subscriber_by_id($id){
    if((empty($id)) || (!is_numeric($id))){
      return new WP_Error( 'rest_bad_request', 'Subscriber not found', array( 'status' => 404 ) );
    }
    if (!class_exists('MailsterSubscribers')) {
      require_once ABSPATH . 'wp-content/plugins/mailster/classes/subscribers.class.php';
    }
    $mailster_subscribers = new MailsterSubscribers();
    $subscriber_data = (array)$mailster_subscribers->get($id,true);
    if(empty($subscriber_data['ID'])){
      return new WP_Error( 'rest_bad_request', 'Subscriber not found', array( 'status' => 404 ) );
    }
    $subscriber_list = array('list'=>(array)$mailster_subscribers->get_lists($subscriber_data['ID']));
    $subscriber_tags = array('tag'=>(array)$mailster_subscribers->get_tags($subscriber_data['ID']));
    $subscriber_details = array_merge($subscriber_data,array_merge($subscriber_list,$subscriber_tags));
    return $subscriber_details;
  }

	//to fetch subscriber either by id, email or wordpres user id. list not supported yet.
  public function get_subscriber($request){
    $search_field = $request['search_field'];
    $search_value = $request['search_value'];
    if((empty($search_field)) || (empty($search_value))){
      return new WP_Error( 'rest_bad_request', 'Parameters missing', array( 'status' => 400 ) );
    }
    if (!class_exists('MailsterSubscribers')) {
      require_once ABSPATH . 'wp-content/plugins/mailster/classes/subscribers.class.php';
    }
    $mailster_subscribers = new MailsterSubscribers();
    $subscriber_data = array();
    if(($search_field == 'ID') and (is_numeric($search_value))){
      $subscriber_data = (array)$mailster_subscribers->get($search_value,true);
      if(empty($subscriber_data['ID'])){
        return new WP_Error( 'rest_bad_request', 'Subscriber not found', array( 'status' => 404 ) );
      }
    }
    else if(($search_field == 'email') and (filter_var($search_value, FILTER_VALIDATE_EMAIL))){
      $subscriber_data = (array)$mailster_subscribers->get_by_mail($search_value,true);
      if(empty($subscriber_data['ID'])){
        return new WP_Error( 'rest_bad_request', 'Subscriber not found', array( 'status' => 404 ) );
      }
    }
    else if(($search_field == 'wpid') and (is_numeric($search_value))){
      $subscriber_data = (array)$mailster_subscribers->get_by_wpid($search_value,true);
      if(empty($subscriber_data['ID'])){
        return new WP_Error( 'rest_bad_request', 'Subscriber not found', array( 'status' => 404 ) );
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Validation failed', array( 'status' => 400 ) );
    }
    $subscriber_list = array('list'=>(array)$mailster_subscribers->get_lists($subscriber_data['ID']));
    $subscriber_tags = array('tag'=>(array)$mailster_subscribers->get_tags($subscriber_data['ID']));
    $subscriber_details = array_merge($subscriber_data,array_merge($subscriber_list,$subscriber_tags));
    return rest_ensure_response($subscriber_details);
  }

	//to add new subscriber to mailster. **overwriting subscriber will trigger subscriber_added event.
  public function add_subscriber($request){
    $entry = json_decode($request->get_body());
    $overwrite = false;
    $subscriber_notification = true;
		//Passing ID will update the subscriber.
    if(!empty($entry->ID)){
      $entry->ID = null;
    }
    if(isset($request['overwrite']) && (!empty($request['overwrite']))){
      $overwrite = $request['overwrite'];
    }
    if(isset($request['subscriber_notification']) && (!empty($request['subscriber_notification']))){
      $subscriber_notification = $request['subscriber_notification'];
    }
    if (!class_exists('MailsterSubscribers')) {
      require_once ABSPATH . 'wp-content/plugins/mailster/classes/subscribers.class.php';
    }
    $mailster_subscribers = new MailsterSubscribers();
    $subscriber_id = $mailster_subscribers->add($entry, $overwrite, false, $subscriber_notification);
    if(is_wp_error($subscriber_id)){
      $errors = $subscriber_id->get_error_messages();
			//One subscriber can be overwrited once in minute, to avoid failures in flow end returned 412 for rerun.
			if( strstr( $errors[0], 'Please wait') !== false ){
        return new WP_Error( 'rest_bad_request', $errors[0], array( 'status' => 412, 'errors' => $errors ) );
      }
      return new WP_Error( 'rest_bad_request', $errors[0], array( 'status' => 400, 'errors' => $errors ) );
    }
    $subscriber_details = $this->get_subscriber_by_id($subscriber_id);
    if(is_wp_error($subscriber_details)){
      $errors = $subscriber_details->get_error_messages();
      return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
    }
    return rest_ensure_response($subscriber_details);
  }

	//to update subscriber by it's ID
  public function update_subscriber($request){
    $entry = json_decode($request->get_body());
    $subscriber_id = $request['subscriber_id'];
    if(!empty($subscriber_id)){
      $entry->ID = $subscriber_id;
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Subscriber not found', array( 'status' => 404 ) );
    }
    $overwrite = true;
    $merge = false;
    $subscriber_notification = false;
    if(isset($request['overwrite']) && (!empty($request['overwrite']))){
      $overwrite = $request['overwrite'];
    }
    if(isset($request['subscriber_notification']) && (!empty($request['subscriber_notification']))){
      $subscriber_notification = $request['subscriber_notification'];
    }
    if(isset($request['merge']) && (!empty($request['merge']))){
      $merge = $request['merge'];
    }
    if (!class_exists('MailsterSubscribers')) {
      require_once ABSPATH . 'wp-content/plugins/mailster/classes/subscribers.class.php';
    }
    $mailster_subscribers = new MailsterSubscribers();
    $subscriber_id = $mailster_subscribers->update($entry, $overwrite, $merge, $subscriber_notification);
    if(is_wp_error($subscriber_id)){
      $errors = $subscriber_id->get_error_messages();
      if(str_contains($errors[0],'Please wait')){
        return new WP_Error( 'rest_bad_request', $errors[0], array( 'status' => 412, 'errors' => $errors ) );
      }
      return new WP_Error( 'rest_bad_request', $errors[0], array( 'status' => 400, 'errors' => $errors ) );
    }
    $subscriber_details = $this->get_subscriber_by_id($subscriber_id);
    if(is_wp_error($subscriber_details)){
      $errors = $subscriber_details->get_error_messages();
      return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
    }
    return rest_ensure_response($subscriber_details);
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
      $post_name = "Mailster " .$entry->event;
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
	//For Subscriber added trigger
  public function payload_add_subscriber($subscriber_id){
    $args = array(
      'event' => 'subscriber_added'
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'subscriber_added',
      'data' => $this->get_subscriber_by_id($subscriber_id)
    );
    foreach($webhooks as $webhook){
			$url = $webhook->url;
			zoho_flow_execute_webhook($url, $event_data,array());
		}
  }

	//For Subscriber added or updated trigger
  public function payload_update_subscriber($subscriber_id){
    $args = array(
      'event' => 'subscriber_updated'
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'subscriber_updated',
      'data' => $this->get_subscriber_by_id($subscriber_id)
    );
    foreach($webhooks as $webhook){
			$url = $webhook->url;
			zoho_flow_execute_webhook($url, $event_data,array());
		}
  }

	//For Subscriber tagged trigger
  public function payload_subscriber_tag($tag_id, $subscriber_id, $name){
    $args = array(
      'event' => 'subscriber_tag'
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'subscriber_tag',
      'data' => $this->get_subscriber_by_id($subscriber_id),
      'tag_id' => $tag_id,
      'tag_name' => $name
    );
    foreach($webhooks as $webhook){
      $url = $webhook->url;
      zoho_flow_execute_webhook($url, $event_data,array());
    }
  }

	//For Subscriber added to list trigger
  public function payload_add_subscriber_to_list($list_id, $subscriber_id, $added){
    $args = array(
      'event' => 'subscriber_list_added'
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'subscriber_list_added',
      'data' => $this->get_subscriber_by_id($subscriber_id),
      'list_id' => $list_id
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
		$plugin_dir = ABSPATH . 'wp-content/plugins/mailster/mailster.php';
		if(file_exists($plugin_dir)){
			$plugin_data = get_plugin_data( $plugin_dir );
			$system_info['mailster_plugin'] = $plugin_data['Version'];
		}
		return rest_ensure_response( $system_info );
	}

}
