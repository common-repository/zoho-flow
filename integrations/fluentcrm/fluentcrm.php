<?php

use FluentCrm\App\Models\Subscriber;

class Zoho_Flow_FluentCRM extends Zoho_Flow_Service
{
    public function get_forms( $request ) {
        $formApi = fluentFormApi('forms');
        $formObj = $formApi->forms();

        return rest_ensure_response( $formObj);
    }
    public function get_contacts( $request ) {

         $contactApi = FluentCrmApi('contacts');
         $contacts = $contactApi->get();

         return rest_ensure_response( $contacts);
    }

    public function fetch_contact( $request ) {
        $contactApi = FluentCrmApi('contacts');
        if(empty($request['id']) || !ctype_digit($request['id'])){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Contact ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $contact = $contactApi->getContact($request['id']);

        return rest_ensure_response( $contact );
    }

    public function create_contact( $request ) {
        if(empty(json_decode($request->get_body())->contact->email)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Email should not be empty.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $contactApi = FluentCrmApi('contacts');
        $subscriber = $contactApi->getContact(json_decode($request->get_body())->contact->email);

        if(!empty($subscriber)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Contact already exists.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        /*
         * Update/Insert a contact
         * You can create or update a contact in a single call
         */
        $request_body = $request->get_body();
        $input = json_decode($request_body, true);
        $input = $input['contact'];

        $keys = array_keys($input);
        $data = array();
        foreach ($input as $key => $value){
            if(in_array($key, $keys)){
                $data[$key] = $value;
            }
        }

        $contact = $contactApi->createOrUpdate($data);

        // send a double opt-in email if the status is pending
        if($contact && $contact->status == 'pending') {
            $contact->sendDoubleOptinEmail();
        }
        return rest_ensure_response( $contact );
    }

    public function update_contact( $request ) {
        if(empty(json_decode($request->get_body())->contact->email)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Email should not be empty.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $contactApi = FluentCrmApi('contacts');
        $subscriber = $contactApi->getContact(json_decode($request->get_body())->contact->email);

        if(empty($subscriber)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Contact does not exists.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $request_body = $request->get_body();
        $input = json_decode($request_body, true);
        $input = $input['contact'];

        $keys = array_keys($input);
        $data = array();
        foreach ($input as $key => $value){
            if(in_array($key, $keys)){
                $data[$key] = $value;
            }
        }

        $contact = $contactApi->createOrUpdate($data, true);

        // send a double opt-in email if the status is pending
        if($contact && $contact->status == 'pending') {
            $contact->sendDoubleOptinEmail();
        }
        return rest_ensure_response( $contact );
    }

    public function fetch_tags( $request ) {
        $tagApi = FluentCrmApi('tags');
        $tags = $tagApi->all();
        return rest_ensure_response( $tags );
    }

    public function fetch_lists( $request ) {
        $listApi = FluentCrmApi('lists');
        $lists = $listApi->all();
        return rest_ensure_response( $lists );
    }

    public function create_tags( $request ) {
        $tagApi = FluentCrmApi('tags');

        $request_body = json_decode($request->get_body());
        if(empty($request_body)){
            return new WP_Error( 'rest_not_found', esc_html__( 'Invalid input given.', 'zoho-flow' ), array( 'status' => 404 ) );
        }
        $tags = $request_body->tags;
        $arr = array();
        foreach ($tags as $tag){
            $newarr = array(
                "title" => $tag->title,
                "slug" => $tag->slug
            );
            array_push($arr, $newarr);
        }
        $importedTags = $tagApi->importBulk($arr);
        return rest_ensure_response( $importedTags );
    }

    public function create_lists( $request ) {
        $listApi = FluentCrmApi('lists');

        $request_body = json_decode($request->get_body());
        if(empty($request_body)){
            return new WP_Error( 'rest_not_found', esc_html__( 'Invalid input given.', 'zoho-flow' ), array( 'status' => 404 ) );
        }
        $lists = $request_body->lists;
        $arr = array();
        foreach ($lists as $list){
            $newarr = array(
                "title" => $list->title,
                "slug" => $list->slug
            );
            array_push($arr, $newarr);
        }

        $importedLists = $listApi->importBulk($arr);
        return rest_ensure_response( $importedLists );
    }

    public function get_webhooks($request){
        $type = $request['type'];
        $args = array(
            'type' => $type
        );
        $webhooks = $this->get_webhook_posts($args);

        if ( empty( $webhooks ) ) {
            return rest_ensure_response( $webhooks );
        }

        $data = array();

        foreach ( $webhooks as $webhook ) {
            $webhook = array(
                'plugin_service' => $this->get_service_name(),
                'id' => $webhook->ID,
                'type' => $webhook->type,
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }

        return rest_ensure_response( $data );
    }

    public function create_webhook( $request ) {
        $type = $request['type'];
        $url = esc_url_raw($request['url']);

        $post_id = $this->create_webhook_post($type, array(
            'type' => $type,
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'type' => $type,
            'url' => $url
        ) );
    }

    public function delete_webhook( $request ) {
        $webhook_id = $request['webhook_id'];
        if(!ctype_digit($webhook_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The webhook ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $result = $this->delete_webhook_post($webhook_id);
        if(is_wp_error($result)){
            return $result;
        }
        return rest_ensure_response(array(
            'plugin_service' => $this->get_service_name(),
            'id' => $result->ID
        ));
        return rest_ensure_response($result);
    }

    public function process_form_submission($subscriber)
    {
        $args = array(
            'type' => "contact_created"
        );
        $webhooks = $this->get_webhook_posts($args);

        $files = array();
    	foreach ( $webhooks as $webhook ) {
    		$url = $webhook->url;
	        zoho_flow_execute_webhook($url, $subscriber, $files);
    	}
    }

    public function process_contact_updated($subscriber){
      $args = array(
          'type' => "contact_updated"
      );
      $webhooks = $this->get_webhook_posts($args);

      $files = array();
      foreach ( $webhooks as $webhook ) {
        $url = $webhook->url;
        zoho_flow_execute_webhook($url, $subscriber, $files);
      }
    }
	//default API
    public function get_system_info(){
	$system_info = parent::get_system_info();
	if( ! function_exists('get_plugin_data') ){
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	$plugin_dir = ABSPATH . 'wp-content/plugins/fluentcrm/fluentcrm.php';
	if(file_exists($plugin_dir)){
		$plugin_data = get_plugin_data( $plugin_dir );
		$system_info['fluentcrm_plugin'] = $plugin_data['Version'];
	}
	return rest_ensure_response( $system_info );
    }

}
