<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//support from
//zohoflow 2.1.0
//wp_mail_smtp 2.8.5
class Zoho_Flow_Post_SMTP extends Zoho_Flow_Service{

  //webhook events supported
  public static $supported_events = array("mail_success","mail_failure");

  //To get stats
  public function get_stats($request){
    //$PostmanState = new PostmanState();
    if(class_exists ( "PostmanState" )){
      $return_array = array(
        'successful_deliveries' => PostmanState::getInstance()->getSuccessfulDeliveries(),
        'failed_deliveries' => PostmanState::getInstance()->getFailedDeliveries(),
      );
      return $return_array;
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Stats not available', array( 'status' => 400 ) );
    }


  }

	//utilities
	private function convert_email($email_payload){
		$return_data = array();
		if(is_object($email_payload)){
			$email_array = array(
				"email" => $email_payload->getEmail(),
				"name" => $email_payload->getName()
			);
			array_push($return_data, $email_array);
		}
		else if(is_array($email_payload)){
			foreach($email_payload as $email_obj){
				$email_array = array(
					"email" => $email_obj->getEmail(),
					"name" => $email_obj->getName()
				);
				array_push($return_data, $email_array);
			}
		}
		return $return_data;
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
      $post_name = "Post SMTP ";
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
  public function payload_email_success($log, $message, $engine, $transport){
    $args = array(
       'event' => 'mail_success'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			$return_data = array(
				"subject" => $message->getSubject(),
				"body" => $message->getBody(),
				"bodyTextPart" => $message->getBodyTextPart(),
				"bodyHtmlPart" => $message->getBodyHtmlPart(),
				"headers" => $message->getHeaders(),
				"attachments" => $message->getAttachments(),
				"date" => $message->getDate(),
				"messageId" => $message->getMessageId(),
				"contentType" => $message->getContentType(),
				"charset" => $message->getCharset(),
				"boundary" => $message->getHeaders(),
				"from" =>$this->convert_email($message->getFromAddress()),
				"toRecipients" =>$this->convert_email($message->getToRecipients()),
				"ccRecipients" =>$this->convert_email($message->getCcRecipients()),
				"bccRecipients" =>$this->convert_email($message->getBccRecipients()),
				"replyTo" =>$this->convert_email($message->getReplyTo()),
			);
      $event_data = array(
        'event' => 'mail_success',
        'data' => $return_data
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  //To handle email failed payload
  public function payload_email_failure($log, $message, $engine, $transport, $error_message){
    $args = array(
       'event' => 'mail_failure'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			$return_data = array(
				"subject" => $message->getSubject(),
				"body" => $message->getBody(),
				"bodyTextPart" => $message->getBodyTextPart(),
				"bodyHtmlPart" => $message->getBodyHtmlPart(),
				"headers" => $message->getHeaders(),
				"attachments" => $message->getAttachments(),
				"date" => $message->getDate(),
				"messageId" => $message->getMessageId(),
				"contentType" => $message->getContentType(),
				"charset" => $message->getCharset(),
				"boundary" => $message->getHeaders(),
				"from" =>$this->convert_email($message->getFromAddress()),
				"toRecipients" =>$this->convert_email($message->getToRecipients()),
				"ccRecipients" =>$this->convert_email($message->getCcRecipients()),
				"bccRecipients" =>$this->convert_email($message->getBccRecipients()),
				"replyTo" =>$this->convert_email($message->getReplyTo()),
				"error_message" => $error_message,
			);
      $event_data = array(
        'event' => 'mail_failure',
        'data' => $return_data,
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/post-smtp/postman-smtp.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['post-smtp'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
