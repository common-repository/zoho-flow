<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//support from
//zohoflow 2.0.0
//wp-travel-engine 5.7.6
class Zoho_Flow_WP_Travel_Engine extends Zoho_Flow_Service{

	//webhook events supported
	public static $supported_events = array("booking_created","enquiry_created");

	//To get trips list
  public function get_trips($request){
    $args = array(
  		'post_type'   => 'trip',
  		'numberposts' => -1,
  	);

  	$trips = get_posts( $args );
    return rest_ensure_response( $trips );
  }


  //utilities
  public function get_post($post_id){
    if((!empty($post_id)) || (is_numeric($post_id))){
      $post_data = get_post($post_id);
      $post_meta = get_post_meta($post_id);
      $post_array = array();
      foreach ($post_data as $key => $value) {
        $post_array[$key] = $value;
      }
      foreach ($post_meta as $key => $value) {
        $post_array[$key] = maybe_unserialize($value[0]);
      }
      return $post_array;
    }
    return new WP_Error( 'rest_bad_request', 'Invalid ID', array( 'status' => 404 ) );
  }

  public function is_valid_trip($trip_id){
    if((!empty($trip_id)) || (is_numeric($trip_id))){
      $post_data = get_post($trip_id);
      if($post_data->post_type == 'trip'){
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
    if((!empty($name)) && (!empty($url)) && (!empty($event)) && (in_array($event, self::$supported_events)) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url))){
      $args = array(
        'name' => $name,
        'url' => $url,
        'event' => $event,
        'trip_id' => 0
      );
      if(isset($entry->trip_id) && !empty($entry->trip_id)){
        if($this->is_valid_trip($entry->trip_id)){
          $args['trip_id'] = $entry->trip_id;
        }
        else{
          return new WP_Error( 'rest_bad_request', 'Invalid trip ID', array( 'status' => 400 ) );
        }

      }
      $post_name = "WP Travel Engine ";
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

  //payload
  //For new enquiry created
  public function payload_enquiry_created($post_id){
    $post_data = $this->get_post($post_id);
    $trip_id = $post_data['wp_travel_engine_setting']['enquiry']['pname'];
    if($post_data['post_type'] == 'enquiry'){
      $webhooks = array_merge(
        $this->get_webhook_posts(
          array(
            'event' => 'enquiry_created',
            'trip_id' => $trip_id
          )
        ),
        $this->get_webhook_posts(
          array(
            'event' => 'enquiry_created',
            'trip_id' => 0
          )
        )
      );
      $event_data = array(
        'event' => 'enquiry_created',
        'data' => $post_data
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  //For booking created
  public function payload_booking_created($post_id){
    $post_data = $this->get_post($post_id);
    $trip_id = $post_data['wp_travel_engine_booking_setting']['place_order']['tid'];
    if($post_data['post_type'] == 'booking'){
      $webhooks = array_merge(
        $this->get_webhook_posts(
          array(
            'event' => 'booking_created',
            'trip_id' => $trip_id
          )
        ),
        $this->get_webhook_posts(
          array(
            'event' => 'booking_created',
            'trip_id' => 0
          )
        )
      );
      $event_data = array(
        'event' => 'booking_created',
        'data' => $post_data
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/wp-travel-engine/wp-travel-engine.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['wp_travel_engine_plugin'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
