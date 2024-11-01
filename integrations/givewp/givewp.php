<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//support from
//zohoflow 1.5.0
//givewp 2.31.0
class Zoho_Flow_GiveWP extends Zoho_Flow_Service{

	//webhook events supported
  public static $supported_events = array("donation_added","donor_added");

	//To get forms list
  public function get_forms($request){
    if (!class_exists('Give_Forms_Query')) {
      require_once ABSPATH . 'wp-content/plugins/give/includes/forms/class-give-forms-query.php';
    }
    $givewp_forms_object = new Give_Forms_Query();
    $forms_list = $givewp_forms_object->get_forms();
    return rest_ensure_response( $forms_list );
  }

	//To fetch donor details
	public function get_donor($request){
		$donor_id = $request['id'];
		$donor_email = $request['email'];
		if(!empty($donor_id)){
			if(is_numeric($donor_id)){
				$donor_array = $this->get_donor_by_id_or_email($donor_id);
				if(is_wp_error($donor_array)){
					return new WP_Error( 'rest_bad_request', 'Donor not found', array( 'status' => 404 ) );
				}
				return $donor_array;
			}
			else{
				return new WP_Error( 'rest_bad_request', 'Donor ID should be numeric', array( 'status' => 404 ) );
			}
		}
		else if(!empty($donor_email)){
			if(filter_var($donor_email, FILTER_VALIDATE_EMAIL)){
				$donor_array = $this->get_donor_by_id_or_email($donor_email);
				if(is_wp_error($donor_array)){
					return new WP_Error( 'rest_bad_request', 'Donor not found', array( 'status' => 404 ) );
				}
				return $donor_array;
			}
			else{
				return new WP_Error( 'rest_bad_request', 'Email address validation failed', array( 'status' => 404 ) );
			}
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Parameters invalid / missing', array( 'status' => 400 ) );
		}
		if((empty($donor_id)) || (!is_numeric($donor_id))){
			return false;
		}
		if (!class_exists('Give_Donor')) {
			require_once ABSPATH . 'wp-content/plugins/give/includes/class-give-donor.php';
		}
	}

	//To add notes to donor
	public function add_donor_note($request){
		$donor_id = $request['donor_id'];
		$request_body = json_decode($request->get_body());
		$note = $request_body->note;
		if($this->is_valid_givewp_donor($donor_id)){
			if(!empty($note)){
				if (!class_exists('Give_Donor')) {
					require_once ABSPATH . 'wp-content/plugins/give/includes/class-give-donor.php';
				}
				$donor_object = new Give_Donor($donor_id);
				$note_formated = $donor_object->add_note($note);
				if($note_formated){
					$return_array = array(
						'donor_id' => $donor_id,
						'note' => $note,
						'note_formated' => $note_formated
					);
					return $return_array;
				}
				else{
					return new WP_Error( 'rest_bad_request', 'Unable to add note', array( 'status' => 400 ) );
				}
			}
			else{
				return new WP_Error( 'rest_bad_request', 'Invalid note', array( 'status' => 400 ) );
			}
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Invalid donor ID', array( 'status' => 400 ) );
		}

  }

	//utilities
	public function is_valid_givewp_form($form_id){
		if((empty($form_id)) || (!is_numeric($form_id))){
			return false;
		}
		if (!class_exists('Give_Donate_Form')) {
      require_once ABSPATH . 'wp-content/plugins/give/includes/class-give-donate-form.php';
    }
		$givewp_form_object = new Give_Donate_Form($form_id);
		if($givewp_form_object->get_ID() == 0){
			return false;
		}
		return true;
	}

	public function is_valid_givewp_donor($donor_id){
		if((empty($donor_id)) || (!is_numeric($donor_id))){
			return false;
		}
		if (!class_exists('Give_Donor')) {
      require_once ABSPATH . 'wp-content/plugins/give/includes/class-give-donor.php';
    }
		$givewp_donor_object = new Give_Donor($donor_id);
		if($givewp_donor_object->id == 0){
			return false;
		}
		return true;
	}

	public function get_donor_by_id_or_email($id_or_email){
		$givewp_donor_object = new Give_Donor($id_or_email);
		if($givewp_donor_object->id == 0){
			return new WP_Error( 'rest_bad_request', 'Donor not found', array( 'status' => 404 ) );
		}
		return $givewp_donor_object;
	}

	//webhooks
  public function create_webhook($request){
    $entry = json_decode($request->get_body());
    $name = $entry->name;
    $url = $entry->url;
    $event = $entry->event;
    $form_id = $entry->form_id;
		if(($event == 'donation_added') && (!$this->is_valid_givewp_form($form_id))){
			return new WP_Error( 'rest_bad_request', 'Invalid form ID', array( 'status' => 400 ) );
		}
    $supported_events = self::$supported_events;
    if((!empty($name)) && (!empty($url)) && (!empty($event)) && (in_array($event, self::$supported_events)) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url))){
      $args = array(
        'name' => $name,
        'url' => $url,
        'event' => $event
      );
			if($event == 'donation_added'){
				$args['form_id'] = $form_id;
			}
      $post_name = "GiveWP ";
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
	//For new donors
	public function payload_donar_added($created, $donor_details){
		if(is_numeric($created)){
			$donor_details['donor_id'] = $created;
			$args = array(
	      'event' => 'donor_added'
	    );
	    $webhooks = $this->get_webhook_posts($args);
	    $event_data = array(
	      'event' => 'donor_added',
	      'data' => $donor_details
	    );
	    foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//For donation form submission
	public function payload_donation_form_complete($form_id, $payment_id, $payment_meta){
		$payment_meta['_give_payment_id'] = $payment_id;
		$args = array(
      'event' => 'donation_added',
			'form_id' => $form_id
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'donation_added',
      'data' => $payment_meta
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/give/give.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['givewp_plugin'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }

}
