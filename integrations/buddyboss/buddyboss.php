<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Zoho_Flow_BuddyBoss extends Zoho_Flow_Service
{
    public function get_activities( $request ) {
        $args = array(
            'per_page' => 50,
            'order_by' => 'date_recorded',
            'sort' => 'DESC',
            'max' => 100,
            'count_total' => true
        );
        $activities = BP_Activity_Activity::get($args);
        return rest_ensure_response( $activities);
    }

    public function get_groups(){
        $args = array(
            'per_page' => 100,
            'order_by' => 'date_recorded',
            'sort' => 'DESC',
            'max' => 200,
            'count_total' => true
        );
        $groups = BP_Groups_Group::get($args);
        return rest_ensure_response($groups);
    }

    public function get_members($request){
        $membersObj = new BP_REST_Members_Endpoint();
        $members = $membersObj->get_items($request);
        return rest_ensure_response($members);
    }

    //Actions
    public function activity_post_update($request){
        $request_body = $request->get_body();
        $input = json_decode($request_body, true);
        $args = $input['activity_post'];
        $activity_id = bp_activity_post_update($args);
        $activityObj = new BP_Activity_Activity($activity_id);
        $activity = $activityObj->get(array('in' => array($activity_id)));
        return rest_ensure_response($activity['activities'][0]);
    }

    public function create_group($request){
        $groupObj = new BP_REST_Groups_Endpoint();
        if($groupObj->create_item_permissions_check($request)){
            $data = $groupObj->create_item($request);
            return rest_ensure_response($data);
        }
    }

    public function invite_member_to_group($request) {
        $data = array();
        if((empty($request['group_id']) || !ctype_digit($request['group_id'])) || (empty($request['inviter_id']) || !ctype_digit($request['inviter_id'])) || ((empty($request['user_id']) || !ctype_digit($request['user_id'])))){
            return new WP_Error( 'rest_bad_request', esc_html__( 'User Id/Follower ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $memberInvObj = new BP_REST_Group_Invites_Endpoint();
        if($memberInvObj->create_item_permissions_check($request)){
            $dataobj = $memberInvObj->create_item($request);
            if(isset($dataobj->errors)){
                return rest_ensure_response($dataobj);
            }else {
                $data['invite_details'] = $dataobj->data;
            }
            $groupObj = new BP_REST_Groups_Endpoint();
            $data['group'] = $groupObj->get_item($request)->data;
            $data['inviter'] = get_user_by('ID', $request['inviter_id'])->data;
            $data['invitee'] = get_user_by('ID', $request['user_id'])->data;
            return rest_ensure_response($data);
        }
    }

    public function follow_request($request) {
        $data =  array();
        $args = array(
            'leader_id' => $request['id'],
            'follower_id' => $request['follower_id']
        );

        if((empty($request['id']) || !ctype_digit($request['id'])) || ((empty($request['follower_id']) || !ctype_digit($request['follower_id'])))){
            return new WP_Error( 'rest_bad_request', esc_html__( 'User Id/Follower ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }

        $followers = bp_get_followers(array('user_id' => $request['id']));
        if($request['action'] === 'follow'){
            if(!in_array($request['follower_id'], $followers)){
                bp_start_following($args);
                $data['status'] = "You're following the user.";
            }else {
                $data['status'] = "You're already following this user.";
            }
        } else if($request['action'] === 'unfollow'){
            if(in_array($request['follower_id'], $followers)){
                bp_stop_following($args);
                $data['status'] = "User unfollowed.";
            }else {
                $data['status'] = "User already unfollowed";
            }
        }
        $data['action'] = $request['action'];
        $data['leader'] = get_user_by("ID", $request['id'])->data;
        $data['follower'] = get_user_by("ID", $request['follower_id'])->data;
        return rest_ensure_response($data);
    }

    public function create_friendship($request){
        $data = array();

        if((empty($request['initiator_id']) || !ctype_digit($request['initiator_id'])) || ((empty($request['friend_id']) || !ctype_digit($request['friend_id'])))){
            return new WP_Error( 'rest_bad_request', esc_html__( 'Initiator ID/Friend ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $friendship = new BP_REST_Friends_Endpoint();
        $item = $friendship->create_item($request);
        if(isset($item->errors)){
            return rest_ensure_response($item);
        }else{
            $data['data'] = $item;
            $data['initiator'] = get_user_by("ID", $request['initiator_id'])->data;
            $data['friend'] = get_user_by("ID", $request['friend_id'])->data;
            return rest_ensure_response($data);
        }
    }

    public function send_invite($request){

        $invites = new BP_REST_Invites_Endpoint();
        $data = $invites->create_item($request);
        return rest_ensure_response($data);
    }
    public function create_topic($request){
        $data = array();
        $topic = new BP_REST_Topics_Endpoint();
        $data = $topic->create_item($request);
        return rest_ensure_response($data);
    }

    public function get_forums($request){
        $forums = new BP_REST_Forums_Endpoint();
        $data = $forums->get_items($request);
        return rest_ensure_response($data);
    }

    public function get_webhooks($request){
        $type = $request['type'];
        $args = array(
            'type' => $type
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
                'type' => $webhook->type,
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }

        return rest_ensure_response( $data );
    }

    public function create_webhook( $request ) {
        $type = $request['type'];
        $url = esc_url_raw($request['url']);

        $post_id = $this->create_webhook_post($type, array(
            'type' => $type,
            'url' => $url
        ));

        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'type' => $type,
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

    //Hooks
    public function trigger_new_activity($activity){
        $args = array(
            'type' => "new_activity"
        );
        $webhooks = $this->get_webhook_posts($args);

        $activity->webhook_type = "new_activity";
        $files = array();
        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            zoho_flow_execute_webhook($url, $activity, $files);
        }
    }

    public function trigger_new_invite($user_id, $post_id){
        $args = array(
            'type' => "new_invite"
        );
        $data = array();
        $webhooks = $this->get_webhook_posts($args);
        $invite = get_post_meta($post_id);

        foreach ($invite as $key => $value){
            $value = strval($value[0]);
            if(($key == '_bp_invitee_email') || ($key == '_bp_invitee_name') || ($key == '_bp_inviter_name') || ($key == '_bp_invitee_status') || ($key == '_bp_invitee_member_type'))
            {
                $key = str_replace('_bp_', '', $key);
            }else{
                $key = str_replace('bp_', '', $key);
//                 $data[$key] = strval($value[0] == "" ? false : $value[0]);
                $value = (empty($value)) ? false : $value;
            }
            $data[$key] = $value;
        }

        $data['ID'] = $post_id;
        $data['user_id'] = $user_id;
        $data->webhook_type = "new_invite";

        $files = array();
        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            zoho_flow_execute_webhook($url, $data, $files);
        }
    }

    public function trigger_new_forum($forum_id){
        $args = array(
            'type' => "new_forum"
        );
        $forum = bbp_get_forum($forum_id);
        $forum->webhook_type = "new_forum";
        $webhooks = $this->get_webhook_posts($args);
        $files = array();
        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            zoho_flow_execute_webhook($url, $forum, $files);
        }
    }

    public function trigger_new_member($user_id, $user_login, $user_password, $user_email, $user_meta){
        $args = array(
            'type' => "new_member"
        );
        $webhooks = $this->get_webhook_posts($args);

        $files = array();
        $member = get_user_by('id', $user_id);
        $member->webhook_type = "new_member";
        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            zoho_flow_execute_webhook($url, $member->data, $files);
        }
    }

    public function trigger_new_notification($notify){
        $activityObj = new BP_Activity_Activity($notify->item_id);
        $activity = $activityObj->get(array('in' => array($notify->item_id)));

        $notify->data = $activity['activities'][0];
        $notify->webhook_type = "new_notification";
        $args = array(
            'type' => "new_notification"
        );
        $webhooks = $this->get_webhook_posts($args);

        $files = array();
        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            zoho_flow_execute_webhook($url, $notify, $files);
        }
    }

    //default API
    public function get_system_info(){
        $system_info = parent::get_system_info();
        if( ! function_exists('get_plugin_data') ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/buddyboss-platform/bp-loader.php';
        if(file_exists($plugin_dir)){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['buddyboss'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}
