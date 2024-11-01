<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
	support from
	zohoflow 2.1.0
	akismet 5.3
*/
class Zoho_Flow_Akismet extends Zoho_Flow_Service{

  //webhook events supported
  public static $supported_events = array("spam_comment_caught","spam_comment_submitted","nonspam_comment_submitted");

  //To recheck the comment status
  public function recheck_comment($request){
    $comment_id = $request['comment_id'];
    $recheck_status = Akismet::recheck_comment($comment_id);
    if(is_wp_error($recheck_status)){
      return new WP_Error( 'rest_bad_request', $recheck_status->get_error_messages()[0], array( 'status' => 400 ) );
    }
    return $this->get_comment_with_meta($comment_id);
  }

  //Utilities
  private function get_comment_with_meta($comment_id){
    if((!empty($comment_id)) && (is_numeric($comment_id))){
      $comment_data = get_comment($comment_id);
      $comment_meta = get_comment_meta($comment_id);
      $comment_meta_unserialized = array();
      foreach ($comment_meta as $key => $value) {
        $comment_meta_unserialized[$key] = maybe_unserialize($value[0]);
      }
      $comment_data->meta = $comment_meta_unserialized;
      return $comment_data;
    }
  }

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
      $post_name = "Akismet ";
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

  //For new comment with spam status marked by akismet. Will not trigger for Recheck case
  public function payload_spam_comment($comment_id, $comment_approved, $commentdata){
    if( ($comment_approved == 'spam') && (!empty($commentdata['akismet_result'])) && ($commentdata['akismet_result'] == 'true')){
      try{
        $args = array(
           'event' => 'spam_comment_caught'
         );
        $webhooks = $this->get_webhook_posts($args);
        $event_data = array(
          'event' => 'spam_comment_caught',
          'data' => $this->get_comment_with_meta($comment_id)
        );
        foreach($webhooks as $webhook){
      		$url = $webhook->url;
      		zoho_flow_execute_webhook($url, $event_data,array());
        }
      }catch (Exception $e) {}
    }
  }

  //For manual spam mark
  public function payload_submit_spam($comment_id, $comment_message){
    $args = array(
       'event' => 'spam_comment_submitted'
     );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'spam_comment_submitted',
      'data' => $this->get_comment_with_meta($comment_id)
    );
    foreach($webhooks as $webhook){
      $url = $webhook->url;
      zoho_flow_execute_webhook($url, $event_data,array());
    }
  }

  //For manual non spam mark
  public function payload_submit_nonspam($comment_id, $comment_message){
    $args = array(
       'event' => 'nonspam_comment_submitted'
     );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'nonspam_comment_submitted',
      'data' => $this->get_comment_with_meta($comment_id)
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/akismet/akismet.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['akismet'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
