<?php
class Zoho_Flow_Ninja_Forms extends Zoho_Flow_Service
{
    public function get_forms( $request ) {
        $forms = Ninja_Forms()->form()->get_forms();

        $data = array();
        if ( empty( $forms ) ) {
            return rest_ensure_response( $data );
        }
        $schema = $this->get_form_schema( $request );

        foreach ( $forms as $form ) {

            if ( isset( $schema['properties']['id'] ) ) {
                $post_data['id'] = $form->get_id();
            }

            if ( isset( $schema['properties']['title'] ) ) {
                $post_data['title'] = $form->get_setting( 'title' );
            }
            if ( isset( $schema['properties']['created_at'] ) ) {
                $post_data['created_at'] = $form->get_setting( 'created_at' );
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
                    'description'  => esc_html__( 'ID of the Ninja Form.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'title' => array(
                    'description'  => esc_html__( 'The title of the Ninja Form.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit'),
                ),
                'created_at' => array(
                    'description' => esc_html__("Created Date of the Ninja Form", "zoho-flow"),
                    'type'        => 'string',
                    'context'     => array('view'),
                    'readonly'    => true,
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
        $form_fields = Ninja_Forms()->form( $form_id )->get_fields();

        if(empty($form_fields)){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        if ( empty( $form_fields) ) {
            return rest_ensure_response( $form_fields );
        }

        $fields = array();
        foreach( $form_fields as $field ){
            $data = array(
                'label' => $field->get_setting('label'),
                'key'=> $field->get_setting('key'),
                'type'=> $field->get_setting('type'),
                'required' => $field->get_setting('required'),
            );
            array_push($fields, $data);
        }
        return rest_ensure_response( $fields );
    }

    public function get_webhooks($request){
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $ninja_form = Ninja_Forms()->form($form_id);

        if(empty($ninja_form->get_fields())){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $args = array(
            'form_id' => $ninja_form->get_id()
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
                'form_id' => $ninja_form->get_id(),
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
        $ninja_form = Ninja_Forms()->form($form_id);;

        if(empty($ninja_form->get_fields())){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $form_title = $ninja_form->get()->get_setting('title');

        $post_id = $this->create_webhook_post($form_title, array(
            'form_id' => $ninja_form->get_id(),
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'form_id' => $ninja_form->get_id(),
            'url' => $url
        ) );
    }

    public function delete_webhook( $request ) {
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $ninja_form = Ninja_Forms()->form( $form_id );

        if(empty($ninja_form->get_fields())){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
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

    public function process_form_submission($ninja_form)
    {
        $id = $ninja_form['form_id'];
        $args = array(
            'form_id' => $id
        );
        $webhooks = $this->get_webhook_posts($args);
        $data = $this->construct_form_data($ninja_form);
        $files = array();
    	foreach ( $webhooks as $webhook ) {
    		$url = $webhook->url;
	        zoho_flow_execute_webhook($url, $data, $files);
    	}
    }

    public function construct_form_data($ninja_form){
        $data = array();
        foreach ($ninja_form['fields'] as $field){
            foreach ($field as $item){
                $type = $field['type'];
                if(isset($item['key']) || isset($item['value']) || isset($item['label'])) {
                    $value = $item['value'];
                        switch ($type){
                            case 'checkbox':
                                $value = ($value == 0) ? false : true;
                                break;
                            case 'listimage':
                                $imageoptions = $item['image_options'];
                                $option = array();
                                foreach ($imageoptions as $totalimages){
                                    $mainlabel = $totalimages['label'];
                                    foreach ($value as $selectedimages){
                                        $selectedlabel = explode("." , $selectedimages)[0];
                                        if($mainlabel==$selectedlabel){
                                            array_push($option, $totalimages['image']);
                                        }
                                    }
                                }
                                $data[$item['key']] = $option;
                                break;
                        }
                        if($type!=='listimage'){
                            $data[$item['key']] = $value;
                        }
                }
            }
        }
        return $data;
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