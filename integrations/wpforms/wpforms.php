<?php

class Zoho_Flow_WPForms extends Zoho_Flow_Service
{
    public function get_forms( $request ) {
        if (!function_exists( 'wpforms' ) ) {
            return;
        }
        $forms = wpforms()->form->get('');

        $data = array();

        if ( empty( $forms ) ) {
            return rest_ensure_response( $data );
        }

        foreach ( $forms as $form ) {

	        $post_data = array();

	        $schema = $this->get_form_schema();

	        if ( isset( $schema['properties']['id'] ) ) {
	            $post_data['id'] = (int) $form->ID;
	        }

	        if ( isset( $schema['properties']['title'] ) ) {
	            $post_data['title'] = ! empty( $form->post_title ) ? $form->post_title : $form->post_name;
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
                    'description'  => esc_html__( 'ID of the form.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'title' => array(
                    'description'  => esc_html__( 'The title of the form.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit'),
                ),
            ),
        );

        return $schema;
    }

    public function get_fields( $request ) {
        if (!function_exists( 'wpforms' ) ) {
            return;
        }
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $form = wpforms()->form->get($form_id);

        if(!$form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }
        $form_data   = apply_filters( 'wpforms_frontend_form_data', wpforms_decode( $form->post_content ) );

                // 'text',
                // 'textarea',
                // 'select',
                // 'radio',
                // 'checkbox',
                // 'divider',
                // 'email',
                // 'url',
                // 'hidden',
                // 'html',
                // 'name',
                // 'password',
                // 'address',
                // 'phone',
                // 'date-time',
                // 'number',
                // 'page-break',
                // 'rating',
                // 'file-upload',
                // 'payment-single',
                // 'payment-multiple',
                // 'payment-checkbox',
                // 'payment-dropdown',
                // 'payment-credit-card',
                // 'payment-total',
        $fields = array();
        foreach($form_data['fields'] as $field){
            $type = $field['type'];
            $label = $field['label'];
            $id = $field['id'];
            $is_required = array_key_exists('required', $field) && ( $field['required'] == 1 );

            $data = array(
                'id' => $id,
                'unique_name' => $this->generate_field_name($id, $label),
                'label' => $label,
                'type' => $type,
                'is_required' => $is_required
            );

            if($type == 'date-time'){
                $format = $field['format'];
                if($format == 'date-time'){
                    $data['type'] = 'date-time';
                }
                else if($format == 'date'){
                    $data['type'] = 'date';
                }
                else if($format == 'time'){
                    $data['type'] = 'time';
                }
                $format = str_replace('-', ' ', $format);
                $format = str_replace('date', $field['date_format'], $format);
                $format = str_replace('time', $field['time_format'], $format);
                $format = zoho_flow_convert_php_java_datepattern($format);
                $data['format'] = $format;
            }
            else if(array_key_exists('format', $field)){
                $data['format'] = $field['format'];

                $format = $field['format'];
                if($type == 'name' && $format !== 'simple'){
                    $labels = explode('-', $format);
                    foreach ($labels as $nid){
                        $formatdata = array(
                            'id' => $nid,
                            'unique_name' => $nid,
                            'label' => $nid,
                            'type' => $type,
                        );
                        array_push($fields, $formatdata);
                    }
                }
            }

            if(array_key_exists('choices', $field) && is_array($field['choices'])){
                $data['choices'] = $field['choices'];
            }
            array_push($fields, $data);
        }

        return rest_ensure_response($fields);
    }

