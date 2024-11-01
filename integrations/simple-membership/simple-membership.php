<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if (!class_exists('SwpmMembershipLevel')) {
    require_once ABSPATH . 'wp-content/plugins/simple-membership/classes/class.swpm-membership-level.php';
}

class Zoho_Flow_Simple_Membership extends Zoho_Flow_Service
{
    private static $tables = array(
        'members' => 'swpm_members_tbl',
        'membership' => 'swpm_membership_tbl',
        'getmembership' => 'swpm_membership_tbl',
    );

    public static function gettable($key){
        return self::$tables[$key];
    }

    /**
     * get_members - List all members of SWPM
     * @param unknown $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_members($request){
        return rest_ensure_response($this->Fetch_Query_Details('members', null));
    }

    /**
     * get_membership_levels - List all the membershiplevels
     * @param unknown $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_membership_levels($request) {
        $data = array();
        $result = $this->Fetch_Query_Details('membership', null)->data;
        foreach ($result as $member) {
            $valid_for = $this->column_default($member, 'valid_for');
            $member['valid_for'] = $valid_for;
            array_push($data, $member);
        }
        return rest_ensure_response($data);
    }

    public function create_membership($request){
        global $wpdb;
        $data = array();

        if(empty($request['alias']) || empty($request['role'])){
            $msg = "";
            if(empty($request['alias'])) {
                $msg= "Alias is required.";
            }
            if(empty($request['role'])) {
                $msg = "Role is required.";
            }
            return new WP_Error( 'rest_bad_request', esc_html__( $msg, 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $level_info = $request->get_params();
        $wpdb->insert($wpdb->prefix . "swpm_membership_tbl", $level_info);
        $membership_id = $wpdb->insert_id;

        if(is_wp_error($membership_id)){
            $errors = $membership_id->get_error_messages();
            $error_code = $membership_id->get_error_code();
            foreach ($errors as $error) {
                return new WP_Error( $error_code, esc_html__( $error, 'zoho-flow' ), array('status' => 400) );
            }
        }
        $response = $this->Fetch_Query_Details('getmembership', array('id'=>$membership_id));
        $valid_for = $this->column_default($request, 'valid_for');
        $data = (array) $response->data[0];
        $data['valid_for'] = $valid_for;

        $this->trigger_webhook($data, 'membership');
        return rest_ensure_response($data);
    }

    public function update_membership($request) {
        global $wpdb;
        $data = array();

        $membership_id = $request['id'];

        if(!ctype_digit($membership_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Membership id is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        if(empty($request['alias']) || empty($request['role'])){
            $msg = "";
            if(empty($request['alias'])) {
                $msg= "Alias is required.";
            }
            if(empty($request['role'])) {
                $msg = "Role is required.";
            }
            return new WP_Error( 'rest_bad_request', esc_html__( $msg, 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "swpm_membership_tbl WHERE " . ' id=%d', $membership_id);
        $level = $wpdb->get_row($query, ARRAY_A);
        $level = (array) $level;

        if(empty($level)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Membership row does not exists.', 'zoho-flow' ), array( 'status' => 400 ) );
        } else {
            $level_info = $request->get_params();
            $result = $wpdb->update($wpdb->prefix . "swpm_membership_tbl", $level_info, array('id'=> $membership_id));

            $response = $this->Fetch_Query_Details('getmembership', $request);
            $valid_for = $this->column_default($request, 'valid_for');
            $data = (array) $response->data[0];
            $data['valid_for'] = $valid_for;
            if ( ! $result ) {
                if(is_wp_error($membership_id)){
                    //DB error occurred
                    $errormsg = 'Update membership level - DB error occurred: ' . json_encode( $wpdb->last_result );
                    return new WP_Error('rest_bad_request', esc_html__($errormsg, 'zoho-flow'), array('status' => 400));
                }
            }
        }
        $this->trigger_webhook($data, 'membership');
        return rest_ensure_response($data);
    }

    public function create_member($request){
        global $wpdb;
        //First, check if email or username belongs to an existing admin user.
        SwpmMemberUtils::check_and_die_if_email_belongs_to_admin_user($request['email']);
        SwpmMemberUtils::check_and_die_if_username_belongs_to_admin_user($request['user_name']);

        $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "swpm_members_tbl WHERE " . ' user_name=%s', $request['user_name']);
        $profile = $wpdb->get_row($query, ARRAY_A);
        $profile = (array) $profile;

        if (!empty($profile)) {
            return new WP_Error( 'rest_bad_request', esc_html__( 'The member already exists.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $profile = $request->get_params();
        $profile['member_since'] = SwpmUtils::get_current_date_in_wp_zone();

        $member_id = SwpmMemberUtils::create_swpm_member_entry_from_array_data($profile);

        if(is_wp_error($member_id)){
            $errors = $member_id->get_error_messages();
            $error_code = $member_id->get_error_code();
            foreach ($errors as $error) {
                return new WP_Error( $error_code, esc_html__( $error, 'zoho-flow' ), array('status' => 400) );
            }
        }

        $data = $this->get_member(array('member_id'=>$member_id, 'login'=>null));
        $this->trigger_webhook($data, 'members');
        return rest_ensure_response($data);
    }

    public function update_member($request) {
        global $wpdb;
        $member_id = $request['member_id'];

        if(!ctype_digit($member_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Member ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "swpm_members_tbl WHERE " . ' member_id=%d', $member_id);
        $profile = $wpdb->get_row($query, ARRAY_A);
        $profile = (array) $profile;

        if(empty($profile)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Member does not exists.', 'zoho-flow' ), array( 'status' => 400 ) );
        } else {
            $profile = $request->get_params();
            $result = $wpdb->update($wpdb->prefix . "swpm_members_tbl", $profile, array('member_id'=> $member_id));
            if ( ! $result ) {
                if(is_wp_error($member_id)){
                    //DB error occurred
                    $errormsg = 'Update member - DB error occurred: ' . json_encode( $wpdb->last_result );
                    return new WP_Error('rest_bad_request', esc_html__($errormsg, 'zoho-flow'), array('status' => 400));
                }
            }
        }
        $data = $this->get_member(array('member_id'=>$member_id, 'login'=>null));
        $this->trigger_webhook($data, 'members');
        return rest_ensure_response($data);
    }

    /**
     * get_member - Get member using username/email/memberid
     * @param unknown $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_member($request) {
        return rest_ensure_response($this->Fetch_Query_Details('getmember' , $request));
    }

    /**
     * Fetch_Query_Details is the function to execute the query for given modules.
     * @param string  $action Module that related to query. Choose the table based on the param.
     * @return WP_REST_Response|WP_Error
     */
    private function Fetch_Query_Details( $action, $request){
        global $wpdb;

        if($action==='getmember'){
            $table = $this->gettable('members');
            $login = esc_attr($request['login']);
            if(isset($login) && filter_var($request['login'], FILTER_VALIDATE_EMAIL)){
                $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . $table . " WHERE  email = %s", $login);
            } else if(isset($request['member_id'])){
                $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . $table . " WHERE  member_id = %d", $request['member_id']);
            }else if(isset($request['login'])){
                $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . $table . " WHERE  user_name = %s", $login);
            }
        }else {
            $table = $this->gettable($action);
            if($action === 'getmembership'){
                $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . $table . " WHERE id=%d", $request['id']);
            } else {
                $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . $table);
            }
        }
        $totalitems = $wpdb->query($query);
        if($totalitems > 0){
            $members = $wpdb->get_results($query, ARRAY_A);
            return rest_ensure_response($members);
        }else {
            return rest_ensure_response(array());
        }
    }

    public function get_webhooks($request){
        $data = array();
        $args = array(
            'type' => $request['type']
        );
        $webhooks = $this->get_webhook_posts($args);
        foreach ( $webhooks as $webhook ) {
            $webhook = array(
                'plugin_service' => $this->get_service_name(),
                'id' => $webhook->ID,
                'type' => $request['type'],
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }

        return rest_ensure_response( $data );
    }

    public function create_webhook_deprecated($request){
        $post_title = $request['type'];
        $url = esc_url_raw($request['url']);
        $post_id = $this->create_webhook_post($post_title, array(
            'type' => $request['type'],
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'type' =>$request['type'],
            'url' => $url
        ) );
    }

    public function delete_webhook_deprecated($request) {
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

    public function update_membership_level_of_member($request) {
        $member_id = $request['member_id'];
        $membership_lvl = $request['membership_level'];
        if(!ctype_digit($member_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Member ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        if(!ctype_digit($membership_lvl)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The Membership level is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        SwpmMemberUtils::update_membership_level($member_id, $request['membership_level_id']);
        return rest_ensure_response($this->get_member(array('member_id'=>$member_id, 'login'=>null)));
    }

    public function trigger_webhook($data, $type){
        $args = array(
            'type'    =>  $type,
        );
        $webhooks = $this->get_webhook_posts($args);
        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            zoho_flow_execute_webhook($url, $data, array());
        }
    }

    //Hooks
    public function process_swpm_registration_user_data($member_info) {
        $args = array(
            'type'=>"members",
        );

        $user = SwpmMemberUtils::get_user_by_email($member_info['email']);
        $member_info['member_id'] = $user->member_id;
        $webhooks = $this->get_webhook_posts($args);
        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            $member_info['type']=$webhook->type;
            zoho_flow_execute_webhook($url, $member_info,array());
        }
    }

    /**
     * column_default - Used to update membership level validity.
     * @param array $item
     * @param string $column_name
     * @return string unknown
     */
    function column_default($item, $column_name) {
        if ($column_name == 'valid_for') {
            if ($item['subscription_duration_type'] == SwpmMembershipLevel::NO_EXPIRY) {
                return 'No Expiry';
            }
            if ($item['subscription_duration_type'] == SwpmMembershipLevel::FIXED_DATE) {
                $formatted_date = SwpmUtils::get_formatted_date_according_to_wp_settings($item['subscription_period']);
                return $formatted_date;
            }
            if ($item['subscription_duration_type'] == SwpmMembershipLevel::DAYS) {
                return $item['subscription_period'] . " Day(s)";
            }
            if ($item['subscription_duration_type'] == SwpmMembershipLevel::WEEKS) {
                return $item['subscription_period'] . " Week(s)";
            }
            if ($item['subscription_duration_type'] == SwpmMembershipLevel::MONTHS) {
                return $item['subscription_period'] . " Month(s)";
            }
            if ($item['subscription_duration_type'] == SwpmMembershipLevel::YEARS) {
                return $item['subscription_period'] . " Year(s)";
            }
        }
        return stripslashes($item[$column_name]);
    }

    /**
     * webhook events supported
     * @since 2.6.0
     */
    public static $supported_events = array( "member_added_admin_end", "member_updated_admin_end", "member_added_front_end", "member_updated_front_end", "member_level_updated" );

    /**
     * List members
     * @since 2.6.0
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error WP_REST_Response Array of member details| WP_Error Error details.
     */
    public function list_members( $request ){
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}swpm_members_tbl ORDER BY member_id DESC LIMIT 1000"
            )
        );
        $member_array = array();
        foreach ( $results as $member_obj ){
            $member_details = json_decode(json_encode($member_obj), true);
            unset($member_details['password']);
            $member_array[] = $member_details;
        }
        return rest_ensure_response( $member_array );
    }

    /**
     * Fetch member details
     * @since 2.6.0
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error|WP_Error WP_REST_Response member details| WP_Error Error details.
     */
    public function fetch_member( $request ){
        $fetch_field = esc_sql( $request['fetch_field'] );
        $fetch_value = esc_sql( $request['fetch_value'] );
        if( !empty( $fetch_field ) && !empty( $fetch_value ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}swpm_members_tbl WHERE $fetch_field = %s ORDER BY member_id DESC LIMIT 1000",
                    $fetch_value
                )
            );
            $member_array = array();
            foreach ( $results as $member_obj ){
                $member_details = json_decode(json_encode($member_obj), true);
                unset($member_details['password']);
                $member_array[] = $member_details;
            }
            return rest_ensure_response( $member_array );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Number of parameters mismatch', array( 'status' => 404 ));
        }
    }

    /**
     * List membership levels
     * @since 2.6.0
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error WP_REST_Response Array of membershiplevel details| WP_Error Error details.
     */
    public function list_membership_levels( $request ){
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}swpm_membership_tbl ORDER BY id DESC LIMIT 1000"
            )
            );
        $membership_level_array = array();
        foreach ( $results as $membrship_level_obj ){
            $membership_level_details = json_decode(json_encode($membrship_level_obj), true);
            $membership_level_array[] = $membership_level_details;
        }
        return rest_ensure_response( $membership_level_array );
    }

    /**
     * Fetch membership level details
     * @since 2.6.0
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error|WP_Error WP_REST_Response membership level details| WP_Error Error details.
     */
    public function fetch_membership_level( $request ){
        $fetch_field = esc_sql( $request['fetch_field'] );
        $fetch_value = esc_sql( $request['fetch_value'] );
        if( !empty( $fetch_field ) && !empty( $fetch_value ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}swpm_membership_tbl WHERE $fetch_field = %s ORDER BY id DESC LIMIT 1000",
                    $fetch_value
                )
                );
            $membership_level_array = array();
            foreach ( $results as $membership_level_obj ){
                $membership_level_details = json_decode(json_encode($membership_level_obj), true);
                $membership_level_array[] = $membership_level_details;
            }
            return rest_ensure_response( $membership_level_array );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Number of parameters mismatch', array( 'status' => 404 ));
        }
    }

    /**
     * Updates membership level of a member
     * @since 2.6.0
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error|WP_Error WP_REST_Response member details| WP_Error Error details.
     */
    public function update_membership_level( $request ){
        $member_id = $request['member_id'];
        $membership_level_id = $request['membership_level_id'];
        if( !$this->get_member_by_id( $member_id ) ){
            return new WP_Error( 'rest_bad_request', 'Member does not exist! ', array( 'status' => 400 ));
        }
        if( !$this->get_membership_level_by_id( $membership_level_id ) ){
            return new WP_Error( 'rest_bad_request', 'Membership level does not exist! ', array( 'status' => 400 ));
        }
        SwpmMemberUtils::update_membership_level_and_role( $member_id, $membership_level_id );
        return rest_ensure_response( $this->get_member_by_id( $member_id ) );
    }

    /**
     * Fetch the member details by ID
     * @since 2.6.0
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return array|boolean Array of member details| boolean false if member not found.
     */
    private function get_member_by_id( $member_id ){
        if( ( isset( $member_id ) )  && ( is_numeric( $member_id ) ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}swpm_members_tbl WHERE member_id = %s",
                    $member_id
                )
            );
            if( $results ){
                $member_details = json_decode(json_encode($results[0]), true);
                unset($member_details['password']);
                return $member_details;
            }
        }
        return false;
    }

    /**
     * Fetch the member details by Email address
     * @since 2.6.0
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return array|boolean Array of member details| boolean false if member not found.
     */
    private function get_member_by_email( $email ){
        if( isset( $email ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}swpm_members_tbl WHERE email = %s",
                    $email
                )
                );
            if( $results ){
                $member_details = json_decode(json_encode($results[0]), true);
                unset($member_details['password']);
                return $member_details;
            }
        }
        return false;
    }

    /**
     * Fetch the membership level details by ID
     * @since 2.6.0
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return array|boolean Array of membership level details| boolean false if membership level not found.
     */
    private function get_membership_level_by_id( $membership_level_id ){
        if( ( isset( $membership_level_id ) )  && ( is_numeric( $membership_level_id ) ) ){
            global $wpdb;
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}swpm_membership_tbl WHERE id = %s",
                    $membership_level_id
                )
                );
            if( $results ){
                $membership_level_details = json_decode(json_encode($results[0]), true);
                return $membership_level_details;
            }
        }
        return false;
    }

    /**
     * Create a webhook entry
     * @since 2.6.0
     *
     * The events available in $supported_events array only accepted
     *
     * @param WP_REST_Request $request WP_REST_Request onject.
     *
     * @return array|WP_Error Array with Webhook ID | WP_Error object with error details.
     */
    public function create_webhook( $request ){
        $entry = json_decode( $request->get_body() );
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event
            );
            $post_name = "Simple Membership ";
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
     * Delete a webhook entry
     * @since 2.6.0
     *
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
     * Fires once a admin adds a member.
     * @since 2.6.0
     *
     * @param array    $member_info     array of member details.
     * @return array|null Webhook payload. member details | null if criteria not match.
     */
    public function payload_member_added_admin_end( $member_info ){
        $args = array(
            'event' => 'member_added_admin_end'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            unset( $member_info['password'] );
            unset( $member_info['plain_password'] );
            if( isset( $member_info['email'] ) ){
                $member_details = $this->get_member_by_email( $member_info['email'] );
                if( $member_details ){
                    $member_info = array_merge( $member_info, $member_details );
                }
            }
            $event_data = array(
                'event' => 'member_added_admin_end',
                'data' => $member_info
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }

    /**
     * Fires once a admin updates a member.
     * @since 2.6.0
     *
     * @param array    $member_info     array of member details.
     * @return array|null Webhook payload. member details | null if criteria not match.
     */
    public function payload_member_updated_admin_end( $member_info ){
        $args = array(
            'event' => 'member_updated_admin_end'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            unset( $member_info['password'] );
            unset( $member_info['plain_password'] );
            if( isset( $member_info['member_id'] ) ){
                $member_details = $this->get_member_by_id( $member_info['member_id'] );
                if( $member_details ){
                    $member_info = array_merge( $member_info, $member_details );
                    unset( $member_info['password'] );
                    unset( $member_info['plain_password'] );
                }
            }
            $event_data = array(
                'event' => 'member_updated_admin_end',
                'data' => $member_info
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }

    /**
     * Fires once a member added from front end (Member sign up).
     * @since 2.6.0
     *
     * @param array    $member_info     array of member details.
     * @return array|null Webhook payload. member details | null if criteria not match.
     */
    public function payload_member_added_front_end( $member_info ){
        $args = array(
            'event' => 'member_added_front_end'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            unset( $member_info['password'] );
            unset( $member_info['plain_password'] );
            if( isset( $member_info['email'] ) ){
                $member_details = $this->get_member_by_email( $member_info['email'] );
                if( $member_details ){
                    $member_info = array_merge( $member_info, $member_details );
                }
            }
            $event_data = array(
                'event' => 'member_added_front_end',
                'data' => $member_info
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }

    /**
     * Fires once a member updated from front end (Profile page update).
     * @since 2.6.0
     *
     * @param array    $member_info     array of member details.
     * @return array|null Webhook payload. member details | null if criteria not match.
     */
    public function payload_member_updated_front_end( $member_info ){
        $args = array(
            'event' => 'member_updated_front_end'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            unset( $member_info['password'] );
            unset( $member_info['plain_password'] );
            if( isset( $member_info['email'] ) ){
                $member_details = $this->get_member_by_email( $member_info['email'] );
                if( $member_details ){
                    $member_info = array_merge( $member_info, $member_details );
                }
            }
            $event_data = array(
                'event' => 'member_updated_front_end',
                'data' => $member_info
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }

    /**
     * Fires once a membership level of a member updated.
     * @since 2.6.0
     *
     * @param array    $membership_level_details    array with member id and current, previous membership level IDs.
     * @return array|null Webhook payload. member details | null if criteria not match.
     */
    public function payload_member_level_updated( $membership_level_details ){
        $args = array(
            'event' => 'member_level_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $member_info = $this->get_member_by_id( $membership_level_details['member_id'] );
            $member_info['prev_membership_level'] = $membership_level_details['from_level'];
            $event_data = array(
                'event' => 'member_level_updated',
                'data' => $member_info
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
     * @since 2.6.0
     *
     * @return array|WP_Error System and logged in user details | WP_Error object with error details.
     */
    public function get_system_info(){
        $system_info = parent::get_system_info();
        if( ! function_exists( 'get_plugin_data' ) ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/simple-membership/simple-wp-membership.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['simple_membership'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}
