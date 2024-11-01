<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow        2.5.0
 * @since userfeedback    1.0.14
 */
class Zoho_Flow_UserFeedback extends Zoho_Flow_Service{

  /**
   * webhook events supported
   */
  public static $supported_events = array( "response_added" );

  /**
   * list surveys
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * request params  Optional. Arguments for querying surveys.
   * @type int    per_page   Number of results per call. Default 10.
   * @type int    page       Index of page. Default 1.
   *
   * @return array Array of survey details.
   */
  public function list_surveys( $request ){
    $survey_controller = New UserFeedback_Survey();
    $survey_controller->paginate(
      isset( $request['per_page'] ) ? $request['per_page'] : 10,
      isset( $request['page'] ) ? $request['page'] : 1
    );
		$surveys = ( $survey_controller->get() )['items'];
    $surveys_list = array();
    foreach ( $surveys as $survey_obj ) {
      $survey = array(
        'id' => $survey_obj->id,
        'title' => $survey_obj->title,
        'status' => $survey_obj->status,
        'impressions' => $survey_obj->impressions,
        'publish_at' => $survey_obj->publish_at,
        'created_at' => $survey_obj->created_at
      );
      array_push( $surveys_list, $survey );
    }
    return rest_ensure_response( $surveys_list );
  }

  /**
   * list survey questions
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * request path param  Mandatory.
   * @type int  survey_id   Survey ID to retrive the questions for.
   *
   * @return array|WP_Error array of survey question details | WP_Error object with error details.
   */
  public function list_survey_questions( $request ){
    $survey_controller = New UserFeedback_Survey();
    $survey = $survey_controller->find( $request['survey_id'] );
    if( $survey ){
      return rest_ensure_response( $survey->questions );
    }
    return new WP_Error( 'rest_bad_request', 'Invalid survey ID', array( 'status' => 404 ) );
  }

  /**
   * Check whether the survey ID is valid or not.
   *
   * @param int   $survey_id    Survey ID.
   *
   * @return bool true if the survey exists | false for others.
   */
  private function is_valid_survey_id( $survey_id ){
    if( isset( $survey_id ) ){
      $survey_controller = New UserFeedback_Survey();
      $survey = $survey_controller->find( $survey_id );
      if( $survey ){
        return true;
      }
      return false;
    }
    else{
      return false;
    }
  }

  /**
   * Creates a webhook entry
   * The events available in $supported_events array only accepted
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * @return array|WP_Error Array with Webhook ID | WP_Error object with error details.
   */
  public function create_webhook( $request ){
    $entry = json_decode( $request->get_body() );
    if( ( !isset( $entry->survey_id ) ) || !$this->is_valid_survey_id( $entry->survey_id ) ){
      return new WP_Error( 'rest_bad_request', "Survey does not exist!", array( 'status' => 400 ) );
    }
    if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
      $args = array(
        'name' => $entry->name,
        'url' => $entry->url,
        'event' => $entry->event,
        'survey_id' => $entry->survey_id
      );
      $post_name = "UserFeedback ";
      $post_id = $this->create_webhook_post( $post_name, $args );
      if( is_wp_error( $post_id ) ){
        $errors = $post_id->get_error_messages();
        return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
      }
      return rest_ensure_response(
        array(
          'webhook_id' => $post_id
        )
      );
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Data validation failed', array( 'status' => 400 ) );
    }
  }

 /**
   * Deletes a webhook entry
   * Webhook ID returned from webhook create event should be used. Use minimum user scope.
   *
   * @param WP_REST_Request $request WP_REST_Request onject.
   *
   * @return array|WP_Error Array with success message | WP_Error object with error details.
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
   * Fires once a response is succesfully stored in DB.
   *
   * @param int     $survey_id          Survey ID.
   * @param int     $response_id        Response ID.
   * @param array   $get_json_params    submitted response details. Question answers and page details included
   *
   * @return array|null Webhook payload. event and response details | null if criteria not match.
   */
  public function payload_response_added( $survey_id, $response_id, $get_json_params ){
    $args = array(
      'event' => 'response_added',
      'survey_id' => $survey_id
    );
    $webhooks = $this->get_webhook_posts( $args );
    if( !empty( $webhooks ) ){
      $event_data = array(
        'event' => 'response_added',
        'data' => array(
           'survey_id' => $survey_id,
           'response_id' => $response_id,
           'question_data' => $get_json_params['answers'],
           'page_data' => $get_json_params['page_submitted']
         )
      );
      foreach( $webhooks as $webhook ){
        $url = $webhook->url;
        zoho_flow_execute_webhook( $url, $event_data, array() );
      }
    }
  }

  /**
	 * default API
	 * Get user and system info.
   *
   * @return array|WP_Error System and logged in user details | WP_Error object with error details.
	 */
  public function get_system_info(){
    $system_info = parent::get_system_info();
    if( ! function_exists( 'get_plugin_data' ) ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_dir = ABSPATH . 'wp-content/plugins/userfeedback-lite/userfeedback.php';
    if(file_exists( $plugin_dir ) ){
      $plugin_data = get_plugin_data( $plugin_dir );
      @$system_info['userfeedback'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
