<?php

use ElementorPro\Modules\Forms\Submissions\Database\Repositories\Form_Snapshot_Repository;
class Zoho_Flow_Elementor extends Zoho_Flow_Service
{
    public function get_forms( $request ) {

        $forms = array();
        $formsnaps = Form_Snapshot_Repository::instance()->all();

        foreach ($formsnaps as $form){
            $data = array();
            $schema = $this->get_form_schema();
            if ( isset( $schema['properties']['id'] ) ) {
                $data['id'] = $form->id;
            }
            if ( isset( $schema['properties']['title'] ) ) {
                $data['title'] = $form->name;
            }
            if ( isset( $schema['properties']['post_id'] ) ) {
                $data['post id'] = $form->post_id;
            }
            array_push($forms, $data);
        }

        return rest_ensure_response( $forms );
    }

    public function get_form_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'form',
            'type'                 => 'form',
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'ID of the Elementor Form.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit'),
                    'readonly'     => true,
                ),
                'title' => array(
                    'description'  => esc_html__( 'The title of the Elementor Form.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit'),
                ),
                'post_id' => array(
                    'description'  => esc_html__( 'The Post Id of the Elementor Form.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit'),
                ),
            ),
        );

        return $schema;
    }

    public function get_fields( $request ) {
        $id = $request['form_id'];
        $ids = $this->splitPostAndFormId($id);
        $formsnaps = Form_Snapshot_Repository::instance()->find($ids[0], $ids[1], true);

        if(empty($formsnaps)){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $form_fields = $formsnaps->fields;

        if ( empty( $form_fields) ) {
            return rest_ensure_response( $form_fields );
        }
        $fields = array();
        foreach( $form_fields as $field ){
            $data = array(
                'id'=> $field['id'],
                'type'=> $field['type'],
                'label' => $field['label'],
            );
            array_push($fields, $data);
        }
        return rest_ensure_response( $fields );
    }

    public function get_webhooks($request){
        $id = $request['form_id'];
        $ids = $this->splitPostAndFormId($id);
        $formsnaps = Form_Snapshot_Repository::instance()->find($ids[0], $ids[1], true);

        if(empty($formsnaps)){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $args = array(
            'form_id' => $id
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
                'form_id' => $id,
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }

        return rest_ensure_response( $data );
    }

    public function create_webhook( $request ) {
        $id = $request['form_id'];
        $url = esc_url_raw($request['url']);
        $ids = $this->splitPostAndFormId($id);
        $formsnaps = Form_Snapshot_Repository::instance()->find($ids[0], $ids[1], true);

        if(empty($formsnaps)){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $form_title = $formsnaps->name;

        $post_id = $this->create_webhook_post($form_title, array(
            'form_id' => $id,
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'form_id' => $id,
            'url' => $url
        ) );
    }

    public function delete_webhook( $request ) {
        $id = $request['form_id'];
        $ids = $this->splitPostAndFormId($id);
        $formsnaps = Form_Snapshot_Repository::instance()->find($ids[0], $ids[1], true);

        if(empty($formsnaps)){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }

        $webhook_id = $request['webhook_id'];
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

    public function process_form_submission($record, $handler)
    {
        $form_details = $handler->get_current_form();
        $formid = $form_details['id'];
        $postid = $form_details['settings']['form_post_id'];
        $formpost_id = implode('_', array($postid, $formid));

        $args = array(
            'form_id' => $formpost_id
        );
        $webhooks = $this->get_webhook_posts($args);
        if( !empty( $webhooks ) ){
          $raw_fields = $record->get( 'fields' );
          $data = array();
          foreach ( $raw_fields as $id => $field ) {
              $type = $field['type'];
              $fieldId = $field['id'];
              if( $type === 'step'){
                  continue;
              }
              $data[$fieldId] = $field['value'];
          }
          $files = array();
      	foreach ( $webhooks as $webhook ) {
      		$url = $webhook->url;
      		zoho_flow_execute_webhook($url, $data, $files);
      	}
        }

    }

    private function splitPostAndFormId($id){
        return explode('_', $id);
    }
}
