<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use FluentMail\App\Models\Logger;

//support from
//zohoflow 2.1.0
//fluentsmtp 2.2.6
class Zoho_Flow_FluentSMTP extends Zoho_Flow_Service{

  //webhook events supported
  public static $supported_events = array("mail_failed");

	//To resend emails
  public function resend_mail_from_logger($request){
    $log_id = $request['log_id'];
    $fsmtp_logger_obj = new Logger();
    return $fsmtp_logger_obj->resendEmailFromLog($log_id);
  }

	//To get all stats available in logger
  public function get_overall_stats($request){
    $fsmtp_logger_obj = new Logger();
    return $fsmtp_logger_obj->getStats();
  }

	//To get stats from a given period of time
	public function get_periodic_stats($request){
		$start_date = $request['start_date'];
		$end_date = $request['end_date'];
    $fsmtp_logger_obj = new Logger();
		$stats_array = array(
			"sent" => $fsmtp_logger_obj->getTotalCountStat('sent',$start_date, $end_date),
			"failed" => $fsmtp_logger_obj->getTotalCountStat('failed',$start_date, $end_date)
		);
    return $stats_array;
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
      $post_name = "FluentSMTP ";
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

	//To handle failed emails
  public function payload_send_failure($logId, $handler){
    $args = array(
       'event' => 'mail_failed'
     );
    $webhooks = $this->get_webhook_posts($args);
		$payload_array = array(
			"log_id" => $logId,
			"mail" => $handler->getPhpMailer(),
			"mail_addresses"=> array(
				"To" => $handler->getPhpMailer()->getToAddresses(),
				"Cc" => $handler->getPhpMailer()->getCcAddresses(),
				"Bcc" => $handler->getPhpMailer()->getBccAddresses(),
				"ReplyTo" => $handler->getPhpMailer()->getReplyToAddresses(),
				"AllRecipients" => $handler->getPhpMailer()->getAllRecipientAddresses(),
			),
		);
    $event_data = array(
      'event' => 'mail_failed',
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/fluent-smtp/fluent-smtp.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['fluentsmtp'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
  }
