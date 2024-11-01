<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Zoho_Flow_Planso_Forms extends Zoho_Flow_Service
{
    public function get_forms($request) {
        $members = $this->fetch_forms($request);
        if(!empty($members)){
            return rest_ensure_response($members);
        } else {
            return rest_ensure_response(array());
        }
        return rest_ensure_response(array());
    }
    
    public function get_fields($request) {
        $members = $this->fetch_forms($request);

        if(!empty($members)){
            $fields_json = $members[0]->post_content;
            $flds_arr = json_decode($fields_json, true);
            $flds_arr = $flds_arr['fields'];
            $fields = array();
            foreach ($flds_arr as $arr) {
                foreach($arr as $field)
                {
                    if(in_array($field['type'] , array("p","h","div","submit"))) {
                        continue;
                    }
                    $list = array();
                    $list['id'] = $field['id'];
                    $list['name'] = $field['name'];
                    $list['label'] = $field['label'];
                    $list['type'] = $field['type'];
                    $list['readonly'] = $field['readonly'];
                    $list['required'] = $field['required'];
                    array_push($fields, $list);
                }
            }
            return rest_ensure_response($fields);
        }else {
            return rest_ensure_response(array());
        }
        return rest_ensure_response(array());
    }
    
    public function fetch_forms($request){
        $query_param=$request->get_query_params();
        
        $query_args = array(
            'post_type'         =>   'psfb',
            'posts_per_page'    =>   -1,
            'orderby'           =>   'date',
            'order'             =>   'DESC',
            'no_paging'	 => 	true,
            'post_status'       => 'draft'
        );
        if(sizeof($query_param)>0){
            foreach(array_keys($query_param) as $key){
                $query_args[$key]=$query_param[$key];
                if($key=='id'||$key=='ID')
                    $query_args['p']=$query_param[$key];
            }
        }
        if($request['form_id'] && ctype_digit($request['form_id'])){
            $query_args['p']=$request['form_id'];
        }
        $query_results = new WP_Query( $query_args );
        if(empty($query_results->posts)){
            return rest_ensure_response(array());
        }
        if(is_object($query_results->posts)){
            foreach($query_results->posts as $item){
                $item->{'post_meta'}=get_post_meta($item->{'ID'});
                $tax_terms = get_post_taxonomies($item->{'ID'});
                foreach($tax_terms as $term){
                    $item->$term = get_the_terms($item->{'ID'}, $term);
                }
            }
            return rest_ensure_response(array($query_results->posts));
        }
        else{
            foreach($query_results->posts as $item){
                $item->{'post_meta'}=get_post_meta($item->{'ID'});
                $tax_terms = get_post_taxonomies($item->{'ID'});
                foreach($tax_terms as $term){
                    $item->$term = get_the_terms($item->{'ID'}, $term);
                }
            }
            return $query_results->posts;
        }
    }
    
    public function get_webhooks( $request ) {
        $form = $this->fetch_forms($request);
        $form = $form[0];
        if(!$form){
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
        $url = esc_url_raw($request['url']);
        $form = $this->fetch_forms( $request, 'form');
        $form = $form[0];
        
        if(!$form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }
        
        $form_title = $form->post_title;
        
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
    
    public function delete_webhook($request) {
        $webhook_id = $request['webhook_id'];
        if(!ctype_digit($webhook_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The post ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
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
    
    public function process_form_submission($form) {
        $data = $form['mail_replace'];
        $zattachments = $form['zattachments'];
        if($form['has_attachments'] === true){
            $fields = $this->get_fields(array('form_id'=>$form['id']));
            $fields = $fields->data;
            foreach ($fields as $field){
                $type = $field['type'];
                $name = $field['name'];
                if(($type === 'file' || $type === 'multifile') && array_key_exists($name, $zattachments)){
                    $list = array();
                    $files = $zattachments[$name];
                    foreach($files as $file){
                        $pos = strripos($file, '/');
                        $filename = substr($file,$pos+1);
                        if($type === 'multifile'){
                            array_push($list, $filename);
                        } else{
                            $data[$name] = $filename;
                        }
                    }
                    if($type === 'multifile'){
                        $data[$name] = $list;
                    }
                }
            }
        }
   
        $id = $form['id'];
        $args = array(
            'form_id' => $id
        );
        $webhooks = $this->get_webhook_posts($args);
        $files = array();
        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            zoho_flow_execute_webhook($url, $data, $files);
        }
        return rest_ensure_response($data);
    }
}
