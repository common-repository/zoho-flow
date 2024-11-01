<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Zoho_Flow_Services {

	private static $instance;
	private $services;
	private function __construct() {
		$this->services = array();
	}

	public static function get_instance(){
		if(empty( self::$instance )){
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function add_service($service){
		$name = (string)$service['name'];
		$gallery_app_link = (string)$service['gallery_app_link'];
		$description = (string)$service['description'];
		$app_documentation_link = (string)$service['app_documentation_link'];
		$embed_link = (string)$service['embed_link'];
		$icon_file = (string)$service['icon_file'];
		$class_test = (string)$service['class_test'];
		//$id = $this->snake_case($name);
		$id = (string)$service['api_path'];
		$plugin_file = __DIR__ . '/../integrations/' . $id . '/' . $id . '.php';
		$is_available = file_exists($plugin_file) && ($class_test!=''?class_exists($class_test):true);
		if( !$is_available && isset( $service['function_test'] ) && ( function_exists( $service['function_test'] ) ) ){
			$is_available = true;
		}

		if(!array_key_exists($name, $this->services)){
			$this->services[$id] = array(
				'id' => $id,
				'name'=>$name,
				'gallery_app_link'=>$gallery_app_link,
				'description'=>$description,
				'app_documentation_link'=>$app_documentation_link,
				'embed_link'=>$embed_link,
				'icon_file'=>$icon_file,
				'is_available'=>$is_available
			);
			if($is_available){
				require_once $plugin_file;
				//$class_name = 'Zoho_Flow_' . $this->get_class_name($name);
				$class_name = (string)$service['class_name'];
				$instance = new $class_name($id, $service);
				$this->services[$id]['instance'] = $instance;
				add_action( 'rest_api_init', [$instance, 'register_apis']);
				$instance->register_hooks();

				$hook = 'zoho_flow_register_service_' . str_replace('-', '_', $id);
				do_action( $hook, $this->services[$id]);
			}
		}
	}

	public function get_services(){
		return $this->services;
	}

	public function get_service($id){
		if(array_key_exists($id, $this->services)){
			return $this->services[$id];
		}
		else{
			return NULL;
		}
	}

	private function get_class_name($service_name){
        $str = preg_replace('/[^a-z0-9]+/i', '_', $service_name);
        $str = trim($str);
        return $str;
	}

	private function snake_case($str)
	{
        $str = preg_replace('/[^a-z0-9]+/i', ' ', $str);
        $str = trim($str);
        $str = str_replace(" ", "-", $str);
        $str = strtolower($str);

        return $str;
	}

	public function is_current_user_has_api_keys(){
		$args = array(
						'post_type' => WP_ZOHO_FLOW_API_KEY_POST_TYPE,
						'posts_per_page' => -1,
						'author' => get_current_user_id(),
						'fields' => 'ids'
					);
		$api_keys = get_posts( $args );
		if(isset($api_keys) && (sizeof($api_keys)>0))	{
			return true;
		}
		return false;
	}
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Zoho_Flow_Services_List_Table extends WP_List_Table {

	function get_columns(){
	  $columns = array(
	  	'icon_file' => '',
	    'name' => 'Plugin',
	    'description'    => 'Description'
	  );
	  return $columns;
	}

	function prepare_items() {
	  $columns = $this->get_columns();
	  $hidden = array();
	  $sortable = array();
	  $this->_column_headers = array($columns, $hidden, $sortable);
	  $this->items = Zoho_Flow_Services::get_instance()->get_services();
	}

	function column_default( $item, $column_name ) {
	  switch( $column_name ) {
	    case 'icon_file':
	    case 'name':
	    case 'description':
	      return $item[ $column_name ];
	    default:
	      return '';
	  }
	}

	function column_name($item){

		if(!get_option('permalink_structure') || !$item['is_available']){
			return esc_html( $item['name'] );
		}
		else{

			$edit_link = add_query_arg(
				array(
					'service' => $item['id']
				),
				menu_page_url( 'zoho_flow', false )
			);

			$output = sprintf(
				'<a class="row-title" href="%1$s" aria-label="%2$s">%3$s</a>',
				esc_url( $edit_link),
				// translators: %s refers to the plugin name
				esc_attr( sprintf( __( 'Edit %s', 'zoho-flow' ),
					$item['name'] ) ),
				esc_html( $item['name'] )
			);

			$output = sprintf( '<strong>%s</strong>', $output );

			return $output;
		}

	}

	function column_icon_file($item){
		$file = $item['icon_file'];
		if(!file_exists(__DIR__ . '/../assets/images/logos/' . $file)){
			return '<img>';
		}
		return "<img src='" . esc_attr(esc_url(plugins_url('../assets/images/logos/' . $file, __FILE__))) . "'>";
	}

	function get_table_classes(){
		$classes = parent::get_table_classes();
		array_push($classes, 'zoho-flow-plugin-services-table');
		return $classes;
	}

}
