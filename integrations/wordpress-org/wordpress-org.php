<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Zoho_Flow_WordPress_org extends Zoho_Flow_Service
{
	public function init(){
		require_once __DIR__ . '/webhook-processor.php';
	}
	public function addActionHooks(){
	}
	public function initRestApis(){
	}

  public function get_posts( $request ){
	    $data = array();
	    $args = array("posts_per_page" => 100, "orderby" => "comment_count");

	    $posts_array = get_posts($args);
	    $schema = $this->get_post_schema( $request );
	    $list = array();
	    foreach($posts_array as $post)
	    {
	        if( isset( $schema['properties']['post_id'])){
	    	    $post_data['post_id'] = $post->ID;
	        }
	    	if ( isset( $schema['properties']['post_title'] ) ) {
		    $post_data['post_title'] = $post->post_title;
	    	}
	    	if ( isset( $schema['properties']['post_content'] ) ) {
		    $post_data['post_content'] = $post->post_content;
	    	}
	    	if ( isset( $schema['properties']['post_date'] ) ) {
		    $post_data['post_date'] = $post->post_date;
	    	}
	    	if ( isset( $schema['properties']['post_status'] ) ) {
		    $post_data['post_status'] = $post->post_status;
	    	}
	    	if ( isset( $schema['properties']['comment_count'] ) ) {
		    $post_data['comment_count'] = $post->comment_count;
	    	}

	    	array_push($list, $post_data);
	    }
	    $data['posts'] = $list;
	    $data['found'] = count($list);
	    return rest_ensure_response($data);
    	}

	public function upload_media($request) {
	    $file = $request['file'];
			if(!empty($file)){
				$filename = "";
				if(!empty($request['media_file_name'])){
					$filename = $request['media_file_name'];
				}
				else{
					$filename = basename($file);
				}

		    $upload_file = wp_upload_bits($filename, null, file_get_contents($file));

		    if (!$upload_file['error']) {
		        $wp_filetype = wp_check_filetype($filename, null );
		        $attachment = array(
		            'post_mime_type' => $wp_filetype['type'],
		            'post_parent' => 0,
		            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
		            'post_content' => '',
		            'post_status' => 'inherit'
		        );
		        $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], 0 );
		        if (!is_wp_error($attachment_id)) {
		            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
		            wp_update_attachment_metadata( $attachment_id,  $attachment_data );
		        }
		    }
		    return rest_ensure_response($upload_file);
			}
			else{
				return new WP_Error( 'rest_bad_request', esc_html__( 'Invalid media file.', 'zoho-flow' ), array( 'status' => 400 ) );
			}
	}

	public function upload_media_multipart($request){
		if(!empty($request->get_file_params()['media_file'])){
			$parent_post_id = 0;
			if(!empty($request['post_id'])){
				$parent_post_id = $request['post_id'];
			}
			$file_object = $request->get_file_params()['media_file'];
			$filename = "";
			if(!empty($request['media_file_name'])){
				$filename = $request['media_file_name'];
			}
			else{
		    $filename = $file_object['name'];
			}

		  $upload_file = wp_upload_bits($filename, null, file_get_contents($file_object['tmp_name']));

			$wp_filetype = wp_check_filetype($filename, null);
	    if (!$upload_file['error']) {
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_parent' => $parent_post_id,
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );
        if (!is_wp_error($attachment_id)) {
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
            wp_update_attachment_metadata( $attachment_id,  $attachment_data );
						$response_object = array(
				    	"file" => $upload_file['file'],
				    	"url" => $upload_file['url'],
				    	"type" => $upload_file['type'],
				    	"name" => preg_replace('/\.[^.]+$/', '', $filename),
				    	"size" => $file_object['size'],
				    	"id" => $attachment_id
				    );
        }
        else{
        	return new WP_Error( 'rest_bad_request', esc_html__( 'Error in file upload', 'zoho-flow' ), array( 'status' => 400 ));
        }
	    }
	    else{
	    	return new WP_Error( 'rest_bad_request', esc_html__( $upload_file['error'], 'zoho-flow' ), array( 'status' => 400 ));
	    }
		   return rest_ensure_response($response_object);
		}
		else{
			return new WP_Error( 'rest_bad_request', esc_html__( 'Invalid media file.', 'zoho-flow' ), array( 'status' => 400 ) );
		}

	}

	public function remove_media($request){
		$attachment_id = $request['attachment_id'];
	    if(!ctype_digit($attachment_id)){
	        return new WP_Error( 'rest_bad_request', esc_html__( 'The attachment ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
	    }
	    $attachment_data = wp_delete_attachment($attachment_id);
	    if(empty($attachment_data) || $attachment_data == false){
	    	return new WP_Error( 'rest_bad_request', esc_html__( 'Unable to remove attachment' ), array( 'status' => 400 ) );
	    }
	    return rest_ensure_response( $attachment_data);
	}

	//From v2.0.0
	//To list all media files
	public function get_media_files($request){
		global $wp_query;
		$args = array(
		    'post_type' => 'attachment',
		    'posts_per_page' => (((!empty($request['per_page_count'])) && (is_numeric($request['per_page_count'])))? $request['per_page_count'] : 20),
				'paged' => (((!empty($request['page_number'])) && (is_numeric($request['page_number'])))? $request['page_number'] : 1),
				'orderby' => ((!empty($request['orderby']))? $request['orderby'] : 'date'),
				'order' => ((!empty($request['order']))? $request['order'] : 'DESC'),
				'post_mime_type' => ((!empty($request['mime_type']))? $request['mime_type'] : ''),
		    );
		$attachments = get_posts( $args );
		$attachments_array = array();
		foreach ($attachments as $attachment) {
			$attachment_object = array();
			$attachment_object['id'] = $attachment->ID;
			$attachment_object['name'] = $attachment->post_title;
			$attachment_object['post_parent'] = $attachment->post_parent;
			$attachment_object['type'] = $attachment->post_mime_type;
			$attachment_object['thumb_url'] = wp_get_attachment_thumb_url($attachment->ID); //false of non image files
			$attachment_object['attachment_url'] = wp_get_attachment_url($attachment->ID);
			$post_meta = $this->get_post_meta($attachment->ID);
			$attachment_object['file'] = $post_meta['_wp_attached_file'];
			$attachment_object['filesize'] = $post_meta['_wp_attachment_metadata']['filesize'];
			if(wp_attachment_is_image($attachment->ID)){
				$attachment_object['width'] = $post_meta['_wp_attachment_metadata']['width'];
				$attachment_object['height'] = $post_meta['_wp_attachment_metadata']['height'];
			}
			array_push($attachments_array, $attachment_object);
		}
		$post_total_object = wp_count_attachments((!empty($request['mime_type']))? $request['mime_type'] : '');
		$total_count = 0;
		foreach ($post_total_object as $key => $value) {
			if($key != 'trash'){
				$total_count = $total_count + $value;
			}
		}
		$attachments_array_to_return = array(
			'total' => $total_count,
			'current_page_count' => count($attachments_array),
			'per_page_count' => (int)$args['posts_per_page'],
			'page_number' => (int)$args['paged'],
			'data' => $attachments_array,

		);
		return rest_ensure_response($attachments_array_to_return);
	}

	public function get_self($request){
	    $user = wp_get_current_user();
			unset($user->data->user_pass);

	    if(empty($user)){
	        return rest_ensure_response($user);
	    }
	    if(empty($user->roles)){
	        $user->roles = null;
	        $user->caps = null;
	        $user->allcaps = null;
	    }
	    $user->metadata = $this->returnMeta($user->ID, true);
	    return rest_ensure_response($user);
	}

	public function get_user_by( $request ){

	    $login = esc_attr($request['login']);

	    if(isset($login) && filter_var($request['login'], FILTER_VALIDATE_EMAIL)){
	        $user = get_user_by('email', $login);
	    }
	    else if(isset($request['user_id'])){
	        $user_id = $request['user_id'];
	        if(!ctype_digit($user_id)){
	            return new WP_Error( 'rest_bad_request', esc_html__( 'The User ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
	        }
	        $user = get_user_by('id', $user_id);
	    }
	    else if (isset($request['login'])) {
	        $user = get_user_by('login', $request['login']);
	    }
	    if(empty($user)){
	        return rest_ensure_response($user);
	    }
	    if(empty($user->roles)){
	        $user->roles = null;
	        $user->caps = null;
	        $user->allcaps = null;
	    }
	    if(isset($request['user_id'])){
	       $user->metadata = $this->returnMeta($user_id, true);
	    } else {
	        $user->metadata = $this->returnMeta($user->ID, true);
	    }

	    return rest_ensure_response($user);
	}

	public function get_users( $request ){
	    $data = array();
			$users = array();
			$params = $_GET;
			$custom_args = array();
			foreach ($params as $param => $value) {
				if(!empty($value)){
					if(($param == 'role') || ($param == 'role__in') || ($param == 'role__not_in') || ($param == 'meta_key') || ($param == 'meta_value') || ($param == 'capability') || ($param == 'capability__in')
					 || ($param == 'capability__not_in') || ($param == 'include') || ($param == 'exclude') || ($param == 'search_columns') || ($param == 'orderby') || ($param == 'fields') || ($param == 'nicename__in')
						|| ($param == 'nicename__not_in') || ($param == 'login__in') || ($param == 'login__not_in')){
							$custom_args[$param] = explode(',',$value);
						}
						else{
							$custom_args[$param] = $value;
						}
				}
			}
			if(sizeof($custom_args) > 0){
				if(empty($params['number'])){
					$custom_args['number'] = 1000;
				}
				$users = get_users( $custom_args );
			}
			else{
				$most_privilege = array(
					'orderby' => 'ID',
					'order' => 'ASC',
					'role__in' => array( 'administrator','editor', 'author' ),
					'number'=> 500,
					'paged'=> 1,
					'count_total'=> false  );
				$least_privilege = array(
	    		'orderby' => 'ID',
	    		'order' => 'DESC',
	    		'role__not_in' => array( 'administrator','editor', 'author' ),
	    		'number'=> 500,
	    		'paged'=> 1,
	    		'count_total'=> false  );
	    	$users = array_merge(
	    		get_users( $most_privilege ),
	    		get_users( $least_privilege ) );
			}

	    $schema = $this->get_user_schema();

	    foreach($users as $user){
	        if( isset( $schema['properties']['user_id'])){
	            $post_data['user_id'] = $user->ID;
	        }
	        if( isset( $schema['properties']['user_login'])){
	           $post_data['user_login'] = $user->user_login;
	        }
	        if( isset( $schema['properties']['user_email'])){
	            $post_data['user_email'] = $user->user_email;
	        }
	        if( isset( $schema['properties']['user_registered'])){
	            $post_data['user_registered'] = $user->user_registered;
	        }
	        if( isset( $schema['properties']['display_name'])){
	            $post_data['display_name'] = $user->display_name;
	        }
	        if( isset( $schema['properties']['role'])){
	           $post_data['role'] = $user->caps;
	        }
	        if( isset( $schema['properties']['roles'])){
	           $post_data['roles'] = $user->allcaps;
	        }
	        array_push($data, $post_data);
	    }
	    return rest_ensure_response($data);
	}

	public function get_comments( $request ){
	    $data = array();
            $args = array('');
	        $comments = get_comments($args);

	        $schema = $this->get_comment_schema();
	        foreach ($comments as $comment){
	            if( isset( $schema['properties']['comment_id'])){
	               $post_data['comment_id'] =$comment->comment_ID;
	            }
	            if( isset( $schema['properties']['comment_post_id'])){
	                $post_data['comment_post_id']=$comment->comment_post_ID;
	            }
	            if(isset($schema['properties']['comment_author'])){
	                $post_data['comment_author'] =$comment->comment_author;
	            }
	            if(isset($schema['properties']['comment_author_email'])){
	                $post_data['comment_author_email'] = $comment->comment_author_email;
	            }
	            if(isset($schema['properties']['comment_content'])){
	                $post_data['comment_content'] = $comment->comment_content;
	            }
	            if(isset($schema['properties']['comment_date'])){
	                $post_data['comment_date'] = $comment->comment_date;
	            }
	            array_push($data, $post_data);
	        }
	    return rest_ensure_response($data);
	}

	public function create_post($request){
		if($request->get_header('api-version') == '1.1'){
			$user_data = $this->create_post_v_1_1($request);
			if(is_wp_error($user_data)){
				return new WP_Error( 'rest_bad_request', $user_data->get_error_messages()[0], array( 'status' => 400 ) );
			}
			return rest_ensure_response($user_data);
		}
	    $postarr = array(
	        'post_title'   =>  wp_strip_all_tags($request['post_title']),
	        'post_content' =>  wp_strip_all_tags($request['post_content']),
	        'post_status'  =>  $request['post_status'],
	        'post_author'  =>  get_current_user_id(),
	        'post_type'    =>  'post',
	        'post_date'    =>  date( 'Y-m-d H:i:s', time() ),
	        'comment_status'   =>  $request['comment_status'],
	        'ping_status'  =>  $request['ping_status']
	    );

	    $post_id = wp_insert_post( $postarr);
	    wp_set_post_tags($post_id, wp_strip_all_tags($request['tags']), true);
	    wp_set_post_categories($post_id, wp_strip_all_tags($request['category']), false);
	    if(is_wp_error($post_id)){
                //the post is valid
                $errors = $post_id->get_error_messages();
                $error_code = $post_id->get_error_code();
                foreach ($errors as $error) {
                    return new WP_Error( $error_code, esc_html__( $error, 'zoho-flow' ), array('status' => 400) );
                }
            }
	    $request['post_id'] = $post_id;
	    $this->call_webhook_for_post($post_id, $postarr['post_type']);

	    return rest_ensure_response(get_post($post_id));

	}

	public function create_post_insert($request){
	    $postarr = array(
	        'post_title'   =>  $request['post_title'],
	        'post_content' =>  $request['post_content'],
	        'post_title' =>  $request['post_title'],
	        'post_excerpt' =>  $request['post_excerpt'],
	        'post_status'  =>  $request['post_status'],
	        'comment_status'   =>  $request['comment_status'],
	        'ping_status'  =>  $request['ping_status'],
	        'post_name'  =>  $request['post_name'],
	        'post_parent'  =>  $request['post_parent'],
	        'menu_order'  =>  $request['menu_order'],
	        'post_mime_type'  =>  $request['post_mime_type'],
	        'import_id'  =>  $request['import_id'],
	        'ping_status'  =>  $request['ping_status'],
	        'post_category'  =>  $request['post_category'],
	        'tags_input'  =>  $request['tags_input']

	    );
	    if(!empty($request['ID'])){
	    	$postarr['ID'] = $request['ID'];
	    }
			if(empty($request['post_author'])){
				$postarr['post_author'] = get_current_user_id();
			}
	    if(empty($request['post_type'])){
	    	$postarr['post_type'] = $request['post_type'];
	    }
	    else{
	    	$postarr['post_type'] = 'post';
	    }
	    if(!empty($request['post_password'])){
	    	$postarr['post_password'] = $request['post_password'];
	    }
	    if(!empty($request['guid'])){
	    	$postarr['guid'] = $request['guid'];
	    }
	    if(!empty($request['import_id'])){
	    	$postarr['import_id'] = $request['import_id'];
	    }

			if((!empty($request['post_author'])) && ($request['post_author'] != get_current_user_id())){
				if(current_user_can('edit_others_posts')){
					if(user_can($request['post_author'], 'edit_posts')){
						$postarr['post_author'] = $request['post_author'];
					}
					else{
						return new WP_Error( 'rest_bad_request', 'Selected user has no permission to create post.', array( 'status' => 400 ) );
					}
				}
				else{
					return new WP_Error( 'rest_bad_request', 'You have no permission to create posts for other users.', array( 'status' => 400 ) );
				}
			}
			if((!empty($request['post_status'])) && ($request['post_status'] == 'publish')){
				if(!current_user_can('publish_posts')){
					return new WP_Error( 'rest_bad_request', 'You have no permission to publish posts.', array( 'status' => 400 ) );
				}
			}

			kses_remove_filters();
			$post_id = wp_insert_post( $postarr, true);
			kses_init_filters();

	    if(is_wp_error($post_id)){
                //the post is valid
                $errors = $post_id->get_error_messages();
                $error_code = $post_id->get_error_code();
                foreach ($errors as $error) {
                    return new WP_Error( $error_code, esc_html__( $error, 'zoho-flow' ), array('status' => 400) );
                }
            }
	    $request['post_id'] = $post_id;
	    $this->call_webhook_for_post($post_id, $postarr['post_type']);

	    $post_data = get_post($post_id);
	    $data = $this->update_tags_and_categories($post_id);

	    $post_data->tags = $data['tags'];
	    $post_data->categories = $data['categories'];
	    $post_data->permalink = get_permalink($post_id);

	    return rest_ensure_response($post_data);

	}

	public function update_post($request){
		if($request->get_header('api-version') == '1.1'){
			$user_data = $this->update_post_v_1_1($request);
			if(is_wp_error($user_data)){
				return new WP_Error( 'rest_bad_request', $user_data->get_error_messages()[0], array( 'status' => 400 ) );
			}
			return rest_ensure_response($user_data);
		}
	    $post_id = $request['post_id'];
	    if(!ctype_digit($post_id)){
	        return new WP_Error( 'rest_bad_request', esc_html__( 'The post ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
	    }

	    $post_arr = array(
	        'ID'           =>  $post_id,
	        'post_title'   =>  wp_strip_all_tags($request['post_title']),
	        'post_content' =>  wp_strip_all_tags($request['post_content']),
	        'post_status'  =>  $request['post_status'],
	        'post_author'  =>  $request['post_author'],
	        'post_type'    =>  'post',
	        'post_date'    =>  date( 'Y-m-d H:i:s', time() )
	    );
	    $post_id = wp_update_post($post_arr);
	    wp_set_post_tags($post_id, wp_strip_all_tags($request['tags']), true);
	    wp_set_post_categories($post_id, wp_strip_all_tags($request['category']), true);
	    if (is_wp_error($post_id)) {
	        $errors = $post_id->get_error_messages();
	        $error_code = $post_id->get_error_code();
	        foreach ($errors as $error) {
	            return new WP_Error( $error_code, esc_html__( $error, 'zoho-flow' ) , array('status' => 400));
	        }
	    }
	    $this->call_webhook_for_post($post_id, $post_arr['post_type']);

	    return rest_ensure_response(get_post($post_id));
	}

	public function create_user($request){
		if($request->get_header('api-version') == '1.1'){
			$user_data = $this->create_user_v_1_1($request);
			if(is_wp_error($user_data)){
				return new WP_Error( 'rest_bad_request', $user_data->get_error_messages()[0], array( 'status' => 400 ) );
			}
			return rest_ensure_response($user_data);
		}
	    $userdata = array(
	        'user_login'   =>  $request['user_login'],
	        'user_pass'    =>  $request['user_pass'],
	        'user_email'   =>  $request['user_email'],
	        'last_name'    =>  $request['last_name'],
	        'first_name'   =>  $request['first_name'],
	        'user_registered'  =>  date( 'Y-m-d H:i:s', time()),
	        'role'         =>  $request['role'],
	        'user_url'     =>  $request['user_url'],
	        'description'  =>  $request['description'],
	        'nickname'	   =>  $request['nickname']
	    );
	    $user_id = wp_insert_user( $userdata ) ;
	    $this->update_meta_values($request, $user_id, $userdata);

	    if ( is_wp_error( $user_id ) ) {
	        $errors = $user_id->get_error_messages();
	        $error_code = $user_id->get_error_code();
	        foreach ($errors as $error) {
	            return new WP_Error($error_code, esc_html__( $error, 'zoho-flow' ), array( 'status' => 400 )  );
	        }
	    }
	    return rest_ensure_response(get_user_by('ID', $user_id));
	}

	public function update_user($request){
		if($request->get_header('api-version') == '1.1'){
			$user_data = $this->update_user_v_1_1($request);
			if(is_wp_error($user_data)){
				return new WP_Error( 'rest_bad_request', $user_data->get_error_messages()[0], array( 'status' => 400 ) );
			}
			return rest_ensure_response($user_data);
		}
	    $user_id = $request['user_id'];
	    if(!ctype_digit($user_id)){
	        return new WP_Error( 'rest_bad_request', esc_html__( 'The user ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
	    }
	    $olddata = get_user_by('ID', $user_id);

	    $userdata = array(
	        'ID'           => $request['user_id'],
	        'user_pass'    =>  (isset($request['user_pass']) && !empty($request['user_pass'])) ? $request['user_pass'] : $olddata->user_pass,
	        'user_email'   =>  (isset($request['user_email']) && !empty($request['user_email'])) ? $request['user_email'] : $olddata->user_email,
	        'last_name'    =>  (isset($request['last_name']) && !empty($request['last_name'])) ? $request['last_name'] : $olddata->last_name,
	        'first_name'   =>  (isset($request['first_name']) && !empty($request['first_name'])) ? $request['first_name'] : $olddata->first_name,
	        'user_registered'  =>  date( 'Y-m-d H:i:s', time()),
	        'user_url'     =>  (isset($request['user_url']) && !empty($request['user_url'])) ? $request['user_url'] : $olddata->user_url,
	        'description'  =>  (isset($request['description']) && !empty($request['description'])) ? $request['description'] : $olddata->description,
	        'nickname'     =>  (isset($request['nickname']) && !empty($request['nickname'])) ? $request['nickname'] : $olddata->nickname
	    );

	    $data = wp_update_user( $userdata ) ;
	    if ( is_wp_error( $data ) ) {
	        $errors = $data->get_error_messages();
	        foreach ($errors as $error) {
	            return new WP_Error( 'rest_bad_request', esc_html__( $error, 'zoho-flow' ), array('status' => 400) );
	        }
	    }
	    $user_data = $this->update_meta_values($request, $user_id, $userdata);
	    return rest_ensure_response($user_data);
	}

	public function get_resetpassword_link($request) {
	    $data = array();
	    $user_login = $request['user_login'];
	    if($user_login == "" || empty($user_login)){
	        return new WP_Error( 'rest_bad_request', esc_html__( 'User Login must not be empty.', 'zoho-flow' ), array( 'status' => 400 ) );
	    }

	    $current_userid = get_current_user_id();
	    $user_login = trim( wp_unslash( $user_login ) );
	    $user_data = get_user_by( 'login', $user_login );

	    if(empty($user_data)){
	        return rest_ensure_response($user_data);
	    }

	    if ( ! current_user_can( 'edit_user', $user_data->ID ) || $current_userid === $user_data->ID) {
	        return new WP_Error( 'rest_bad_request', esc_html('Sorry, you are not allowed to edit this user.', 'zoho-flow'), array('status' => 400) );
	    }

	    if ( is_multisite() ) {
	        $site_name = get_network()->site_name;
	    } else {
	        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	    }

	    $key = get_password_reset_key( $user_data );
	    $locale = get_user_locale( $user_data );
	    $rplink = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . '&wp_lang=' . $locale;

	    $data['link'] = $rplink;
	    $data['site'] = $site_name;
	    $data['user_login'] = $user_login;
	    return rest_ensure_response($data);
	}

	public function call_webhook_for_post($post_id, $post_type){
	    $data = get_post($post_id);
	    $termdata = $this->update_tags_and_categories($post_id);
	    $data->tags = $termdata['tags'];
	    $data->categories = $termdata['categories'];
	    $args = array(
	        'post_type'    =>  'posts'
	    );
	    $webhooks = $this->get_webhook_posts($args);
	    foreach ( $webhooks as $webhook ) {
	        $url = $webhook->url;
	        zoho_flow_execute_webhook($url, $data, array());
	    }
	}

	public function get_webhooks($request){
	    $data = array();
	    $post_id = $request['post_id'];
	    if(!ctype_digit($post_id)){
	        return new WP_Error( 'rest_bad_request', esc_html__( 'The post ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
	    }
	    $args = array(
	        'post_id' => $post_id
	    );
	    $webhooks = $this->get_webhook_posts($args);

	    foreach ( $webhooks as $webhook ) {
	        $webhook = array(
	            'plugin_service' => $this->get_service_name(),
	            'id' => $webhook->ID,
	            'form_id' => $post_id,
	            'url' => $webhook->url
	        );
	        array_push($data, $webhook);
	    }

	    return rest_ensure_response( $data );
	}

	public function create_post_comments_webhook($request){
	    $postid = $request['post_id'];
	    $url = esc_url_raw($request['url']);
	    if(!ctype_digit($postid)){
	        return new WP_Error( 'rest_bad_request', esc_html__( 'The post ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
	    }
	    $post_obj = get_post($postid);
	    if(empty($post_obj)){
	        return new WP_Error( 'rest_not_found', esc_html__( 'The post is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
	    }
	    $post_title = $post_obj->post_title;

	    $post_id = $this->create_webhook_post($post_title, array(
	        'post_id' => $postid,
	        'url' => $url
	    ));
	    return rest_ensure_response( array(
	        'plugin_service' => $this->get_service_name(),
	        'id' => $post_id,
	        'post_id' =>$postid,
	        'url' => $url
	    ) );
	}

	public function delete_webhook_deprecated($request){
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

	public function get_webhooks_for_post( $request ){

	    $data = array();
	    $args = array(
	        'post_type' => $request['post_type']
	    );
	    $webhooks = $this->get_webhook_posts($args);
	    foreach ( $webhooks as $webhook ) {
	        $webhook = array(
	            'plugin_service' => $this->get_service_name(),
	            'id' => $webhook->ID,
	            'post_type' => $request['post_type'],
	            'url' => $webhook->url
	        );
	        array_push($data, $webhook);
	    }

	    return rest_ensure_response( $data );
	}

	public function create_webhook_for_post( $request ){
	    $post_title = $request['post_type'];
	    $url = esc_url_raw($request['url']);
	    $post_id = $this->create_webhook_post($post_title, array(
	        'post_type' => $request['post_type'],
	        'url' => $url
	    ));

	    return rest_ensure_response( array(
	        'plugin_service' => $this->get_service_name(),
	        'id' => $post_id,
	        'post_type' =>$request['post_type'],
	        'url' => $url
	    ) );
	}

	public function get_comments_webhooks( $request ){

	    $data = array();
	    $args = array(
	        'post_type' => 'comments'
	    );
	    $webhooks = $this->get_webhook_posts($args);

	    foreach ( $webhooks as $webhook ) {
	        $webhook = array(
	            'plugin_service' => $this->get_service_name(),
	            'id' => $webhook->ID,
	            'url' => $webhook->url
	        );
	        array_push($data, $webhook);
	    }

	    return rest_ensure_response( $data );
	}

	public function create_comments_webhooks( $request ) {
	    $post_type ='comments';
	    $url = esc_url_raw($request['url']);
	    $post_id = $this->create_webhook_post($post_type, array(
	    	'post_type' => $$post_type,
	        'url' => $url
	    ));

	    return rest_ensure_response( array(
	        'plugin_service' => $this->get_service_name(),
	        'id' => $post_id,
	        'post_type' =>$request['post_type'],
	        'url' => $url
	    ) );
	}

	public function wp_core_register_post_type(){
	    if(empty(post_type_exists('users'))){
    	    $args = array(
    	        'public'    => true,
    	        'label'     => __( 'Users', 'textdomain' ),
    	        'capability_type' => 'users'
    	    );
    	    register_post_type( 'users', $args );
	    }
	}

	public function process_comment_post($comment_id,  $commentdata_comment_approved, $commentdata){

	    $args = array(
	        'post_id' => $commentdata['comment_post_ID']
	    );
	    $commentdata['comment_id'] = $comment_id;

	    $webhooks = $this->get_webhook_posts($args);
	    if(empty($webhooks)){
	        $args_array = array(
	            'post_type' => 'comments'
	        );
	        $webhooks = $this->get_webhook_posts($args_array);
	    }
	    foreach ( $webhooks as $webhook ) {
	        $url = $webhook->url;
	        zoho_flow_execute_webhook($url, $commentdata, array());
	    }
	}

	public function process_spammed_comment($comment_id, $comment){

	    $args = array(
	        'post_id' => $comment->comment_post_ID
	    );

	    $webhooks = $this->get_webhook_posts($args);
	    foreach ( $webhooks as $webhook ) {
	        $url = $webhook->url;
	        zoho_flow_execute_webhook($url, $comment, array());
	    }
	}

	public function process_edit_comment($comment_ID, $comment){

	    $comment['commment_id'] = $comment_ID;
	    $args = array(
	        'post_id'=> $comment['comment_post_ID']
	    );

	    $webhooks = $this->get_webhook_posts($args);
	    foreach ( $webhooks as $webhook ) {
	        $url = $webhook->url;
	        zoho_flow_execute_webhook($url, $comment, array());
	    }
	}

	public function process_set_comment_status($comment_id, $comment_status){

	    $comment = get_comment($comment_id);
			if((is_object($comment)) && ($comment->comment_ID)){
				$comment->comment_status = $comment_status;
		    $args = array(
		        'post_id'=> $comment->comment_post_ID
		    );

		    $webhooks = $this->get_webhook_posts($args);
		    foreach ( $webhooks as $webhook ) {
		        $url = $webhook->url;
		        zoho_flow_execute_webhook($url, $comment, array());
		    }
			}
	}

	public function process_user_register($user_id){
	    $user = get_user_by('ID', $user_id);
	    $args = array(
	        'post_type'=> 'users'
	    );

	    $webhooks = $this->get_webhook_posts($args);
	    foreach ( $webhooks as $webhook ) {
	        $url = $webhook->url;
	        zoho_flow_execute_webhook($url, $user, array());
	    }
	}

	public function process_profile_update($user_id, $old_user_data){
	    $user = get_user_by('ID', $user_id);
	    $times = did_action('profile_update');
	    if($times ===1){
	    	    $args = array(
	    	        'post_type'=> 'users'
	    	    );

	    	    $webhooks = $this->get_webhook_posts($args);
	    	    foreach ( $webhooks as $webhook ) {
	    	        $url = $webhook->url;
	    	        zoho_flow_execute_webhook($url, $user, array());
	    	    }
	    	    return rest_ensure_response($user);
	    }
	}

	public function process_save_post($post_id, $post , $update){
	    $post_status = $post->post_status;
	    $post_type = $post->post_type;
	    if (wp_is_post_revision($post_id)) {
	        return;
	    }
	    $times = did_action('save_post');
	    if($times === 1 && $post_type ==='post' && $post_status==='publish'){
    		$args = array(
    		    'post_type' => 'posts'
    		);
    		$webhooks = $this->get_webhook_posts($args);
    		foreach($webhooks as $webhook){
    		   $url = $webhook->url;
    		   zoho_flow_execute_webhook($url, $post, array());
    		}
	    }
	}

	public function process_wp_login($user_login, $user){
	    $args = array(
	        'post_type'=> 'user_login'
	    );

	    $webhooks = $this->get_webhook_posts($args);
	    foreach ( $webhooks as $webhook ) {
	        $url = $webhook->url;
	        zoho_flow_execute_webhook($url, $user, array());
	    }
	}

	public function get_post_schema() {
	    $schema = array(
	        '$schema'              => 'http://json-schema.org/draft-04/schema#',
	        'title'                => 'posts',
	        'type'                 => 'post',
	        'properties'           => array(
	            'post_id' => array(
	                'description'  => esc_html__( 'Post Id', 'zoho-flow' ),
	                'type'         => 'integer',
	                'context'      => array('view'),
	            ),
	            'post_title' => array(
	                'description'  => esc_html__( 'Post Title', 'zoho-flow' ),
	                'type'         => 'string',
	                'context'      => array( 'view', 'edit'),
	                'readonly'     => true,
	            ),
	            'post_content' => array(
	                'description'  => esc_html__( 'Content of a Post', 'zoho-flow' ),
	                'type'         => 'string',
	                'context'      => array( 'view', 'edit'),
	            ),
	            'post_date' => array(
	                'description' => esc_html__("Created Date of Post", "zoho-flow"),
	                'type'        => 'date',
	                'context'     => array('view'),
	                'readonly'    => true,
	            ),
	            'post_status' => array(
	                'description' => esc_html__( 'Post status', 'zoho-flow' ),
	                'type'        => 'string',
	                'context'     => array('view'),
	            ),
	            'comment_count' => array(
	                'description' => esc_html__('Comment count', 'zoho-flow'),
	                'type'        => 'integer',
	                'context'     => array('view'),
	            ),
	        ),
	    );

	    return $schema;
	}

	public function get_user_schema() {
	    $schema = array(
	        '$schema'              => 'http://json-schema.org/draft-04/schema#',
	        'title'                => 'users',
	        'type'                 => 'user',
	        'properties'           => array(
	            'user_id' => array(
	                'description'  => esc_html__( 'User Id', 'zoho-flow' ),
	                'type'         => 'integer',
	                'context'      => array('view'),
	            ),
	            'user_login' => array(
	                'description'  => esc_html__( 'User login', 'zoho-flow' ),
	                'type'         => 'string',
	                'context'      => array( 'view', 'edit'),
	                'readonly'     => true,
	            ),
	            'user_email' => array(
	                'description'  => esc_html__( 'User email', 'zoho-flow' ),
	                'type'         => 'string',
	                'context'      => array( 'view', 'edit'),
	            ),
	            'user_registered' => array(
	                'description' => esc_html__("User registered date", "zoho-flow"),
	                'type'        => 'date',
	                'context'     => array('view'),
	                'readonly'    => true,
	            ),
	            'display_name' => array(
	                'description' => esc_html__( 'Display Name', 'zoho-flow' ),
	                'type'        => 'string',
	                'context'     => array('view'),
	            ),
	            'role' => array(
	                'description' => esc_html__('Comment count', 'zoho-flow'),
	                'type'        => 'array',
	                'context'     => array('view'),
	            ),
	            'roles' => array(
	                'description' => esc_html__('User role', 'zoho-flow'),
	                'type'        => 'array',
	                'context'     => array('view'),
	            ),
	        ),
	    );

	    return $schema;
	}

	public function get_comment_schema() {
	    $schema = array(
	        '$schema'              => 'http://json-schema.org/draft-04/schema#',
	        'title'                => 'posts',
	        'type'                 => 'post',
	        'properties'           => array(
	            'comment_id' => array(
	                'description'  => esc_html__( 'Comment Id', 'zoho-flow' ),
	                'type'         => 'integer',
	                'context'      => array('view'),
	            ),
	            'comment_post_id' => array(
	                'description'  => esc_html__( 'Comment Post Id', 'zoho-flow' ),
	                'type'         => 'integer',
	                'context'      => array( 'view'),
	                'readonly'     => true,
	            ),
	            'comment_author' => array(
	                'description'  => esc_html__( 'Author of the comment', 'zoho-flow' ),
	                'type'         => 'string',
	                'context'      => array( 'view', 'edit'),
	            ),
	            'comment_author_email' => array(
	                'description' => esc_html__("Email of the comment author", "zoho-flow"),
	                'type'        => 'string',
	                'context'     => array('view'),
	                'readonly'    => true,
	            ),
	            'comment_content' => array(
	                'description' => esc_html__( 'Comment content', 'zoho-flow' ),
	                'type'        => 'string',
	                'context'     => array('view', 'edit'),
	            ),
	            'comment_date' => array(
	                'description' => esc_html__('Commented date', 'zoho-flow'),
	                'type'        => 'date',
	                'context'     => array('view'),
	            ),
	        ),
	    );

	    return $schema;
	}

	public function  get_userinfo_meta($request){
	    $metakeys = $this->returnMeta($request['user_id'], FALSE);
	    return rest_ensure_response($metakeys);
	}

	private function update_meta_values($request, $user_id, $userdata) {
	    $dataarr = array();
	    foreach ($request->get_params() as $key => $value){
	        update_user_meta($user_id, $key, $request[$key]);
	        if(isset($request[$key])){
	            $value1 = get_user_meta($user_id, $key, true);
	            $dataarr[$key] = $value1;
	        }
	    }

	    $u = new WP_User( $user_id);
	    $role = $request['role'];
	    if(isset($role) && !empty($role)) {
	        $role= trim($role);
	        $role = str_replace(" ", "_", $role);
	        $role = strtolower($role);
	        $u->set_role($role);
	    }
	    return $dataarr;
	}

	public function get_categories(){
	    $data = array();
	    $list = array();
	    $args = array('taxonomy' => 'category', 'orderby' => 'name', 'hide_empty' => false );
	    $categories = get_categories($args);
	    foreach( array_keys( $categories ) as $key){
	        array_push($list, $categories[$key]);
	    }
	    $data['found'] = count($list);
	    $data['categories'] = $list;
	    return rest_ensure_response($data);
	}

	public function get_tags() {
	    $data = array();
	    $args = array('taxonomy' => 'post_tag', 'orderby' => 'name', 'hide_empty' => false );
	    $tags = get_tags($args);
	    $data['found'] = count($tags);
	    $data['tags'] = $tags;
	    return rest_ensure_response($data);
	}

	private function returnMeta($id, $returnKeyValue){

	    $meta = get_user_meta($id);
	    $usermeta = array();
	    $metadata = array();
	    foreach ($meta as $key => $value){
	        $data = array(
	            'meta_key' => $key,
	        );
	        $metadata[$key] = $value[0];
	        array_push($usermeta, $data);
	    }
	    if($returnKeyValue){
	        return $metadata ;
	    } else {
	        return rest_ensure_response($usermeta);
	    }
	}

	public function get_post($request){
		if($request->get_header('api-version') == '1.1'){
			$user_data = $this->fetch_post_with_posttype($request);
			if(is_wp_error($user_data)){
				return new WP_Error( 'rest_bad_request', $user_data->get_error_messages()[0], array( 'status' => 400 ) );
			}
			return rest_ensure_response($user_data);
		}

			$post_id = $request['post_id'];
			if(!ctype_digit($post_id)){
					return new WP_Error( 'rest_bad_request', esc_html__( 'The post ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
			}
			$post_data = get_post($post_id);
			if(!empty($post_data)){
				$data = $this->update_tags_and_categories($post_id);
				$post_data->permalink = get_permalink($post_id);
				$post_data->tags = $data['tags'];
				$post_data->categories = $data['categories'];
			}

			return rest_ensure_response( $post_data);
	}


	private function update_tags_and_categories($post_id){
			$data = array();
			$tags = array();
			$categories = array();
			$list = wp_get_post_tags($post_id);
			foreach($list as $tag){
					array_push($tags, $tag->name);
			}
			$list = wp_get_post_categories($post_id);
			foreach($list as $categoryid){
					$category = get_category($categoryid);
					array_push($categories, $category->name);
			}
			$data['tags'] = $tags;
			$data['categories'] = $categories;
			return $data;
	}

	//refactored in v2.0.0
	public function get_post_types(){
			$posttypes = get_post_types(array(), 'objects', 'and');
			if($posttypes){
				$result = array();
				foreach ($posttypes as $posttype){
					//To prevent recursion error while converting php array to json in return handler
					$posttype_array = array(
						"name" => $posttype->name,
						"label" => $posttype->label,
						"labels" => $posttype->labels,
						"description" => $posttype->description,
						"public" => $posttype->public,
						"hierarchical" => $posttype->hierarchical,
						"exclude_from_search" => $posttype->exclude_from_search,
						"publicly_queryable" => $posttype->publicly_queryable,
						"show_ui" => $posttype->show_ui,
						"show_in_menu" => $posttype->show_in_menu,
						"show_in_nav_menus" => $posttype->show_in_nav_menus,
						"show_in_admin_bar" => $posttype->show_in_admin_bar,
						"menu_position" => $posttype->menu_position,
						"menu_icon" => $posttype->menu_icon,
						"capability_type" => $posttype->capability_type,
						"map_meta_cap" => $posttype->map_meta_cap,
						"taxonomies" => $posttype->taxonomies,
						"has_archive" => $posttype->has_archive,
						"query_var" => $posttype->query_var,
						"can_export" => $posttype->can_export,
						"delete_with_user" => $posttype->delete_with_user,
						"cap" => $posttype->cap,
						"rewrite" => $posttype->rewrite,
						"show_in_rest" => $posttype->show_in_rest,
						"rest_base" => $posttype->rest_base,
						"rest_namespace" => $posttype->rest_namespace,
						"rest_controller_class" => $posttype->rest_controller_class
					);
					array_push($result, $posttype_array);
				}
				return rest_ensure_response($result);
			}
			return new WP_Error( 'rest_bad_request', 'Bad request', array( 'status' => 400 ) );
	}


	//From v2.0.0 New APIs
	//utilities

	private function get_post_tags($post_id){
		$tags = array();
		$list = wp_get_post_tags($post_id);
		foreach($list as $tag){
				array_push($tags, $tag->name);
		}
		return $tags;
	}

	private function get_post_categories($post_id){
		$categories = array();
		$list = wp_get_post_categories($post_id);
		foreach($list as $categoryid){
				$category = get_category($categoryid);
				array_push($categories, $category->name);
		}
		return $categories;
	}

	private function get_post_meta($post_id){
		if((!empty($post_id)) && (is_numeric($post_id))){
			$post_meta = get_post_meta($post_id);
			$post_meta_unserialized = array();
			foreach ($post_meta as $key => $value) {
				$post_meta_unserialized[$key] = maybe_unserialize($value[0]);
			}
			return $post_meta_unserialized;
		}
	}

	private function get_post_taxonomy($post_id){
		$post_type = get_post_type($post_id);
		if(!empty($post_id) && $post_type){
			$post_taxonomies = array();
			$supported_taxonomies = get_object_taxonomies($post_type,'object');
			foreach ($supported_taxonomies as $key => $value) {
				$the_terms = get_the_terms($post_id,$key);
				if(($the_terms) && (!is_wp_error($the_terms))){
					$post_taxonomies[$key] = $the_terms;
				}
			}
			return $post_taxonomies;
		}
		return array();
	}

	private function get_post_with_meta($post_id){
		if((!empty($post_id)) && (is_numeric($post_id))){
			$post_data = get_post($post_id);
			$post_data->permalink = get_permalink($post_id);
			$post_data->tags_input = $this->get_post_tags($post_id);
			$post_data->post_category = $this->get_post_categories($post_id);
			$post_data->meta = $this->get_post_meta($post_id);
			$post_data->taxonomy = $this->get_post_taxonomy($post_id);
			return $post_data;
		}
		return array();
	}

	private function get_user_meta($user_id){
		if((!empty($user_id)) && (is_numeric($user_id))){
			$user_meta = get_user_meta($user_id);
			$user_meta_unserialized = array();
			foreach ($user_meta as $key => $value) {
				$user_meta_unserialized[$key] = maybe_unserialize($value[0]);
			}
			return $user_meta_unserialized;
		}
	}

	private function get_user_with_meta($user_id){
		if((!empty($user_id)) && (is_numeric($user_id))){
			$user_unparsed_data = get_user_by('ID', $user_id);
			$user_data = $user_unparsed_data->data;
				$user_data = $user_unparsed_data->data;
				$user_data->caps = $user_unparsed_data->caps;
				$user_data->cap_key = $user_unparsed_data->cap_key;
				$user_data->roles = $user_unparsed_data->roles;
				$user_data->allcaps = $user_unparsed_data->allcaps;
			$user_data->user_pass = ''; //Hidding password in return objects
			$user_data->meta = $this->get_user_meta($user_id);
			return $user_data;
		}
		return array();
	}

	private function update_metadata($meta_type, $object_id, $meta_key, $meta_value){
		update_metadata($meta_type, $object_id, $meta_key, $meta_value);
	}

	private function is_valid_post($post_id){
		if((!empty($post_id)) && (is_numeric($post_id))){
			$post_details = get_post($post_id);
			if(!empty($post_details->ID)){
				return true;
			}
		}
		return false;
	}

	private function is_valid_postype_post($post_id,$post_type){
		if((!empty($post_id)) && (!empty($post_type)) && (is_numeric($post_id))){
			$post_details = get_post($post_id);
			if($post_details->post_type == $post_type){
				return true;
			}
		}
		return false;
	}

	private function is_valid_user($user_id){
		if((!empty($user_id)) && (is_numeric($user_id))){
			$post_details = get_user_by('ID', $user_id);
			if($post_details){
				return true;
			}
		}
		return false;
	}


	//To get meta fields of post type
	public function get_post_type_meta_keys($request){
		global $wpdb;
		$post_type = $request->get_url_params()['post_type'];
		
		$query = $wpdb->prepare(
		    'SELECT DISTINCT(m.meta_key)
                FROM ' . $wpdb->base_prefix . 'postmeta m
                INNER JOIN ' . $wpdb->base_prefix . 'posts p ON p.ID = m.post_id
                WHERE p.post_type = %s
                LIMIT 1400',
		    $post_type
		    );
		
		$meta_keys = $wpdb->get_results($query);
		
		return rest_ensure_response( $meta_keys );
	}

	//To get taxonomies of post type
	public function get_post_type_taxonomies($request){
		$post_type = $request['post_type'];
		if(!empty($post_type)){
			$taxonomies = get_object_taxonomies($post_type,'object');
			$taxonomies_array = json_decode(json_encode($taxonomies),true);
			foreach ($taxonomies_array as $key => $value) {
				$taxonomies_array[$key]['terms'] = get_terms(['taxonomy' => $key, 'hide_empty' => false]);
			}
			return rest_ensure_response($taxonomies_array);
		}
		return new WP_Error( 'rest_bad_request', 'Post type missing', array( 'status' => 404 ) );
	}

	//To get user meta fields
	public function get_user_meta_keys($request){
		global $wpdb;
		$query     = $wpdb->prepare('
			SELECT
				DISTINCT meta_key
			    FROM ' . $wpdb->base_prefix . 'usermeta
            ');
		$meta_keys = $wpdb->get_results( $query );
		return rest_ensure_response($meta_keys);
	}

	//To get comment meta fields
	public function get_comment_meta_keys($request){
		global $wpdb;
		$query     = $wpdb->prepare('
			SELECT
				DISTINCT meta_key
			FROM ' . $wpdb->base_prefix . 'commentmeta
		');
		$meta_keys = $wpdb->get_results( $query );
		return rest_ensure_response($meta_keys);
	}

	// To get statuses of all post types
	public function get_post_statuses($request){
		$post_statuses = get_post_stati(array(), 'objects', 'and');
		if($post_statuses){
			$result = array();
			foreach ($post_statuses as $post_status){
					array_push($result, $post_status);
			}
			return rest_ensure_response($result);
		}
		return new WP_Error( 'rest_bad_request', 'Bad request', array( 'status' => 400 ) );
	}

	//To get available roles
	public function get_roles($request){
		global $wp_roles;
    $all_roles = $wp_roles->roles;
		return rest_ensure_response($all_roles);
	}

	//To fetch user
	public function fetch_user($request){
		$supported_fields = array(
			'ID',
			'id',
			'slug',
			'email',
			'login'
		);
		$fetch_field = $request['fetch_field'];
		$fetch_value = $request['fetch_value'];
		if((!empty($fetch_field)) && (!empty($fetch_value))){
			if(in_array($fetch_field, $supported_fields)){
				$user_details = get_user_by($fetch_field, $fetch_value);
				if($user_details){
					return rest_ensure_response($this->get_user_with_meta($user_details->ID));
				}
				else{
					return new WP_Error( 'rest_bad_request', 'User not found', array( 'status' => 404 ) );
				}
			}
			else{
				return new WP_Error( 'rest_bad_request', 'Invalid fetch field', array( 'status' => 400 ) );
			}
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Empty field or value', array( 'status' => 400 ) );
		}
	}

	public function fetch_post_with_posttype($request){
		if(!$this->is_valid_post($request['post_id'])){
			return new WP_Error( 'rest_bad_request', 'Invalid post', array( 'status' => 404 ) );
		}
		else if(!$this->is_valid_postype_post($request['post_id'],$request['post_type'])){
			return new WP_Error( 'rest_bad_request', 'Invalid post in post type', array( 'status' => 404 ) );
		}
		$post_details = $this->get_post_with_meta($request['post_id']);
		if(!empty($post_details->ID)){
			return rest_ensure_response($post_details);
		}
	}

	//to update post meta fields
	public function update_post_meta($request){
		$request_obj = $request->get_json_params();
		$meta_obj = $request_obj['meta'];
		$post_type = $request_obj['post_type'];
		$post_id = $request['post_id'];
		if($this->is_valid_postype_post($post_id, $post_type)){
			foreach ($meta_obj as $key => $value){
				if(!empty($value)){
					$this->update_metadata('post',$post_id, $key, $value);
				}
			}
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Invalid post ID', array( 'status' => 400 ) );
		}
		return rest_ensure_response($this->get_post_with_meta($post_id));
	}

	//to update post meta fields
	public function update_post_taxonomy($request){
		$request_obj = $request->get_json_params();
		$taxonomy_obj = $request_obj['taxonomy'];
		$post_type = $request_obj['post_type'];
		$append = json_decode( $request_obj['append'] );
		$post_id = $request['post_id'];
		if($this->is_valid_postype_post($post_id, $post_type)){
			foreach ($taxonomy_obj as $key => $value){
				if(!empty($value)){
					$update = wp_set_post_terms($post_id, $value, $key, $append);
				}
			}
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Invalid post ID', array( 'status' => 400 ) );
		}
		return rest_ensure_response($this->get_post_with_meta($post_id));
	}

	//To update user meta fields
	public function update_user_meta($request){
		$request_obj = $request->get_json_params();
		$meta_obj = $request_obj['meta'];
		$user_id = $request['user_id'];
		if($this->is_valid_user($user_id)){
			foreach ($meta_obj as $key => $value){
				if(!empty($value)){
					$this->update_metadata('user',$user_id, $key, $value);
				}
			}
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Invalid user ID', array( 'status' => 400 ) );
		}
		return rest_ensure_response($this->get_user_with_meta($user_id));
	}

	//To add user
	public function create_user_v_1_1($request){
		$supported_fields = array(
			"user_pass",
			"user_login",
			"user_nicename",
			"user_url",
			"user_email",
			"display_name",
			"nickname",
			"first_name",
			"last_name",
			"description",
			"rich_editing",
			"syntax_highlighting",
			"comment_shortcuts",
			"admin_color",
			"use_ssl",
			"user_registered",
			"spam",
			"show_admin_bar_front",
			"role",
			"locale",
			"meta_input"
		);
		$request_obj = array(
			'user_login' => $request['user_login']
		);
		$http_request_obj = $request->get_json_params();
		foreach ($http_request_obj as $key => $value) {
			if((!empty($value)) && (in_array($key,$supported_fields))){
				$request_obj[$key] = $value;
			}
		}

		$user_id = wp_insert_user($request_obj);
		if(is_wp_error($user_id)){
			return new WP_Error( 'rest_bad_request', $user_id->get_error_messages()[0], array( 'status' => 400 ) );
		}
		return rest_ensure_response($this->get_user_with_meta($user_id));
	}

	//To update user
	public function update_user_v_1_1($request){
		$supported_fields = array(
			"user_pass",
			"user_login",
			"user_nicename",
			"user_url",
			"user_email",
			"display_name",
			"nickname",
			"first_name",
			"last_name",
			"description",
			"rich_editing",
			"syntax_highlighting",
			"comment_shortcuts",
			"admin_color",
			"use_ssl",
			"user_registered",
			"spam",
			"show_admin_bar_front",
			"role",
			"locale",
			"meta_input"
		);
		$request_obj = array(
			'ID' => $request['user_id']
		);
		$http_request_obj = $request->get_json_params();
		foreach ($http_request_obj as $key => $value) {
			if((!empty($value)) && (in_array($key,$supported_fields))){
				$request_obj[$key] = $value;
			}
		}
		$user_id = wp_update_user($request_obj);
		if(is_wp_error($user_id)){
			return new WP_Error( 'rest_bad_request', $user_id->get_error_messages()[0], array( 'status' => 400 ) );
		}
		return rest_ensure_response($this->get_user_with_meta($user_id));
	}

	//To add post (custom post type included)
	public function create_post_v_1_1($request){
		$supported_fields = array(
			"post_date",
			"post_date_gmt",
			"post_content",
			"post_content_filtered",
			"post_title",
			"post_excerpt",
			"post_status",
			"post_type",
			"comment_status",
			"ping_status",
			"post_password",
			"post_name",
			"to_ping",
			"pinged",
			"post_parent",
			"menu_order",
			"post_mime_type",
			"guid",
			"import_id",
			"post_category",
			"tags_input",
			"tax_input",
			"meta_input",
			"page_template",
			"post_author",
		);
		$request_obj = array(
			'post_title' => $request['post_title'],
			'post_content' => $request['post_content']
		);
		$http_request_obj = $request->get_json_params();
		foreach ($http_request_obj as $key => $value) {
			if((!empty($value)) && (in_array($key,$supported_fields))){
				$request_obj[$key] = $value;
			}
		}
		if((!empty($request['post_author'])) && ($request['post_type'] == 'post') && ($request['post_author'] != get_current_user_id())){
			if((current_user_can('edit_others_posts'))){
				if(user_can($request['post_author'], 'edit_posts')){
					$request_obj['post_author'] = $request['post_author'];
				}
				else{
					return new WP_Error( 'rest_bad_request', 'Selected user has no permission to create post.', array( 'status' => 400 ) );
				}
			}
			else{
				return new WP_Error( 'rest_bad_request', 'You have no permission to create posts for other users.', array( 'status' => 400 ) );
			}
		}
		if((!empty($request['post_status'])) && ($request['post_status'] == 'publish')){
			if(!current_user_can('publish_posts')){
				return new WP_Error( 'rest_bad_request', 'You have no permission to publish posts.', array( 'status' => 400 ) );
			}
		}
		kses_remove_filters();
		$post_id = wp_insert_post($request_obj, true, true);
		kses_init_filters();
		if(is_wp_error($post_id)){
			return new WP_Error( 'rest_bad_request', $post_id->get_error_messages()[0], array( 'status' => 400 ) );
		}
		return rest_ensure_response($this->get_post_with_meta($post_id));
	}

	public function update_post_v_1_1($request){
		if(!$this->is_valid_post($request['post_id'])){
			return new WP_Error( 'rest_bad_request', 'Invalid post', array( 'status' => 404 ) );
		}
		if ( ! current_user_can( 'edit_post', $request['post_id'] ) ) {
			return new WP_Error( 'rest_bad_request', 'You have no permission to update this post.', array( 'status' => 400 ) );
		}
		$supported_fields = array(
			"post_date",
			"post_date_gmt",
			"post_content",
			"post_content_filtered",
			"post_title",
			"post_excerpt",
			"post_status",
			"comment_status",
			"ping_status",
			"post_password",
			"post_name",
			"to_ping",
			"pinged",
			"post_parent",
			"menu_order",
			"post_mime_type",
			"guid",
			"post_category",
			"tags_input",
			"tax_input",
			"meta_input",
			"page_template",
			"post_type",
			"post_author"
		);
		$request_obj = array(
			'ID' => $request['post_id']
		);
		$http_request_obj = $request->get_json_params();
		foreach ($http_request_obj as $key => $value) {
			if((!empty($value)) && (in_array($key,$supported_fields))){
				$request_obj[$key] = $value;
			}
		}
		if((!empty($request['post_author'])) && ($request['post_type'] == 'post') && ($request['post_author'] != get_current_user_id())){
			if((current_user_can('edit_others_posts'))){
				if(user_can($request['post_author'], 'edit_posts')){
					$request_obj['post_author'] = $request['post_author'];
				}
				else{
					return new WP_Error( 'rest_bad_request', 'Selected user has no permission to update post.', array( 'status' => 400 ) );
				}
			}
			else{
				return new WP_Error( 'rest_bad_request', 'You have no permission to update posts for other users.', array( 'status' => 400 ) );
			}
		}
		if((!empty($request['post_status'])) && ($request['post_status'] == 'publish')){
			if(!current_user_can('publish_posts')){
				return new WP_Error( 'rest_bad_request', 'You have no permission to publish posts.', array( 'status' => 400 ) );
			}
		}
		kses_remove_filters();
		$post_id = wp_insert_post(array_merge(get_post($request['post_id'],'ARRAY_A'),$request_obj), true, true);
		kses_init_filters();
		if(is_wp_error($post_id)){
			return new WP_Error( 'rest_bad_request', $post_id->get_error_messages()[0], array( 'status' => 400 ) );
		}
		return rest_ensure_response($this->get_post_with_meta($post_id));
	}

	//To update post tags
	public function update_post_tag($request){
		if(!$this->is_valid_post($request['post_id'])){
			return new WP_Error( 'rest_bad_request', 'Invalid post', array( 'status' => 404 ) );
		}
		$http_request_obj = $request->get_json_params();
		$tag_obj = wp_set_post_tags($request['post_id'], $http_request_obj['tags'], (!empty($http_request_obj['append'])?$http_request_obj['append']:false));
		if(is_wp_error($tag_obj)){
			return new WP_Error( 'rest_bad_request', $tag_obj->get_error_messages()[0], array( 'status' => 400 ) );
		}
		return rest_ensure_response(array('message' => 'Success'));
	}
	//To update post categories
	public function update_post_categories($request){
		if(!$this->is_valid_post($request['post_id'])){
			return new WP_Error( 'rest_bad_request', 'Invalid post', array( 'status' => 404 ) );
		}
		$http_request_obj = $request->get_json_params();
		$tag_obj = wp_set_post_categories($request['post_id'], $http_request_obj['categories'], (!empty($http_request_obj['append'])?$http_request_obj['append']:false));
		if(is_wp_error($tag_obj)){
			return new WP_Error( 'rest_bad_request', $tag_obj->get_error_messages()[0], array( 'status' => 400 ) );
		}
		return rest_ensure_response(array('message' => 'Success'));
	}

	//To add comment
	public function create_comment($request){
		if(empty($request['comment_content'])){
			return new WP_Error( 'rest_bad_request', 'Comment content missing', array( 'status' => 400 ) );
		}
		$commentdata = array(
			'comment_content' => $request['comment_content'],
			'comment_parent' => (empty($request['comment_parent'])? 0 : $request['comment_parent']),
			'comment_post_ID' => (empty($request['comment_post_ID'])? '' : $request['comment_post_ID']),
		    'user_id' => ( ( isset($request['user_id'] ) ) && ( is_numeric($request['user_id'] ) ) && ( user_can( $request['user_id'], 'moderate_comments' ) ) ) ? $request['user_id'] : get_current_user_id()
		);
		$comment_response = wp_new_comment($commentdata, true);
		if(!is_wp_error($comment_response)){
			return rest_ensure_response(get_comment($comment_response));
		}
		else{
			return new WP_Error( 'rest_bad_request', $comment_response->get_error_messages()[0], array( 'status' => 400 ) );
		}
	}

	//To send an email using SMTP configuration
	public function send_mail($request){
		$subject = ((isset($request['subject'])) && (!empty($request['subject']))) ? $request['subject'] : ' ';
		$content = ((isset($request['content'])) && (!empty($request['content']))) ? $request['content'] : ' ';
		$attachments = array();
		if((isset($request['attachments'])) && (!empty($request['attachments']))){
			if(is_array($request['attachments'])){
				foreach ($request['attachments'] as $value) {
					if(!empty($value)) {
						if( strstr( $value, '/wp-content/uploads/') !== false ) {
							array_push($attachments, $value);
						}
						else{
							array_push($attachments, WP_CONTENT_DIR.'/uploads/'.$value);
						}
					}
				}
			}
			else{
					if( strstr( $request['attachments'], '/wp-content/uploads/') !== false ) {
					array_push($attachments, $request['attachments']);
				}
				else{
					array_push($attachments, WP_CONTENT_DIR.'/uploads/'.$request['attachments']);
				}
			}
		}
		$headers = array();
		if((isset($request['from'])) && (!empty($request['from']))){
			if(filter_var($request['from'], FILTER_VALIDATE_EMAIL)) {
				array_push($headers, 'From:'.$request['from']);
			}
		}
		$to = array();
		if((isset($request['to'])) && (!empty($request['to']))){
			if(is_array($request['to'])){
				foreach ($request['to'] as $value) {
					if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
						array_push($to, $value);
					}
				}
			}
			else{
				if(filter_var($request['to'], FILTER_VALIDATE_EMAIL)) {
					array_push($to, $request['to']);
				}
			}
		}
		$headers = array();
		if((isset($request['from'])) && (!empty($request['from']))){
			if(filter_var($request['from'], FILTER_VALIDATE_EMAIL)) {
				array_push($headers, 'From:'.$request['from']);
			}
		}
		if((isset($request['cc'])) && (!empty($request['cc']))){
			if(is_array($request['cc'])){
				foreach ($request['cc'] as $value) {
					if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
						array_push($headers, 'Cc:'.$value);
					}
				}
			}
			else{
				if(filter_var($request['cc'], FILTER_VALIDATE_EMAIL)) {
					array_push($headers, 'Cc:'.$request['cc']);
				}
			}
		}
		if((isset($request['bcc'])) && (!empty($request['bcc']))){
			if(is_array($request['bcc'])){
				foreach ($request['bcc'] as $value) {
					if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
						array_push($headers, 'Bcc:'.$value);
					}
				}
			}
			else{
				if(filter_var($request['bcc'], FILTER_VALIDATE_EMAIL)) {
					array_push($headers, 'Bcc:'.$request['bcc']);
				}
			}
		}
		if((isset($request['reply_to'])) && (!empty($request['reply_to']))){
			if(filter_var($request['reply_to'], FILTER_VALIDATE_EMAIL)) {
				array_push($headers, 'Reply-To:'.$request['reply_to']);
			}
		}
		if((isset($request['content_type'])) && (!empty($request['content_type']))){
			array_push($headers, 'Content-Type:'.$request['content_type']);
		}
		$email_send = wp_mail( $to, $subject, $content, $headers, $attachments );
		if($email_send){
			$return_array = array(
				"send" => $email_send,
				"message" => "Email sent successfully."
			);
			return rest_ensure_response($return_array);
		}
		return new WP_Error( 'rest_bad_request', 'There is an error in sending the email, Kindly check the inputs', array( 'status' => 400 ) );
	}

	//webhooks
	public static $supported_events = array("post_created","post_updated","post_created_or_updated","post_status_changed","user_created","user_created_or_updated","comment_created","comment_edited","comment_status_changed","user_login","attachment_added","mail_succeeded","mail_failed");

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
			if((($event == 'post_created') || ($event == 'post_status_changed') || ($event == 'post_created_or_updated') || ($event == 'post_updated')) && !empty($entry->post_type)){
				$args['post_type'] = $entry->post_type;
			}
			else if(($event == 'post_created') || ($event == 'post_status_changed') || ($event == 'post_created_or_updated') || ($event == 'post_updated')){
				return new WP_Error( 'rest_bad_request', 'Post type missing', array( 'status' => 400 ) );
			}
			$post_name = "Wordpress.org ";
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

	//for new post trigger
	public function payload_post_created($new_status, $old_status, $post){
	    if($new_status != $old_status){
	        if(($old_status == 'new' || $old_status == 'auto-draft') && ($new_status != 'auto-draft') && ($new_status != 'inherit')){
	            $args = array(
	                'event' => 'post_created',
	                'post_type' => $post->post_type
	            );
	            $webhooks = $this->get_webhook_posts($args);
	            if(!empty($webhooks) && (!empty($post))){
	                $post->permalink = get_permalink( $post->ID );
	                $event_data = array(
	                    'event' => 'post_created',
	                    'data' => $post,
	                    'meta' => $this->get_post_meta($post->ID),
	                    'taxonomy' => $this->get_post_taxonomy($post->ID)
	                );
	                foreach($webhooks as $webhook){
	                    $url = $webhook->url;
	                    zoho_flow_execute_webhook($url, $event_data,array());
	                }
	            }
	        }
	    }
	}

	//for post status change
	public function payload_post_status_changed($new_status, $old_status, $post){
	    if($new_status != $old_status){
	        if(($new_status != 'new' && $new_status != 'auto-draft') && ($new_status != 'inherit') && ($old_status != 'new')){
	            $args = array(
	                'event' => 'post_status_changed',
	                'post_type' => $post->post_type
	            );
	            $webhooks = $this->get_webhook_posts($args);
	            if(!empty($webhooks) && (!empty($post))){
	                $post->permalink = get_permalink( $post->ID );
	                $event_data = array(
	                    'event' => 'post_status_changed',
	                    'new_status' => $new_status,
	                    'old_status' => $old_status,
	                    'data' => $post,
	                    'meta' => $this->get_post_meta($post->ID),
	                    'taxonomy' => $this->get_post_taxonomy($post->ID)
	                );
	                foreach($webhooks as $webhook){
	                    $url = $webhook->url;
	                    zoho_flow_execute_webhook($url, $event_data,array());
	                }
	            }
	        }
	    }
	}

	//for new or updated post trigger
	public function payload_post_created_or_updated($new_status, $old_status, $post){
	    if(($new_status != 'auto-draft') && ($new_status != 'inherit')){
	        $args = array(
	            'event' => 'post_created_or_updated',
	            'post_type' => $post->post_type
	        );
	        $webhooks = $this->get_webhook_posts($args);
	        if(!empty($webhooks) && (!empty($post))){
	            $post->permalink = get_permalink( $post->ID );
	            $event_data = array(
	                'event' => 'post_created_or_updated',
	                'new_status' => $new_status,
	                'old_status' => $old_status,
	                'data' => $post,
	                'meta' => $this->get_post_meta($post->ID),
	                'taxonomy' => $this->get_post_taxonomy($post->ID)
	            );
	            foreach($webhooks as $webhook){
	                $url = $webhook->url;
	                zoho_flow_execute_webhook($url, $event_data,array());
	            }
	        }
	    }
	}

	//for updated post trigger
	public function payload_post_updated($new_status, $old_status, $post){
	    if(($old_status == 'new' || $old_status == 'auto-draft') && ($new_status != 'auto-draft') && ($new_status != 'inherit')){
	        //New post
	    }
	    else if(($new_status != 'auto-draft') && ($new_status != 'inherit')){
	        $args = array(
	            'event' => 'post_updated',
	            'post_type' => $post->post_type
	        );
	        $webhooks = $this->get_webhook_posts($args);
	        if(!empty($webhooks) && (!empty($post))){
	            $post->permalink = get_permalink( $post->ID );
	            $event_data = array(
	                'event' => 'post_updated',
	                'new_status' => $new_status,
	                'old_status' => $old_status,
	                'data' => $post,
	                'meta' => $this->get_post_meta($post->ID),
	                'taxonomy' => $this->get_post_taxonomy($post->ID)
	            );
	            foreach($webhooks as $webhook){
	                $url = $webhook->url;
	                zoho_flow_execute_webhook($url, $event_data,array());
	            }
	        }
	    }
	}

	public static $meta_keys_to_be_ignored = array(
	    "_edit_lock"
	);
	//for updated post trigger
	public function payload_post_meta_created_or_updated_for_post_update($meta_id, $post_id, $meta_key, $meta_value){
	    if( !in_array( $meta_key, self::$meta_keys_to_be_ignored ) ){
	        $post = get_post($post_id);
	        $args = array(
	            'event' => 'post_updated',
	            'post_type' => $post->post_type
	        );
	        $webhooks = $this->get_webhook_posts($args);
	        if(!empty($webhooks) && (!empty($post))){
	            $post->permalink = get_permalink( $post->ID );
	            $event_data = array(
	                'event' => 'post_updated',
	                "meta_id" => $meta_id,
	                "meta_key" => $meta_key,
	                "meta_value" => $meta_value,
	                'data' => $post,
	                'meta' => $this->get_post_meta($post_id),
	                'taxonomy' => $this->get_post_taxonomy($post_id)
	            );
	            foreach($webhooks as $webhook){
	                $url = $webhook->url;
	                zoho_flow_execute_webhook($url, $event_data,array());
	            }
	        }
	    }
	}

	//for created or updated post trigger
	public function payload_post_meta_created_or_updated_for_post_created_or_update($meta_id, $post_id, $meta_key, $meta_value){
	    if( !in_array( $meta_key, self::$meta_keys_to_be_ignored ) ){
	        $post = get_post($post_id);
	        $args = array(
	            'event' => 'post_created_or_updated',
	            'post_type' => $post->post_type
	        );
	        $webhooks = $this->get_webhook_posts($args);
	        if(!empty($webhooks) && (!empty($post))){
	            $post->permalink = get_permalink( $post->ID );
	            $event_data = array(
	                'event' => 'post_created_or_updated',
	                "meta_id" => $meta_id,
	                "meta_key" => $meta_key,
	                "meta_value" => $meta_value,
	                'data' => $post,
	                'meta' => $this->get_post_meta($post_id),
	                'taxonomy' => $this->get_post_taxonomy($post_id)
	            );
	            foreach($webhooks as $webhook){
	                $url = $webhook->url;
	                zoho_flow_execute_webhook($url, $event_data,array());
	            }
	        }
	    }
	}

	/**
	 * Fires after an object's terms have been set.
	 *
	 * @param  integer $post_id    ID of the post.
	 * @param  array   $terms      An array of object term IDs or slugs.
	 * @param  array   $tt_ids     An array of term taxonomy IDs.
	 * @param  string  $taxonomy   Taxonomy slug.
	 * @param  boolean $append     Whether to append new terms to the old terms.
	 * @param  array   $old_tt_ids Old array of term taxonomy IDs.
	 */
	public function payload_post_taxonomy_set_for_post_update($post_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids){
		$post = get_post($post_id);
		$args = array(
			'event' => 'post_updated',
			'post_type' => $post->post_type
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks) && (!empty($post)) && ($tt_ids != $old_tt_ids)){
		    $post->permalink = get_permalink( $post->ID );
			$event_data = array(
				'event' => 'post_updated',
				"taxonomy_name" => $taxonomy,
				"taxonomy_terms" => $terms,
				'data' => $post,
				'meta' => $this->get_post_meta($post_id),
				'taxonomy' => $this->get_post_taxonomy($post_id)
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	/**
	 * Fires after an object's terms have been set.
	 *
	 * @param  $post_id  	 Post ID.
	 * @param  $terms      An array of object term IDs or slugs.
	 * @param  $tt_ids     An array of term taxonomy IDs.
	 * @param  $taxonomy   Taxonomy slug.
	 * @param  $append     Whether to append new terms to the old terms.
	 * @param  $old_tt_ids Old array of term taxonomy IDs.
	 */
	public function payload_post_taxonomy_set_for_post_created_or_update($post_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids){
		$post = get_post($post_id);
		$args = array(
			'event' => 'post_created_or_updated',
			'post_type' => $post->post_type
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks) && (!empty($post)) && (!is_wp_error($post)) && ($tt_ids != $old_tt_ids)){
		    $post->permalink = get_permalink( $post->ID );
			$event_data = array(
				'event' => 'post_created_or_updated',
				"taxonomy_name" => $taxonomy,
				"taxonomy_terms" => $terms,
				'data' => $post,
				'meta' => $this->get_post_meta($post_id),
				'taxonomy' => $this->get_post_taxonomy($post_id)
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//for user created
	public function payload_user_created($user_id, $user_data){
		$args = array(
			'event' => 'user_created',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$user_data['ID'] = $user_id;
			unset($user_data['user_pass'],$user_data['user_activation_key']);
			$user_meta = $this->get_user_meta($user_id);
			unset($user_meta['user_pass'],$user_meta['user_activation_key']);
			$event_data = array(
				'event' => 'user_created',
				'data' => $user_data,
				'meta' => $user_meta
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//for user created
	public function payload_user_for_created_or_updated($user_id, $user_data){
		$args = array(
			'event' => 'user_created_or_updated',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$user_data['ID'] = $user_id;
			unset($user_data['user_pass'],$user_data['user_activation_key']);
			$user_meta = $this->get_user_meta($user_id);
			unset($user_meta['user_pass'],$user_meta['user_activation_key']);
			$event_data = array(
				'event' => 'user_created_or_updated',
				'data' => $user_data,
				'meta' => $user_meta
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//for user update
	//triggers for UI user creation as well.
	public function payload_user_created_or_updated($user_id, $old_user_data, $user_data){
		$args = array(
			'event' => 'user_created_or_updated',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$user_data['ID'] = $user_id;
			unset($user_data['user_pass'],$user_data['user_activation_key']);
			$user_meta = $this->get_user_meta($user_id);
			unset($user_meta['user_pass'],$user_meta['user_activation_key']);
			$event_data = array(
				'event' => 'user_created_or_updated',
				'data' => $user_data,
				'meta' => $user_meta
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//for user meta added or updated
	public function payload_user_meta_added_or_updated($meta_id, $user_id, $meta_key, $meta_value){

		$args = array(
			'event' => 'user_created_or_updated',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$user_data = $this->get_user_with_meta($user_id);
			$user_meta = $this->get_user_meta($user_id);
			$event_data = array(
				'event' => 'user_created_or_updated',
				"meta_id" => $meta_id,
				"meta_key" => $meta_key,
				"meta_value" => $meta_value,
				'data' => $user_data,
				'meta' => $user_meta
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//For new user signin
	public function payload_user_login($user_login, $user_data){
		$args = array(
			'event' => 'user_login',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$user_details = $user_data->data;
			$user_details->caps = $user_data->caps;
			$user_details->cap_key = $user_data->cap_key;
			$user_details->roles = $user_data->roles;
			$user_details->allcaps = $user_data->allcaps;
			$user_details->user_pass = ''; //Hidding password in return objects
			$event_data = array(
				'event' => 'user_login',
				'data' => $user_details,
				'meta' => $this->get_user_meta($user_data->ID)
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//For new comment added
	public function payload_comment_created($comment_id, $comment_approved, $commentdata){
		$args = array(
			'event' => 'comment_created',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$commentdata['comment_ID'] = $comment_id;
			$event_data = array(
				'event' => 'comment_created',
				'data' => $commentdata
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//For comment edit
	public function payload_comment_edited($comment_id, $commentdata){
		$args = array(
			'event' => 'comment_edited',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$commentdata['comment_ID'] = $comment_id;
			$event_data = array(
				'event' => 'comment_edited',
				'data' => $commentdata
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//For comment status change such as approve, spam
	public function payload_comment_status_transition($new_status, $old_status, $commentdata){
		$args = array(
			'event' => 'comment_status_changed',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$event_data = array(
				'event' => 'comment_status_changed',
				'new_status' => $new_status,
				'old_status' => $old_status,
				'data' => $commentdata
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}



	//for new attachment
	public function payload_attachment_added($post_id){
		$args = array(
			'event' => 'attachment_added',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$event_data = array(
				'event' => 'attachment_added',
				'data' => $this->get_post_with_meta($post_id)
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//for email send
	public function payload_mail_succeeded($mail_data){
		$args = array(
			'event' => 'mail_succeeded',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$event_data = array(
				'event' => 'mail_succeeded',
				'data' => $mail_data
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}

	//for email failed
	public function payload_mail_failed($mail_data){
		$args = array(
			'event' => 'mail_failed',
		);
		$webhooks = $this->get_webhook_posts($args);
		if(!empty($webhooks)){
			$event_data = array(
				'event' => 'mail_failed',
				'data' => $mail_data
			);
			foreach($webhooks as $webhook){
				$url = $webhook->url;
				zoho_flow_execute_webhook($url, $event_data,array());
			}
		}
	}


	//From v2.0.0
	//To get site related info
	public function get_site_details(){
		return rest_ensure_response( parent::get_site_info() );
	}

	public function get_system_info(){
		return rest_ensure_response( parent::get_system_info() );
	}
}
