<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

  class Zoho_Flow_System_Info_Menue extends WP_List_Table {

		function get_copy_element($row_name,$row_value){
			return '<span id="'.$row_name.'-span">'. $row_value .'</span> <a id="'.$row_name.'-copy-value" style="padding: 0px 0px;" class="dashicons dashicons-admin-page system-info-copy-icon"></a><span id="'.$row_name.'-span-copy-sucess" style="display: none; padding-right: 5px; padding-left: 5px; color: green;">Copied!</span>';
		}

		function get_rows(){
			$siteinfo = array();
			if(!empty(get_bloginfo('name'))){
				array_push($siteinfo,array(
					'name' => 'Site Name',
					'value' => get_bloginfo('name')
				));
			}
			if(!empty(get_bloginfo('description'))){
				array_push($siteinfo,array(
					'name' => 'Description',
					'value' => get_bloginfo('description')
				));
			}
			if(!empty(get_bloginfo('wpurl'))){
				array_push($siteinfo,array(
					'name' => 'Home URL',
					'value' => $this->get_copy_element('wpurl', get_bloginfo('wpurl'))
				));
			}
			if(!empty(get_bloginfo('url'))){
				array_push($siteinfo,array(
					'name' => 'Site URL',
					'value' => $this->get_copy_element('site_url', get_bloginfo('url'))
				));
				array_push($siteinfo,array(
					'name' => 'Base URL',
					'value' => $this->get_copy_element('base_url', get_bloginfo('url'))
				));
			}
			if(!empty(get_bloginfo('admin_email'))){
				array_push($siteinfo,array(
					'name' => 'Admin Email Address',
					'value' => get_bloginfo('admin_email')
				));
			}
			if(!empty(get_bloginfo('language'))){
				array_push($siteinfo,array(
					'name' => 'Language',
					'value' => get_bloginfo('language')
				));
			}
			if(!empty(wp_timezone_string())){
				array_push($siteinfo,array(
					'name' => 'Timezone',
					'value' => wp_timezone_string()
				));
			}
			if(!empty(PHP_VERSION)){
				array_push($siteinfo,array(
					'name' => 'PHP Version',
					'value' => PHP_VERSION
				));
			}
			if(!empty(get_bloginfo('version'))){
				array_push($siteinfo,array(
					'name' => 'WP Version',
					'value' => get_bloginfo('version')
				));
			}
			$plugin_data = get_plugin_data( WP_ZOHO_FLOW_PLUGIN );
			if(!empty($plugin_data['Version'])){
				array_push($siteinfo,array(
					'name' => 'Zoho Flow Plugin Version',
					'value' => $plugin_data['Version']
				));
			}

			if(!empty(ini_get('default_charset'))){
				array_push($siteinfo,array(
					'name' => 'Default Charset',
					'value' => ini_get('default_charset')
				));
			}
			if(is_multisite()){
				array_push($siteinfo,array(
					'name' => 'WP Multisite',
					'value' => 'Yes'
				));
			}
			else{
				array_push($siteinfo,array(
					'name' => 'WP Multisite',
					'value' => 'No'
				));
			}
			if(!empty(get_bloginfo('atom_url'))){
				array_push($siteinfo,array(
					'name' => 'Atom URL',
					'value' => $this->get_copy_element('atom_url', get_bloginfo('atom_url'))
				));
			}
			if(!empty(get_bloginfo('rdf_url'))){
				array_push($siteinfo,array(
					'name' => 'RDF URL',
					'value' => $this->get_copy_element('rdf_url', get_bloginfo('rdf_url'))
				));
			}
			if(!empty(get_bloginfo('rss_url'))){
				array_push($siteinfo,array(
					'name' => 'RSS URL',
					'value' => $this->get_copy_element('rss_url', get_bloginfo('rss_url'))
				));
			}
			if(!empty(get_bloginfo('rss2_url'))){
				array_push($siteinfo,array(
					'name' => 'RSS 2 URL',
					'value' => $this->get_copy_element('rss2_url', get_bloginfo('rss2_url'))
				));
			}
			if(!empty(get_bloginfo('comments_atom_url'))){
				array_push($siteinfo,array(
					'name' => 'Comments Atom URL',
					'value' => $this->get_copy_element('comments_atom_url', get_bloginfo('comments_atom_url'))
				));
			}
			if(!empty(get_bloginfo('comments_rss2_url'))){
				array_push($siteinfo,array(
					'name' => 'Comments Rss 2 URL',
					'value' => $this->get_copy_element('comments_rss2_url', get_bloginfo('comments_rss2_url'))
				));
			}
			if(!empty($_SERVER['REMOTE_ADDR'])){
				array_push($siteinfo,array(
					'name' => 'Remote IP Address',
					'value' => $_SERVER['REMOTE_ADDR']
				));
			}
			if(!empty($_SERVER['REMOTE_PORT'])){
				array_push($siteinfo,array(
					'name' => 'Remote Port',
					'value' => $_SERVER['REMOTE_PORT']
				));
			}
			if(!empty($_SERVER['SERVER_NAME'])){
				array_push($siteinfo,array(
					'name' => 'Server Address',
					'value' => $_SERVER['SERVER_NAME']
				));
			}
			if(!empty($_SERVER['SERVER_PORT'])){
				array_push($siteinfo,array(
					'name' => 'Server Port',
					'value' => $_SERVER['SERVER_PORT']
				));
			}

			if(!empty(ini_get('memory_limit'))){
				array_push($siteinfo,array(
					'name' => 'PHP Memory Limit',
					'value' => ini_get('memory_limit')
				));
			}
			if(!empty(ini_get('max_input_vars'))){
				array_push($siteinfo,array(
					'name' => 'PHP Max Input Vars',
					'value' => ini_get('max_input_vars')
				));
			}
			if(!empty(ini_get('post_max_size'))){
				array_push($siteinfo,array(
					'name' => 'PHP Max Post Size',
					'value' => ini_get('post_max_size')
				));
			}
			if(!empty(ini_get('default_socket_timeout'))){
				array_push($siteinfo,array(
					'name' => 'Default Socket Timeout',
					'value' => ini_get('default_socket_timeout')
				));
			}
			if(ini_get('file_uploads')){
				array_push($siteinfo,array(
					'name' => 'File Upload',
					'value' => 'Enabled'
				));
			}
			else{
				array_push($siteinfo,array(
					'name' => 'File Upload Enabled?',
					'value' => 'Disabled'
				));
			}
			if(!empty(ini_get('upload_max_filesize'))){
				array_push($siteinfo,array(
					'name' => 'Max File Upload Size',
					'value' => ini_get('upload_max_filesize')
				));
			}
			if(!empty(ini_get('max_file_uploads'))){
				array_push($siteinfo,array(
					'name' => 'Max File Upload',
					'value' => ini_get('max_file_uploads')
				));
			}
			if(!empty(WP_MEMORY_LIMIT)){
				array_push($siteinfo,array(
					'name' => 'WP Memory Limit',
					'value' => WP_MEMORY_LIMIT
				));
			}
			if(!empty(WP_MAX_MEMORY_LIMIT)){
				array_push($siteinfo,array(
					'name' => 'WP Max Memory Limit',
					'value' => WP_MAX_MEMORY_LIMIT
				));
			}

			return $siteinfo;
		}

		function get_columns(){
		  $columns = array(
		    'name'    => 'Label',
		  	'value' => 'Value'
		  );
		  return $columns;
		}

		function prepare_items() {
		  $columns = $this->get_columns();
		  $hidden = array(
		  	'id' => 'ID'
		  );
		  $sortable = array();
		  $this->_column_headers = array($columns, $hidden, $sortable);
			$items = $this->get_rows();
		  $this->items = $items;

		}

		function column_default( $item, $column_name ) {
			switch( $column_name ) {
				case 'name':
				case 'value':
					return $item[ $column_name ];
				default:
					return '';
			}
		}

		function get_table_classes(){
			$classes = parent::get_table_classes();
			array_push($classes, 'zoho-flow-site-info-table');
			return $classes;
		}
  }
