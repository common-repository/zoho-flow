<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow  2.6.0
 * @since wp-polls  2.77.2
 */
class Zoho_Flow_WP_Polls extends Zoho_Flow_Service{

    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array( "poll_response_submitted" );

    /**
     * List polls
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of poll details | WP_Error object with error details.
     */
    public function list_polls( $request ){
        global $wpdb;
        $table_name = $wpdb->prefix .'pollsq';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY pollq_id DESC LIMIT 1000"
                    )
        );
        return rest_ensure_response( $results );
    }
    
    /**
     * Get poll details
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * 
     * Request path param  Mandatory.
     * @type int  poll_id   Poll ID to retrive the details.
     * 
     * @return WP_REST_Response|WP_Error    WP_REST_Response Poll details | WP_Error object with error details.
     */
    public function get_poll( $request ){
        $poll_id = $request['poll_id'];
        $poll_details = $this->is_valid_poll( $poll_id );
        if( $poll_details ){
            return rest_ensure_response( $poll_details[0] );
        }
        return new WP_Error( 'rest_bad_request', "Poll does not exist!", array( 'status' => 404 ) );
    }
    
    /**
     * List poll options
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * Request path param  Mandatory.
     * @type int  poll_id   Poll ID to retrive the options.
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response Array of poll option details | WP_Error object with error details.
     */
    public function list_poll_options( $request ){
        global $wpdb;
        $poll_id = $request['poll_id'];
        if( $this->is_valid_poll( $poll_id ) ){
            $table_name = $wpdb->prefix .'pollsa';
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE polla_qid = %s ORDER BY polla_qid ASC LIMIT 1000",
                    $poll_id
                )
                );
            return rest_ensure_response( $results );
        }
        return new WP_Error( 'rest_bad_request', "Poll does not exist!", array( 'status' => 404 ) );
    }

    /**
     * Check whether the Poll ID is valid or not.
     *
     * @param int $poll_id  Poll ID.
     * @return array|boolean  array if the poll exists | false for others.
     */
    private function is_valid_poll( $poll_id ){
        if( ( isset( $poll_id ) )  && ( is_numeric( $poll_id ) ) ){
            global $wpdb;
            $table_name = $wpdb->prefix .'pollsq';
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE pollq_id = %s",
                    $poll_id
                )
                );
            return $results;
        }
        return false;
    }

    /**
     * Check whether the Option ID is valid or not.
     *
     * @param int $option_id  Option ID.
     * @return array|boolean  array if the option exists | false for others.
     */
    private function is_valid_option( $option_id ){
        if( ( isset( $option_id ) )  && ( is_numeric( $option_id ) ) ){
            global $wpdb;
            $table_name = $wpdb->prefix .'pollsa';
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE polla_aid = %s",
                    $option_id
                    )
                );
            return $results;
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
            $post_name = "WP-Polls ";
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
     * Fires after poll submission stored in DB.
     */
    public function payload_poll_submitted( ){
        $args = array(
            'event' => 'poll_response_submitted'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $poll_data = $_POST;
            $payload_data = array(
                'poll_id' => $poll_data['poll_id']
            );
            $poll_details = $this->is_valid_poll( $poll_data['poll_id'] );
            if( $poll_details ){
                $payload_data['poll_question'] = $poll_details[0]->pollq_question;
                $payload_data['poll_totalvotes'] = $poll_details[0]->pollq_totalvotes;
                $payload_data['poll_totalvoters'] = $poll_details[0]->pollq_totalvoters;
            }
            $payload_data['poll_answer_id'] = $poll_data['poll_'.$poll_data['poll_id']];
            $array_ans_ids = explode( ',', $poll_data['poll_'.$poll_data['poll_id']] );
            $array_ans_options = array();
            foreach( $array_ans_ids as $id ){
                $ans = $this->is_valid_option( $id );
                if( $ans ){
                    $array_ans_options[] = $ans[0]->polla_answers;
                }
            }
            $payload_data['poll_answer'] = implode( ',', $array_ans_options );
            $event_data = array(
                'event' => 'poll_response_submitted',
                'data' => $payload_data
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/wp-polls/wp-polls.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['wp_polls'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}
