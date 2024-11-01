<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
//CONSTANTS
define ( 'COURSE_COMPLETED', 'course_completed');
define ( 'LESSON_COMPLETED', 'lesson_completed');
define ( 'QUIZ_COMPLETED', 'quiz_completed');
define ( 'USER_ENROLLED_TO_COURSE', 'user_enrolled_to_course');
define ( 'USER_REMOVED_FROM_COURSE', 'user_removed_from_course');
define ( 'USER_ENROLLED_TO_MEMBERSHIP', 'user_enrolled_to_membership');
define ( 'USER_REMOVED_FROM_MEMBERSHIP', 'user_removed_from_membership');


class Zoho_Flow_Lifter_LMS extends Zoho_Flow_Service
{
    /**
     * Get list of course details.
     * @param WP_REST_Request $request
     * @return WP_REST_Response Array of Course details | WP_Error Error details
     */
    public function get_courses($request) {
        
        $courses_list  = $this->handle_get_queries($request, 'course');
        $courses = array();
        foreach ( $courses_list as $form ){
            $courses[] = array(
                "ID" => $form->ID,
                "post_title" => $form->post_title,
                "post_status" => $form->post_status,
                "post_author" => $form->post_author,
                "post_date" => $form->post_date,
                "post_modified" => $form->post_modified
            );
        }
        return rest_ensure_response($courses);
    }

    /**
     * Get list of forms details.
     * @param WP_REST_Request $request
     * @return WP_REST_Response Array of Form details | WP_Error Error Details
     */
    public function get_forms($request) {
        $forms_list = $this->handle_get_queries($request, 'llms_form');
        $forms = array();
        foreach ( $forms_list as $form ){
            $forms[] = array(
                "ID" => $form->ID,
                "post_title" => $form->post_title,
                "post_status" => $form->post_status,
                "post_author" => $form->post_author,
                "post_date" => $form->post_date,
                "post_modified" => $form->post_modified,
                "llms_form_location" => $form->post_meta['_llms_form_location'][0]
            );
        }
        return rest_ensure_response($forms);
    }

    /**
     * Get list of lessons.
     * @param WP_REST_Request $request
     * @return WP_REST_Response Array of Lesson details | WP_Error Error Details
     */
    public function get_lessons($request){
		if(isset($request['data_id']) && $request['data_id'] != null){
			$courseObj = new LLMS_Course($request['data_id']);
			$lessons = $courseObj->get_lessons();

			$ret = array();
			foreach ($lessons as $key => $value) {
				array_push($ret, $value->post);
			}
			return $ret;
		}else{
			return rest_ensure_response($this->handle_get_queries($request, 'lesson'));
		}
    }

    /**
     * Get list of sections.
     * @param WP_REST_Request $request
     * @return WP_REST_Response Array of sections details | WP_Error Error Details
     */
    public function get_sections($request) {
        $sections_list = $this->handle_get_queries($request, 'section');
        $sections = array();
        foreach ( $sections_list as $form ){
            $sections[] = array(
                "ID" => $form->ID,
                "post_title" => $form->post_title,
                "post_status" => $form->post_status,
                "post_author" => $form->post_author,
                "post_date" => $form->post_date,
                "post_modified" => $form->post_modified,
            );
        }
        return rest_ensure_response($sections);
    }

