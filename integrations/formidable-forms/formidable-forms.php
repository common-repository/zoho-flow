<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Zoho_Flow_Formidable_Forms extends Zoho_Flow_Service
{
    public function get_forms( $request ) {
        $forms = FrmForm::getAll();
        $data = array();
        foreach ($forms as $form) {
            array_push($data, array(
                'id' => $form->id,
                'title' => $form->name
            ));
        }
        return rest_ensure_response( $data );
    }

    public function get_form_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'form',
            'type'                 => 'form',
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'ID of the Formidable form.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'title' => array(
                    'description'  => esc_html__( 'The title of the Formidable form.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit'),
                ),
            ),
        );

        return $schema;
    }


    public function get_fields( $request ) {
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $form = FrmForm::getOne( $form_id );

        if(!$form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $all_fields = FrmField::get_all_for_form($form_id);
        $fields = array();
        foreach( $all_fields as $field ){
            $data = array(
                'id' => $field->id,
                'name' => $field->name,
                'field-key'=> $field->field_key,
                'type'=> $field->type,
                'required' => $field->required,
            );
            array_push($fields, $data);
        }
        return rest_ensure_response( $fields );
    }

    public function get_field_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'field',
            'type'                 => 'field',
            'properties'           => array(
                'name' => array(
                    'description'  => esc_html__( 'Unique name of the field.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'label' => array(
                    'description'  => esc_html__( 'Label of the field.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit')
                ),
                'type' => array(
                    'description'  => esc_html__( 'Type of the field.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit')
                ),
                'options' => array(
                    'description'  => esc_html__( 'Options of a dropdown/multiselect/checkbox/radio field.', 'zoho-flow' ),
                    'type'         => 'array',
                    'context'      => array( 'view', 'edit')
                ),
                'is_required' => array(
                    'description'  => esc_html__( 'Whether the field is mandatory.', 'zoho-flow' ),
                    'type'         => 'boolean',
                    'context'      => array( 'view', 'edit')
                ),
            ),
        );

        return $schema;
    }



    public function get_webhooks( $request ) {
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $form = FrmForm::getOne( $form_id );

        if(!$form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $args = array(
            'form_id' => $form->id
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
                'form_id' => $form->id,
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }
        return rest_ensure_response( $data );
    }

    public function create_webhook_deprecated( $request ) {
        $form_id = $request['form_id'];
        $url = esc_url_raw($request['url']);
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $form = FrmForm::getOne( $form_id );

        if(!$form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $form_title = $form->name;

        $post_id = $this->create_webhook_post($form_title, array(
            'form_id' => $form->id,
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'form_id' => $form->id,
            'url' => $url
        ) );
    }

    public function delete_webhook_deprecated( $request ) {
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $form = FrmForm::getOne( $form_id );

        if(!$form){
            return new WP_Error( 'not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

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

    public function get_form_webhook_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'webhook',
            'type'                 => 'webhook',
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'Unique id of the webhook.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'form_id' => array(
                    'description'  => esc_html__( 'Unique id of the form.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'url' => array(
                    'description'  => esc_html__( 'The webhook URL.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit')
                ),
            ),
        );

        return $schema;
    }

    public function process_form_submission($entry_id, $form_id){
        $entries = FrmEntryMeta::get_entry_meta_info($entry_id);
        $fldentryVal = new FrmEntryValues($entry_id);
        $userinfo = $fldentryVal->get_user_info();
        $args = array(
            'form_id' => $form_id
        );
        $webhooks = $this->get_webhook_posts($args);
        $data = array();
        if ( !empty( $webhooks ) ) {
            foreach ($entries as $entry){
                $field_id = $entry->field_id;
                $field_info = FrmField::getOne($field_id);
                $name = $field_info->field_key;
                $value = $entry->meta_value;
                $type= $field_info->type;
                switch ($type){
                    case 'checkbox' :
                        $matches = array();
                        preg_match_all('/".*?"/', $value, $matches);
                        foreach ($matches as $match){
                            $data[$name] = $match;
                        }
                        break;
                }
                if($type!=='checkbox'){
                    $data[$name] = $value;
                }
            }
            $data['userinfo']=$userinfo;

            $files = array();
            foreach ( $webhooks as $webhook ) {
							if(!isset($webhook->event) || empty($webhook->event)){
								$url = $webhook->url;
                zoho_flow_execute_webhook($url, $data, $files);
							}
            }
        }

	}

  private function convert_field_name($name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_]+/', ' ', $name);
        $name = trim($name);
        $name = str_replace(" ", "_", $name);
        $name = strtolower($name);

        return $name;
    }

		public static $supported_events = array("entry_created","entry_updated");

		//utilities from v2.1.0
		private function is_valid_form($form_id){
			if((!empty($form_id)) && (is_numeric($form_id))){
				$form = FrmForm::getOne( $form_id );
				if($form){
					return true;
				}
			}
			return false;
		}

	  //webhooks from v2.1.0
	  public function create_webhook($request){
	    $entry = json_decode($request->get_body());
	    $name = $entry->name;
	    $url = $entry->url;
	    $event = $entry->event;
	    $supported_events = self::$supported_events;
	    $form_id = $entry->form_id;
	    if((!empty($name)) && (!empty($url)) && (!empty($event)) && (in_array($event, self::$supported_events)) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url)) && $this->is_valid_form($form_id)){
	      $args = array(
	        'name' => $name,
	        'url' => $url,
	        'event' => $event,
	        'form_id' => $form_id
	      );
	      $post_name = "Formidable Forms ";
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

		// For new form entries from v2.1.0
		public function payload_entry_created($entry_id, $form_id){
			$args = array(
				'event' => 'entry_created',
				'form_id' => $form_id
			);
			$webhooks = $this->get_webhook_posts($args);
			if(!empty($webhooks)){
				$form_entry_values= new FrmEntryValues($entry_id);
				$field_values= $form_entry_values->get_field_values();
				$form_entry = new FrmEntry();
				$payload_array = array();
				$entry_meta = array();
				foreach ($field_values as $value) {
					$entry_meta[$value->get_field_key()] = $value->get_saved_value();
				}
				$payload_array['entry_meta'] = $entry_meta;
				$payload_array['entry_info'] = $form_entry_values->get_entry();
				$payload_array['user_info'] = $form_entry_values->get_user_info();
				$event_data = array(
					'event' => 'entry_created',
					'data' => $payload_array
				);
				foreach($webhooks as $webhook){
					$url = $webhook->url;
					zoho_flow_execute_webhook($url, $event_data,array());
				}
			}
		}

		// For new form entries from v2.1.0
		public function payload_entry_updated($entry_id, $form_id){
			$args = array(
				'event' => 'entry_updated',
				'form_id' => $form_id
			);
			$webhooks = $this->get_webhook_posts($args);
			if(!empty($webhooks)){
				$form_entry_values= new FrmEntryValues($entry_id);
				$field_values= $form_entry_values->get_field_values();
				$payload_array = array();
				$entry_meta = array();
				foreach ($field_values as $value) {
					$entry_meta[$value->get_field_key()] = $value->get_saved_value();
				}
				$payload_array['entry_meta'] = $entry_meta;
				$payload_array['entry_info'] = $form_entry_values->get_entry();
				$payload_array['user_info'] = $form_entry_values->get_user_info();
				$event_data = array(
					'event' => 'entry_updated',
					'data' => $payload_array
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
			$plugin_dir = ABSPATH . 'wp-content/plugins/formidable/formidable.php';
			if(file_exists($plugin_dir)){
				$plugin_data = get_plugin_data( $plugin_dir );
				$system_info['formidable_forms'] = $plugin_data['Version'];
			}
			return rest_ensure_response( $system_info );
		}


}
