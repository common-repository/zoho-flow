<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//support from
//zohoflow 2.0.0
//tablepress 2.1.8
class Zoho_Flow_TablePress extends Zoho_Flow_Service{

  //webhook events supported
  public static $supported_events = array("row_added_or_updated");

	//List all tables
  public function list_tables($request){
    $params = array(
      'option_name'   => 'tablepress_tables',
      'default_value' => array(
        'last_id'    => 0,
        'table_post' => array(),
      ),
    );
    $table_list_option_class_obj = TablePress::load_class( 'TablePress_WP_Option', 'class-wp_option.php', 'classes', $params );
    $table_post = $table_list_option_class_obj->get( 'table_post' );
    $tables_array = array();
    // Load all table IDs and names for a comparison with the file name.
    $table_ids = TablePress::$model_table->load_all( false );
    foreach ( $table_ids as $table_id ) {
      // Load table, without table data, options, and visibility settings.
      $table = TablePress::$model_table->load( $table_id, false, false );
      if ( ! is_wp_error( $table ) ) {
        $table['post_id'] = $table_post[$table['id']];
				$table_details = TablePress::$model_table->load( $table_id, false, true );
		    $column_details = array();
				foreach ($table_details['visibility']['columns'] as $key => $value) {
		      array_push($column_details, array(
		        'index' => $key,
		        'column_number' => $key+1,
		        'column_letter' => TablePress::number_to_letter( $key+1 ),
		        'visibility' => $value,
		      ));
		    }
		    $table['column_details'] = $column_details;
				array_push($tables_array,$table);
      }
			else{
				return new WP_Error( 'rest_bad_request', $table->get_error_messages(), array( 'status' => 400 ) );
			}
    }
    return rest_ensure_response($tables_array);

  }

	//To get table details including column meta
  public function get_table_details($request){
    $table_id = $request['table_id'];
    $table_details = TablePress::$model_table->load( $table_id, false, true );
    $column_details = array();
		$TablePress_Table_Model = new TablePress_Table_Model();
		if($TablePress_Table_Model->table_exists($table_id)){
	    foreach ($table_details['visibility']['columns'] as $key => $value) {
	      array_push($column_details, array(
	        'index' => $key,
	        'column_number' => $key+1,
	        'column_letter' => TablePress::number_to_letter( $key+1 ),
	        'visibility' => $value,
	      ));
	    }
	    $table_details['column_details'] = $column_details;
	    return rest_ensure_response($table_details);
		}
		else{
			return new WP_Error( 'rest_bad_request', 'Invalid table ID', array( 'status' => 400 ) );
		}
  }

