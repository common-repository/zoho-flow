<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPMailSMTP\Reports\Reports;
use WPMailSMTP\Reports\Emails\Summary as SummaryReportEmail;

//support from
//zohoflow 2.1.0
//wp_mail_smtp 3.10.0
class Zoho_Flow_WPMailSMTP extends Zoho_Flow_Service{

  //webhook events supported
  public static $supported_events = array("mail_send");

	//To get summary and stats
  public function get_summary($request){
    $reports = new Reports();
    $summary = new SummaryReportEmail();
    $return_array = array(
      'total_count' => $reports->get_total_emails_sent(),
      'current_week_count' => $reports->get_total_weekly_emails_sent('now'),
      'last_week_count' => $reports->get_total_weekly_emails_sent('previous'),
      'preview_link' => $summary->get_preview_link(),
      'html_content' => $summary->get_content()
    );
    return $return_array;
  }

	//To send summary email to admin
  public function send_summary_to_admin($request){
    $summary = new SummaryReportEmail();
		$return_array = array(
			"message" => "Email sent successfully."
		);
    return $return_array;
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
      $post_name = "WP Mail SMTP ";
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

	//To handle email send payload
	public function payload_send_after($mailer, $mailcatcher){
    $args = array(
       'event' => 'mail_send'
     );
    $webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$return_array= array(
				"mail" => $mailcatcher,
				"mail_addresses"=> array(
					"To" => $mailcatcher->getToAddresses(),
					"Cc" => $mailcatcher->getCcAddresses(),
					"Bcc" => $mailcatcher->getBccAddresses(),
					"ReplyTo" => $mailcatcher->getReplyToAddresses(),
					"AllRecipients" => $mailcatcher->getAllRecipientAddresses(),
				),
			);
	    $event_data = array(
	      'event' => 'mail_send',
	      'data' => $return_array
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/wp-mail-smtp/wp_mail_smtp.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['wp-mail-smtp'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
