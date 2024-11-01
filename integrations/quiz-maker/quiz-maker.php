<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Zoho_Flow_QuizMaker extends Zoho_Flow_Service
{
    private static $tables = array(
        'quiz' => 'aysquiz_quizes',
        'question' => 'aysquiz_questions',
        'report' => 'aysquiz_reports',
        'answers' => 'aysquiz_answers',
    );

    public static function gettable($key){
        return self::$tables[$key];
    }
    public function get_quizzes( $request ) {
        $quizzes = $this->handle_get_queries($request, "quiz");
        return rest_ensure_response( $quizzes);
    }

    public function get_questions($request){
        $data = $this->handle_get_queries($request, "question");
        return rest_ensure_response($data);
    }

    public function get_quiz($request){
        if((empty($request["quiz_id"]) || !ctype_digit($request["quiz_id"])) ){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Quiz ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $quiz = $this->handle_get_queries($request, "get_quiz");
        return rest_ensure_response($quiz);
    }

    public function get_question($request){
        if((empty($request["question_id"]) || !ctype_digit($request["question_id"])) ){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Question ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $question = $this->handle_get_queries($request, "get_question");
        return rest_ensure_response($question);
    }

    public function get_categories($request){
        $module = $request['modulename'];

        $data = $this->handle_get_queries($request, $request["modulename"]);
        return rest_ensure_response($data);
    }

    public function get_reports($request){
        $data = $this->handle_get_queries($request, "report");
        $returnData = array();
        foreach ($data as $obj) {
          $midData = array();
          forEach($obj as $key => $value){
            if($key === 'user_id' && $value !=0){
              $user = get_user_by("ID", $value);
              $midData[$key] = $value;
              $midData['user_name'] = $user->display_name;
              $midData['user_email'] = $user->user_email;
              $midData['user_phone'] = $user->user_phone;
            }else if($value !=null){
              $midData[$key] = $value;
            }
          }
          array_push($returnData, $midData);
        }
        return rest_ensure_response($returnData);
    }

    private function handle_get_queries($request, $typeofmod){
      global $wpdb;

      if(!isset($request['modulename'])){
        if($typeofmod === "quiz" || $typeofmod === "get_quiz"){
          $tablename = $this->gettable("quiz");
          $sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $tablename);
          if(isset($request['quiz_id']) && $request['quiz_id'] != null){
            $condition = $wpdb->prepare(" WHERE id=%d", absint( intval( $request['quiz_id'] )));
            $sql = $sql . $condition;
          }
        }else if($typeofmod === "question" || $typeofmod === "get_question"){
          $tablename = $this->gettable("question");
          $sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $tablename);
          if(isset($request['question_id']) && $request['question_id'] != null){
            $condition = $wpdb->prepare(" WHERE id=%d", absint( intval( $request['question_id'] ) ));
            $sql = $sql . $condition;
          }
        }else if($typeofmod === "report"){
          $tablename = $this->gettable($typeofmod);
          $sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $tablename);
        }else if($typeofmod === "answers"){
          $tablename = $this->gettable($typeofmod);
          $sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $tablename);
          if(isset($request['question_id']) && $request['question_id'] != null){
            $condition = $wpdb->prepare(" WHERE question_id=%d", absint( intval( $request['question_id'] ) ));
            $sql = $sql . $condition;
          }
        }
      }else if(isset($request['modulename']) && $request['modulename'] != null){
        $tablename = ($request['modulename'] === "quiz" ) ? 'aysquiz_quizcategories' : 'aysquiz_categories';//$this->gettable($typeofmod);
        $sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $tablename);
      }

      $result = $wpdb->get_results( $sql, "ARRAY_A");
      if($typeofmod === 'get_quiz' || $typeofmod === 'get_question'){
        $result = $result[0];
      }
      return $result;
    }

    private function get_question_answers($id){
      return $this->handle_get_queries(array('question_id' => $id), "answers");
    }