	//To add / import table and it's data
  public function import_table($request){
    $imported_table = $request['imported_table'];
    $import_type = $request['import_type'];
    $existing_table_id = $request['existing_table_id'];

		//Original import code used in TeblePress
    // Full JSON format table can contain a table ID, try to keep that, by later changing the imported table ID to this.
    $table_id_in_import = isset( $imported_table['id'] ) ? $imported_table['id'] : '';

    // To be able to replace or append to a table, the user must be able to edit the table, or it must be a Cron request (e.g. via the Automatic Periodic Table Import module).
    if ( in_array( $import_type, array( 'replace', 'append' ), true ) && ! ( current_user_can( 'tablepress_edit_table', $existing_table_id ) || wp_doing_cron() ) ) {
      return new WP_Error( 'table_import_replace_append_capability_check_failed', 'You are not allowed to perform the operation.', array( 'status' => 403 ) );
    }

    switch ( $import_type ) {
      case 'add':
        $existing_table = TablePress::$model_table->get_table_template();
        // Import visibility information if it exists, usually only for the JSON format.
        if ( isset( $imported_table['visibility'] ) ) {
          $existing_table['visibility'] = $imported_table['visibility'];
        }
        break;
      case 'replace':
        // Load table, without table data, but with options and visibility settings.
        $existing_table = TablePress::$model_table->load( $existing_table_id, false, true );
        if ( is_wp_error( $existing_table ) ) {
          $error = new WP_Error( 'table_import_replace_table_load', 'Invalid table ID', array( 'status' => 400 ) );
          $error->merge_from( $existing_table );
          return $error;
        }
        // Don't change name and description when a table is replaced.
        $imported_table['name'] = $existing_table['name'];
        $imported_table['description'] = $existing_table['description'];
        // Replace visibility information if it exists.
        if ( isset( $imported_table['visibility'] ) ) {
          $existing_table['visibility'] = $imported_table['visibility'];
        }
        break;
      case 'append':
        // Load table, with table data, options, and visibility settings.
        $existing_table = TablePress::$model_table->load( $existing_table_id, true, true );
        if ( is_wp_error( $existing_table ) ) {
          $error = new WP_Error( 'table_import_append_table_load', 'Invalid table ID', array( 'status' => 400 ) );
          $error->merge_from( $existing_table );
          return $error;
        }
        if ( isset( $existing_table['is_corrupted'] ) && $existing_table['is_corrupted'] ) {
          return new WP_Error( 'table_import_append_table_load_corrupted', 'Invalid data', $existing_table_id );
        }
        // Don't change name and description when a table is appended to.
        $imported_table['name'] = $existing_table['name'];
        $imported_table['description'] = $existing_table['description'];
        // Actual appending:.
        $imported_table['data'] = array_merge( $existing_table['data'], $imported_table['data'] );
        // Append visibility information for rows.
        if ( isset( $imported_table['visibility']['rows'] ) ) {
          $existing_table['visibility']['rows'] = array_merge( $existing_table['visibility']['rows'], $imported_table['visibility']['rows'] );
        }
        // When appending, do not overwrite options, e.g. coming from a JSON file.
        unset( $imported_table['options'] );
        break;
      default:
        return new WP_Error( 'table_import_import_type_invalid', '', array( 'status' => 400 ) );
    }

    // Merge new or existing table with information from the imported table.
    $imported_table['id'] = $existing_table['id']; // Will be false for new table or the existing table ID.
    // Cut visibility array (if the imported table is smaller), and pad correctly if imported table is bigger than existing table (or new template).
    $num_rows = count( $imported_table['data'] );
    $num_columns = count( $imported_table['data'][0] );
    $imported_table['visibility'] = array(
      'rows'    => array_pad( array_slice( $existing_table['visibility']['rows'], 0, $num_rows ), $num_rows, 1 ),
      'columns' => array_pad( array_slice( $existing_table['visibility']['columns'], 0, $num_columns ), $num_columns, 1 ),
    );

    // Check if the new table data is valid and consistent.
    $table = TablePress::$model_table->prepare_table( $existing_table, $imported_table, false );
    if ( is_wp_error( $table ) ) {
      $error = new WP_Error( 'table_import_table_prepare', 'Invalid data', array( 'status' => 400 ) );
      $error->merge_from( $table );
      return $error;
    }

    // DataTables Custom Commands can only be edit by trusted users.
    if ( ! current_user_can( 'unfiltered_html' ) ) {
      $table['options']['datatables_custom_commands'] = $existing_table['options']['datatables_custom_commands'];
    }

    // Replace existing table or add new table.
    if ( in_array( $import_type, array( 'replace', 'append' ), true ) ) {
      // Replace existing table with imported/appended table.
      $table_id = TablePress::$model_table->save( $table );
    } else {
      // Add the imported table (and get its first ID).
      $table_id = TablePress::$model_table->add( $table );
    }

    if ( is_wp_error( $table_id ) ) {
      $error = new WP_Error( 'table_import_table_save_or_add', 'Invalid data', array( 'status' => 400 ) );
      $error->merge_from( $table_id );
      return $error;
    }

    // Try to use ID from imported file (e.g. in full JSON format table).
    if ( '' !== $table_id_in_import && $table_id !== $table_id_in_import && current_user_can( 'tablepress_edit_table_id', $table_id ) ) {
      $id_changed = TablePress::$model_table->change_table_id( $table_id, $table_id_in_import );
      if ( ! is_wp_error( $id_changed ) ) {
        $table_id = $table_id_in_import;
      }
    }

    $table['id'] = $table_id;

    return rest_ensure_response($table);
  }

