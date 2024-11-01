<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Zoho_Flow_Everest_Forms extends Zoho_Flow_Service
{
    public function get_forms( $request ) {
        $args = array('post_type' => 'everest_form', 'posts_per_page' => -1);
        $posts = get_posts( $args );

        $data = array();

        if ( empty( $posts ) ) {
            return rest_ensure_response( $data );
        }

        foreach ( $posts as $post ) {

            $post_data = array();

            $schema = $this->get_form_schema();

            if ( isset( $schema['properties']['id'] ) ) {
                $post_data['id'] = (int) $post->ID;
            }

            if ( isset( $schema['properties']['title'] ) ) {
                $post_data['title'] = $post->post_title;
            }

            array_push($data, $post_data);
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
                    'description'  => esc_html__( 'ID of the Everest Forms form.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'title' => array(
                    'description'  => esc_html__( 'The title of the Everest Forms form.', 'zoho-flow' ),
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
        $everest_form = evf()->form->get($form_id);

        if(!$everest_form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $json = json_decode($everest_form->post_content);

        $fields = array();
        $form_fields = $json->form_fields;
        if ( empty( $form_fields ) ) {
            return rest_ensure_response( $form_fields );
        }

        foreach ( $form_fields as $field ) {
            $type = $field->type;
            $data = array(
                'id' => $field->id,
                'label' => $field->label,
                'type'=> $field->type,
                'required' => (isset($field->required)) ? true : false,
            );
            switch ($type){
                case 'date-time' :
                    $data['data_format'] = $field->date_format;
                    $data['date_localization'] = $field->date_localization;
                    break;
                case 'checkbox' :
                case 'radio' :
                case 'select' :
                    $options = array();
                    $choices = $field->choices;
                    $option = array();
                    foreach ($choices as $choice){
                        $option['label'] = $choice->label;
                        $option['value'] = $choice->value;
                        array_push($options, $option);
                    }
                    $data['options'] = $options;
                    break;
            }

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
        $everest_form = evf()->form->get($form_id );

        if(!$everest_form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $args = array(
            'form_id' => $everest_form->ID
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
                'form_id' => $everest_form->ID,
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }
        return rest_ensure_response( $data );
    }

    public function create_webhook( $request ) {
        $form_id = $request['form_id'];
        $url = esc_url_raw($request['url']);
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $everest_form = evf()->form->get($form_id );

        if(!$everest_form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $form_title = $everest_form->post_title;

        $post_id = $this->create_webhook_post($form_title, array(
            'form_id' => $everest_form->ID,
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'form_id' => $everest_form->ID,
            'url' => $url
        ) );
    }

    public function delete_webhook( $request ) {
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $everest_form = evf()->form->get($form_id);

        if(!$everest_form){
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

    public function process_form_submission($form_fields, $entry, $form_data, $entryid){
        $form_id = $form_data['id'];
        $args = array(
            'form_id' => $form_id
        );
        $webhooks = $this->get_webhook_posts($args);
        $data = array();
        if ( !empty( $webhooks ) ) {
            $entriesfields = $entry['form_fields'];
            foreach ($entriesfields as $key => $value){
                $fieldObj = $form_fields[$key];
                $type = $fieldObj['type'];
                $id = $fieldObj['id'];
                switch ($type){
                    case 'checkbox' :
                        $option = array();
                        foreach ($value as $item){
                            array_push($option, $item);
                        }
                        $data[$id] = $option;
                        break;
                }
                if($type!=='checkbox'){
                    $data[$id] = $value;
                }
            }

        	foreach ( $webhooks as $webhook ) {
        		$url = $webhook->url;
        		zoho_flow_execute_webhook($url, $data, array());
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

}