    /**
     * Get list of quizzes.
     * @param WP_REST_Request $request
     * @return WP_REST_Response Array of quiz details | WP_Error Error Details
     */
    public function get_quizzes($request) {
		if(isset($request['data_id']) && $request['data_id'] != null){
			$lessonObj = new LLMS_Lesson($request['data_id']);
			$course_id = $lessonObj->get_course()->post->ID;
			$courseObj = new LLMS_Course($course_id);
			$quizzes = $courseObj->get_quizzes();

			$ret = array();
			foreach ($quizzes as $key => $value) {
			    $quiz = $this->handle_get_queries(array("ID"=>$value), "llms_quiz");
			    $quizArr = array(
			        "ID" => $quiz->ID,
			        "post_title" => $quiz->post_title,
			        "post_status" => $quiz->post_status,
			        "post_author" => $quiz->post_author,
			        "post_date" => $quiz->post_date,
			        "post_modified" => $quiz->post_modified,
			    );
			    array_push($ret, $quizArr);
			}
			return $ret;
		}else{
		    $quizzes_list = $this->handle_get_queries($request, 'llms_quiz');
		    $quizzes = array();
		    foreach ($quizzes_list as $quiz){
    		    $quizzes[] = array(
    		        "ID" => $quiz->ID,
    		        "post_title" => $quiz->post_title,
    		        "post_status" => $quiz->post_status,
    		        "post_author" => $quiz->post_author,
    		        "post_date" => $quiz->post_date,
    		        "post_modified" => $quiz->post_modified,
    		    );
		    }
		    return rest_ensure_response($quizzes);
		}
    }

    /**
     * Get list of questions.
     * @param WP_REST_Request $request
     * @return WP_REST_Response Array of question details | WP_Error Error Details
     */
    public function get_questions($request) {
        $questions_list = $this->handle_get_queries($request, 'llms_question');
        $questions = array();
        foreach ( $questions_list as $form ){
            $questions[] = array(
                "ID" => $form->ID,
                "post_title" => $form->post_title,
                "post_status" => $form->post_status,
                "post_author" => $form->post_author,
                "post_date" => $form->post_date,
                "post_modified" => $form->post_modified,
            );
        }
        return rest_ensure_response($questions);
    }

    /**
     * Get list of memberships.
     * @param WP_REST_Request $request
     * @return WP_REST_Response Array of membership details | WP_Error Error Details
     */
    public function get_memberships($request){
        $memberships_list = $this->handle_get_queries($request, 'llms_membership');
        $memberships = array();
        foreach ( $memberships_list as $form ){
            $memberships[] = array(
                "ID" => $form->ID,
                "post_title" => $form->post_title,
                "post_status" => $form->post_status,
                "post_author" => $form->post_author,
                "post_date" => $form->post_date,
                "post_modified" => $form->post_modified,
            );
        }
        return rest_ensure_response($memberships);
    }

    /**
     * Get list of access plans.
     * @param WP_REST_Request $request
     * @return WP_REST_Response Array of access plans details | WP_Error Error Details
     */
    public function get_access_plans($request){
        $access_plans_list = $this->handle_get_queries($request, 'llms_access_plan');
        $access_plans = array();
        foreach ( $access_plans_list as $form ){
            $access_plans[] = array(
                "ID" => $form->ID,
                "post_title" => $form->post_title,
                "post_status" => $form->post_status,
                "post_author" => $form->post_author,
                "post_date" => $form->post_date,
                "post_modified" => $form->post_modified,
            );
        }
        return rest_ensure_response($access_plans);
    }

