<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//support from
//zohoflow 1.5.0
//paid-memberships-pro 2.12.2
class Zoho_Flow_Paid_Memberships_Pro extends Zoho_Flow_Service{

	//webhook events supported
	public static $supported_events = array("order_added","order_updated","membership_level_changed");

	//To get member fields
	public function get_fields($request){
		$user_fields = pmpro_get_user_fields();
		$field_array = array();
		foreach ($user_fields as $group => $group_array) {
			foreach ($group_array as $field_obj) {
				$field_array_object = array();
				foreach ($field_obj as $key => $value) {
					if($key != 'save_function'){
						$field_array_object[$key] = $value;
					}
				}
				array_push($field_array,$field_array_object);
			}
		}
		return rest_ensure_response($field_array);
	}

	//To get membership levels
	public function get_levels($request){
		$level_list = pmpro_getAllLevels();
		$level_array = array();
		foreach ($level_list as $position => $level_object) {
			array_push($level_array,$level_object);
		}
		return rest_ensure_response($level_array);
	}

	//To update the membershipship level of a member(User)
	public function change_user_membership_level($request){
		$user_id = $request['user_id'];
		$level_id = $request['level_id'];
		if(!($this->is_valid_user($user_id))){
			return new WP_Error( 'rest_bad_request', 'Invalid user ID', array( 'status' => 400 ) );
		}
		//0 is to cancel the membership
		if((!$this->is_valid_level($level_id)) && ($level_id != 0)){
			return new WP_Error( 'rest_bad_request', 'Invalid Membership level ID', array( 'status' => 400 ) );
		}
		$changestatus = pmpro_changeMembershipLevel($level_id, $user_id);
		if(empty($changestatus)){
			return new WP_Error( 'rest_bad_request', 'User already in same membership level', array( 'status' => 400 ) );
		}
		else if(!$changestatus){
			return new WP_Error( 'rest_bad_request', 'Membership level not updated', array( 'status' => 400 ) );
		}
		return rest_ensure_response(array('message' => 'success'));
	}

	//To Fetch member (WP User). It will fetch even if the user exists in WordPress but is not a Paid Memberships Pro member
	public function get_user($request){
		$search_field = $request['search_field'];
		$search_value = $request['search_value'];
		if((empty($search_field)) || (empty($search_value))){
			return new WP_Error( 'rest_bad_request', 'Parameters missing', array( 'status' => 400 ) );
		}
		if(($search_field == 'ID') && ($this->is_valid_user($search_value))){
			return rest_ensure_response($this->get_member_data($search_value));
		}
		else if(($search_field == 'email') && (filter_var($search_value, FILTER_VALIDATE_EMAIL)) && (get_user_by('email', $search_value))){
			return rest_ensure_response($this->get_member_data(get_user_by('email', $search_value)->ID));
		}
		else{
			return new WP_Error( 'rest_bad_request', 'User not found', array( 'status' => 404 ) );
		}
	}


	//utilities
	public function is_valid_user($user_id){
		if((empty($user_id)) || (!is_numeric($user_id))){
			return false;
		}
		else if(get_user_by('id', $user_id)){
			return true;
		}
		return false;
	}

	public function is_valid_level($level_id){
		if((empty($level_id)) || (!is_numeric($level_id))){
			return false;
		}
		else if(pmpro_getLevel($level_id)){
			return true;
		}
		return false;
	}

	public function get_order_data(MemberOrder $order_data){
		$order_array = array(
			'id' => $order_data->__get('id'),
			'code' => $order_data->__get('code'),
			'user_id' => $order_data->__get('user_id'),
			'membership_id' => $order_data->__get('membership_id'),
			'session_id' => $order_data->__get('session_id'),
			'paypal_token' => $order_data->__get('paypal_token'),
			'billing' => $order_data->__get('billing'),
			'subtotal' => $order_data->__get('subtotal'),
			'tax' => $order_data->__get('tax'),
			'couponamount' => $order_data->__get('couponamount'),
			'certificate_id' => $order_data->__get('certificate_id'),
			'certificateamount' => $order_data->__get('certificateamount'),
			'total' => $order_data->__get('total'),
			'payment_type' => $order_data->__get('payment_type'),
			'cardtype' => $order_data->__get('cardtype'),
			'accountnumber' => $order_data->__get('accountnumber'),
			'expirationmonth' => $order_data->__get('expirationmonth'),
			'expirationyear' => $order_data->__get('expirationyear'),
			'status' => $order_data->__get('status'),
			'gateway' => $order_data->__get('gateway'),
			'gateway_environment' => $order_data->__get('gateway_environment'),
			'payment_transaction_id' => $order_data->__get('payment_transaction_id'),
			'subscription_transaction_id' => $order_data->__get('subscription_transaction_id'),
			'timestamp' => $order_data->__get('timestamp'),
			'affiliate_id' => $order_data->__get('affiliate_id'),
			'affiliate_subid' => $order_data->__get('affiliate_subid'),
			'notes' => $order_data->__get('notes'),
			'checkout_id' => $order_data->__get('checkout_id'),
			'other_properties' => array(
				'Gateway' => $order_data->__get('Gateway'),
				'FirstName' => $order_data->__get('FirstName'),
				'LastName' => $order_data->__get('LastName'),
				'Address1' => $order_data->__get('Address1'),
				'Email' => $order_data->__get('Email'),
				'ExpirationDate' => $order_data->__get('ExpirationDate'),
				'ExpirationDate_YdashM' => $order_data->__get('ExpirationDate_YdashM'),
				'original_status' => $order_data->__get('original_status'),
				'datetime' => $order_data->__get('datetime')
			)
		);
		return $order_array;
	}

