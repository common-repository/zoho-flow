<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Zoho_Flow_Service
{
	protected $service_id;
	protected $service;

    function __construct($service_id, $service)
    {
    	$this->service_id = $service_id;
        $this->service = $service;
    }

    public function get_service_id(){
    	return $this->service_id;
    }

    public function get_service_name(){
    	return (string)$this->service['name'];
    }

    public function get_service_description(){
    	return (string)$this->service['description'];
    }

	public function upload_dir(){
	      $wp_upload_dir = wp_upload_dir();
	      $flow_upload_dir_path = $wp_upload_dir['basedir'] . '/zoho-flow-wordpress-plugin/' . $this->service_id;
	      $mkdir_result = wp_mkdir_p( $flow_upload_dir_path );

	      if ($mkdir_result and is_readable( $flow_upload_dir_path ) and wp_is_writable( $flow_upload_dir_path ) ) {
	            return $flow_upload_dir_path;
	      }
	      return false;
	}

	public function download_file($file_path){
		if(file_exists($file_path)){
	        header('Content-Description: File Transfer');
	        header('Content-Type: application/octet-stream');
	        header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
	        header('Expires: 0');
	        header('Cache-Control: must-revalidate');
	        header('Pragma: public');
	        header('Content-Length: ' . filesize($file_path));
	        readfile($file_path);
	        exit;
		}
	}

	public function register_apis(){
		if(array_key_exists('rest_apis', $this->service)){
			$version = (string)$this->service['version'];
			foreach ($this->service['rest_apis'] as $api_rule) {
				$namespace = 'zoho-flow/' . $this->service_id . '/' . $version;
				$type = (string)$api_rule['type'];
				$http_method = 'GET';
				if($type == 'list'){
					$http_method = 'GET';
				}
				else if($type == 'get'){
					$http_method = 'GET';
				}
				else if($type ==  'create'){
					$http_method = 'POST';
				}
				else if($type ==  'update'){
					$http_method = 'PUT';
				}
				else if($type == 'delete'){
					$http_method = 'DELETE';
				}
				$path = (string)$api_rule['path'];
				$method = (string)$api_rule['method'];
				$capability = (string)$api_rule['capability'];
				$rest_api_object = new Zoho_Flow_API($this, $api_rule);

				$meta = array(array(
					'methods' => $http_method,
					'callback' => array($rest_api_object, 'process'),
					'permission_callback' => array($rest_api_object, 'check_permission')
				));
				if(isset($api_rule['schema_method'])){
					$meta['schema'] = array($rest_api_object, 'get_schema');
				}
				register_rest_route($namespace, $path, $meta);

			}
		}
	}

	public function register_hooks(){
		if(array_key_exists('hooks', $this->service)){
			foreach ($this->service['hooks'] as $hook) {
				$action = (string)$hook['action'];
				$method = (string)$hook['method'];
				if(isset($hook['args_count'])){
					$args_count = (int)$hook['args_count'];
				}
				else{
					$args_count = 1;
				}
		        if (!has_action( $action, array($this, $method) ) ) {
		            add_action( $action, array($this, $method), 10,  $args_count);
		        }
			}
		}
	}

	protected function get_webhook_posts($args_array){
		$meta_query = array(array(
			'key' => 'plugin_service',
			'value' => $this->service_id,
			'compare' => '='
		));
		foreach ($args_array as $key => $value) {
			array_push($meta_query, array(
				'key' => $key,
				'value' => $value,
				'compare' => '='
			));
		}
        $args = array(
            'post_type' => WP_ZOHO_FLOW_WEBHOOK_POST_TYPE,
            'posts_per_page' => -1,
            'meta_query' => $meta_query
        );
        $webhooks = get_posts( $args );
        return $webhooks;
	}

	protected function get_webhook_post($post_id){
	$meta_query = array(
		array(
			'key' => 'plugin_service',
			'value' => $this->service_id,
			'compare' => '='
		)
	);
	$args = array(
			'post_type' => WP_ZOHO_FLOW_WEBHOOK_POST_TYPE,
			'posts_per_page' => -1,
			'meta_query' => $meta_query,
			'p' => $post_id
	);
	$webhooks = get_posts( $args );
	return $webhooks;
}

	protected function create_webhook_post($title, $args_array){

		$meta_input = array_merge($args_array, array('plugin_service' => $this->service_id));
        $post_id = wp_insert_post(array (
           'post_type' => WP_ZOHO_FLOW_WEBHOOK_POST_TYPE,
           'post_title' => $title . ' webhook',
           'post_status' => 'publish',
           'meta_input' => $meta_input
        ));

        return $post_id;
	}

	protected function delete_webhook_post($webhook_id){
		$post = get_post( $webhook_id );
        if ( ! $post or WP_ZOHO_FLOW_WEBHOOK_POST_TYPE != get_post_type( $post ) ) {
            return new WP_Error( 'not_found', esc_html__( 'The webhook is not found.', 'zoho-flow' ), array( 'status' => 404 ));
        }

        $result = wp_delete_post( $webhook_id, true );
        if(!$result){
            return new WP_Error( 'server_error', esc_html__( 'The webhook could not be deleted.', 'zoho-flow' ), array( 'status' => 500 ));
        }

        return $result;
	}


	public function generate_api_key($description){
		$prefix = wp_generate_password(11, false);
		$key = wp_generate_password(58, false);
		$api_key = $prefix . '.' . $key;
		$api_key_hash = hash('sha512', $api_key);

		$all_api_keys = $this->get_all_api_keys();
		if(count($all_api_keys) >= 10){
			return new WP_Error( 'limit_exceeded', esc_html__( 'You can only create a maximum of 10 API keys', 'zoho-flow' ), array( 'status' => 404 ));
		}
        $post_id = wp_insert_post(array (
           'post_type' => WP_ZOHO_FLOW_API_KEY_POST_TYPE,
           'post_title' => $description,
           'post_status' => 'publish',
           'meta_input' => array(
           		'user_id' => get_current_user_id(),
           		'api_key_hash' => $api_key_hash,
           		'prefix' => $prefix,
           		'plugin_service' => $this->service_id,
           		'status' => 'enabled'
           	)
        ));
		return $api_key;
	}

	public function remove_api_key($api_key_id){
		$post = get_post( $api_key_id );
		$user_id = $post->user_id;
		if($user_id != get_current_user_id()){
			return new WP_Error( 'unauthorized', esc_html__( 'You are not authorized to remove the API key.', 'zoho-flow' ), array( 'status' => 500 ));
		}
        if ( ! $post or WP_ZOHO_FLOW_API_KEY_POST_TYPE != get_post_type( $post ) ) {
            return new WP_Error( 'not_found', esc_html__( 'The API key is not found.', 'zoho-flow' ), array( 'status' => 404 ));
        }

        $result = wp_delete_post( $api_key_id, true );
        if(!$result){
            return new WP_Error( 'server_error', esc_html__( 'The API key could not be removed.', 'zoho-flow' ), array( 'status' => 500 ));
        }

        return $result;
	}

	public function get_all_api_keys(){
        $args = array(
            'post_type' => WP_ZOHO_FLOW_API_KEY_POST_TYPE,
            'posts_per_page' => -1,
            'meta_query' => array(
            	'relation' => 'AND',
            	array(
					'key' => 'user_id',
					'value' => get_current_user_id(),
					'compare' => '='
				),
				array(
					'key' => 'plugin_service',
					'value' => $this->service_id,
					'compare' => '='
				)
			)
        );
        $api_keys = get_posts( $args );
        if(isset($api_keys))	{
        	$api_keys_list = array();
        	foreach($api_keys as $api_key_post){
        		$api_key = array();
        		$api_key['id'] = $api_key_post->ID;
        		$api_key['description'] =$api_key_post->post_title;
        		$api_key['prefix'] =$api_key_post->prefix;
        		$api_key['created_on'] =get_the_date(get_option( 'date_format' ), $api_key_post);
        		$api_key['status'] =$api_key_post->status;
        		array_push($api_keys_list, $api_key);
        	}
        	return $api_keys_list;
        }
        return array();
	}

	public function validate_api_key($api_key){
		$api_key_hash = hash('sha512', $api_key);
        $args = array(
            'post_type' => WP_ZOHO_FLOW_API_KEY_POST_TYPE,
            'posts_per_page' => 1,
            'meta_query' => array(
            	'relation' => 'AND',
				array(
					'key' => 'plugin_service',
					'value' => $this->service_id,
					'compare' => '='
				),
				array(
					'key' => 'api_key_hash',
					'value' => $api_key_hash,
					'compare' => '='
				),
				array(
					'key' => 'status',
					'value' => 'enabled',
					'compare' => '='
				)
			)
        );
        $api_keys = get_posts( $args );
        if(isset($api_keys) && !empty($api_keys))	{
        	$api_key = $api_keys[0];
        	$user_id = $api_key->user_id;
        	return $user_id;
        }
        return new WP_Error( 'unauthorized', esc_html__( 'You are not authorized to access the API.', 'zoho-flow' ), array( 'status' => 401 ));
	}
	public function validate_from_service($x_zoho_fromservice){
		$zoho_services_supported = array('ZohoFlow','ZohoWriter');
		if(in_array($x_zoho_fromservice, $zoho_services_supported)){
			return true;
		}
		return new WP_Error( 'rest_forbidden', esc_html__( 'You are not allowed to perform the operation.', 'zoho-flow'), array( 'status' => 403 ) );
	}

	public function get_site_info(){
		$siteinfo = array();
		$siteinfo['name'] = get_bloginfo('name');
		$siteinfo['description'] = get_bloginfo('description');
		$siteinfo['wp_url'] = get_bloginfo('wpurl');
		$siteinfo['site_url'] = get_bloginfo('url');
		$siteinfo['admin_email'] = get_bloginfo('admin_email');
		$siteinfo['version'] = get_bloginfo('version');
		$siteinfo['language'] = get_bloginfo('language');
		$siteinfo['atom_url'] = get_bloginfo('atom_url');
		$siteinfo['rdf_url'] = get_bloginfo('rdf_url');
		$siteinfo['rss_url'] = get_bloginfo('rss_url');
		$siteinfo['rss2_url'] = get_bloginfo('rss2_url');
		$siteinfo['comments_atom_url'] = get_bloginfo('comments_atom_url');
		$siteinfo['comments_rss2_url'] = get_bloginfo('comments_rss2_url');
		$siteinfo['remote_ip'] = $_SERVER['REMOTE_ADDR'];
		$siteinfo['remote_port'] = $_SERVER['REMOTE_PORT'];
		$siteinfo['server_name'] = $_SERVER['SERVER_NAME'];
		$siteinfo['server_port'] = $_SERVER['SERVER_PORT'];
		if(!empty(wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' ))){
			$logo_url = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' )[0];
			if(preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$logo_url));{
				$siteinfo['logo_url']= $logo_url;
			}
		}
		if ( ! function_exists( 'get_plugins' ) ) {
		    require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$siteinfo['all_plugins'] = get_plugins();
		return $siteinfo;
	}

	public function get_system_info(){
		$system_info = array();
		$system_info['php'] = PHP_VERSION;

		global $wp_version;
		if(isset($wp_version)){
			$system_info['wordpress'] = $wp_version;
		}
		if( ! function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( WP_ZOHO_FLOW_PLUGIN );
		$system_info['zohoflow_plugin'] = $plugin_data['Version'];
		$system_info['site_info'] = $this->get_site_info();
		$user_info = wp_get_current_user();
		unset($user_info->data->user_pass);
		$system_info['connection_owner_info'] = $user_info;
		return $system_info;
	}
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Zoho_Flow_API_Key_List_Table extends WP_List_Table {

	private $service_id;

	function set_service_id($service_id){
		$this->service_id = $service_id;
	}

	function get_columns(){
	  $columns = array(
	    'description'    => 'Label',
	  	'api_key' => 'API key',
	  	'created_on' => 'Created on',
	    // 'status' => 'Status',
	    'actions' => ''
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
	  $service = Zoho_Flow_Services::get_instance()->get_service($this->service_id)['instance'];
	  $this->items = $service->get_all_api_keys();

	}

	function column_default( $item, $column_name ) {
	  switch( $column_name ) {
	    case 'api_key':
	    	$prefix = $item['prefix'];
	    	$api_key = $prefix . '.' . str_repeat('x', 58);
	    	return $api_key;
	    case 'created_on':


	    case 'status':
	    case 'description':
	      return $item[ $column_name ];
	    default:
	      return '';
	  }
	}

	function column_actions($item){
		return sprintf('<span id="api-key-%1$s" class="delete-api-key dashicons dashicons-trash"></span>', $item['id']);
	}

	function get_table_classes(){
		$classes = parent::get_table_classes();
		array_push($classes, 'zoho-flow-api-keys-table');
		return $classes;
	}

}