    /**
     * Get list of users.
     * @param WP_REST_Request $request
     * @return WP_REST_Response Array of user details | WP_Error Error Details
     */
    public function get_users( $request ){
        $data = array();
        $users = get_users();
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

    /**
     * Get form using the location
     * @param WP_REST_Request $request
     * @param location "checkout", "registration", or "account"
     * @return WP_REST_Response Form details | WP_Error Error details
     */
    public function get_form($request) {
        $location = esc_attr($request['location']);
        $LLMS_forms = LLMS_Forms::instance();
        if($LLMS_forms->is_location_valid($location)){
            $forms = llms_get_form($location);
        }else {
            echo 'form location is invalid';
        }
        if(!empty($forms)){
            return rest_ensure_response($forms);
        }else {
            return rest_ensure_response(array());
        }
        return rest_ensure_response(array());
    }
    
    /**
     * Get form fields using the location
     * @param WP_REST_Request $request
     * @param location "checkout", "registration", or "account"
     * @return WP_REST_Response Form fields details | WP_Error Error details
     */
    public function get_form_fields($request) {
        $LLMS_forms = LLMS_Forms::instance();
        $location = $request['location'];
        $fields = array();

        $lists = $LLMS_forms->get_form_fields($location);
        foreach ($lists as $list) {
            if($list['type'] != 'password'){
                $data = array(
                    'id' => $list['id'],
                    'label' => $list['label'],
                    'name' => $list['name'],
                    'type' => $list['type'],
                    'required' => $list['required'],
                );
            }

            array_push($fields, $data);
        }
        if(!empty($fields)){
            return rest_ensure_response($fields);
        }else {
            return rest_ensure_response(array());
        }
        return rest_ensure_response($fields);
    }

    /**
     * Get list of quiz attempts
     * @param WP_REST_Request $request
     * @param location "checkout", "registration", or "account"
     * @return Quiz attempts| WP_Error Error details
     */
    /**
     * Get quiz attempts details
     * @param integer $args (quiz id, user id, lesson id )
     * @return Object of Quiz attempts details 
     */
	private function get_quiz_attempts($args){
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix . "lifterlms_quiz_attempts WHERE quiz_id= %d AND student_id= %d AND lesson_id= %d ORDER BY end_date DESC", array(absint( intval( $args['quiz_id'] ) ) , absint( intval( $args['student_id'] ) ), absint(intval($args['lesson_id']))));

		$result = $wpdb->get_results( $sql );
		return $result[0];
	}