	public function get_member_data($user_id){
		$member_details = array();
		$user_data = get_user_by('id', $user_id);
		$user_meta_data = get_user_meta($user_id);
		$pmpro_member_fields = pmpro_get_user_fields_for_profile($user_id,true);
		foreach ($pmpro_member_fields as $group => $group_array) {
			foreach ($group_array as $field_obj) {
				$field_id = $field_obj->id;
				if(array_key_exists($field_id,$user_meta_data)){
					$member_details[$field_id] = maybe_unserialize($user_meta_data[$field_id][0]);
				}
			}
		}
		$member_details['id'] = $user_data->ID;
		$member_details['user_login'] = $user_data->user_login;
		$member_details['user_nicename'] = $user_data->user_nicename;
		$member_details['user_email'] = $user_data->user_email;
		$member_details['user_url'] = $user_data->user_url;
		$member_details['user_registered'] = $user_data->user_registered;
		$member_details['user_status'] = $user_data->user_status;
		$member_details['display_name'] = $user_data->display_name;
		$member_details['first_name'] = $user_meta_data['first_name'][0];
		$member_details['last_name'] = $user_meta_data['last_name'][0];
		$member_details['description'] = $user_meta_data['description'][0];
		return $member_details;
	}

	public function get_membership_level($level_id){
		$level_details = pmpro_getLevel($level_id);
		return $level_details;

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
      $post_name = "Paid Memberships Pro ";
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
	//For new member order
	public function payload_order_added(MemberOrder $order_data){
		$payload_array = array();
		$payload_array['order'] = $this->get_order_data($order_data);
		if(!empty($order_data->__get('user_id'))){
			$payload_array['member'] = $this->get_member_data($order_data->__get('user_id'));
		}
		if(!empty($order_data->__get('membership_id'))){
			$payload_array['membership'] = $this->get_membership_level($order_data->__get('membership_id'));
		}
		$args = array(
      'event' => 'order_added'
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'order_added',
      'data' => $payload_array
    );
    foreach($webhooks as $webhook){
			$url = $webhook->url;
			zoho_flow_execute_webhook($url, $event_data,array());
		}
  }

	//For member order update (including membership change)
	public function payload_order_updated( MemberOrder $order_data){
		$payload_array = array();
		$payload_array['order'] = $this->get_order_data($order_data);
		if(!empty($order_data->__get('user_id'))){
			$payload_array['member'] = $this->get_member_data($order_data->__get('user_id'));
		}
		if(!empty($order_data->__get('membership_id'))){
			$payload_array['membership'] = $this->get_membership_level($order_data->__get('membership_id'));
		}
		$args = array(
      'event' => 'order_updated'
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'order_updated',
      'data' => $payload_array
    );
    foreach($webhooks as $webhook){
			$url = $webhook->url;
			zoho_flow_execute_webhook($url, $event_data,array());
		}
  }

	//For memberhship level change
	public function payload_membership_level_changed($level_id, $user_id, $cancel_level){
		$payload_array = array();
		if(!empty($level_id)){
			$payload_array['membership'] = $this->get_membership_level($level_id);
		}
		if(!empty($user_id)){
			$payload_array['member'] = $this->get_member_data($user_id);
		}
		$args = array(
      'event' => 'membership_level_changed'
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'membership_level_changed',
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/paid-memberships-pro/paid-memberships-pro.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['paid_memberships_pro'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
