<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Zoho_Flow_API{

	private $plugin;
	private $api_rule;

	public function __construct($plugin, $api_rule) {
		$this->plugin = $plugin;
		$this->api_rule = $api_rule;
    }

    public function check_permission($request){
        $api_key = $request->get_header('x-api-key');
        $api_key_validation_result = $this->plugin->validate_api_key($api_key);
        if(is_wp_error($api_key_validation_result)){
            return $api_key_validation_result;
        }
        $zoho_service = $request->get_header('X-Zoho-Fromservice');
        $zoho_service_validation_result = $this->plugin->validate_from_service($zoho_service);
        if(is_wp_error($zoho_service_validation_result)){
            return $zoho_service_validation_result;
        }
        wp_set_current_user($api_key_validation_result);
    		$capability = (string)$this->api_rule['capability'];
        if ( ! current_user_can( $capability ) ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You are not allowed to perform the operation.', 'zoho-flow'), array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }

    public function process($request){
    	$method = (string)$this->api_rule['method'];
    	return $this->plugin->$method($request);
    }

    public function get_schema(){
    	$schema_method = (string)$this->api_rule['schema_method'];
    	if(empty($schema_method)){
    		return;
    	}
    	return $this->plugin->$schema_method();
    }

    private function authorization_status_code() {

        $status = 401;

        if ( is_user_logged_in() ) {
            $status = 403;
        }

        return $status;
    }

}