	/**
	 * Handle the query to DB to get the required details as per post type
	 * @param WP_REST_Request $request
	 * @param string $post_type
	 * @return WP_REST_Response Details of provide post type | WP_Error Error details | WP_Post[] | number[]
	 */
    public function handle_get_queries($request, $post_type) {
        $url_params = array();
        if(gettype($request) == "array"){
            $url_params = $request;
        } else {
            $query_param = $request->get_query_params();
            $url_params = $request->get_url_params();
        }

        if(array_key_exists('form_id', $url_params)) {
            $query_param['ID'] = $url_params['form_id'];
        }
        if(array_key_exists("data_id", $url_params)){
            $query_param['ID'] = $url_params['data_id'];
        }

        $query_args = array(
            'post_type'         =>   $post_type,
            'posts_per_page'    =>   500,
            'orderby'           =>   'date',
            'order'             =>   'DESC',
            'no_paging'			=>   true,
            'post_status'       =>   'publish'
        );
        
        if(sizeof($query_param)>0){
            foreach(array_keys($query_param) as $key){
                $query_args[$key]=$query_param[$key];
                if($key=='id'||$key=='ID')
                    $query_args['p']=$query_param[$key];
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

    /**
     * Fires after user created or updated
     * @param int   $id     Form ID 
     * @param Object $posted_data Submitted form details
     * @param string $location Form location
     * @return WP_Error Error details
     */
    public function process_form_submission($id, $posted_data, $location) {
        $form = llms_get_form($location);

        if(!$form){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }
        $args = array(
            'form_id' => $form->ID,
            'action' => 'forms'
        );
        $webhooks = $this->get_webhook_posts($args);
        if($location == 'registration' || $location == 'account' || $location =='checkout'){
            unset($posted_data['password'], $posted_data['password_confirm']);
        }

        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            zoho_flow_execute_webhook($url, $posted_data, array());
        }
    }

    //Hooks
    /**
     * Fires after student enrolled to a course
     * @param int   $student_id     User ID
     * @param int   $course_id      Course ID
     */
    public function process_llms_user_enrolled_in_course($student_id, $course_id){
        return rest_ensure_response($this->process_hookdata($student_id, $course_id, USER_ENROLLED_TO_COURSE, null));
    }
    
    /**
     * Fires after student removed from a course
     * @param int   $student_id     User ID
     * @param int   $course_id      Course ID
     */
    public function process_llms_user_removed_from_course($student_id, $course_id){
        return rest_ensure_response($this->process_hookdata($student_id, $course_id, USER_REMOVED_FROM_COURSE, null));
    }
    
    /**
     * Fires after student enrolled to a membership
     * @param int       $student_id         User ID
     * @param int       $membership_id      Membership ID
     */
    public function process_llms_user_added_to_membership_level($student_id, $membership_id){
        return rest_ensure_response($this->process_hookdata($student_id, $membership_id, USER_ENROLLED_TO_MEMBERSHIP, null));
    }
    
    /**
     * Fires after a student remove from a membership
     * @param int       $student_id         User ID
     * @param int       $membership_id      Membership ID
     * @param string $trigger
     * @param string  $new_status
     */
    public function process_llms_user_removed_from_membership_level($student_id, $membership_id, $trigger, $new_status){
        return rest_ensure_response($this->process_hookdata($student_id, $membership_id, USER_REMOVED_FROM_MEMBERSHIP, null));
    }
    
    /**
     * Fires after student completes a lesson
     * @param int   $user_id        User ID
     * @param int   $lesson_id      Lesson ID
     */
    public function process_lifterlms_lesson_completed($user_id, $lesson_id){
        return rest_ensure_response($this->process_hookdata($user_id, $lesson_id, LESSON_COMPLETED, null));
    }
    
    /**
     * Fires after student completes a quiz
     * @param int   $student_id     User ID
     * @param int   $quiz_id        Quiz ID
     * @param int   $quizdata       Quiz details
     */
    public function process_lifterlms_quiz_completed($student_id, $quiz_id, $quizdata){
        return rest_ensure_response($this->process_hookdata($student_id, $quiz_id, QUIZ_COMPLETED, $quizdata));
    }

    /**
     * Fires after student completes a course 
     * @param int       $student_id     User ID
     * @param int       $object_id      Course ID
     * @param string    $object_type    Type of submitted details
     * @param string    $trigger
     */
    public function process_lifterlms_course_completed($student_id, $object_id, $object_type, $trigger){
        if($object_type=='course'){
            return rest_ensure_response($this->process_hookdata($student_id, $object_id, COURSE_COMPLETED, null));
        }
    }

    /**
     * Process and submit the  payload to webhooks created.
     * @param int   $user_id    User ID
     * @param int   $form_id    Submitted hook's Object ID
     * @param int   $action     Action to handle data and sent to payload 
     * @param array $extradata  Additional details from the hook
     * @return WP_Error Error details 
     */
    public function process_hookdata($user_id, $form_id, $action, $extradata){
        $returndata = array();
        $studentdata = array();
        
        $form = $this->form_validataion(null, $form_id, $action);

        if(is_wp_error($form)){
            return new WP_Error( 'rest_bad_request', esc_html__( $form->get_error_message($form->get_error_code()), 'zoho-flow' ), $form->get_error_data($form->get_error_code()) );
        }
        
        $newform_id = ($action === COURSE_COMPLETED) ? "0" : $form->ID;
        $args = array(
            'action' => $action,
            "form_id" => $newform_id
        );
        
        $webhooks = $this->get_webhook_posts($args);
        if( !empty( $webhooks )){
            switch($action){
                case COURSE_COMPLETED :
                    $courseObj = new LLMS_Course($form_id);
                    $course = $courseObj->get_product();
                    $returndata['course'] = $course->post;
                    break;
                case LESSON_COMPLETED :
                    $lessonObj = new LLMS_Lesson($form_id);
                    $course = $lessonObj->get_course();
                    $returndata['course'] = $course->post;
                    $returndata['lesson'] = $lessonObj->post;
                    break;
                case QUIZ_COMPLETED :
                    if(!empty($extradata)){
                        $quizObj = new LLMS_Quiz($form_id);
    					$course_id = $quizObj->get_course()->post->ID;
    					$lesson = $quizObj->get_lesson();
    					$lesson_id = $lesson->post->ID;
    					$quiz_attempt = $this->get_quiz_attempts(array('quiz_id'=>$form_id, 'student_id'=>$user_id,'lesson_id'=>$lesson_id));
    					$quiz_attempt->course_id = $course_id;
                        $returndata['quiz'] = $quizObj->post;
                        $returndata['quizresult'] = $quiz_attempt;
                    }
                    break;
                case USER_REMOVED_FROM_MEMBERSHIP:
                case USER_ENROLLED_TO_MEMBERSHIP:
                    $membershipObj = new LLMS_Membership($form_id);
                    $membership = $membershipObj->get_product();
                    unset($membership->post->post_content, $membership->post->post_password);
                    $returndata['membership'] = $membership->post;
                    $studentdata = $membershipObj->get_students();
                    break;
                case USER_ENROLLED_TO_COURSE:
                case USER_REMOVED_FROM_COURSE:
                    $courseObj = new LLMS_Course($form_id);
                    $course = $courseObj->get_product();
                    unset($course->post->post_content, $course->post->post_password);
                    $returndata['course'] = $course->post;
                    $studentdata = $courseObj->get_students();
            }
            if(($action == USER_ENROLLED_TO_MEMBERSHIP || $action == USER_ENROLLED_TO_COURSE) && !empty($studentdata) ) {
                if(in_array($user_id, $studentdata)){
                    $student = get_user_by('ID', $user_id);
                    unset($student->data->user_pass);
                    $returndata['student'] = $student->data;
                }
            } else {
                $student = get_user_by('ID', $user_id);
                unset($student->data->user_pass);
                $returndata['student'] = $student->data;
            }
            $returndata['action'] = $action;
    
            foreach ( $webhooks as $webhook ) {
                $url = $webhook->url;
                zoho_flow_execute_webhook($url, $returndata, array());
            }
        }
    }

    //Actions
    /**
     * Enroll user to course or membership
     * @param WP_REST_Request $request object
     * @return WP_REST_Response Enrollment object | WP_Error Error details
     */
    public function enroll_users_to_course_or_membership($request){
        $type = $request['type'];
        $post_id = $request['post_id'];
        if(!ctype_digit($post_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The '. $type .' ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $usersIds =json_decode($request->get_body())->{'users'};
		$invalid_users = array();
		foreach($usersIds as $user_id){
            $users=get_user_by("ID",$user_id);
            if(empty($users)){
                unset($usersIds[$user_id]);
                array_push($invalid_users, $user_id);
            }
        }

        $form = (($type == 'course') ? $this->handle_get_queries(array('form_id'=>$post_id), 'course') : $this->handle_get_queries(array('form_id'=>$post_id), 'llms_membership'));
        $product = (is_object($form))? $form->data : $form[0];

        if(empty($product)){
            return new WP_Error( 'invalid_'.$type.'_id', __('No '. $type .' found'), array( 'status' => 400 ) );
        }

        $enrolled_users = llms_get_enrolled_students($post_id);
        $students = array();
        foreach($usersIds as $student){
            if(!in_array($student, $enrolled_users)){
                llms_enroll_student($student, $post_id);
                array_push($students, $student);
            }else{
                array_push($invalid_users, $student);
            }
        }
        $form = $form[0];
        $formData = array(
            "ID" => $form->ID,
            "post_title" => $form->post_title,
            "post_status" => $form->post_status,
            "post_author" => $form->post_author,
            "post_date" => $form->post_date,
            "post_modified" => $form->post_modified,
        );
        $data = array(
            $type => $formData,
            'new_users' => $students,
            'invalid_users' => $invalid_users
		);
        return rest_ensure_response(array('data' => $data));
    }

    /**
     * Remove users from course or membership
     * @param WP_REST_Request $request WP_REST_Request object
     * @return WP_REST_Response Unenrollment object | WP_Error Error details
     */
    public function remove_users_from_course_or_membership($request){
        $type = $request['type'];
        $post_id = $request['post_id'];
        if(!ctype_digit($post_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The '.$type.' ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $usersIds =json_decode($request->get_body())->{'users'};
		$invalid_users = array();
		foreach($usersIds as $user_id){
            $users=get_user_by("ID",$user_id);
            if(empty($users)){
                unset($usersIds[$user_id]);
                array_push($invalid_users, $user_id);
            }
        }

        $form = (($type == 'course') ? $this->handle_get_queries(array('form_id'=>$post_id), 'course') : $this->handle_get_queries(array('form_id'=>$post_id), 'llms_membership'));
        $product = (is_object($form))? $form->data : $form[0];
        if(empty($product)){
            return new WP_Error( 'invalid_'.$type.'_id', __('No '.$type.' found'), array( 'status' => 400 ) );
        }

        $enrolled_users=llms_get_enrolled_students($post_id);
        $students=array();
        foreach($usersIds as $student){
            if(in_array($student, $enrolled_users)){
                llms_unenroll_student($student, $post_id);
                array_push($students, $student);
            }else{
                array_push($invalid_users, $student);
            }
        }
        $form = $form[0];
        $formData = array(
            "ID" => $form->ID,
            "post_title" => $form->post_title,
            "post_status" => $form->post_status,
            "post_author" => $form->post_author,
            "post_date" => $form->post_date,
            "post_modified" => $form->post_modified,
        );
        $data = array($type => $formData, "removed_users" => $students, 'invalid_users' => $invalid_users);
        return rest_ensure_response(array('data' => $data));
    }

    /**
     * Returns the list of enrolled users
     * @param WP_REST_Request $request Object
     * @return WP_REST_Response Enrolled user details | WP_Error Error details
     */
    public function get_enrolled_users($request){
        $post_id = $request['post_id'];
        $type = $request['type'];
        if(!ctype_digit($post_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The '.$type.' ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $form = (($type == 'course') ? $this->handle_get_queries(array('form_id'=>$post_id), 'course') : $this->handle_get_queries(array('form_id'=>$post_id), 'llms_membership'));
        $product = (is_object($form))? $form->data : $form[0];
        if(empty($product)){
            return new WP_Error( 'invalid_'.$type.'_id', __('No '.$type.' found'), array( 'status' => 400 ) );
        }
        $data = llms_get_enrolled_students($post_id);
        return rest_ensure_response(array('users'=>$data));
    }

    /**
     * Check to ensure the given post object is valid
     * @param WP_REST_Request $request object
     * @param int       $form_id    Object ID
     * @param string    $action     Action of the form validation    
     * @return WP_Error Error details | mixed Returns details of given Object
     */
    private function form_validataion($request, $form_id, $action){
        switch($action){
            case 'forms' :
                $post_type = 'llms_form';
                break;
            case COURSE_COMPLETED:
            case USER_ENROLLED_TO_COURSE:
            case USER_REMOVED_FROM_COURSE:
                $post_type = 'course';
                break;
            case LESSON_COMPLETED:
                $post_type = 'lesson';
                break;
            case QUIZ_COMPLETED:
                $post_type = 'llms_quiz';
                break;
            case USER_ENROLLED_TO_MEMBERSHIP:
            case USER_REMOVED_FROM_MEMBERSHIP:
                $post_type = 'llms_membership';
                break;
        }

        if($request==null && $form_id!=null){
            $form = $this->handle_get_queries(array('form_id'=>$form_id), $post_type);
						$form = (is_object($form))? $form->data : $form[0];
        } else {
            $form = $this->handle_get_queries($request, $post_type);
            $form = (is_object($form))? $form->data : $form[0];
        }

        if(empty($form) || $form ==null){
            return new WP_Error( 'rest_not_found', esc_html__( 'The form is not found.', 'zoho-flow' ), array( 'status' => 404 ) );
        }
        return $form;
    }

    //Webhooks
    /**
     * List webhooks related to the object type
     * @param WP_REST_Request $request object
     * @return WP_REST_Response Array of webhooks | WP_Error error details
     */
    public function get_webhooks( $request ) {
        $action = $request['action'];
        if($action != "forms" && !ctype_digit($request['form_id'])){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

				$form = null;
				if($request['form_id'] !=0){
					if($action ==="forms"){
						$form = $this->get_form(array("location"=> $request['form_id']));
						$form = $form->data;
					}else{
						$form = $this->form_validataion($request, null, $action);
					}
				}

        if(is_wp_error($form)){
            return new WP_Error( 'rest_bad_request', esc_html__( $form->get_error_message($form->get_error_code()), 'zoho-flow' ), $form->get_error_data($form->get_error_code()) );
        }
				$form_id = ($form === null) ? $request['form_id'] : $form->ID;
        $args = array(
            'form_id' => $form_id,
            'action' => $action,
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
                'url' => $webhook->url,
                'action' => $webhook->action,
            );
            array_push($data, $webhook);
        }
        return rest_ensure_response( $data );
    }

    /**
     * Creates a webhook entry
     * @param WP_REST_Request $request object
     * @return WP_Error|WP_REST_Response Array with webhook id |WP_Error Error details
     */
    public function create_webhook( $request ) {
        $url = esc_url_raw($request['url']);
        $action = $request['action'];

        if($action != "forms" && !ctype_digit($request['form_id'])){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Form ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

		$form = null;
		if($request['form_id'] != 0){
			if($action ==="forms"){
				$form = $this->get_form(array("location"=> $request['form_id']));
				$form = $form->data;
			}else{
				$form = $this->form_validataion($request, null, $action);
			}
		}

        if(is_wp_error($form)){
            return new WP_Error( 'rest_bad_request', esc_html__( $form->get_error_message($form->get_error_code()), 'zoho-flow' ), $form->get_error_data($form->get_error_code()) );
        }
				$form_id = ($form === null) ? $request['form_id'] : $form->ID;

        $form_title = ($form === null) ? "all" : $form->post_title;
        $post_id = $this->create_webhook_post($form_title, array(
            'form_id' => $form_id,
            'url' => $url,
            'action' => $action
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'form_id' => $form_id,
            'url' => $url,
            'action' => $action
        ) );
    }

    /**
     * Deteles a webhook entry
     * @param WP_REST_Request $request object
     * @return WP_REST_Response Success message with deleted webhook ID | WP_Error Error details 
     */
    public function delete_webhook($request) {
        //TODO Delete webhooks
        $webhook_id = $request['webhook_id'];
        if(!ctype_digit($webhook_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Webhook ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
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

    /**
     * List all the webhooks
     * @return WP_REST_Response All the webhook details | WP_Error Error details
     */
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
                'form_id' => $webhook->form_id,
                'url' => $webhook->url,
                'action' => $webhook->action
            );
            array_push($data, $webhook);
        }
        return rest_ensure_response( $data );
    }

    //Schemas
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
                    'description' => esc_html__('User role', 'zoho-flow'),
                    'type'        => 'array',
                    'context'     => array('view'),
                ),
                'roles' => array(
                    'description' => esc_html__('User roles', 'zoho-flow'),
                    'type'        => 'array',
                    'context'     => array('view'),
                ),
            ),
        );

        return $schema;
    }
    
    
    public function get_data_by_module($request){
        if($request['data_id']===null || !ctype_digit($request['data_id'])){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The course ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        
        $data_id=$request['data_id'];
        $submodule = $request['submodule'];
        
        switch($submodule){
            case "lessons" :
                $post_type = "lesson";
                $meta_key = "_llms_parent_course";
                $meta_value = $data_id;
                break;
            case "quizzes" :
                $post_type = "llms_quiz";
                $meta_key = "_llms_lesson_id";
                $meta_value = $data_id;
        }
        
        $query_param=$request->get_query_params();
        if ( !empty( $data_id ) ) {
            $query_args = array(
                'post_type'         =>   $post_type,
                'posts_per_page'    =>   500,
                'orderby'           =>   'date',
                'order'             =>   'DESC',
                'meta_key'          => $meta_key,
                'meta_value'        => $meta_value,
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

    /**
     * Get user and system info.
     * Default API
     *
     * @return WP_REST_Response|WP_Error  WP_REST_Response system and logged in user details | WP_Error object with error details.
     */
    
    public function get_system_info(){

        $system_info = parent::get_system_info();
        if( ! function_exists('get_plugin_data') ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/lifterlms/lifterlms.php';
        if(file_exists($plugin_dir)){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['lifter-lms_plugin'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}
