<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $gen_array;
$gen_array = array('post' => array('post_type','post_template','post_status','post_format','post_category','post_taxonomy','post'), 'user' => array('current_user','current_user_role','user_form','user_role'));

class Zoho_Flow_Advanced_Custom_Fields extends Zoho_Flow_Service
{
    public function get_field_groups( $request ) {
        $query_args = array(
            'post_type'         =>   'acf-field-group',
            'posts_per_page'    =>   -1,
            'orderby'           =>   'date',
            'order'             =>   'DESC',
            'no_paging'			=>   true,
            'post_status'       =>   'publish'
        );

        return rest_ensure_response($this->handle_queries($query_args, false));
    }

    public function get_field_group_by_id ( $request ){
        $field_group_id = $request['field_group_id '];
        $query_args = array(
            'post_type'         =>   'acf-field-group',
            'posts_per_page'    =>   -1,
            'orderby'           =>   'date',
            'order'             =>   'DESC',
            'no_paging'			=>   true,
            'post_status'       =>   'publish',
            'p'                =>  $field_group_id
        );

        return $this->handle_queries($query_args, true);
    }

    public function get_all_fields( $request ){

        $query_args = array(
            'post_type'         =>   'acf-field',
            'posts_per_page'    =>   -1,
            'orderby'           =>   'date',
            'order'             =>   'DESC',
            'no_paging'			=>   true,
            'post_status'       =>   'publish'
        );

        $result = $this->handle_queries($query_args, false);
        return rest_ensure_response( $this->get_field_object($result));
    }

    public function get_fields_by_group( $request ) {
        $query_param=$request->get_query_params();

        $query_args = array(
            'post_type'         =>   'acf-field',
            'posts_per_page'    =>   -1,
            'orderby'           =>   'date',
            'order'             =>   'DESC',
            'no_paging'			=>   true,
            'post_status'       =>   'publish',
            'post_parent'       =>   $request['post_parent']
        );

        if(sizeof($query_param)>0){
            foreach(array_keys($query_param) as $key){
                $query_args[$key]=$query_param[$key];
                if($key=='id'||$key=='ID')
                    $query_args['p']=$query_param[$key];
            }
        }

        $result = $this->handle_queries($query_args, false);
        return rest_ensure_response( $this->get_field_object($result));
    }

    private function get_field_object($result){
        $data = array();
        if(is_array($result)){
            foreach ($result as $new){
                $field =  (is_object($new)) ? get_field_object($new->post_name) : get_field_object($new);
                array_push($data, $field);
            }
        } else {
            $field = get_field_object($result);
            array_push($data, $field);
        }
        return $data;
    }

    private function handle_queries( $query_args , $isSingle) {
        $query_results = new WP_Query( $query_args );

        if(empty($query_results->posts)){
            return array();
        }
        if($isSingle){
            return $query_results->post;
        }
        if(is_object($query_results->posts)){
            foreach($query_results->posts as $item){
                $item->{'post_meta'}=get_post_meta($item->{'ID'});
                $tax_terms = get_post_taxonomies($item->{'ID'});
                foreach($tax_terms as $term){
                    $item->$term = get_the_terms($item->{'ID'}, $term);
                }
            }
            return array($query_results->posts);
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
        $form_id = $request['form_id'];

        $args = array(
            'form_id' => $form_id
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
                'form_id' => $webhook->form_id,
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }
        return rest_ensure_response( $data );
    }

    public function create_webhook( $request ) {
        $form_id = $request['form_id'];
        $url = esc_url_raw($request['url']);

        $post_id = $this->create_webhook_post('field_group', array(
            'form_id' => $form_id,
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'form_id' => $form_id,
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

    private function get_field_groupids($data) {
        $object = $this->get_field_object(array_keys( $data));
        $ids = array();
        foreach ($object as $newObj){
            $field_group_id = $newObj['parent'];
            if(!in_array($field_group_id, $ids)){
                array_push($ids, $field_group_id);
            }
        }
        return $ids;
    }

    public function process_save_post($id){

    	$data = $_POST['acf'];
    	$field_group_ids = $this->get_field_groupids($data);
			if( strstr( $id, 'user') !== false ){
    	   $id = explode("_",$id)[1];
    	   $post = get_userdata($id);
    	   $post = $post->data;

    	   $details = array(
    	       'ID' => $post->ID,
    	       'user_login' => $post->user_login,
    	       'user_email' => $post->user_email
    	   );
    	   $data['user'] = $details;
    	} else if ( strstr( $id, 'term') !== false ){
    	   $id = explode("_",$id)[1];
    	   $term = get_term($id);
    	   $details = array(
    	       'ID' => $term->term_id,
    	       'name' => $term->name,
    	       'slug' => $term->slug,
    	       'taxonomy' => $term->taxonomy,
    	   );
    	   $data[$term->taxonomy] = $details;
    	} else if ( strstr( $id, 'comment') !== false ){
            $id = explode("_",$id)[1];
            $comment = get_comment($id);
            $details = array(
                'ID' => $comment->comment_ID,
                'post_id' => $comment->comment_post_ID,
                'comment_content' => $comment->comment_content,
                'comment_author' => $comment->comment_author,
                'type' => 'comment',
            );
            $data['comment'] = $details;
        }else {
    	   $post = get_post($id);

    	   $details = array(
    	       'ID' => $post->ID,
    	       'post_title' => $post->post_title,
    	       'post_type' => $post->post_type
    	   );
    	   $data[$post->post_type] = $details;
    	}

    	foreach ($field_group_ids as $field_group_id){
    	    $args = array(
    	        'form_id' => $field_group_id
    	    );

    	    $webhooks = $this->get_webhook_posts($args);

    	    if ( !empty( $webhooks ) ) {
    	        foreach ( $webhooks as $webhook ) {
    	            $url = $webhook->url;
    	            zoho_flow_execute_webhook($url, $data, array());
    	        }
    	    }
    	}
    }

		public function fetch_fields($request){
			$result = array();
			$type = $request['type'];

			$fieldgroups = $this->get_field_groups($request)->data;
			foreach ($fieldgroups as $fieldgroup){
					$field_group_id = $fieldgroup->ID;
					$post_content = unserialize($fieldgroup->post_content);
					$locations = $post_content['location'];

					foreach ($locations as $location){
							$param = $location[0]['param'];

							if(in_array($param, $GLOBALS['gen_array'][$type])){
									$query_args = array(
											'post_type'         =>   'acf-field',
											'posts_per_page'    =>   -1,
											'orderby'           =>   'menu_order',
											'order'             =>   'ASC',
											'no_paging'			=>   true,
											'post_status'       =>   'publish',
											'post_parent'       =>   $field_group_id
									);

									$newfields = $this->get_field_object($this->handle_queries($query_args, false));
									foreach ($newfields as $value) {
											array_push($result, $value);
									}
							}
					}
			}
			return rest_ensure_response($result);
		}

		public function update_fields($request) {
			$fields =json_decode($request->get_body());

			$fieldparam = $this->get_field_param($request);

			foreach ($fields as $key => $value) {
				update_field( $key, $value ,$fieldparam);
			}

			return rest_ensure_response($fields);
		}

		private function get_field_param($request) {
			$type = $request['type'];
			$id = $request['id'];

			$fieldparam = "";
			if ($type === 'user') {
					$fieldparam = $type.'_'.$id;
			} else if($type === 'term') {
					$fieldparam = $type.'_'.$id;
			} else if($type === 'comment') {
					$fieldparam = $type.'_'.$id;
			} else if($type === 'post') {
					$fieldparam = $id;
			}

			return $fieldparam;
		}
}
