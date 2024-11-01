<?php
use EDD\Vendor\Symfony\Component\Translation\Loader\IcuDatFileLoader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow              2.9.0
 * @since the-events-calendar   6.6.2
 */
class Zoho_Flow_TheEventsCalendar extends Zoho_Flow_Service{
    
    /**
     *
     * @var array Webhook events supported.
     */
    public static $supported_events = array(
        "event_created_or_updated",
        "event_status_updated",
        "organizer_created_or_updated",
        "venue_created_or_updated"
    );
    
    /**
     * List all event meta keys
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error Array of all meta keys | WP_Error object with error details.
     */
    public function list_event_meta_keys( $request ){
        global $wpdb;
        
        $query = $wpdb->prepare(
            'SELECT DISTINCT(m.meta_key)
                FROM ' . $wpdb->base_prefix . 'postmeta m
                INNER JOIN ' . $wpdb->base_prefix . 'posts p ON p.ID = m.post_id
                WHERE p.post_type = %s
                LIMIT 1400',
            'tribe_events'
            );
        
        $meta_keys = $wpdb->get_results($query);
        
        return rest_ensure_response( $meta_keys );
    }
    
    /**
     * List all organizer meta keys
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error Array of all meta keys | WP_Error object with error details.
     */
    public function list_organizer_meta_keys( $request ){
        global $wpdb;
        
        $query = $wpdb->prepare(
            'SELECT DISTINCT(m.meta_key)
                FROM ' . $wpdb->base_prefix . 'postmeta m
                INNER JOIN ' . $wpdb->base_prefix . 'posts p ON p.ID = m.post_id
                WHERE p.post_type = %s
                LIMIT 1400',
            'tribe_organizer'
            );
        
        $meta_keys = $wpdb->get_results($query);
        
        return rest_ensure_response( $meta_keys );
    }
    
    /**
     * List all venue meta keys
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error Array of all meta keys | WP_Error object with error details.
     */
    public function list_venue_meta_keys( $request ){
        global $wpdb;
        
        $query = $wpdb->prepare(
            'SELECT DISTINCT(m.meta_key)
                FROM ' . $wpdb->base_prefix . 'postmeta m
                INNER JOIN ' . $wpdb->base_prefix . 'posts p ON p.ID = m.post_id
                WHERE p.post_type = %s
                LIMIT 1400',
            'tribe_venue'
            );
        
        $meta_keys = $wpdb->get_results($query);
        
        return rest_ensure_response( $meta_keys );
    }
    
    /**
     * Fetch the details of an event
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error Array of event details | WP_Error object with error details.
     */
    public function fetch_event( $request ){
        $event_id = $request->get_url_params()['event_id'];
        $event_data = $this->get_event_data( $event_id );
        if( $event_data ){
            return rest_ensure_response( $event_data );
        }
        return new WP_Error( 'rest_bad_request', 'Event does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Fetch the details of an organizer
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error Array of organizer details | WP_Error object with error details.
     */
    public function fetch_organizer( $request ){
        $organizer_id = $request->get_url_params()['organizer_id'];
        $organizer_data = $this->get_organizer_data( $organizer_id );
        if( $organizer_data ){
            return rest_ensure_response( $organizer_data );
        }
        return new WP_Error( 'rest_bad_request', 'Organizer does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Fetch the details of a venue
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     * @return WP_REST_Response|WP_Error Array of venue details | WP_Error object with error details.
     */
    public function fetch_venue( $request ){
        $venue_id = $request->get_url_params()['venue_id'];
        $venue_data = $this->get_venue_data( $venue_id );
        if( $venue_data ){
            return rest_ensure_response( $venue_data );
        }
        return new WP_Error( 'rest_bad_request', 'Venue does not exist!', array( 'status' => 404 ) );
    }
    
    /**
     * Return event details.
     *
     * @param   int     $event_post_id  Event ID.
     * @return  array|boolean   Event array if the event exists | false for others.
     */
    private function get_event_data( $event_post_id ) {
        if( isset( $event_post_id ) && is_numeric( $event_post_id ) && ('tribe_events' === get_post_type( $event_post_id ) ) ){
            $post = get_post( $event_post_id, 'ARRAY_A' );
            $post['meta'] = $this->get_post_meta( $event_post_id );
            return $post;
        }
        return false;
    }
    
    /**
     * Return organizer details.
     *
     * @param   int     $organizer_post_id  Organizer ID.
     * @return  array|boolean   Organizer array if the organizer exists | false for others.
     */
    private function get_organizer_data( $organizer_post_id ) {
        if( isset( $organizer_post_id ) && is_numeric( $organizer_post_id ) && ('tribe_organizer' === get_post_type( $organizer_post_id ) ) ){
            $post = get_post( $organizer_post_id, 'ARRAY_A' );
            $post['meta'] = $this->get_post_meta( $organizer_post_id );
            return $post;
        }
        return false;
    }
    
    /**
     * Return venue details.
     *
     * @param   int     $organizer_post_id  Venue ID.
     * @return  array|boolean   Venue array if the venue exists | false for others.
     */
    private function get_venue_data( $organizer_post_id ) {
        if( isset( $organizer_post_id ) && is_numeric( $organizer_post_id ) && ('tribe_venue' === get_post_type( $organizer_post_id ) ) ){
            $post = get_post( $organizer_post_id, 'ARRAY_A' );
            $post['meta'] = $this->get_post_meta( $organizer_post_id );
            return $post;
        }
        return false;
    }
    
    /**
     * Return meta details of a given post ID.
     *
     * @param   int     $post_id  Post ID.
     * @return  array   Array of meta key values.
     */
    private function get_post_meta( $post_id ){
        if( isset( $post_id ) && is_numeric( $post_id ) ){
            $post_meta = get_post_meta($post_id);
            $post_meta_unserialized = array();
            foreach ($post_meta as $key => $value) {
                $post_meta_unserialized[$key] = maybe_unserialize($value[0]);
            }
            return $post_meta_unserialized;
        }
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
            $post_name = "The Events Calendar ";
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
     * Fires when event is created or updated.
     *
     * @param int       $event_id   Event ID 
     * @param array     $data       Array of event details
     * @param WP_Post   $event      Post object
     */
    public function payload_event_created_or_updated( $event_id, $data, $event ){
        $args = array(
            'event' => 'event_created_or_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'event_created_or_updated',
                'data' => $this->get_event_data( $event_id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires when event status is updated.
     *
     * @param int       $event_id   Event ID
     * @param array     $data       Array of event details
     */
    public function payload_event_status_updated( $event_id, $data ){
        $args = array(
            'event' => 'event_status_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'event_status_updated',
                'data' => $this->get_event_data( $event_id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires when organizer is created or updated.
     *
     * @param int       $organizer_id   Organizer ID
     * @param array     $data           Array of organizer details
     */
    public function payload_organizer_created_or_updated( $organizer_id, $data ){
        $args = array(
            'event' => 'organizer_created_or_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'organizer_created_or_updated',
                'data' => $this->get_organizer_data( $organizer_id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires when venue is created or updated.
     *
     * @param int       $venue_id       Venue ID
     * @param array     $data           Array of venue details
     */
    public function payload_venue_created_or_updated( $venue_id, $data ){
        $args = array(
            'event' => 'venue_created_or_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'venue_created_or_updated',
                'data' => $this->get_venue_data( $venue_id )
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
        $plugin_dir = ABSPATH . 'wp-content/plugins/the-events-calendar/the-events-calendar.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['the_events_calendar'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
}