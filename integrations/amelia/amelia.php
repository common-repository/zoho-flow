<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow  2.5.0
 * @since amelia    1.1.2
 */
class Zoho_Flow_Amelia extends Zoho_Flow_Service{

  /**
   * Webhook events supported
   */
  public static $supported_events = array( "event_booking_added", "service_booking_added", "service_booking_canceled", "service_booking_rescheduled", "customer_added", "payment_added" );

  /**
   * list events
   *
   * @param WP_REST_Request $request WP_REST_Request object.
   *
   * @return WP_REST_Response WP_REST_Response array of event details.
   */
  public function list_events( $request ){
    global $wpdb;
    $results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, parentId, name, status, maxCapacity, price, created FROM {$wpdb->prefix}amelia_events WHERE status = %s ORDER BY created DESC LIMIT 1000",
				'approved'
				    )
		);
    return rest_ensure_response( $results );
  }

  /**
   * list services
   *
   * @param WP_REST_Request $request WP_REST_Request object.
   *
   * @return WP_REST_Response WP_REST_Response array of service details.
   */
  public function list_services( $request ){
      global $wpdb;
      $results = $wpdb->get_results(
          $wpdb->prepare(
              "SELECT * FROM {$wpdb->prefix}amelia_services WHERE status = %s ORDER BY id DESC LIMIT 1000",
              'visible'
                  )
      );
      return rest_ensure_response( $results );
  }

  /**
   * list categories
   *
   * @param WP_REST_Request $request WP_REST_Request object.
   *
   * @return WP_REST_Response WP_REST_Response array of category details.
   */
  public function list_categories( $request ){
      global $wpdb;
      $results = $wpdb->get_results(
          $wpdb->prepare(
              "SELECT * FROM {$wpdb->prefix}amelia_categories WHERE status = %s ORDER BY id DESC LIMIT 1000",
              'visible'
                  )
          );
      return rest_ensure_response( $results );
  }

  /**
   * list services by cetegory
   *
   * @param WP_REST_Request $request WP_REST_Request object.
   *
   * @return WP_REST_Response|WP_Error WP_REST_Response array of service details. | WP_Error object with error details.
   */
  public function list_services_by_category( $request ){
      if( $this->is_valid_category_id( $request['category_id'] ) ){
          global $wpdb;
          $results = $wpdb->get_results(
              $wpdb->prepare(
                  "SELECT * FROM {$wpdb->prefix}amelia_services WHERE status = %s AND categoryId = %d ORDER BY id DESC LIMIT 1000",
                  'visible',
                  $request['category_id']
                  )
              );
          return rest_ensure_response( $results );
      }
      else{
          return new WP_Error( 'rest_bad_request', "Category does not exist!", array( 'status' => 400 ) );
      }
  }

  /**
   * Check whether the Event ID is valid or not.
   *
   * @param int $event_id Event ID
   *
   * @return boolean true if the event exists | false for others.
   */
  private function is_valid_event_id( $event_id ){
      if( isset( $event_id ) && is_numeric( $event_id ) ){
          global $wpdb;
          $results = $wpdb->get_results(
              $wpdb->prepare(
                  "SELECT * FROM {$wpdb->prefix}amelia_events WHERE id = %d LIMIT 1",
                  $event_id
              )
              );
          if( 0 < sizeof($results) ){
              return true;
          }
          return false;
      }
      else{
          return false;
      }
  }

  /**
   * Check whether the Category ID is valid or not.
   *
   * @param int $category_id Category ID
   *
   * @return boolean true if the category exists | false for others.
   */
  private function is_valid_category_id( $category_id ){
      if( isset( $category_id ) && is_numeric( $category_id ) ){
          global $wpdb;
          $results = $wpdb->get_results(
              $wpdb->prepare(
                  "SELECT * FROM {$wpdb->prefix}amelia_categories WHERE id = %d LIMIT 1",
                  $category_id
                  )
              );
          if( 0 < sizeof($results) ){
              return true;
          }
          return false;
      }
      else{
          return false;
      }
  }

  /**
   * Check whether the Service ID is valid or not.
   *
   * @param int $service_id Service ID
   *
   * @return boolean true if the service exists | false for others.
   */
  private function is_valid_service_id( $service_id ){
      if( isset( $service_id ) && is_numeric( $service_id ) ){
          global $wpdb;
          $results = $wpdb->get_results(
              $wpdb->prepare(
                  "SELECT * FROM {$wpdb->prefix}amelia_services WHERE id = %d LIMIT 1",
                  $service_id
              )
              );
          if( 0 < sizeof($results) ){
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
   * @param WP_REST_Request $request WP_REST_Request object.
   *
   * @return WP_REST_Response|WP_Error WP_REST_Response array with Webhook ID | WP_Error object with error details.
   */
  public function create_webhook( $request ){
      $entry = json_decode( $request->get_body() );
      if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
          $args = array(
              'name' => $entry->name,
              'url' => $entry->url,
              'event' => $entry->event
          );
          if( ( isset( $entry->event_id ) ) && $this->is_valid_event_id( $entry->event_id ) && ( "event_booking_added" === $entry->event ) ){
              $args['event_id'] = $entry->event_id;
          }
          elseif ( ( isset( $entry->service_id ) ) && $this->is_valid_service_id( $entry->service_id ) && ( ( "service_booking_added" === $entry->event) || ( "service_booking_canceled" === $entry->event) ) || ( "service_booking_rescheduled" === $entry->event) ){
              $args['service_id'] = $entry->service_id;
          }
          elseif( 'customer_added' !== $entry->event && 'payment_added' !== $entry->event ){
              return new WP_Error( 'rest_bad_request', 'Invalid Module ID', array( 'status' => 400 ) );
          }
          $post_name = "Amelia ";
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
   * @param WP_REST_Request $request WP_REST_Request object
   *
   * @return WP_REST_Response|WP_Error  WP_REST_Response array with success message | WP_Error object with error details.
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
   * Fires once booking is added
   *
   * @param array $booking Booking data
   *
   * @return array|null Webhook payload. event, and booking details | null if criteria not match.
   */
  public function payload_event_booking_added( $booking ){
      if( 'event' === $booking['type'] ){
          $args = array(
              'event' => 'event_booking_added',
              'event_id' => $booking['event']['id']
          );
          $webhooks = $this->get_webhook_posts( $args );
          if( !empty( $webhooks ) ){
              unset($booking['bookable']['bookings']);
              unset($booking['event']['bookings']);
              $event_data = array(
                  'event' => 'event_booking_added',
                  'data' => $booking
              );
              foreach( $webhooks as $webhook ){
                  $url = $webhook->url;
                  zoho_flow_execute_webhook( $url, $event_data, array() );
              }
          }
      }
  }

  /**
   * Fires once booking is added
   *
   * @param array $booking Booking data
   *
   * @return array|null Webhook payload. event, and booking details | null if criteria not match.
   */
  public function payload_service_booking_added( $booking ){
      if( 'appointment' === $booking['type'] ){
          $args = array(
              'event' => 'service_booking_added',
              'service_id' => $booking['bookable']['id']
          );
          $webhooks = $this->get_webhook_posts( $args );
          if( !empty( $webhooks ) ){
              $event_data = array(
                  'event' => 'service_booking_added',
                  'data' => $booking
              );
              foreach( $webhooks as $webhook ){
                  $url = $webhook->url;
                  zoho_flow_execute_webhook( $url, $event_data, array() );
              }
          }
      }
  }

  /**
   * Fires once booking is canceled.
   * Payload handler for amelia_after_booking_canceled. Not tested yet. Need a customer panel to test the feature.
   *
   * @param array $booking Booking data
   *
   * @return array|null Webhook payload. event, and booking details | null if criteria not match.
   */
  public function payload_booking_canceled( $booking ){
      if( 'appointment' === $booking['type'] ){
          $args = array(
              'event' => 'service_booking_canceled',
              'service_id' => $booking['bookable']['id']
          );
          $webhooks = $this->get_webhook_posts( $args );
          if( !empty( $webhooks ) ){
              $event_data = array(
                  'event' => 'service_booking_canceled',
                  'data' => $booking
              );
              foreach( $webhooks as $webhook ){
                  $url = $webhook->url;
                  zoho_flow_execute_webhook( $url, $event_data, array() );
              }
          }
      }
  }

  /**
   * Fires once booking is rescheduled
   * Payload handler for amelia_after_booking_rescheduled. Not tested yet. Need a customer panel to test the feature.
   *
   * @param array $booking Booking data
   *
   * @return array|null Webhook payload. event, and booking details | null if criteria not match.
   */
  public function payload_booking_rescheduled( $booking ){
      if( 'appointment' === $booking['type'] ){
          $args = array(
              'event' => 'service_booking_rescheduled',
              'service_id' => $booking['bookable']['id']
          );
          $webhooks = $this->get_webhook_posts( $args );
          if( !empty( $webhooks ) ){
              $event_data = array(
                  'event' => 'service_booking_rescheduled',
                  'data' => $booking
              );
              foreach( $webhooks as $webhook ){
                  $url = $webhook->url;
                  zoho_flow_execute_webhook( $url, $event_data, array() );
              }
          }
      }
  }

  /**
   * Fires once customer is added
   *
   * @param array $customer Customer data
   *
   * @return array|null Webhook payload. event, and customer details | null if criteria not match.
   */
  public function payload_customer_added( $customer ){
      if( is_array( $customer ) ){
          $args = array(
              'event' => 'customer_added'
          );
          $webhooks = $this->get_webhook_posts( $args );
          if( !empty( $webhooks ) ){
              $event_data = array(
                  'event' => 'customer_added',
                  'data' => $customer
              );
              foreach( $webhooks as $webhook ){
                  $url = $webhook->url;
                  zoho_flow_execute_webhook( $url, $event_data, array() );
              }
          }
      }
  }

  /**
   * Fires once payment is done
   * Payload handler for amelia_after_payment_added. Not tested yet. Need a paid account to test this feature.
   *
   * @param array $payment Payment data
   *
   * @return array|null Webhook payload. event, and customer details | null if criteria not match.
   */
  public function payload_payment_added( $payment ){
      $args = array(
          'event' => 'payment_added'
      );
      $webhooks = $this->get_webhook_posts( $args );
      if( !empty( $webhooks ) ){
          $event_data = array(
              'event' => 'payment_added',
              'data' => $payment
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
    $plugin_dir = ABSPATH . 'wp-content/plugins/ameliabooking/ameliabooking.php';
    if(file_exists( $plugin_dir ) ){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['amelia'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