//  Actions
    public function edit_quiz($request){
      return $this->add_or_edit_quiz($request, 'edit');
    }

    public function edit_question($request){
      return $this->add_or_edit_question($request, 'edit');
    }
    public function add_or_edit_quiz($request, $type = null){
        $data = array();
        global $wpdb;
        $quiz_table = $wpdb->prefix . 'aysquiz_quizes';
        $message = "failed";

        $request_body = $request->get_body();
        $input = json_decode($request_body, true);
        $args = $input["quiz"];
        // $question_ids = $args['question_ids'];

        $id = null;
        if(isset($args["id"])){
            $id = $args['id'];
            if($id == null || empty($id)){
              return new WP_Error( 'rest_bad_request', esc_html__( 'Quiz ID cannot be empty', 'zoho-flow' ), array( 'status' => 400 ) );
            }else if(!ctype_digit($id)){
                return new WP_Error( 'rest_bad_request', esc_html__( 'Quiz ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
            }
        }else if(!isset($args["id"]) && $type=="edit"){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Quiz ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        if($id == null) {
            $count = $wpdb->insert(
                $quiz_table,
                $args
            );
            $quiz_id = $wpdb->insert_id;
            $message = "created";
        }else{
            $quizObj = $this->get_quiz(array("quiz_id"=>$id))->data;
            foreach ($quizObj as $key => $value) {
              if(array_key_exists($key, $args)){
                if(($args[$key] ==null || empty($args[$key])) && ($value != null || !empty($value))){
                  $args[$key] = $value;
                }
              }
            }
            //args after inserted the existing values
            $count = $wpdb->update(
                $quiz_table,
                $args,
                array( 'id' => $id ),
            );
            $quiz_id = $id;
            $message = 'updated';
        }

        settype($quiz_id, "string");
        if($count >=0){
            $data["result"] = $this->get_quiz(array("quiz_id"=>$quiz_id));
        }
        $questionArr = array();
        if(isset($args['question_ids']) && $args['question_ids'] != null || !empty($args['question_ids'])){
          $question_ids = $args['question_ids'];
          $question_ids = explode(",", $question_ids);
          foreach ($question_ids as $index=>$value){
              $questionObj = $this->get_question(array("question_id"=>$value))->data;
              $questionArr[$index] = $questionObj['question'];
          }
        }
        $data['question_ids'] = $questionArr;
        $data["messsage"] = $message;
        return rest_ensure_response($data);
    }

    public function add_or_edit_question($request, $type = null){
        $data = array();
        global $wpdb;
        $questions_table = $wpdb->prefix . "aysquiz_questions";
        $answers_table = $wpdb->prefix . "aysquiz_answers";

        $message = "failed";

        $request_body = $request->get_body();
        $input = json_decode($request_body, true);
        $args = $input["question"];
        $text_types = array('text', 'short_text', 'number');

	      if((!array_key_exists('type',$args)) || $args['type'] == null || empty($args['type'])){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Question type cannot be empty', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        if($args["answers"] == null || $args["correct_answers"] == null){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Answers / Correct answers cannot be empty', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $answers = (in_array($args['type'], $text_types)) ? array($args["answers"]) : explode(',', $args["answers"]);
        $correct_answers = (in_array($args['type'], $text_types)) ? array($args["correct_answers"]) : explode(',', $args["correct_answers"]);
        unset($args["answers"]);
        unset($args["correct_answers"]);

        foreach ($correct_answers as $value) {
          $value = (int)$value;
          if(!array_key_exists($value-1, $answers)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Correct answers index must be less than the size of answers.', 'zoho-flow' ), array( 'status' => 400 ) );
          }
        }

        $id = null;
        if(isset($args["id"])){
            $id = $args['id'];
            if($id == null || empty($id)){
              return new WP_Error( 'rest_bad_request', esc_html__( 'Question ID cannot be empty', 'zoho-flow' ), array( 'status' => 400 ) );
            }else if(!ctype_digit($id)){
              return new WP_Error( 'rest_bad_request', esc_html__( 'Question ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
            }
        }else if(!isset($args["id"]) && $type =="edit"){
          return new WP_Error( 'rest_bad_request', esc_html__( 'Question ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        if(!in_array( $args['type'], $text_types )){
            if(!in_array($args['type'], $text_types) && sizeof($answers) <2){
                return new WP_Error( 'rest_bad_request', esc_html__( 'You must have at least two answers.', 'zoho-flow' ), array( 'status' => 400 ) );
            }
        }

        $question_result = null;
        if($id == null) {
            $question_result = $wpdb->insert(
                $questions_table,
                $args,
            );

            $answers_results = array();
            $question_id = $wpdb->insert_id;
            $flag = true;

            foreach ($answers as $index => $answer_value) {

                if(in_array( $args['type'], $text_types )){
                    $correct = 1;
                    $answer_value = htmlspecialchars_decode($answer_value, ENT_QUOTES );
                }else{
                    $correct = (in_array(($index + 1), $correct_answers)) ? 1 : 0;
                }
                if (!in_array( $args['type'], $text_types ) && trim($answer_value) == '') {
                    continue;
                }
                $answers_results[] = $wpdb->insert(
                    $answers_table,
                    array(
                        'question_id'   => $question_id,
                        'answer'        => (trim($answer_value)),
                        'correct'       => $correct,
                        'ordering'      => ($index + 1),
                    ),
                );
            }

            if ($answers_results >= 0) {
                    $flag = true;
                } else {
                    $flag = false;
                }
            $message = "created";
        }else{
            $questionObj = $this->get_question(array("question_id"=>$id))->data;
            foreach ($questionObj as $key => $value) {
              if(array_key_exists($key, $args)){
                if(($args[$key] ==null || empty($args[$key])) && ($value != null || !empty($value))){
                  $args[$key] = $value;
                }
              }
            }
            //args after inserted the existing values
            $question_result = $wpdb->update(
                $questions_table,
                $args,
                array( 'id' => $id ),
            );

            $answers_results = array();
            $flag = true;
            $type = $args['type'];

            $old_answers = $this->get_question_answers( $id );
            $old_answers_count = count( $old_answers );

            if($old_answers_count == count($answers)){
                foreach ($answers as $index => $answer_value) {
                    if(in_array( $type, $text_types )){
                        $correct = 1;
                        $answer_value = htmlspecialchars_decode($answer_value, ENT_QUOTES );
                    }else{
                        $correct = (in_array(($index + 1), $correct_answers)) ? 1 : 0;
                    }
                    if (!in_array( $type, $text_types ) && trim($answer_value) == '') {
                        continue;
                    }
                    $answers_results[] = $wpdb->update(
                        $answers_table,
                        array(
                            'question_id'   => $id,
                            'answer'        => (trim($answer_value)),
                            'correct'       => $correct,
                            'ordering'      => ($index + 1),
                        ),
                        array('id' => $old_answers[$index]["id"]),
                        );
                }
            }

            if($old_answers_count < count($answers)){
                foreach ($answers as $index => $answer_value) {
                    if(in_array( $type, $text_types )){
                        $correct = 1;
                        $answer_value = htmlspecialchars_decode($answer_value, ENT_QUOTES );
                    }else{
                        $correct = (in_array(($index + 1), $correct_answers)) ? 1 : 0;
                    }
                    if (!in_array( $type, $text_types ) && trim($answer_value) == '') {
                        continue;
                    }
                    if( $old_answers_count < ( $index + 1) ){
                        $answers_results[] = $wpdb->insert(
                            $answers_table,
                            array(
                                'question_id'   => $id,
                                'answer'        => (trim($answer_value)),
                                'correct'       => $correct,
                                'ordering'      => ($index + 1),
                            ),
                        );
                    }else{
                        $answers_results[] = $wpdb->update(
                            $answers_table,
                            array(
                                'question_id'   => $id,
                                'answer'        => (trim($answer_value)),
                                'correct'       => $correct,
                                'ordering'      => ($index + 1),
                            ),
                            array('id' => $old_answers[$index]["id"]),
                            );
                    }
                }
            }

            if($old_answers_count > count($answers)){

                $diff = $old_answers_count - count($answers);

                $removeable_answers = array_slice( $old_answers, -$diff, $diff );

                foreach ( $removeable_answers as $removeable_answer ){
                    $delete_result = $wpdb->delete( $answers_table, array('id' => intval( $removeable_answer["id"] )) );
                }

                foreach ($answers as $index => $answer_value) {
                    if(in_array( $type, $text_types )){
                        $correct = 1;
                        $answer_value = htmlspecialchars_decode($answer_value, ENT_QUOTES );
                    }else{
                        $correct = (in_array(($index + 1), $correct_answers)) ? 1 : 0;
                    }
                    if (!in_array( $type, $text_types ) && trim($answer_value) == '') {
                        continue;
                    }

                    $answers_results[] = $wpdb->update(
                        $answers_table,
                        array(
                            'question_id'   => $id,
                            'answer'        => (trim($answer_value)),
                            'correct'       => $correct,
                            'ordering'      => ($index + 1),
                        ),
                        array('question_id' => $id),
                    );
                }
            }

            foreach ($answers_results as $answers_result) {
                if ($answers_result >= 0) {
                    $flag = true;
                } else {
                    $flag = false;
                    break;
                }
            }
            $question_id= $id;
            $message = 'updated';
        }

        if(is_wp_error($question_id)){
            $errors = $question_id->get_error_messages();
            $error_code = $question_id->get_error_code();
            foreach ($errors as $error) {
                return new WP_Error( $error_code, esc_html__( $error, 'zoho-flow' ), array('status' => 400) );
            }
        }

        settype($question_id, "string");
        if($question_result >=0 && $flag == true){
            $data["result"] = $this->get_question(array("question_id"=>$question_id ));
        }

        $data["messsage"] = $message;
        $data['answers'] = $answers;
        $correctAnsArr = array();
        forEach($correct_answers as $index=>$value){
            $correctAnsArr[$index] = $answers[$value-1];
        }
        $data['correct_answers'] = $correctAnsArr;

        return rest_ensure_response($data);
    }

    //default API
    public function get_system_info(){
        $system_info = parent::get_system_info();
        if( ! function_exists('get_plugin_data') ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/quiz-maker/quiz-maker.php';
        if(file_exists($plugin_dir)){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['quiz-maker'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}
