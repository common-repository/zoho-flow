<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use FluentSupport\App\Models\Product;
use FluentSupport\App\Models\Customer;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\MailBox;

//support from
//zohoflow 2.0.0
//fluent-support 1.7.3
class Zoho_Flow_Fluent_Support extends Zoho_Flow_Service{

  //webhook events supported
  public static $supported_events = array("ticket_created","tickets_moved","ticket_closed","ticket_reopened","ticket_agent_changed","customer_created");

	// To list available products
  public function get_all_products($request){
    if(class_exists('FluentSupport\App\Models\Product')){
      return rest_ensure_response(Product::paginate());
    }
    else{
      return new WP_Error( 'rest_bad_request', 'You are not authorized to access this resource', array( 'status' => 403 ) );
    }
  }

	// To list available customers
  public function get_all_customers($request){
    if(class_exists('FluentSupport\App\Models\Customer')){
      return rest_ensure_response(Customer::paginate());
    }
    else{
      return new WP_Error( 'rest_bad_request', 'You are not authorized to access this resource', array( 'status' => 403 ) );
    }
  }

	// To list available tickets
  public function get_all_tickets($request){
    if(class_exists('FluentSupport\App\Models\Ticket')){
      $ticketsQuery = Ticket::with([
            'customer' => function ($query) {
                $query->select(['first_name', 'last_name', 'id', 'email']);
            }, 'agent' => function ($query) {
                $query->select(['first_name', 'last_name', 'id', 'email']);
            }
        ])->orderBy('id', 'DESC');
        $tickets = $ticketsQuery->paginate();
      return rest_ensure_response($tickets);
    }
    else{
      return new WP_Error( 'rest_bad_request', 'You are not authorized to access this resource', array( 'status' => 403 ) );
    }

  }

	// To list available agents
  public function get_all_agents($request){
    if(class_exists('FluentSupport\App\Models\Agent')){
      return rest_ensure_response(Agent::paginate());
    }
    else{
      return new WP_Error( 'rest_bad_request', 'You are not authorized to access this resource', array( 'status' => 403 ) );
    }
  }

	//To list available mailbox
	public function get_all_mailbox($request){
	if(class_exists('FluentSupport\App\Models\MailBox')){
		return rest_ensure_response(MailBox::paginate());
	}
	else{
		return new WP_Error( 'rest_bad_request', 'You are not authorized to access this resource', array( 'status' => 403 ) );
	}
}

	// To fetch ticket
  public function get_ticket($request){
		$fetch_field = $request['fetch_field']; //Supported fields: id, slug, title, content
		$fetch_value = $request['fetch_value'];
		if((!empty($fetch_field)) && (!empty($fetch_value))){
        $fs_ticket = Ticket::where($fetch_field, $fetch_value);
        return rest_ensure_response($fs_ticket->get()->toarray());
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Empty field or value', array( 'status' => 400 ) );
		}
	}

	// To fetch customer
  public function get_customer($request){
		$fetch_field = $request['fetch_field']; //Supported fields: id, email, first_name, last_name, address_line_1, address_line_2, country
		$fetch_value = $request['fetch_value'];
		if((!empty($fetch_field)) && (!empty($fetch_value))){
        $fs_customer = Customer::where($fetch_field, $fetch_value);
        return rest_ensure_response($fs_customer->get()->toarray());
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Empty field or value', array( 'status' => 400 ) );
		}
	}

	// To fetch agent
	public function get_agent($request){
		$fetch_field = $request['fetch_field']; //Supported fields: id, email, first_name, last_name, address_line_1, address_line_2, country
		$fetch_value = $request['fetch_value'];
		if((!empty($fetch_field)) && (!empty($fetch_value))){
				$fs_customer = Agent::where($fetch_field, $fetch_value);
				return rest_ensure_response($fs_customer->get()->toarray());
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Empty field or value', array( 'status' => 400 ) );
		}
	}

	// To create customer
  public function customer_create($request){
    $request_body = (array)json_decode($request->get_body());
    $fs_customer = Customer::create($request_body);
    return rest_ensure_response($fs_customer);
  }

	// To create ticket
  public function ticket_create($request){
    $request_body = (array)json_decode($request->get_body());
    $fs_customer = Ticket::create($request_body);
    return rest_ensure_response($fs_customer);
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
      $post_name = "Fluent Support ";
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

  //Webhook payload
  //For ticket created
  public function payload_ticket_created($ticket, $customer){
    $return_data = array(
      'ticket' => $ticket->toArray(),
      'customer' => $customer->toArray()
    );
 		$args = array(
      'event' => 'ticket_created'
     );
     $webhooks = $this->get_webhook_posts($args);
     $event_data = array(
       'event' => 'ticket_created',
       'data' => $return_data
     );
     foreach($webhooks as $webhook){
 			$url = $webhook->url;
 			zoho_flow_execute_webhook($url, $event_data,array());
 		}
   }

  //For customer created
  //**Not working**
  public function payload_customer_created($customer){
    $return_data = array(
       'customer' => $customer->toArray()
     );
		$args = array(
     'event' => 'customer_created'
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'customer_created',
      'data' => $return_data
    );
    foreach($webhooks as $webhook){
			$url = $webhook->url;
			zoho_flow_execute_webhook($url, $event_data,array());
		}
  }

  //For Ticket agent changed
  public function payload_ticket_agent_changed($ticket, $person){
	  $return_data = array(
	     'ticket' => $ticket->toArray(),
	     'assigner' => $person->toArray()
	   );
    $args = array(
     'event' => 'ticket_agent_changed'
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'ticket_agent_changed',
      'data' => $return_data
    );
    foreach($webhooks as $webhook){
      $url = $webhook->url;
      zoho_flow_execute_webhook($url, $event_data,array());
    }
  }

  //For Ticket moved
 	public function payload_tickets_moved($tickets, $oldBox, $newBox){
	   $ticket = Ticket::findOrFail($tickets['ticket_ids'][0]);
	   $ticket_array = array();
	   foreach ($tickets['ticket_ids'] as $ticket_id) {
	     array_push($ticket_array,Ticket::findOrFail($ticket_id));
	   }
	   $return_data = array(
	     'tickets' => $ticket_array,
	     'oldBox' => $oldBox->toArray(),
	     'newBox' => $newBox->toArray()
	   );
	    $args = array(
	     'event' => 'tickets_moved'
	    );
	    $webhooks = $this->get_webhook_posts($args);
	    $event_data = array(
	      'event' => 'tickets_moved',
	      'data' => $return_data
	    );
	    foreach($webhooks as $webhook){
	      $url = $webhook->url;
	      zoho_flow_execute_webhook($url, $event_data,array());
	    }
	  }

	//For ticket closed
  public function payload_ticket_closed($ticket, $person){
    $return_data = array(
      'ticket' => $ticket->toArray(),
      'person' => $person->toArray()
    );
     $args = array(
      'event' => 'ticket_closed'
     );
     $webhooks = $this->get_webhook_posts($args);
     $event_data = array(
       'event' => 'ticket_closed',
       'data' => $return_data
     );
     foreach($webhooks as $webhook){
       $url = $webhook->url;
       zoho_flow_execute_webhook($url, $event_data,array());
     }
   }

	//For ticket reopen
  public function payload_ticket_reopened($ticket, $person){
   $return_data = array(
     'ticket' => $ticket->toArray(),
     'person' => $person->toArray()
   );
    $args = array(
     'event' => 'ticket_reopened'
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'ticket_reopened',
      'data' => $return_data
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/fluent-support/fluent-support.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['fluent_support'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
