<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow                  2.6.0
 * @since quiz-and-survey-master    9.0.1
 */
class Zoho_Flow_Quiz_And_Survey_Master extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array( "quiz_submitted" );
    
    /**
     * List Quizzes
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Optional. Arguments for querying quizzes.
     * @type string  $order_by      Quiz list order by the field. Default: quiz_id.
     * @type string  $order         Quiz list order. Values: ASC|DESC. Default: DESC.
     * @type int     limit          Number of quizzes to query for. Default: 200.
     * @type int     offset         Page number. Default: 0.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of quiz details | WP_Error object with error details.
     */
    public function list_quizzes( $request ){
        global $mlwQuizMasterNext;
        $quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes(
            false,
            $request['order_by'] ? $request['order_by'] : 'quiz_id',
            $request['order'] ? $request['order'] : 'DESC',
            array(),
            '',
            $request['limit'] ? $request['limit'] : '200',
            $request['offset'] ? $request['offset'] : '0'
            );
        $quizzes_return_list = array();
        foreach ( $quizzes as $quiz ){
            $quizzes_return_list[] = array(
                "quiz_id" => $quiz->quiz_id,
                "quiz_name" => $quiz->quiz_name,
                "timer_limit" => $quiz->timer_limit,
                "last_activity" => $quiz->last_activity,
                "require_log_in" => $quiz->require_log_in,
                "limit_total_entries" => $quiz->limit_total_entries,
                "quiz_views" => $quiz->quiz_views,
                "quiz_taken" => $quiz->quiz_taken,
                "quiz_author_id" => $quiz->quiz_author_id
            );
        }
        return rest_ensure_response( $quizzes_return_list );
    }
    
    /**
     * List quiz questions
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int  $quiz_id   Quiz ID to retrive the questions for.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with quiz question details | WP_Error object with error details.
     */
    public function list_quiz_questions( $request ){
        $quiz_id = $request['quiz_id'];
        if( $this->is_valid_quiz( $quiz_id ) ){
            $questions = QSM_Questions::load_questions( $quiz_id );
            $questions_return_list = array();
            foreach ( $questions as $question_id => $question ){
                unset( $question['answer_array'] );
                unset( $question['question_settings'] );
                $questions_return_list[] = $question;
            }
            return rest_ensure_response( $questions_return_list );
        }
        return new WP_Error( 'rest_bad_request', "Quiz does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * List quiz contact fields
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int  $quiz_id   Quiz ID to retrive the fields for.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with quiz contact fields details | WP_Error object with error details.
     */
    public function list_quiz_contact_fields( $request ){
        $quiz_id = $request['quiz_id'];
        if( $this->is_valid_quiz( $quiz_id ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %s",
                    $quiz_id
                    )
                );
            if( isset( $results[0]->quiz_settings ) && is_string( $results[0]->quiz_settings ) ){
                $settings = unserialize( $results[0]->quiz_settings );
                if( isset( $settings['contact_form'] ) && $settings['contact_form'] && is_string( $settings['contact_form'] ) ){
                    $contact_form = unserialize( $settings['contact_form'] );
                    return rest_ensure_response( $contact_form );
                }
                return rest_ensure_response([]);
            }
        }
        return new WP_Error( 'rest_bad_request', "Quiz does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * Check whether the Quiz ID is valid or not.
     *
     * @param int $quiz_id  Quiz ID.
     * @return array|boolean  array if the quiz exists | false for others.
     */
    private function is_valid_quiz( $quiz_id ){
        if( ( isset( $quiz_id ) )  && ( is_numeric( $quiz_id ) ) ){
            global $mlwQuizMasterNext;
            if( $mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id ) ){
                return true;
            }
            return false;
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
        if( ( !isset( $entry->quiz_id ) ) || !$this->is_valid_quiz( $entry->quiz_id ) ){
            return new WP_Error( 'rest_bad_request', "Quiz does not exist!", array( 'status' => 400 ) );
        }
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event,
                'quiz_id' => $entry->quiz_id
            );
            $post_name = "Quiz And Survey Master ";
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
     * Fired after the responses are submitted
     *
     * @param int   $form_args  Form entry details and placeholder details
     */
    public function payload_quiz_submitted( $results_array, $results_id, $qmn_quiz_options, $qmn_array_for_variables ){
        $quiz_id = $qmn_array_for_variables['quiz_id'];
        $args = array(
            'event' => 'quiz_submitted',
            'quiz_id' => $quiz_id
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'quiz_submitted',
                'data' => $qmn_array_for_variables
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /*
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/quiz-master-next/mlw_quizmaster2.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['quiz_and_survey_master'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}