    public function get_field_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'field',
            'type'                 => 'field',
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'Unique id of the field.', 'zoho-flow' ),
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
                'is_required' => array(
                    'description'  => esc_html__( 'Whether the field is mandatory.', 'zoho-flow' ),
                    'type'         => 'boolean',
                    'context'      => array( 'view', 'edit')
                ),
                'format' => array(
                    'description'  => esc_html__( 'Format of name field', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit')
                ),
                'choices' => array(
                    'description'  => esc_html__( 'Choices in a dropdown/multiselect/checkbox/radio field', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit')
                ),
            ),
        );

        return $schema;
    }

    public function get_webhooks( $request ) {
        if (!function_exists( 'wpforms' ) ) {
            return;
        }
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $form = wpforms()->form->get($form_id);
        if(!isset($form->ID)){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $args = array(
            'form_id' => $form->ID
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
                'form_id' => $form->ID,
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }

        return rest_ensure_response( $data );
    }

    public function create_webhook( $request ) {
        if (!function_exists( 'wpforms' ) ) {
            return;
        }
        $form_id = $request['form_id'];
        $url = esc_url_raw($request['url']);
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $form = wpforms()->form->get($form_id);

        if(!$form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $form_title = ! empty( $form->post_title ) ? $form->post_title : $form->post_name;

        $post_id = $this->create_webhook_post($form_title, array(
            'form_id' => $form->ID,
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'form_id' => $form->ID,
            'url' => $url
        ) );
    }

    public function delete_webhook( $request ) {
        if (!function_exists( 'wpforms' ) ) {
            return;
        }
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $contact_form = wpforms()->form->get($form_id);

        if(!$contact_form){
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

    public function process_form_submission($fields, $entry, $form_data, $entry_id){
        $data = array();
        $files = array();

        $form_id = $form_data['id'];

        $args = array(
            'form_id' => $form_id
        );
        $webhooks = $this->get_webhook_posts($args);
        if ( !empty( $webhooks ) ) {
            foreach ($fields as $key => $field) {
                $label = $field['name'];
                $id = $field['id'];
                $unique_name = $this->generate_field_name($id, $label);
                $type = $field['type'];
                $value = $field['value'];
                if($type == 'name'){
                    $field_meta = $form_data['fields'][$id];
                    $format = $field_meta['format'];
                    if($format !== 'simple'){
                        $labels = explode('-', $format);
                        foreach ($labels as $nid){
                            $data[$nid] = $field[$nid];
                        }
                    }
                }
                else if($type == 'select'){
                    $value = $field['value_raw'];
                }
                else if($type == 'checkbox'){
                    if(isset($value)){
                        $value = explode("\n", $value);
                    }
                }
                else if($type == 'date-time'){
                    $unix = $field['unix'];
                    //$unix will be empty for only time fields
                    if(!empty($unix)){
                        $field_meta = $form_data['fields'][$id];
                        $format = $field_meta['format'];
                        $format = str_replace('-', ' ', $format);
                        $format = str_replace('date', $field_meta['date_format'], $format);
                        $format = str_replace('time', $field_meta['time_format'], $format);
                        $old_value = $value;
                        $value = date($format, $unix);
                    }
                }
                else if($type == 'file-upload'){
                    if(isset($field['value_raw']) && is_array($field['value_raw'])){
                        $file_objects = $field['value_raw'];
                        $dest_dir = $this->upload_dir();
                        for($i=0; $i < count($file_objects); $i++){
                            $file_object = $file_objects[$i];
                            $file_name_original = $file_object['file_original'];
                            $file_name = $file_object['file'];
                            $path = $file_object['value'];
                            $mime_type = $file_object['type'];

                            $uploads              = wp_upload_dir();
                            $file_base_url = trailingslashit( $uploads['baseurl'] ) . 'wpforms';
                            $stub = str_replace($file_base_url, '', $path);

                            // $unique_file_name = wp_unique_filename($dest_dir, $file_name);
                            // $dest_path = $dest_dir . '/' . $unique_file_name;
                            // copy($file_path, $dest_path);

                            $file = file_get_contents($path);

                        }
                        $value_raw = $field['value_raw'][0];
                        continue;
                    }
                }
                else if($type == 'payment-single'){
                    $value = $field['amount'];
                }
                else if($type == 'payment-multiple'){
                    $value = $field['value_choice'];
                }
                else if($type == 'payment-checkbox'){
                    $value = explode("\n", $field['value_choice']);
                }
                else if($type == 'payment-select'){
                    $value = $field['value_choice'];
                }
                else if($type == 'payment-total'){
                    $value = $field['amount'];
                }

                $data[$unique_name] = $value;

            }
            foreach ( $webhooks as $webhook ) {
                $url = $webhook->url;
                zoho_flow_execute_webhook($url, $data, $files);
            }
        }
    }

    private function generate_field_name($id, $label)
    {
        $label = preg_replace('/[^a-zA-Z0-9_]+/', ' ', $label);
        $label = trim($label);
        $label = str_replace(" ", "_", $label);
        $label = strtolower($label);
        $name = $label . '_' . $id;

        return $name;
    }
}
