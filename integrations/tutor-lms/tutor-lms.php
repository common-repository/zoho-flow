<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow      2.6.0
 * @since tutor-lms     2.6.2
 */
class Zoho_Flow_Tutor_LMS extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array( "user_enrolled_to_course", "user_completed_course", "user_completed_lesson", "user_started_quiz", "user_completed_quiz", "student_signup" );
    
    /**
     * List courses
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Optional. Arguments for querying forms.
     * @type int     limit        Number of courses to query. Default: 200.
     * @type string  $order_by    Course list order by the field. Default: post_modified.
     * @type string  $order       Course list order Values: ASC|DESC. Default: DESC.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of course details | WP_Error object with error details.
     */
    public function list_courses( $request ){
        $args = array(
            "post_type" => "courses",
            "numberposts" => ($request['limit']) ? $request['limit'] : '200',
            "orderby" => ($request['order_by']) ? $request['order_by'] : 'post_modified',
            "order" => ($request['order']) ? $request['order'] : 'DESC',
        );
        $courses_list = get_posts( $args );
        $courses_return_list = array();
        foreach ( $courses_list as $form ){
            $courses_return_list[] = array(
                "ID" => $form->ID,
                "post_title" => $form->post_title,
                "post_status" => $form->post_status,
                "post_author" => $form->post_author,
                "post_date" => $form->post_date,
                "post_modified" => $form->post_modified
            );
        }
        return rest_ensure_response( $courses_return_list );
    }
    
    /**
     * List topics
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Optional. Arguments for querying forms.
     * @type int     limit        Number of topics to query. Default: 200.
     * @type string  $order_by    Topics list order by the field. Default: menu_order.
     * @type string  $order       Topics list order Values: ASC|DESC. Default: ASC.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of topic details | WP_Error object with error details.
     */
    public function list_course_topics( $request ){
        $course_id = $request['course_id'];
        if( $this->is_valid_course( $course_id ) ){
            $args = array(
                "post_type" => "topics",
                "numberposts" => ($request['limit']) ? $request['limit'] : '200',
                "orderby" => ($request['order_by']) ? $request['order_by'] : 'menu_order',
                "order" => ($request['order']) ? $request['order'] : 'ASC',
                "post_parent" => $course_id
            );
            $topics_list = get_posts( $args );
            $topics_return_list = array();
            foreach ( $topics_list as $form ){
                $topics_return_list[] = array(
                    "ID" => $form->ID,
                    "post_title" => $form->post_title,
                    "post_status" => $form->post_status,
                    "post_author" => $form->post_author,
                    "post_date" => $form->post_date,
                    "post_modified" => $form->post_modified,
                    "post_parent" => $form->post_parent
                );
            }
            return rest_ensure_response( $topics_return_list );
        }
        return new WP_Error( 'rest_bad_request', "Course does not exist!", array( 'status' => 400 ) );
    }
    
    /**
     * List topics
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Optional. Arguments for querying forms.
     * @type int     limit        Number of topics to query. Default: 200.
     * @type string  $order_by    Topics list order by the field. Default: menu_order.
     * @type string  $order       Topics list order Values: ASC|DESC. Default: ASC.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of topic details | WP_Error object with error details.
     */
    public function list_topic_lessons( $request ){
        $topic_id = $request['topic_id'];
        if( $this->is_valid_topic( $topic_id ) ){
            $args = array(
                "post_type" => "lesson",
                "numberposts" => ($request['limit']) ? $request['limit'] : '200',
                "orderby" => ($request['order_by']) ? $request['order_by'] : 'menu_order',
                "order" => ($request['order']) ? $request['order'] : 'ASC',
                "post_parent" => $topic_id
            );
            $topics_list = get_posts( $args );
            $topics_return_list = array();
            foreach ( $topics_list as $form ){
                $topics_return_list[] = array(
                    "ID" => $form->ID,
                    "post_title" => $form->post_title,
                    "post_status" => $form->post_status,
                    "post_author" => $form->post_author,
                    "post_date" => $form->post_date,
                    "post_modified" => $form->post_modified,
                    "post_parent" => $form->post_parent
                );
            }
            return rest_ensure_response( $topics_return_list );
        }
        return new WP_Error( 'rest_bad_request', "Topic does not exist!", array( 'status' => 400 ) );
    }
    
    /**
     * List topics
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Optional. Arguments for querying forms.
     * @type int     limit        Number of topics to query. Default: 200.
     * @type string  $order_by    Topics list order by the field. Default: menu_order.
     * @type string  $order       Topics list order Values: ASC|DESC. Default: ASC.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of topic details | WP_Error object with error details.
     */
    public function list_topic_quizzes( $request ){
        $topic_id = $request['topic_id'];
        if( $this->is_valid_topic( $topic_id ) ){
            $args = array(
                "post_type" => "tutor_quiz",
                "numberposts" => ($request['limit']) ? $request['limit'] : '200',
                "orderby" => ($request['order_by']) ? $request['order_by'] : 'menu_order',
                "order" => ($request['order']) ? $request['order'] : 'ASC',
                "post_parent" => $topic_id
            );
            $topics_list = get_posts( $args );
            $topics_return_list = array();
            foreach ( $topics_list as $form ){
                $topics_return_list[] = array(
                    "ID" => $form->ID,
                    "post_title" => $form->post_title,
                    "post_status" => $form->post_status,
                    "post_author" => $form->post_author,
                    "post_date" => $form->post_date,
                    "post_modified" => $form->post_modified,
                    "post_parent" => $form->post_parent
                );
            }
            return rest_ensure_response( $topics_return_list );
        }
        return new WP_Error( 'rest_bad_request', "Topic does not exist!", array( 'status' => 400 ) );
    }
    
    /**
     * List quiz questions
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int  $quiz_id   Quiz ID to retrive the fields for.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with quiz questions details | WP_Error object with error details.
     */
    public function list_quiz_questions( $request ){
        $quiz_id = $request['quiz_id'];
        if( $this->is_valid_quiz( $quiz_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id = %s ORDER BY question_order ASC LIMIT 1000",
                    $quiz_id
                )
                );
            $questions_return_list = array();
            foreach ( $results as $question ){
                $questions_return_list[] = array(
                    "question_id" => $question->question_id,
                    "quiz_id" => $question->quiz_id,
                    "question_title" => $question->question_title,
                    "question_description" => $question->question_description,
                    "answer_explanation" => $question->answer_explanation,
                    "question_type" => $question->question_type,
                    "question_mark" => $question->question_mark,
                    "question_order" => $question->question_order,
                    "question_settings" => unserialize( $question->question_settings )
                );
            }
            return rest_ensure_response( $questions_return_list );
        }
        return new WP_Error( 'rest_bad_request', "Quiz does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * Enroll user to course
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * 
     * @return WP_REST_Response|WP_Error    WP_REST_Response enrollment object | WP_Error object with error details.
     */
    public function enroll_user_to_course( $request ){
        $tutor_utils_obj = New TUTOR\Utils();
        $course_id = $request['course_id'];
        $fetch_field = $request['user_fetch_field'];
        $fetch_value = $request['user_fetch_value'];
        if( !empty( $fetch_field ) && !empty( $fetch_value ) ){
            $user = get_user_by( $fetch_field, $fetch_value );
            if( !$this->is_valid_course( $course_id ) ){
                return new WP_Error( 'rest_bad_request', "Course does not exist!", array( 'status' => 404 ) );
            }
            if( !$user ){
                return new WP_Error( 'rest_bad_request', "User does not exist!", array( 'status' => 404 ) );
            }
            $enroll_id = $tutor_utils_obj->do_enroll( $course_id, 0, $user->ID );
            return rest_ensure_response( $this->is_valid_enroll( $enroll_id ) );
        }
        
    }
    
    /**
     * Check whether the Course ID is valid or not.
     *
     * @param int $course_id  Course ID.
     * @return mixed  course array if the course exists | false for others.
     */
    private function is_valid_course( $course_id ){
        if( isset( $course_id ) ){
            if( "courses" === get_post_type( $course_id ) ){
                $post = get_post( $course_id, 'ARRAY_A' );
                unset( $post["post_password"] );
                return $post;
            }
            return false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Check whether the Topic ID is valid or not.
     *
     * @param int $topic_id  Topic ID.
     * @return mixed  topic array if the topic exists | false for others.
     */
    private function is_valid_topic( $topic_id ){
        if( isset( $topic_id ) ){
            if( "topics" === get_post_type( $topic_id ) ){
                $post = get_post( $topic_id, 'ARRAY_A' );
                unset( $post["post_password"] );
                return $post;
            }
            return false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Check whether the Lesson ID is valid or not.
     *
     * @param int $lesson_id  Lesson ID.
     * @return mixed  lesson array if the lesson exists | false for others.
     */
    private function is_valid_lesson( $lesson_id ){
        if( isset( $lesson_id ) ){
            if( "lesson" === get_post_type( $lesson_id ) ){
                $post = get_post( $lesson_id, 'ARRAY_A' );
                unset( $post["post_password"] );
                return $post;
            }
            return false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Check whether the Quiz ID is valid or not.
     *
     * @param int $quiz_id  Quiz ID.
     * @return mixed  quiz array if the quiz exists | false for others.
     */
    private function is_valid_quiz( $quiz_id ){
        if( isset( $quiz_id ) ){
            if( "tutor_quiz" === get_post_type( $quiz_id ) ){
                $post = get_post( $quiz_id, 'ARRAY_A' );
                unset( $post["post_password"] );
                return $post;
            }
            return false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Check whether the Enrollment ID is valid or not.
     *
     * @param int $enroll_id  Enrollment ID.
     * @return mixed  enrollment array if the enrollment exists | false for others.
     */
    private function is_valid_enroll( $enroll_id ){
        if( isset( $enroll_id ) ){
            if( "tutor_enrolled" === get_post_type( $enroll_id ) ){
                $post = get_post( $enroll_id, 'ARRAY_A' );
                unset( $post["post_password"] );
                $post['course'] = $this->is_valid_course( $post["post_parent"] );
                $student = get_user_by( 'ID', $post["post_author"] )->data;
                unset( $student->user_pass, $student->user_activation_key );
                $post['student'] = $student;
                return $post;
            }
            return false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Check whether the User ID is valid or not.
     *
     * @param int $form_id  User ID.
     * @return mixed  user array if the user exists | false for others.
     */
    private function is_valid_user( $user_id ){
        if( isset( $user_id ) ){
            if( get_user_by( 'ID', $user_id ) ){
                $student = get_user_by( 'ID', $user_id )->data;
                unset( $student->user_pass, $student->user_activation_key );
                return $student;
            }
            return false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Check whether the Attempt ID is valid or not.
     *
     * @param int $enroll_id  Attempt ID.
     * @return mixed  attempt array if the attempt exists | false for others.
     */
    private function is_valid_attempt( $attempt_id ){
        if( isset( $attempt_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts WHERE attempt_id = %s ORDER BY attempt_id ASC LIMIT 10",
                    $attempt_id
                )
                );
            if( !empty( $results ) ){
                $attempt_details = array(
                    "attempt_id" => $results[0]->attempt_id,
                    "course_id" => $results[0]->course_id,
                    "quiz_id" => $results[0]->quiz_id,
                    "user_id" => $results[0]->user_id,
                    "total_questions" => $results[0]->total_questions,
                    "total_answered_questions" => $results[0]->total_answered_questions,
                    "total_marks" => $results[0]->total_marks,
                    "earned_marks" => $results[0]->earned_marks,
                    "attempt_status" => $results[0]->attempt_status,
                    "attempt_ip" => $results[0]->attempt_ip,
                    "attempt_started_at" => $results[0]->attempt_started_at,
                    "attempt_ended_at" => $results[0]->attempt_ended_at,
                    "is_manually_reviewed" => $results[0]->is_manually_reviewed,
                    "manually_reviewed_at" => $results[0]->manually_reviewed_at,
                    "attempt_info" => unserialize( $results[0]->attempt_info )
                );
                return $attempt_details;
            }
            
        }
        return false;
    }
    
    /**
     * Creates a webhook entry
     * The events available in $supported_events array only accepted
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with Webhook ID | WP_Error object with error details.
     */
    public function create_webhook( $request ){
        $entry = json_decode( $request->get_body() );
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event
            );
            $post_name = "Tutor LMS ";
            $post_id = $this->create_webhook_post( $post_name, $args );
            if( is_wp_error( $post_id ) ){
                $errors = $post_id->get_error_messages();
                return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
            }
            return rest_ensure_response(
                array(
                    'webhook_id' => $post_id
                ) );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Data validation failed', array( 'status' => 400 ) );
        }
    }
    
    /**
     * Deletes a webhook entry
     * Webhook ID returned from webhook create event should be used. Use minimum user scope.
     *
     * @param WP_REST_Request   $request    WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with success message | WP_Error object with error details.
     */
    public function delete_webhook( $request ){
        $webhook_id = $request['webhook_id'];
        if( is_numeric( $webhook_id ) ){
            $webhook_post = $this->get_webhook_post( $webhook_id );
            if( !empty( $webhook_post[0]->ID ) ){
                $delete_webhook = $this->delete_webhook_post( $webhook_id );
                if( is_wp_error( $delete_webhook ) ){
                    $errors = $delete_webhook->get_error_messages();
                    return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
                }
                else{
                    return rest_ensure_response( array( 'message' => 'Success' ) );
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
    
    /**
     * Fires after student enrolled to a course.
     *
     * @param int   $course_id      Course ID
     * @param int   $user_id        User ID
     * @param int   $enrolled_id    Enrolled ID
     */
    public function payload_user_enrolled_to_course( $course_id, $user_id, $enrolled_id ){
        $args = array(
            'event' => 'user_enrolled_to_course'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'user_enrolled_to_course',
                'data' => array(
                    'course' => $this->is_valid_course( $course_id ),
                    'student' => $this->is_valid_user( $user_id ),
                    'enroll' => $this->is_valid_enroll( $enrolled_id ),
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after student completed a lesson.
     *
     * @param int   $lesson_id  Lesson ID
     * @param int   $user_id    User ID
     */
    public function payload_user_completed_lesson( $lesson_id, $user_id ){
        $args = array(
            'event' => 'user_completed_lesson'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $lesson = $this->is_valid_lesson( $lesson_id );
            $topic = $this->is_valid_topic( $lesson['post_parent'] );
            $course = $this->is_valid_course( $topic['post_parent'] );
            $event_data = array(
                'event' => 'user_completed_lesson',
                'data' => array(
                    'lesson' => $lesson,
                    'topic' => $topic,
                    'course' => $course,
                    'student' => $this->is_valid_user( $user_id )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after student completed a course.
     *
     * @param int   $course_id      Course ID
     * @param int   $user_id        User ID
     */
    public function payload_user_completed_course( $course_id, $user_id ){
        $args = array(
            'event' => 'user_completed_course'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'user_completed_course',
                'data' => array(
                    'course' => $this->is_valid_course( $course_id ),
                    'student' => $this->is_valid_user( $user_id )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after student started answering quiz.
     *
     * @param int   $quiz_id        Quiz ID
     * @param int   $user_id        User ID
     * @param int   $attempt_id     Attempt ID
     */
    public function payload_user_started_quiz( $quiz_id, $user_id, $attempt_id ){
        $args = array(
            'event' => 'user_started_quiz'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $quiz =  $this->is_valid_quiz( $quiz_id );
            $topic = $this->is_valid_topic( $quiz['post_parent'] );
            $course = $this->is_valid_course( $topic['post_parent'] );
            $event_data = array(
                'event' => 'user_started_quiz',
                'data' => array(
                    'quiz' => $quiz,
                    'topic' => $topic,
                    'course' => $course,
                    'student' => $this->is_valid_user( $user_id ),
                    'attempt' => $this->is_valid_attempt( $attempt_id )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after student completed a quiz.
     *
     * @param int   $attempt_id     Attempt ID
     * @param int   $course_id      Course ID
     * @param int   $user_id        User ID
     */
    public function payload_user_completed_quiz( $attempt_id, $course_id, $user_id ){
        $args = array(
            'event' => 'user_completed_quiz'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $attempt =  $this->is_valid_attempt( $attempt_id );
            $quiz =  $this->is_valid_quiz( $attempt['quiz_id'] );
            $topic = $this->is_valid_topic( $quiz['post_parent'] );
            $event_data = array(
                'event' => 'user_completed_quiz',
                'data' => array(
                    'quiz' => $quiz,
                    'topic' => $topic,
                    'course' => $this->is_valid_course( $course_id ),
                    'student' => $this->is_valid_user( $user_id ),
                    'attempt' => $attempt
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires after student signed up.
     *
     * @param int   $user_id        User ID
     */
    public function payload_student_signup( $user_id ){
        $args = array(
            'event' => 'student_signup'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'student_signup',
                'data' => array(
                    'student' => $this->is_valid_user( $user_id )
                )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
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
        if( ! function_exists( 'get_plugin_data' ) ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/tutor/tutor.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['tutor_lms'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    } 
}
  