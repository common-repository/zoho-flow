<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Zoho_Flow_LearnDash extends Zoho_Flow_Service
{

	public function process_enrolled_into_course($user_id, $course_id, $access_list, $remove) {
		$course_data=get_post($course_id);
		$course_data->{'post_meta'}=get_post_meta($course_id);
		$tax_terms = get_post_taxonomies($course_id);
		foreach($tax_terms as $term){
			$course_data->{$term} = get_the_terms($course_id, $term);
		}
		$data = array(
				"user_id"   => $user_id,
				"course_id" => $course_id,
				"course_data" => $course_data,
				"user_data" => get_user_by("id",$user_id),
				"access_list" => $access_list,
				"remove"=> $remove

			);
		if($remove)
		return rest_ensure_response($data);
		$args = array(
			'action' => 'course_enrolled',
			'form_id' => $course_id
		);

		$webhooks = $this->get_webhook_posts($args);
		foreach($webhooks as $webhook){
			$url = $webhook->url;
			$data['action']=$webhook->action;
			zoho_flow_execute_webhook($url, $data,array());
		}
	}

	public function process_group_enrolled( $user_id,$group_id ) {
		$args = array(
			'action' => 'group_enrolled',
			'form_id' => $group_id
		);
		$group_data=get_post($group_id);
		$group_data->{'post_meta'}=get_post_meta($group_id);
		$tax_terms = get_post_taxonomies($group_id);
		foreach($tax_terms as $term){
			$group_data->{$term} = get_the_terms($group_id, $term);
		}
		$data=array("user"=>get_user_by("id",$user_id),"group"=>$group_id,"group_data"=>$group_data);
		$webhooks = $this->get_webhook_posts($args);
		foreach($webhooks as $webhook){
			$url = $webhook->url;
			$data['action']=$webhook->action;
			zoho_flow_execute_webhook($url, $data,array());
		}

	}
	public function process_course_completed($data) {
	    $form_ids = array($data['course']->ID, 0);
	    foreach ($form_ids as $form_id) {
			$args = array(
				'action' => 'course_completed',
                		'form_id' => $form_id
			);

			$data["course"]->{'post_meta'}=get_post_meta($data["course"]->ID);
			$tax_terms = get_post_taxonomies($data["course"]->ID);
			foreach($tax_terms as $term){
				$data["course"]->{$term} = get_the_terms($data["course"]->ID, $term);
			}

			$webhooks = $this->get_webhook_posts($args);
			$result = array();
			if ( !empty( $webhooks ) ) {

				foreach ( $webhooks as $webhook ) {
					$url = $webhook->url;
					$data['action']=$webhook->action;
					zoho_flow_execute_webhook($url, $data,array());
				}

			}
	    }
	}

	public function get_ldquestions($request){
		$query_param=$request->get_query_params();
		$meta=array();
		if(!empty($query_param['course_id'])){
			$meta['key']='course_id';
			$meta['value']=$query_param['course_id'];
		}
		$data=$this->WordPressCustomQuery('sfwd-question',$query_param,$request,'date','DESC',$meta);
		if(!empty($query_param['quiz_id'])){
			$quiz_questions=learndash_get_quiz_questions($query_param['quiz_id']);
			$data=array();
			foreach(array_keys($quiz_questions) as $key){
				$quiz_data=get_post($key);
				$meta_data=get_post_meta($key);
				$quiz_data->{'post_meta'}=$meta_data;

				$quiz_data->{'question_type'}=$quiz_data->{'post_meta'}["question_type"][0];
				array_push($data,$quiz_data);
			}
		}else{
			foreach($data as $item){
				$item->{'post_meta'}=get_post_meta($item->{'ID'});
				$item->{'question_type'}=($item->{'post_meta'})["question_type"][0];
			}
		}

		return rest_ensure_response($data);
	}
	public function get_essay_submissions($request){

		$query_param=$request->get_query_params();

		$query_args = array(
			'post_type'         =>   'sfwd-essays',
			'posts_per_page'    =>   -1,
			'orderby'           =>   'date',
			'order'             =>   'DESC',
			'no_paging'			=> 	true,
		);
		if(sizeof($query_param)>0){
			foreach(array_keys($query_param) as $key){

				if($key=='id'||$key=='ID')
				$query_args['p']=$query_param[$key];
				else
				$query_args[$key]=$query_param[$key];
			}
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
	public function list_post_types($request){
		return rest_ensure_response(LDLMS_Post_Types::get_post_types());
	}
	public function process_lesson_completed($data) {
		if(!empty($data["user"]->ID) && !empty($data["lesson"]->ID) && !empty($data["course"]->ID)){
			$args = array(
				'action' => 'lesson_completed',
				'form_id' => $data["lesson"]->{'ID'}
			);
			$data["lesson"]->{'post_meta'}=get_post_meta($data["lesson"]->ID);
			$tax_terms = get_post_taxonomies($data["lesson"]->ID);
			foreach($tax_terms as $term){
				$data["lesson"]->{$term} = get_the_terms($data["lesson"]->ID, $term);
			}
			if($data["course"]->ID!=0){
				$data["course"]->{'post_meta'}=get_post_meta($data["course"]->ID);
				$tax_terms = get_post_taxonomies($data["course"]->ID);
				foreach($tax_terms as $term){
					$data["course"]->{$term} = get_the_terms($data["course"]->ID, $term);
				}
			}
			$webhooks = $this->get_webhook_posts($args);
			foreach ( $webhooks as $webhook ) {
				$url = $webhook->url;
				$data['action']=$webhook->action;
				zoho_flow_execute_webhook($url, $data,array());
			}
		}
	}
	public function process_essay_submitted($essay_id,$essay_arg){
		$question=$this->WordPressCustomQuery('sfwd-question',array("title"=>$essay_arg["post_title"]))[0];
		$question->{'post_meta'}=get_post_meta($question->ID);
		$tax_terms=get_post_taxonomies($question->ID);
		foreach($tax_terms as $term){
			$question->{$term}=get_the_terms($question->ID,$term);
		}
		$args = array(
			'action' => 'essay_submitted',
			'form_id' => $question->ID
		);
		$essay_data=get_post($essay_id);
		$essay_data->{'post_meta'}=get_post_meta($essay_id);
		$tax_terms=get_post_taxonomies($essay_id);
		foreach($tax_terms as $term){
			$essay_data->{$term}=get_the_terms($essay_id,$term);
		}
		$webhooks = $this->get_webhook_posts($args);
		foreach ( $webhooks as $webhook ) {
			zoho_flow_execute_webhook($webhook->url, array("essay_submission_id"=>$essay_id,"essay_data"=>get_post($essay_id),"action"=>$webhook->action,"question"=>$question,"essay_arg"=>$essay_arg),array());
		}
	}
	public function process_topic_completed($data) {
		$args = array(
			'action'=>"topic_completed",
			'form_id' => $data["topic"]->{"ID"}
		);
		$data["topic"]->{'post_meta'}=get_post_meta($data["topic"]->ID);
		$tax_terms = get_post_taxonomies($data["topic"]->ID);
		foreach($tax_terms as $term){
			$data["topic"]->{$term} = get_the_terms($data["topic"]->ID, $term);
		}
		if($data["course"]->ID!=0){
			$data["course"]->{'post_meta'}=get_post_meta($data["course"]->ID);
			$tax_terms = get_post_taxonomies($data["course"]->ID);
			foreach($tax_terms as $term){
				$data["course"]->{$term} = get_the_terms($data["course"]->ID, $term);
			}
		}
		if($data["lesson"]->ID!=0){
			$data["lesson"]->{'post_meta'}=get_post_meta($data["lesson"]->ID);
			$tax_terms = get_post_taxonomies($data["lesson"]->ID);
			foreach($tax_terms as $term){
				$data["lesson"]->{$term} = get_the_terms($data["lesson"]->ID, $term);
			}
		}
		$webhooks = $this->get_webhook_posts($args);
		foreach ( $webhooks as $webhook ) {
			$url = $webhook->url;
			$data['action']=$webhook->action;
			zoho_flow_execute_webhook($url, $data,array());
		}
	}

	function process_quiz_completed( $data, $user ) {
		if ( ! empty( $user->{'ID'} ) && ! empty( $data['quiz'] ) )  {
			$args = array(
				'action'=>"quiz_completed",
				'form_id' => $data["quiz"]
			);
			$data["quiz_data"]=get_post($data["quiz"]);
			$data["quiz_data"]->{'post_meta'}=get_post_meta($data["quiz"]);
			$tax_terms = get_post_taxonomies($data["quiz"]);
			foreach($tax_terms as $term){
				$data["quiz_data"]->{$term} = get_the_terms($data["quiz"], $term);
			}
			if(is_object($data["course"])){

				$data["course"]->{'post_meta'}=get_post_meta($data["course"]->ID);
				$tax_terms = get_post_taxonomies($data["course"]->ID);
				foreach($tax_terms as $term){
					$data["course"]->{$term} = get_the_terms($data["course"]->ID, $term);
				}
			}
			if(is_object($data["lesson"])){
				$data["lesson"]->{'post_meta'}=get_post_meta($data["lesson"]->ID);
				$tax_terms = get_post_taxonomies($data["lesson"]->ID);
				foreach($tax_terms as $term){
					$data["lesson"]->{$term} = get_the_terms($data["lesson"]->ID, $term);
				}
			}
			if(is_object($data["topic"])){
				$data["topic_data"]=get_post($data["topic"]->ID);
				$data["topic_data"]->{'post_meta'}=get_post_meta($data["topic"]);
				$tax_terms = get_post_taxonomies($data["topic"]);
				foreach($tax_terms as $term){
					$data["topic_data"]->{$term} = get_the_terms($data["topic"], $term);
				}
			}
			$webhooks = $this->get_webhook_posts($args);
			foreach ( $webhooks as $webhook ) {
				$url = $webhook->url;

				zoho_flow_execute_webhook($url, array("data"=>$data,"action"=>$webhook->action,"user"=>($user->{'data'})),array());
			}
		}
	}
	public function get_course_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'course',
            'type'                 => 'course',
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'Unique identifier for the object.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit','embed'),
                    'readonly'     => true,
                ),
                'link' => array(
                    'description'  => esc_html__( 'URL to the object.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit','embed'),
                ),
				'title' => array(
					'description' => esc_html__( 'The title for the object.','zoho-flow'),
					'type'         => 'string',
                    'context'      => array( 'view', 'edit','embed'),
				)
            ),
        );

        return $schema;
    }
	public function get_group_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'course',
            'type'                 => 'course',
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'Unique identifier for the object.', 'zoho-flow' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit','embed'),
                    'readonly'     => true,
                ),
                'link' => array(
                    'description'  => esc_html__( 'URL to the object.', 'zoho-flow' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit','embed'),
                ),
				'title' => array(
					'description' => esc_html__( 'The title for the object.','zoho-flow'),
					'type'         => 'string',
                    'context'      => array( 'view', 'edit','embed'),
				)
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
	public function get_users( $request ){
	  $data = array();
	  $schema = $this->get_user_schema();
		$query_param=$request->get_query_params();
		$arg=array();
		$arg['meta_query']=array();
		$users = array();
		if(!empty($query_param['user_id'])){
			$fetch_data = get_user_by("id",$query_param['user_id']);
			if($fetch_data){
				array_push($users, $fetch_data);
			}
			else{
				return rest_ensure_response(array());
			}
		}
		else if(!empty($query_param['user_email'])){
			$fetch_data = get_user_by("email",$query_param['user_email']);
			if($fetch_data){
				array_push($users, $fetch_data);
			}
			else{
				return rest_ensure_response(array());
			}
		}
		else if(!empty($query_param['user_login'])){
			$fetch_data = get_user_by("login",$query_param['user_login']);
			if($fetch_data){
				array_push($users, $fetch_data);
			}
			else{
				return rest_ensure_response(array());
			}
		}
		else{
			$users = array_merge(
	    		get_users( array(
		    		'orderby' => 'ID',
		    		'order' => 'ASC',
		    		'role__in' => array( 'administrator','editor', 'author' ),
		    		'number'=> 500,
		    		'paged'=> 1,
		    		'count_total'=> false  ) ),
	    		get_users( array(
		    		'orderby' => 'ID',
		    		'order' => 'DESC',
		    		'role__not_in' => array( 'administrator','editor', 'author' ),
		    		'number'=> 500,
		    		'paged'=> 1,
		    		'count_total'=> false  ) ) );
		}
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

	public function enroll_user_to_course($request){
		$course_id = $request['course_id'];
		$r_user_id = $request['user_id'];
		$users = explode (",", $r_user_id);
		$duplicate_users = array();
		$new_users = array();
		$invalid_users = array();
		if((!is_numeric($course_id)) || (!learndash_is_course_post($course_id))){
			return new WP_Error( 'rest_bad_request', 'Invalid Course ID ' .$course_id. ' provided', array( 'status' => 400 ) );
		}
		if(!is_array($users)){
			return new WP_Error( 'rest_bad_request', 'Invalid Users', array( 'status' => 400 ) );
		}
		else{
			$users_of_course = learndash_get_users_for_course($request['course_id']);
			$enrolled_users = array();
			if(is_object($users_of_course)){
				$enrolled_users = $users_of_course->{'query_vars'}['include'];
			}
			foreach ($users as $user_id) {
				$user = New WP_User($user_id);
				if((is_numeric($user_id)) && ($user->exists())){
					if(in_array($user_id, $enrolled_users)){
						array_push($duplicate_users, (int)$user_id);
					}
					else{
						array_push($new_users, (int)$user_id);
					}
				}
				else{
					array_push($invalid_users, $user_id);
				}
			}
		}
		if(sizeof($new_users)>0){
			learndash_set_users_for_course($course_id, array_merge($enrolled_users,$new_users));
		}
		$updated_users_of_course = learndash_get_users_for_course($course_id);
		$updated_enrolled_users = $updated_users_of_course->{'query_vars'}['include'];
		$data = array(
			"course_id" => (int)$course_id,
			"duplicate_users" => $duplicate_users,
			"new_users" => $new_users,
			"invalid_users" => $invalid_users,
			"course_users" => $updated_enrolled_users
		);
		return rest_ensure_response(array('data'=>$data));
	}

	public function remove_users_from_course($request){
		$course_id = $request['course_id'];
		$r_user_id = $request['user_id'];
		$users = explode (",", $r_user_id);
		$removed_users = array();
		$users_not_in_group = array();
		$invalid_users = array();
		if((!is_numeric($course_id)) || (!learndash_is_course_post($course_id))){
			return new WP_Error( 'rest_bad_request', 'Invalid Course ID ' .$course_id. ' provided', array( 'status' => 400 ) );
		}
		if(!is_array($users)){
			return new WP_Error( 'rest_bad_request', 'Invalid Users', array( 'status' => 400 ) );
		}
		else{
			$users_of_course = learndash_get_users_for_course($request['course_id']);
			$enrolled_users = array();
			if(is_object($users_of_course)){
				$enrolled_users = $users_of_course->{'query_vars'}['include'];
			}
			foreach ($users as $user_id) {
				$user = New WP_User($user_id);
				if((is_numeric($user_id)) && ($user->exists())){
					if(in_array($user_id, $enrolled_users)){
						array_push($removed_users, (int)$user_id);
					}
					else{
						array_push($users_not_in_group, (int)$user_id);
					}
				}
				else{
					array_push($invalid_users, $user_id);
				}
			}
		}
		if(sizeof($removed_users)>0){
			learndash_set_users_for_course($course_id, array_diff($enrolled_users,$removed_users));
		}
		$updated_users_of_course = learndash_get_users_for_course($course_id);
		$updated_enrolled_users = $updated_users_of_course->{'query_vars'}['include'];
		$data = array(
			"course_id" => (int)$course_id,
			"removed_users" => $removed_users,
			"users_not_in_group" => $users_not_in_group,
			"invalid_users" => $invalid_users,
			"course_users" => $updated_enrolled_users
		);
		return rest_ensure_response(array('data'=>$data));
	}

	public function add_users_to_group($request){
		$group_id = $request['group_id'];
		$users = $request['users'];
		$duplicate_users = array();
		$new_users = array();
		$invalid_users = array();
		if((!is_numeric($group_id)) || (!learndash_is_group_post($group_id))){
			return new WP_Error( 'rest_bad_request', 'Invalid Group ID ' .$group_id. ' provided', array( 'status' => 400 ) );
		}
		if(!is_array($users)){
			return new WP_Error( 'rest_bad_request', 'Invalid Users', array( 'status' => 400 ) );
		}
		else{
			foreach ($users as $user_id) {
				$user = New WP_User($user_id);
				if((is_numeric($user_id)) && ($user->exists())){
					if(learndash_is_user_in_group($user_id, $group_id)){
						array_push($duplicate_users, (int)$user_id);
					}
					else{
						array_push($new_users, (int)$user_id);
					}
				}
				else{
					array_push($invalid_users, $user_id);
				}
			}
		}
		if(sizeof($new_users)>0){
			$existing_users = learndash_get_groups_user_ids($group_id);
			learndash_set_groups_users($group_id, array_merge($existing_users,$new_users));
		}
		$data = array(
			"group_id" => (int)$group_id,
			"duplicate_users" => $duplicate_users,
			"new_users" => $new_users,
			"invalid_users" => $invalid_users,
			"users" => learndash_get_groups_user_ids($group_id)
		);
		return rest_ensure_response(array('data'=>$data));
	}

	public function remove_users_from_group($request){
		$group_id = $request['group_id'];
		$users = $request['users'];
		$removed_users = array();
		$users_not_in_group = array();
		$invalid_users = array();
		if((!is_numeric($group_id)) || (!learndash_is_group_post($group_id))){
			return new WP_Error( 'rest_bad_request', 'Invalid Group ID ' .$group_id. ' provided', array( 'status' => 400 ) );
		}
		if(!is_array($users)){
			return new WP_Error( 'rest_bad_request', 'Invalid Users', array( 'status' => 400 ) );
		}
		else{
			foreach ($users as $user_id) {
				$user = New WP_User($user_id);
				if((is_numeric($user_id)) && ($user->exists())){
					if(learndash_is_user_in_group($user_id, $group_id)){
						array_push($removed_users, (int)$user_id);
					}
					else{
						array_push($users_not_in_group, (int)$user_id);
					}
				}
				else{
					array_push($invalid_users, $user_id);
				}
			}
		}
		if(sizeof($removed_users)>0){
			$existing_users = learndash_get_groups_user_ids($group_id);
			learndash_set_groups_users($group_id, array_diff($existing_users,$removed_users));
		}
		$data = array(
			"group_id" => (int)$group_id,
			"removed_users" => $removed_users,
			"users_not_in_group" => $users_not_in_group,
			"invalid_users" => $invalid_users,
			"users" => learndash_get_groups_user_ids($group_id)
		);
		return rest_ensure_response(array('data'=>$data));
	}

	public function group_users($request){
		$group_users=learndash_get_groups_users((int)$request['group_id']);
		return rest_ensure_response($group_users);
	}
	public function user_courses($request){

		$user_courses=learndash_user_get_enrolled_courses($request['user_id']);
		$response=array();
		foreach($user_courses as $course){
			array_push($response,$this->WordPressCustomQuery('sfwd-courses',array('id'=>$course))[0]);
		}
		return rest_ensure_response($response);
	}
	function WordPressCustomQuery($post_type,$query_param,$request=array("course_id"=>-1),$orderby='data',$order='DESC',$meta=array()){
		$query_args = array(
			'post_type'         =>   $post_type,
			'posts_per_page'    =>   -1,
			'orderby'           =>   $orderby,
			'order'             =>   $order,
			'no_paging'			=> 	true,
		);
		if(!empty($meta)){
			$query_args['meta_key']=$meta['key'];
			$query_args['meta_value']=$meta['value'];
			$query_args['meta_compare']='=';
		}
		if(sizeof($query_param)>0){
			foreach(array_keys($query_param) as $key){
				$query_args[$key]=$query_param[$key];
				if($key=='id'||$key=='ID')
				$query_args['p']=$query_param[$key];
			}
		}
		if($request['course_id']!=-1 && ctype_digit($request['course_id'])){
			$query_args['p']=$request['course_id'];
		}
		$query_results = new WP_Query( $query_args );

		if(empty($query_results->posts)){
			return rest_ensure_response(array());
		}
		if(is_object($query_results->posts)){
			return rest_ensure_response(array($query_results->posts));
		}
			else{
			return $query_results->posts;
		}
	}
	public function get_courses($request){


		$query_param=$request->get_query_params();

		$query_args = array(
			'post_type'         =>   'sfwd-courses',
			'posts_per_page'    =>   -1,
			'orderby'           =>   'date',
			'order'             =>   'DESC',
			'no_paging'			=> 	true,
		);
		if(sizeof($query_param)>0){
			foreach(array_keys($query_param) as $key){
				$query_args[$key]=$query_param[$key];
				if($key=='id'||$key=='ID'){
					if(!ctype_digit($query_param[$key])){
						return rest_ensure_response(array());
					}
					$query_args['p']=(int)$query_param[$key];
				}
			}
		}
		if($request['course_id'] && ctype_digit($request['course_id'])){
			$query_args['p']=$request['course_id'];
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
	public function get_course($request){
		$attr=array("course_id"=>$request["course_id"]);

		return rest_ensure_response(learndash_courseinfo($attr));
	}
	public function get_lessons_legacy($request){
		if($request['course_id']===null || !ctype_digit($request['course_id'])){

			return new WP_Error( 'rest_bad_request', esc_html__( 'The course ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );

		}
		$query_param=$request->get_query_params();
		if($query_param['per_page']==null){
			$query_param['per_page']=100;
		}
		$course_id=$request['course_id'];

		return rest_ensure_response(learndash_course_get_lessons($course_id,$query_param));

	}
	public function get_lessons($request){
		if($request['course_id']===null || !ctype_digit($request['course_id'])){

			return new WP_Error( 'rest_bad_request', esc_html__( 'The course ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );

		}
		$course_id=$request['course_id'];
		$query_param=$request->get_query_params();
		$meta_query=array(
			array(
				'key'=> 'course_id',
				'value'=>$course_id,
				'compare'=> '='
			)
		);
		if($query_param){
			$meta_query['relation']='AND';
			foreach (array_keys($query_param) as $key){
				array_push($meta_query,array(
					'key' => $key,
					'value' => $query_param[$key],
					'compare' => '='
				));
			}
		}

			if ( !empty( $course_id ) ) {
				$query_args = array(
					'post_type'         =>   'sfwd-lessons',
					'posts_per_page'    =>   -1,
					'orderby'           =>   'date',
					'order'             =>   'DESC',
					'meta_key'          => 'course_id',
					'meta_value'        => $course_id,
					'meta_compare'      => '=',
					'no_paging'			=> 	true,
				);
				if(sizeof($query_param)>0){
					foreach(array_keys($query_param) as $key){
						$query_args[$key]=$query_param[$key];
						if($key=='id'||$key=='ID')
						$query_args['p']=$query_param[$key];
					}
				}
				$query_results = new WP_Query( $query_args );
			}

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

	public function get_topics($request){

		if($request['lesson_id']==null || !ctype_digit($request['lesson_id'])){

			return new WP_Error( 'rest_bad_request', esc_html__( 'The lesson ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );

		}
		$course_id=$request['course_id'];
		$lesson_id=$request['lesson_id'];
		$query_param=$request->get_query_params();
		$meta_query=array(
			array(
				'key'=> 'course_id',
				'value'=>$course_id,
				'compare'=> '='
			)
		);
		if($query_param){
			$meta_query['relation']='AND';
			foreach (array_keys($query_param) as $key){
				array_push($meta_query,array(
					'key' => $key,
					'value' => $query_param[$key],
					'compare' => '='
				));
			}
		}


			if ( !empty( $lesson_id ) ) {
				$query_args = array(
					'post_type'         =>   'sfwd-topic',
					'posts_per_page'    =>   -1,
					'orderby'           =>   'date',
					'order'             =>   'DESC',
					'meta_key'          => 'lesson_id',
					'meta_value'        => $lesson_id,
					'meta_compare'      => '=',
					'no_paging'			=> 	true,
				);
				if(sizeof($query_param)>0){
					foreach(array_keys($query_param) as $key){
						$query_args[$key]=$query_param[$key];
						if($key=='id'||$key=='ID')
						$query_args['p']=$query_param[$key];
					}
				}
				$query_results = new WP_Query( $query_args );

			}
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
	public function get_quizzes($request){
		$course_id=$request['course_id'];
		$query_param=$request->get_query_params();
		$meta_query=array(
			array(
				'key'=> 'course_id',
				'value'=>$course_id,
				'compare'=> '='
			)
		);
		if($query_param){
			$meta_query['relation']='AND';
			foreach (array_keys($query_param) as $key){
				array_push($meta_query,array(
					'key' => $key,
					'value' => $query_param[$key],
					'compare' => '='
				));
			}
		}


			if ( !empty( $course_id ) ) {
				$query_args = array(
					'post_type'         =>   'sfwd-quiz',
					'posts_per_page'    =>   -1,
					'orderby'           =>   'title',
					'order'             =>   'ASC',
					'meta_key'          => 'course_id',
					'meta_value'        => $course_id,
					'meta_compare'      => '=',
					'no_paging'			=> 	true,
				);
				if(sizeof($query_param)>0){
					foreach(array_keys($query_param) as $key){
						$query_args[$key]=$query_param[$key];
						if($key=='id'||$key=='ID')
						$query_args['p']=$query_param[$key];
					}
				}
				$query_results = new WP_Query( $query_args );
			}

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
	public function get_groups($request){
		$query_param=$request->get_query_params();
		$query_args = array(
					'post_type'         =>   'groups',
					'posts_per_page'    =>   -1,
					'orderby'           =>   'date',
					'order'             =>   'DESC',
					'no_paging'			=> 	true
				);
		if(sizeof($query_param)>0){
			foreach(array_keys($query_param) as $key){
				$query_args[$key]=$query_param[$key];
				if($key=='id'||$key=='ID'){
					if(!ctype_digit($query_param[$key])){
						return rest_ensure_response(array());
					}
					$query_args['p']=(int)$query_param[$key];
				}

			}
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

	public function create_webhook( $request ) {
		$form=$request['action'];
        $form_id = $request['form_id'];
        $url = esc_url_raw($request['url']);


        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        } else if ($form_id!=0){
    		if($form=="course_completed"){
            $form_data = get_post( $form_id );
    		}
    		if($form=="topic_completed"){
    			$form_data = get_post( $form_id );

    		}
    		if($form=="lesson_completed"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="quiz_completed"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="essay_submitted"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="group_completed"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="course_enrolled"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="group_enrolled"){
    			$form_data = get_post( $form_id );
    		}

    		if(!$form_data || (!in_array(($form_data->{'post_type'}),LDLMS_Post_Types::get_post_types()))){
    			return new WP_Error( 'rest_not_found', esc_html__( 'The '.substr($form,0,strrpos($form,'_')).' is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
    		}
        }

        $form_title = (empty($form_data) ? 'all' : $form_data->{'title'});
        $form_id = (empty($form_data) ? $form_id : $form_data->{'ID'});

        $post_id = $this->create_webhook_post($form_title, array(
			'action'=>$form,
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
		$form=$request['action'];


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
	public function get_webhooks( $request ) {
        $form=$request['action'];
        $form_id = $request['form_id'];
        if(!ctype_digit($form_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        } else if($form_id!=0){
    		if($form=="course_completed"){
            $form_data = get_post( $form_id );
    		}
    		if($form=="topic_completed"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="lesson_completed"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="quiz_completed"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="essay_submitted"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="group_completed"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="course_enrolled"){
    			$form_data = get_post( $form_id );
    		}
    		if($form=="group_enrolled"){
    			$form_data = get_post( $form_id );
    		}



    		if(!$form_data || (!in_array(($form_data->{'post_type'}),LDLMS_Post_Types::get_post_types()))){
    			return new WP_Error( 'rest_not_found', esc_html__( 'The '.substr($form,0,strrpos($form,'_')).' is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
    		}
        }

        $args = array(
            'action' => $form,
            'form_id' => (empty($form_data)) ? $form_id : $form_data->{"ID"}
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
                'form_id' => $webhook->{"form_id"},
                'url' => $webhook->url,
				'action' => $webhook->action
            );
            array_push($data, $webhook);
        }
        return rest_ensure_response( $data );
    }
	public function get_all_webhooks(){
		$webhooks = $this->get_webhook_posts(array());
        if ( empty( $webhooks ) ) {
            return rest_ensure_response( array() );
        }


        $data = array();

        foreach ( $webhooks as $webhook ) {
            $webhook = array(
                'plugin_service' => $this->get_service_name(),
                'id' => $webhook->ID,
                'form_id' => $webhook->{"form_id"},
                'url' => $webhook->url,
				'action' => $webhook->action
            );
            array_push($data, $webhook);
        }
        return rest_ensure_response( $data );
	}
	public function get_system_info(){
		$system_info = parent::get_system_info();
		if( ! function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_dir = ABSPATH . 'wp-content/plugins/sfwd-lms/sfwd_lms.php';
		if(file_exists($plugin_dir)){
			$plugin_data = get_plugin_data( $plugin_dir );
			$system_info['learndash_plugin'] = $plugin_data['Version'];
		}
		return rest_ensure_response( $system_info );
	}

}
