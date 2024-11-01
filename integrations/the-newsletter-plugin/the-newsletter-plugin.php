<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Support from
 * zohoflow 2.4.0
 * the-newsletter-plugin 8.2.4
 */
class Zoho_Flow_The_Newsletter_Plugin extends Zoho_Flow_Service{

  /*
	 * webhook events supported
	 */
  public static $supported_events = array("subscriber_added","subscriber_confirmed","subscriber_unsubscribed","subscriber_resubscribed");

  /*
   * Returns custom field lists
   * Both public and private custom fields will be returned, No filter available to control
   */
  public function get_all_custom_fields($request){
		$module_obj = New NewsletterModule('profile');
		return $module_obj->get_customfields();
  }

  /*
   * Returns subscriber lists
   * Both public and private lists will be returned, No filter available to control
   */
	public function get_all_lists($request){
		$module_obj = New NewsletterModule('users');
		return $module_obj->get_lists();
  }

  /*
   * Fetch subscriber by ID or Email address
   */
	public function get_subscriber($request){
		$id_or_email = $request['id_or_email'];
		if(!empty($id_or_email)){
			$module_obj = New NewsletterModule('users');
			$subscriber = $module_obj->get_user($id_or_email);
			if($subscriber){
				return $this->process_subscriber($subscriber);
			}
			else{
				return new WP_Error( 'rest_bad_request', 'Subscriber not found', array( 'status' => 404 ));
			}
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Number of parameters mismatch', array( 'status' => 404 ));
		}
  }

  /*
   * Returns latest 20 subscribers
   */
  public function list_subscribers($request){
    return TNP::subscribers(array());
  }

  /*
   * Adds subscriber
   */
	public function add_subscriber($request){
		$subscription_obj = New TNP_Subscription();
		if(!empty($request['email'])){
			$subscription_obj->data->email = $request['email'];
		}
		if(!empty($request['name'])){
			$subscription_obj->data->name = $request['name'];
		}
		if(!empty($request['surname'])){
			$subscription_obj->data->surname = $request['surname'];
		}
		if(!empty($request['sex'])){
			$subscription_obj->data->sex = $request['sex'];
		}
		if(!empty($request['language'])){
			$subscription_obj->data->language = $request['language'];
		}
		if(!empty($request['referrer'])){
			$subscription_obj->data->referrer = $request['referrer'];
		}
		if(!empty($request['http_referer'])){
			$subscription_obj->data->http_referer = $request['http_referer'];
		}
		if(!empty($request['ip'])){
			$subscription_obj->data->ip = $request['ip'];
		}
		if(!empty($request['country'])){
			$subscription_obj->data->country = $request['country'];
		}
		if(!empty($request['region'])){
			$subscription_obj->data->region = $request['region'];
		}
		if(!empty($request['city'])){
			$subscription_obj->data->city = $request['city'];
		}
		if(!empty($request['wp_user_id'])){
			$subscription_obj->data->wp_user_id = $request['wp_user_id'];
		}
		if(!empty($request['lists'])){
			$subscription_obj->data->add_lists($request['lists']);
		}
		if(!empty($request['profiles'])){
			$subscription_obj->data->profiles = $request['profiles'];
		}

		if(!empty($request['spamcheck'])){
			$subscription_obj->spamcheck = $request['spamcheck'];
		}
		if(!empty($request['optin'])){
			$subscription_obj->optin = $request['optin'];
		}
		if(!empty($request['if_exists'])){
			$subscription_obj->if_exists = $request['if_exists'];
		}
		if(!empty($request['send_emails'])){
			$subscription_obj->send_emails = $request['send_emails'];
		}
		if(!empty($request['welcome_email_id'])){
			$subscription_obj->welcome_email_id = $request['welcome_email_id'];
		}
		if(!empty($request['welcome_page_id'])){
			$subscription_obj->welcome_page_id = $request['welcome_page_id'];
		}
		$subscriber_obj = New NewsletterSubscription();
		$subscriber = $subscriber_obj->subscribe2($subscription_obj);
		if(is_wp_error($subscriber)){
			return new WP_Error( 'rest_bad_request', $subscriber->get_error_messages()[0], array( 'status' => 400 ));
		}
		return $this->process_subscriber($subscriber);
	}

  /*
   * Unsubscribe subscriber
   */
  public function unsubscribe_subscriber($request){
    $subscriber = array(
      "email" => $request['email']
    );
    $unsubscribe = TNP::unsubscribe($subscriber);
    if(is_wp_error($unsubscribe)){
			return new WP_Error( 'rest_bad_request', $unsubscribe->get_error_messages()[0], array( 'status' => 400 ));
		}
    return array(
      "message" => "success"
    );
  }

  /*
   * @param $subscriber subscriber object.
   * Proccess the subscriber object and returns API returnable array
   */
	private function process_subscriber($subscriber){
		$subscription_obj = New NewsletterSubscription();
		$user_obj = New TNP_User();
		$subscriber_array = json_decode(json_encode($subscriber),true);
		if($subscriber_array['status']){
			$subscriber_array['status'] = $user_obj->get_status_label($subscriber_array['status']);
		}
		$lists_array = array();

		foreach ($subscriber_array as $key => $value) {
			if(substr( $key, 0, 5 ) === "list_"){
				if($value){
					$list_id = str_replace('list_','',$key);
					array_push($lists_array,$subscription_obj->get_list($list_id));
				}
				unset($subscriber_array[$key]);
			}
		}
		$subscriber_array["lists"] = $lists_array;
		return $subscriber_array;
	}

  /*
   * To create webhook
   * The events available in $supported_events only accepted
   * returns webhook ID, should be used to delete the webhook
   */
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
     $post_name = "The Newsletter Plugin ";
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

 /*
  * To delete webhook
  * Webhook ID returned from webhook create event should be used. Use minimum user scope.
  * returns success response object
  */
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

 /*
  * @param $subscriber subscriber object.
  * Works only for the events done by the subscriber
  */
 public function payload_subscriber_added($subscriber){
    $args = array(
       'event' => 'subscriber_added'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
      $event_data = array(
        'event' => 'subscriber_added',
        'data' => $this->process_subscriber($subscriber)
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $subscriber subscriber object.
   * Works only for the events done by the subscriber
   */
  public function payload_subscriber_confirmed($subscriber){
    $args = array(
       'event' => 'subscriber_confirmed'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
      $event_data = array(
        'event' => 'subscriber_confirmed',
        'data' => $this->process_subscriber($subscriber)
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $subscriber subscriber object.
   * Works only for the events done by the subscriber
   */
  public function payload_subscriber_unsubscribed($subscriber){
    $args = array(
       'event' => 'subscriber_unsubscribed'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
      $event_data = array(
        'event' => 'subscriber_unsubscribed',
        'data' => $this->process_subscriber($subscriber)
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $subscriber subscriber object.
   * Works only for the events done by the subscriber
   */
  public function payload_subscriber_resubscribed($subscriber){
    $args = array(
       'event' => 'subscriber_resubscribed'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
      $event_data = array(
        'event' => 'subscriber_resubscribed',
        'data' => $this->process_subscriber($subscriber)
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }


  /*
	 * default API
	 * used to get user and system info.
	 */
  public function get_system_info(){
    $system_info = parent::get_system_info();
    if( ! function_exists('get_plugin_data') ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_dir = ABSPATH . 'wp-content/plugins/newsletter/plugin.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['the_newsletter_plugin'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
