<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Support from
 * zohoflow 2.4.0
 * affiliatewp 2.24.0
 */
class Zoho_Flow_AffiliateWP extends Zoho_Flow_Service{

  /*
	 * webhook events supported
	 */
  public static $supported_events = array("affiliate_added","affiliate_updated","affiliate_status_updated","referral_added","referral_updated","referral_status_updated","payout_added","creative_added","creative_status_updated");

	/*
	 * To list all affiliates
	 * filters accepted
	 * returns array of affiliate objects
	 */
  public function get_all_affiliates($request){
    $affiliates_db = New Affiliate_WP_DB_Affiliates();
    return $affiliates_db->get_affiliates($request->get_params(),false);
  }

	/*
	 * To get single affiliate
	 * Always return single affiliate object in response eventhough the more than one entry match found
	 */
  public function get_affiliate($request){
    $fetch_field = $request['fetch_field'];
    $fetch_value = $request['fetch_value'];
    if(!empty($fetch_field) && !empty($fetch_value)){
      $affiliate = affwp_get_affiliate_by($fetch_field,$fetch_value);
      if(!is_wp_error($affiliate)){
        return $affiliate;
      }
      else{
        return new WP_Error( 'rest_bad_request', $affiliate->get_error_messages()[0], array( 'status' => 404 ));
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Number of parameters mismatch', array( 'status' => 404 ));
    }
  }

	/*
	 * To add affiliate
	 * returns affiliate object in response
	 */
  public function add_affiliate($request){
    $supported_fields = array(
			"status",
			"date_registered",
			"rate",
			"rate_type",
			"payment_email",
			"earnings",
			"referrals",
			"visits",
			"user_id",
			"user_name",
			"notes",
			"website_url",
      "dynamic_coupon",
      "registration_method",
      "registration_url",
      "flat_rate_basis"
		);
		$request_obj = array();
		$http_request_obj = $request->get_json_params();
		foreach ($http_request_obj as $key => $value) {
			if((!empty($value)) && (in_array($key,$supported_fields))){
				$request_obj[$key] = $value;
			}
		}
    $affiliate_id = affwp_add_affiliate($request_obj);
    if($affiliate_id){
      $affiliate = affwp_get_affiliate($affiliate_id);
      if($affiliate){
        return $affiliate;
      }
      else{
        return new WP_Error( 'rest_bad_request', 'Something went wrong', array( 'status' => 400 ));
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Affiliate not added', array( 'status' => 400 ));
    }
  }

	/*
	 * To update affiliate status
	 * returns affiliate object in response
	 */
  public function updated_affiliate_status($request){
    $affiliate_id = $request['affiliate_id'];
    $status = $request['status'];
    if(affwp_get_affiliate($affiliate_id)){
      if(affwp_set_affiliate_status($affiliate_id, $status)){
        return affwp_get_affiliate($affiliate_id);
      }
      else{
        return new WP_Error( 'rest_bad_request', 'Status not updated', array( 'status' => 400 ));
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Invalid affiliate ID', array( 'status' => 400 ));
    }
  }

	/*
	 * To list all referrals
	 * filters accepted
	 * returns array of referral objects
	 */
  public function get_all_referrals($request){
    $referrals_db = New Affiliate_WP_Referrals_DB();
    return $referrals_db->get_referrals($request->get_params(),false);
  }

	/*
	 * To get single referral
	 * Always return single referral object in response eventhough the more than one entry match found
	 */
  public function get_referral($request){
    $fetch_field = $request['fetch_field'];
    $fetch_value = $request['fetch_value'];
    if(!empty($fetch_field) && !empty($fetch_value)){
      $referral = affwp_get_referral_by($fetch_field,$fetch_value);
      if(!is_wp_error($referral)){
        return $referral;
      }
      else{
        return new WP_Error( 'rest_bad_request', $referral->get_error_messages()[0], array( 'status' => 404 ));
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Number of parameters mismatch', array( 'status' => 404 ));
    }
  }

	/*
	 * To add referral
	 * returns referral object in response
	 */
  public function add_referral($request){
    $supported_fields = array(
			"user_id",
			"user_name",
			"affiliate_id",
			"amount",
			"description",
			"products",
			"currency",
			"campaign",
			"reference",
			"context",
			"custom",
			"status",
      "order_total",
      "parent_id",
      "date",
      "type",
      "flag"
		);
		$request_obj = array();
		$http_request_obj = $request->get_json_params();
		foreach ($http_request_obj as $key => $value) {
			if((!empty($value)) && (in_array($key,$supported_fields))){
				$request_obj[$key] = $value;
			}
		}
    $referral_id = affwp_add_referral($request_obj);
    if($referral_id){
      $referral = affwp_get_referral($referral_id);
      if($referral){
        return $referral;
      }
      else{
        return new WP_Error( 'rest_bad_request', 'Something went wrong', array( 'status' => 400 ));
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Referral not added', array( 'status' => 400 ));
    }
  }

	/*
	 * To update referral status
	 * returns referral object in response
	 */
  public function updated_referral_status($request){
    $referral_id = $request['referral_id'];
    $status = $request['status'];
    if(affwp_get_referral($referral_id)){
      if(affwp_set_referral_status($referral_id, $status)){
        return affwp_get_referral($referral_id);
      }
      else{
        return new WP_Error( 'rest_bad_request', 'Status not updated', array( 'status' => 400 ));
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Invalid referral ID', array( 'status' => 400 ));
    }
  }

	/*
	 * To list all payouts
	 * filters accepted
	 * returns array of payout objects
	 */
  public function get_all_payouts($request){
    $payouts_db = New Affiliate_WP_Payouts_DB();
    return $payouts_db->get_payouts($request->get_params(),false);
  }

	/*
	 * To get single payout
	 * Always return single payout object in response eventhough the more than one entry match found
	 */
  public function get_payout($request){
    $fetch_field = $request['fetch_field'];
    $fetch_value = $request['fetch_value'];
    if(!empty($fetch_field) && !empty($fetch_value)){
      $payout = affwp_get_payout_by($fetch_field,$fetch_value);
      if(!is_wp_error($payout)){
        return $payout;
      }
      else{
        return new WP_Error( 'rest_bad_request', $payout->get_error_messages()[0], array( 'status' => 404 ));
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Number of parameters mismatch', array( 'status' => 404 ));
    }
  }

	/*
	 * To list all creatives
	 * filters accepted
	 * returns array of creative objects
	 */
  public function get_all_creatives($request){
    $payouts_db = New Affiliate_WP_Creatives_DB();
    return $payouts_db->get_creatives($request->get_params(),false);
  }

	/*
	 * To get single creative
	 * Always return single creative object in response eventhough the more than one entry match found
	 */
  public function get_creative($request){
    $fetch_field = $request['fetch_field'];
    $fetch_value = $request['fetch_value'];
    if(!empty($fetch_field) && !empty($fetch_value)){
      $creative = affwp_get_creative_by($fetch_field,$fetch_value);
      if(!is_wp_error($creative)){
        return $creative;
      }
      else{
        return new WP_Error( 'rest_bad_request', $creative->get_error_messages()[0], array( 'status' => 404 ));
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Number of parameters mismatch', array( 'status' => 404 ));
    }
  }

	/*
	 * To add creative
	 * returns creative object in response
	 */
  public function add_creative($request){
    $supported_fields = array(
			"name",
			"description",
			"url",
			"text",
			"image",
			"type",
			"status",
			"date",
			"notes",
			"start_date",
			"end_date",
			"qrcode_code_color",
      "qrcode_bg_color"
		);
		$request_obj = array();
    $request_obj['type'] = 'image';
		$http_request_obj = $request->get_json_params();
		foreach ($http_request_obj as $key => $value) {
			if((!empty($value)) && (in_array($key,$supported_fields))){
				$request_obj[$key] = $value;
			}
		}
    $creative_id = affwp_add_creative($request_obj);
    if($creative_id){
      $creative = affwp_get_creative($creative_id);
      if($creative){
        return $creative;
      }
      else{
        return new WP_Error( 'rest_bad_request', 'Something went wrong', array( 'status' => 400 ));
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Creative not added', array( 'status' => 400 ));
    }
  }

	/*
	 * To update creative
	 * returns creative object in response
	 */
  public function update_creative($request){
    $supported_fields = array(
			"name",
			"description",
			"url",
			"text",
			"image",
			"type",
			"status",
			"date",
			"notes",
			"start_date",
			"end_date",
			"qrcode_code_color",
      "qrcode_bg_color"
		);
    $creative_before_update = affwp_get_creative($request['creative_id']);
    if($creative_before_update){
      $creative_before_update = json_decode(json_encode($creative_before_update),true);
      $request_obj = array();
  		$http_request_obj = $request->get_json_params();
  		foreach ($http_request_obj as $key => $value) {
  			if((!empty($value)) && (in_array($key,$supported_fields))){
  				$request_obj[$key] = $value;
  			}
  		}
      $request_obj = wp_parse_args( $request_obj, $creative_before_update );
      $update_status = affwp_update_creative($request_obj);
      if($update_status){
        $creative = affwp_get_creative($request['creative_id']);
        if($creative){
          return $creative;
        }
        else{
          return new WP_Error( 'rest_bad_request', 'Something went wrong', array( 'status' => 400 ));
        }
      }
      else{
        return new WP_Error( 'rest_bad_request', 'Creative not updated', array( 'status' => 400 ));
      }
    }
  }

	/*
	 * To list all visits
	 * filters accepted
	 * returns array of visit objects
	 */
  public function get_all_visits($request){
    $visits_db = New Affiliate_WP_Visits_DB();
    return $visits_db->get_visits($request->get_params(),false);
  }

	/*
	 * To get single visit
	 * Always return single visit object in response eventhough the more than one entry match found
	 */
  public function get_visit($request){
    $fetch_field = $request['fetch_field'];
    $fetch_value = $request['fetch_value'];
    if(!empty($fetch_field) && !empty($fetch_value)){
      $visit = affwp_get_visit_by($fetch_field,$fetch_value);
      if(!is_wp_error($visit)){
        return $visit;
      }
      else{
        return new WP_Error( 'rest_bad_request', $visit->get_error_messages()[0], array( 'status' => 404 ));
      }
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Number of parameters mismatch', array( 'status' => 404 ));
    }
  }

	/*
	 * To list all groups
	 * filters accepted
	 * returns array of group objects
	 * can be used for both affiliate group and creative category
	 */
  public function get_all_groups($request){
    $group_ids = affiliate_wp()->groups->get_groups($request->get_params(),false);
    $group_array = array();
    foreach ($group_ids as $value) {
      $group_obj = array(
        'group_id' => $value,
        'title' => affiliate_wp()->groups->get_group_title($value),
        'type' => affiliate_wp()->groups->get_group_type($value)
      );
      array_push($group_array, $group_obj);
    }
    return $group_array;
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
			$post_name = "AffiliateWP ";
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
   * @param $affiliate_id  The new affiliate ID.
   * @param $args The arguments passed to the insert method.
   */
  public function payload_affiliate_added($affiliate_id, $args){
		$args = array(
       'event' => 'affiliate_added'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			$affiliate = json_decode(json_encode(affwp_get_affiliate($affiliate_id)), true);
      $event_data = array(
        'event' => 'affiliate_added',
        'data' => $affiliate
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $affiliate Updated affiliate object.
   * @param $updated   Whether the update was successful.
   */
  public function payload_affiliate_updated($affiliate, $updated){
		$args = array(
       'event' => 'affiliate_updated'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
      $event_data = array(
        'event' => 'affiliate_updated',
        'data' => $affiliate
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $affiliate_id  The new affiliate ID.
   * @param $status     The new affiliate status. Optional.
   * @param $old_status The old affiliate status.
   */
  public function payload_affiliate_status_updated($affiliate_id, $new_status, $old_status){
		$args = array(
       'event' => 'affiliate_status_updated'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			$affiliate = json_decode(json_encode(affwp_get_affiliate($affiliate_id)), true);
			$affiliate['new_status'] = $new_status;
			$affiliate['old_status'] = $old_status;
      $event_data = array(
        'event' => 'affiliate_status_updated',
        'data' => $affiliate
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $referral_id Referral ID.
   */
  public function payload_referral_added($referral_id){
		$args = array(
       'event' => 'referral_added'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			$referral = json_decode(json_encode(affwp_get_referral($referral_id)), true);
      $event_data = array(
        'event' => 'referral_added',
        'data' => $referral
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $updated_referral Updated referral object.
	 * @param $referral         Original referral object.
	 * @param $updated          Whether the referral was successfully updated.
	 */
  public function payload_referral_updated($updated_referral, $referral, $updated){
		$args = array(
       'event' => 'referral_updated'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
      $event_data = array(
        'event' => 'referral_updated',
        'data' => $updated_referral
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $referral_id Referral ID.
   * @param $new_status  New referral status.
	 * @param $old_status  Old referral status.
   */
  public function payload_referral_status_updated($referral_id, $new_status, $old_status){
		$args = array(
       'event' => 'referral_status_updated'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			$referral = json_decode(json_encode(affwp_get_referral($referral_id)), true);
			$referral['new_status'] = $new_status;
			$referral['old_status'] = $old_status;
      $event_data = array(
        'event' => 'referral_status_updated',
        'data' => $referral
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $payout_id  The new payout ID.
   */
  public function payload_payout_added($payout_id){
		$args = array(
       'event' => 'payout_added'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			$payout = json_decode(json_encode(affwp_get_payout($payout_id)), true);
      $event_data = array(
        'event' => 'payout_added',
        'data' => $payout
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $creative_id  The new creative ID.
   * @param $args The arguments passed to the insert method.
   */
  public function payload_creative_added($creative_id, $args){
		$args = array(
       'event' => 'creative_added'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			$creative = json_decode(json_encode(affwp_get_creative($creative_id)), true);
      $event_data = array(
        'event' => 'creative_added',
        'data' => $creative
      );
      foreach($webhooks as $webhook){
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $event_data,array());
      }
    }
  }

  /*
   * @param $creative_id Creative ID.
   * @param $new_status  New referral status.
	 * @param $old_status  Old referral status.
   */
  public function payload_creative_status_updated($creative_id, $new_status, $old_status){
		$args = array(
       'event' => 'creative_status_updated'
     );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
			$creative = json_decode(json_encode(affwp_get_creative($creative_id)), true);
			$creative['new_status'] = $new_status;
			$creative['old_status'] = $old_status;
      $event_data = array(
        'event' => 'creative_status_updated',
        'data' => $creative
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/affiliate-wp/affiliate-wp.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['affiliatewp'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }

}
