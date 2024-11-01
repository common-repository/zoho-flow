<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use NinjaTables\App\App;
use NinjaTables\App\Models\NinjaTableItem;

//support from
//zohoflow 2.0.0
//ninja-tables 5.0.4
class Zoho_Flow_NinjaTables extends Zoho_Flow_Service{

  //webhook events supported
  public static $supported_events = array("row_added","row_updated");

	//To list all available ninja tables
  public function list_tables($request){
    $args = array(
  		'post_type'   => 'ninja-table',
  		'numberposts' => -1,
  	);

  	$tables = get_posts( $args );
    return rest_ensure_response( $tables );
  }

	//To fetch specific ninja table detail including column details
  public function get_table_details($request){
    $table_id = $request['table_id'];
    if($this->is_valid_table($table_id)){
      $table_data = get_post($table_id);
      $table_meta = get_post_meta($table_id);
      $table_array = array();
      foreach ($table_data as $key => $value) {
        $table_array[$key] = $value;
      }
      foreach ($table_meta as $key => $value) {
        $table_array[$key] = maybe_unserialize($value[0]);
      }
      return rest_ensure_response($table_array);
    }
    return new WP_Error( 'rest_bad_request', 'Invalid Table ID', array( 'status' => 400 ) );
  }

  //To fetch specific table row details
  public function fetch_table_row($request){
      $table_id = $request->get_url_params()['table_id'];
      $row_id = $request->get_url_params()['row_id'];
      if($this->is_valid_table($table_id)){
          if($this->is_valid_table_row($table_id,$row_id)){
              $row_data = $this->get_table_row($table_id,$row_id);
              $row_data->value= json_decode($row_data->value);
              return rest_ensure_response($row_data);
          }
          else{
              return new WP_Error( 'rest_bad_request', 'Invalid Row ID', array( 'status' => 404 ) );
          }
      }
      else{
          return new WP_Error( 'rest_bad_request', 'Invalid Table ID', array( 'status' => 404 ) );
      }
  }

	//To add row to an existing ninja table
  public function add_table_row($request){
    $table_id = $request['table_id'];
    $request_body = (array)json_decode($request->get_body());
    if($this->is_valid_table($table_id)){
      $table_data = NinjaTableItem::insertTableItem('', $table_id, $request_body, '', '', '');
      return rest_ensure_response($table_data);
    }
    return new WP_Error( 'rest_bad_request', 'Invalid Table ID', array( 'status' => 400 ) );
  }

	//To update en existing ninja table row
  public function update_table_row($request){
    $table_id = $request['table_id'];
    $row_id = $request['row_id'];
    $request_body = (array)json_decode($request->get_body());
    if($this->is_valid_table($table_id)){
      if($this->is_valid_table($table_id)){
        $row_data = $this->get_table_row($table_id,$row_id);
        $row_value = json_decode($row_data->value);
        foreach ($request_body as $key => $value) {
          if(!empty($value)){
            $row_value->$key = $value;
          }
        }
        $table_data = NinjaTableItem::insertTableItem($row_id, $table_id, $row_value, '', '', $row_data->settings);
        return rest_ensure_response($table_data);
      }
      else{
        return new WP_Error( 'rest_bad_request', 'Invalid Row ID', array( 'status' => 400 ) );
      }
    }
    return new WP_Error( 'rest_bad_request', 'Invalid Table ID', array( 'status' => 400 ) );
  }

  //utilities
  private function is_valid_table($table_id){
    if((!empty($table_id)) || (is_numeric($table_id))){
      $post_data = get_post($table_id);
      if($post_data->post_type == 'ninja-table'){
        return true;
      }
      return false;
    }
    return false;
  }

  private function is_valid_table_row($table_id, $row_id){
      if((is_numeric($table_id)) || (is_numeric($row_id))){
          global $wpdb;
          $tableName = $wpdb->prefix . ninja_tables_db_table_name();
          $fetch_q = $wpdb->prepare(
              "SELECT * FROM {$tableName} WHERE table_id = %d AND id = %d",
              $table_id,
              $row_id
          );
          return $wpdb->query($fetch_q);
          
      }
      return false;
  }

  private function get_table_row($table_id, $row_id){
      if($this->is_valid_table_row($table_id, $row_id)){
          global $wpdb;
          $tableName = $wpdb->prefix . ninja_tables_db_table_name();
          $fetch_q = $wpdb->prepare(
              "SELECT * FROM {$tableName} WHERE table_id = %d AND id = %d",
              $table_id,
              $row_id
          );
          return $wpdb->get_row($fetch_q);
      }
  }

  //webhooks
  public function create_webhook($request){
    $entry = json_decode($request->get_body());
    $name = $entry->name;
    $url = $entry->url;
    $event = $entry->event;
    $table_id = $entry->table_id;
    if((!empty($name)) && (!empty($url)) && (!empty($event)) && (in_array($event, self::$supported_events)) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url)) && $this->is_valid_table($table_id)){
      $args = array(
        'name' => $name,
        'url' => $url,
        'event' => $event,
        'table_id' => $table_id
      );
      $post_name = "Ninja Tables ";
      $post_id = $this->create_webhook_post($post_name, $args);
      if(is_wp_error($post_id)){
        $errors = $post_id->get_error_messages();
        return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
      }
      return rest_ensure_response( array(
          'webhook_id' => $post_id
      ) );
    }
    else{
      return new WP_Error( 'rest_bad_request', 'Data validation failed', array( 'status' => 400 ) );
    }
  }

  public function delete_webhook($request){
    $webhook_id = $request['webhook_id'];
    if(is_numeric($webhook_id)){
      $webhook_post = $this->get_webhook_post($webhook_id);
      if(!empty($webhook_post[0]->ID)){
        $delete_webhook = $this->delete_webhook_post($webhook_id);
        if(is_wp_error($delete_webhook)){
          $errors = $delete_webhook->get_error_messages();
          return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
        }
        else{
          return rest_ensure_response(array('message' => 'Success'));
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

	//To handle new row addition payload
  public function payload_added_item($insert_id, $table_id, $attributes){
    $attributes['id'] = $insert_id;
		$attributes['value'] = json_decode($attributes['value']);
    $args = array(
      'event' => 'row_added',
      'table_id' => $table_id
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'row_added',
      'data' => $attributes
    );
    foreach($webhooks as $webhook){
      $url = $webhook->url;
      zoho_flow_execute_webhook($url, $event_data,array());
    }
  }

	//To handle row update payload
  public function payload_updated_item($insert_id, $table_id, $attributes){
    $attributes['id'] = $insert_id;
    $attributes['value'] = json_decode($attributes['value']);
    $args = array(
      'event' => 'row_updated',
      'table_id' => $table_id
    );
    $webhooks = $this->get_webhook_posts($args);
    $event_data = array(
      'event' => 'row_updated',
      'data' => $attributes
    );
    foreach($webhooks as $webhook){
      $url = $webhook->url;
      zoho_flow_execute_webhook($url, $event_data,array());
    }
  }

  //default API
  public function get_system_info(){
    $system_info = parent::get_system_info();
    if( ! function_exists('get_plugin_data') ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_dir = ABSPATH . 'wp-content/plugins/ninja-tables/ninja-tables.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['ninja-tables'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