  //webhooks
	public function create_webhook($request){
	  $entry = json_decode($request->get_body());
	  $name = $entry->name;
	  $url = $entry->url;
	  $event = $entry->event;
	  $supported_events = self::$supported_events;
	  $table_id = $entry->table_id;
	  $TablePress_Table_Model = new TablePress_Table_Model();
	  if((!empty($name)) && (!empty($url)) && (!empty($event)) && (in_array($event, self::$supported_events)) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url)) && $TablePress_Table_Model->table_exists($table_id)){
	    $args = array(
	      'name' => $name,
	      'url' => $url,
	      'event' => $event,
	      'table_id' => $table_id
	    );
	    $post_name = "TablePress ";
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


	//To handle table data changes payload
  public function payload_table_save($table_id){
    $args = array(
      'event' => 'row_added_or_updated',
      'table_id' => $table_id
    );
    $webhooks = $this->get_webhook_posts($args);
    if(!empty($webhooks)){
      $table_details = TablePress::$model_table->load( $table_id, true, true );
      $params = array(
        'option_name'   => 'tablepress_tables',
        'default_value' => array(
          'last_id'    => 0,
          'table_post' => array(),
        ),
      );
      $table_list_option_class_obj = TablePress::load_class( 'TablePress_WP_Option', 'class-wp_option.php', 'classes', $params );
      $table_post = $table_list_option_class_obj->get( 'table_post' );
      $table_post_id = $table_post[$table_id];
  		$rivision_history = wp_get_post_revisions($table_post_id);
  		foreach ($rivision_history as $post_revision_id => $post_obj) {
  			if($post_obj->post_date < $table_details['last_modified']){
  				$current_payload = $table_details['data'];
  				$last_edited_payload = json_decode($post_obj->post_content);
          $payload_to_return = $this->check_diff_multi($current_payload, $last_edited_payload);
          if(!empty($payload_to_return)){
						/*
						Since huge payload can expected to send in webhook. Paylaods splited into 20 rows and 2 secs delay introduced
						inbetween each webhook calls to dilute the traffic in platform end.
						Since action calls are synchronous calls, increasing the delay will make the table save api call timeout.
						We may need to make this webhook method calls as asynchronous to handle huge payloads in future.
						*/
						$split_payload = array_chunk($payload_to_return,20,true);
						foreach ($split_payload as $data_index => $data_array) {
							/*
							Maximum row tracking set to 200 (20*10)
							*/
							if($data_index >= 10){
								return;
							}
							$data_array = array_values(array_filter($data_array));
							$event_data = array(
	              'event' => 'row_added_or_updated',
	              'data' => $data_array
	            );
	            foreach($webhooks as $webhook){
	              $url = $webhook->url;
	              zoho_flow_execute_webhook($url, $event_data,array());
	            }
							sleep(2);
						}

          }
          return;
  			}
  		}
    }
  }

	public function check_diff_multi($current_array, $old_array){
		$result = array();
    foreach($current_array as $key => $val) {
         if(isset($old_array[$key])){
					 $diff_array = array_diff($current_array[$key], $old_array[$key]);
					 if(!empty($diff_array)){
						 $row_data = array();
						 $row_data['row_id'] = $key+1;
						 foreach ($current_array[$key] as $key1 => $value1) {
						 	$row_data[TablePress::number_to_letter( $key1+1 )] = $value1;
						 }
						 array_push($result, $row_data);
					 }
       } else {
				 $row_data = array();
				 $row_data['row_id'] = $key+1;
				 foreach ($current_array[$key] as $key1 => $value1) {
				 	$row_data[TablePress::number_to_letter( $key1+1 )] = $value1;
				 }
				 array_push($result, $row_data);
       }
    }
    return $result;
	}


  //default API
  public function get_system_info(){
    $system_info = parent::get_system_info();
    if( ! function_exists('get_plugin_data') ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_dir = ABSPATH . 'wp-content/plugins/tablepress/tablepress.php';
    if(file_exists($plugin_dir)){
      $plugin_data = get_plugin_data( $plugin_dir );
      $system_info['tablepress'] = $plugin_data['Version'];
    }
    return rest_ensure_response( $system_info );
  }
}
