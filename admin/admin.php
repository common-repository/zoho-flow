<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
add_action( 'admin_init', 'zoho_flow_admin_init', 10, 0 );

function zoho_flow_admin_init() {
	do_action( 'wp_zoho_flow_admin_init' );
}

add_action( 'admin_menu', 'zoho_flow_admin_menu', 9, 0 );
function zoho_flow_admin_menu() {
	global $_wp_last_object_menu;

	$_wp_last_object_menu++;

	do_action( 'zoho_flow_admin_menu' );

	add_menu_page(
		__( 'Zoho Flow', 'zoho-flow' )
		,__( 'Zoho Flow', 'zoho-flow' )
		,'zoho_flow_admin_page'
		, 'zoho_flow'
		,'zoho_flow_show_admin_page'
		, 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDI0LjEuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCAxMDI0IDEwMjQiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDEwMjQgMTAyNDsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsOiMwMDA7fQoJLnN0MXtmaWxsOiMwMDk4NDk7fQoJLnN0MntmaWxsOiNGRkZGRkY7fQo8L3N0eWxlPgo8cmVjdCBjbGFzcz0ic3QwIiB3aWR0aD0iMTAyNCIgaGVpZ2h0PSIxMDI0IiB0cmFuc2Zvcm09InNjYWxlKC4wMDA2KSIvPgo8Zz4KCTxnPgoJCTxwYXRoIGNsYXNzPSJzdDIiIGQ9Ik0yMTEuMDMsODU1LjQ4Yy0xMC4zNywwLTIwLjQ1LTUuMzgtMjYuMDEtMTVMNC4wMiw1MjdjLTUuMzYtOS4yOC01LjM2LTIwLjcyLDAtMzBsMjQxLTQxNy4zOAoJCQljNS4zNi05LjI4LDE1LjI2LTE1LDI1Ljk4LTE1aDQ4MmMxNC4xNywwLDI2LjUzLDkuODYsMjkuNDcsMjMuNzJjMi45MywxMy44NC00LjA4LDI3Ljc5LTE2Ljk5LDMzLjU2CgkJCWMtMS43OSwwLjg1LTQyLjkzLDIwLjgxLTg5LjIyLDcwLjkyYy0yNy42LDI5Ljg3LTUxLjY0LDY0LjUxLTcxLjQ3LDEwMi45NWMtMTkuODEsMzguNDEtMzUuNDUsODAuNzYtNDYuNjYsMTI2LjI5bDgyLjc2LTAuMDYKCQkJYzAuMDEsMCwwLjAxLDAsMC4wMiwwYzE2LjU2LDAsMjkuOTksMTMuNDIsMzAsMjkuOThjMC4wMSwxNi41Ny0xMy40MSwzMC4wMS0yOS45OCwzMC4wMmwtMTIwLDAuMDhjLTAuMDEsMC0wLjAxLDAtMC4wMiwwCgkJCWMtOC45MywwLTE3LjQtMy45OC0yMy4xLTEwLjg2Yy01LjctNi44OC04LjA0LTE1Ljk1LTYuMzgtMjQuNzNjMTIuNDctNjUuNzgsMzMuMDYtMTI2LjQ5LDYxLjE5LTE4MC40NgoJCQljMjIuNjgtNDMuNTEsNTAuMjktODIuNzQsODIuMDUtMTE2LjYxYzguNTYtOS4xMiwxNi45OC0xNy4zOCwyNS4wOS0yNC44MkgyODguMzJMNjQuNjQsNTEybDE3Mi4zNCwyOTguNDcKCQkJYzguMjgsMTQuMzUsMy4zNywzMi43LTEwLjk4LDQwLjk4QzIyMS4yOCw4NTQuMTgsMjE2LjEyLDg1NS40OCwyMTEuMDMsODU1LjQ4eiIvPgoJPC9nPgoJPGc+CgkJPHBhdGggY2xhc3M9InN0MiIgZD0iTTc1Myw5NTkuMzhIMjcxYy0xNC4xNywwLTI2LjUzLTkuODYtMjkuNDctMjMuNzJjLTIuOTMtMTMuODQsNC4wOC0yNy43OSwxNi45OS0zMy41NgoJCQljMS43MS0wLjgxLDQyLjg4LTIwLjc2LDg5LjIyLTcwLjkxYzI3LjU5LTI5Ljg3LDUxLjYzLTY0LjUsNzEuNDYtMTAyLjkzYzE5LjgxLTM4LjQsMzUuNDUtODAuNzUsNDYuNjctMTI2LjI3bC04Mi43NiwwCgkJCWMtMTYuNTcsMC0zMC0xMy40My0zMC0zMGMwLTE2LjU3LDEzLjQzLTMwLDMwLTMwbDExOS45OCwwYzguOTQsMCwxNy40MSwzLjk4LDIzLjExLDEwLjg3YzUuNyw2Ljg4LDguMDMsMTUuOTUsNi4zNywyNC43MwoJCQljLTEyLjQ3LDY1Ljc2LTMzLjA2LDEyNi40Ni02MS4yLDE4MC40MWMtMjIuNjgsNDMuNS01MC4yOSw4Mi43Mi04Mi4wNSwxMTYuNThjLTguNTUsOS4xMi0xNi45NiwxNy4zNy0yNS4wNywyNC44aDM3MS40NAoJCQlMOTU5LjM2LDUxMkw3ODcuMTcsMjEzLjc4Yy04LjI4LTE0LjM1LTMuMzctMzIuNywxMC45OC00MC45OGMxNC4zNS04LjI5LDMyLjctMy4zNyw0MC45OCwxMC45OEwxMDE5Ljk4LDQ5NwoJCQljNS4zNiw5LjI4LDUuMzYsMjAuNzIsMCwzMGwtMjQxLDQxNy4zOEM3NzMuNjIsOTUzLjY3LDc2My43Miw5NTkuMzgsNzUzLDk1OS4zOHoiLz4KCTwvZz4KPC9nPgo8L3N2Zz4K'
		,$_wp_last_object_menu );

		add_submenu_page(
			'zoho_flow'
			,__( 'Zoho Flow', 'zoho-flow' )
			,__( 'Integrations', 'zoho-flow' )
			,'zoho_flow_admin_page'
			,'zoho_flow'
			,'zoho_flow_show_admin_page');

		add_submenu_page(
		  'zoho_flow'
		  ,__( 'Zoho Flow', 'zoho-flow' )
		  ,__( 'System Info', 'zoho-flow' )
		  ,'zoho_flow_admin_page'
		  ,'zoho_flow_system_info'
		  ,'zoho_flow_show_system_info_page');

}

add_filter( 'admin_footer_text', 'admin_footer_text', 1 );
function admin_footer_text( $footer_text ) {
	if ( ! current_user_can( 'zoho_flow_admin_page' ) ) {
		return $footer_text;
	}
	$current_screen = get_current_screen();

	if(isset( $current_screen->id ) && (($current_screen->id == 'toplevel_page_zoho_flow') || ($current_screen->id == 'zoho-flow_page_zoho_flow_system_info'))){
			$footer_text = sprintf(
				/* translators: 1: Zoho Flow 2:: five stars */
				esc_html__( 'If you love using %1$s, please leave us a %2$s rating.', 'zoho-flow' ),
				sprintf( '<strong>%s</strong>', esc_html__( 'Zoho Flow', 'zoho-flow' ) ),
				'<a href="https://wordpress.org/support/plugin/zoho-flow/reviews?rate=5#new-post" target="_blank" class="zoho-flow-rating-link" data-rated="' . esc_attr__( 'Thanks &#128522;', 'zoho-flow' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
	}
	return $footer_text;
}


add_action( 'wp_ajax_zoho_flow_rated', 'zoho_flow_rated' );
function zoho_flow_rated(){
	if ( ! current_user_can( 'zoho_flow_admin_page' ) ) {
		wp_die( -1 );
	}
	update_option( 'zoho_flow_admin_footer_text_rated', 1 );
	update_option( 'last_review_notice_date', date('Y-m-d', strtotime('+2 Months') ) );
	wp_die();
}

function zoho_flow_enqueue_scripts(){

	wp_register_script('zoho-flow-admin', plugins_url('../assets/js/zoho-flow-admin.js', __FILE__), array('jquery'),null, false);
	wp_register_script('zoho-flow-review-notice', plugins_url('../assets/js/zoho-flow-review-notice.js', __FILE__), array('jquery'),null, false);
	wp_register_script('zoho-flow-suggestion-notice', plugins_url('../assets/js/zoho-flow-suggestion-notice.js', __FILE__), array('jquery'),null, false);
	wp_register_script('zoho-flow-system-info', plugins_url('../assets/js/zoho-flow-system-info.js', __FILE__), array('jquery'),null, false);
	$i18n_array = array(
		'enter_description' => __('Enter description'),
		'generating' => __('Generating...'),
		'generate' => __('Generate'),
		'remove_api_key_confirmation' => __('Remove API key?'),
		'unable_to_copy_api_key' => __('Unable to copy the API key')
	);
	wp_localize_script( 'zoho-flow-admin', 'i18n', $i18n_array );
	wp_enqueue_script('zoho-flow-admin');

	wp_register_style('zoho-flow-admin', plugins_url('../assets/css/zoho-flow-admin.css', __FILE__));
	wp_enqueue_style('zoho-flow-admin');

	add_thickbox();
}

function zoho_flow_generate_api_key(){
    if ( ! current_user_can( 'zoho_flow_admin_page' ) ) {
        return new WP_Error( 'ajax_forbidden', esc_html__( 'You are not allowed to perform the operation.', 'zoho-flow'), array( 'status' => 403 ) );
    }
	if(isset($_POST['service_id'])) {
	  if(!wp_verify_nonce($_POST['api_key_generation_nonce'],'generate_api_key')){
	      wp_send_json_error(__('Unable to generate API Key. Please try after refreshing the page.', 'zoho-flow'), 403);
	   }else{
	      $service_id = sanitize_key($_POST['service_id']);
	      $description = sanitize_text_field($_POST['description']);
	      $service = Zoho_Flow_Services::get_instance()->get_service($service_id)['instance'];
	      if(isset($service)){
	      	$result = $service->generate_api_key($description);
	      	if(is_wp_error($result)){
						if(isset($result->get_error_data()['status'])){
							$status = $result->get_error_data()['status'];
			            	wp_send_json_error($result->get_error_message(), $status);
						}
						else{
							wp_send_json_error($result, 500);
						}
			    }
	      	else{
						update_option( 'last_review_notice_date', date('Y-m-d', strtotime('-4 Months') ) );
	      		echo $result;
	      	}
	      }
	      else{
			wp_send_json_error(__('Service not found', 'zoho-flow'), 400);
	      }
	  }
	}
	wp_die();
}
add_action( 'wp_ajax_zoho_flow_generate_api_key', 'zoho_flow_generate_api_key' );

function zoho_flow_remove_api_key(){
    if ( ! current_user_can( 'zoho_flow_admin_page' ) ) {
        return new WP_Error( 'ajax_forbidden', esc_html__( 'You are not allowed to perform the operation.', 'zoho-flow'), array( 'status' => 403 ) );
    }
	if(isset($_POST['service_id'])) {
	  if(!wp_verify_nonce($_POST['api_key_removal_nonce'],'remove_api_key')){
	      wp_send_json_error(__('Unable to remove API Key. Please try after refreshing the page.', 'zoho-flow'), 403);
	   }else{
	   	$api_key_id = sanitize_key($_POST['api_key_id']);
	   	if(!ctype_digit($api_key_id)){
	   		wp_send_json_error(__('Invalid API Key ID provided', 'zoho-flow'), 400);
	   		wp_die();
	   		return;
	   	}
		$service_id = sanitize_key($_POST['service_id']);
		$service = Zoho_Flow_Services::get_instance()->get_service($service_id)['instance'];
		if(isset($service)){
			$result = $service->remove_api_key($api_key_id);
	        if(is_wp_error($result)){
				if(isset($result->get_error_data()['status'])){
					$status = $result->get_error_data()['status'];
	            	wp_send_json_error($result->get_error_message(), $status);
				}
				else{
					wp_send_json_error($result, 500);
				}
	        }
	        else{
				echo __('API key removed', 'zoho-flow');
	        }
		}
		else{
			wp_send_json_error(__('Service not found', 'zoho-flow'), 400);
		}
	  }
	}
	wp_die();
}
add_action( 'wp_ajax_zoho_flow_remove_api_key', 'zoho_flow_remove_api_key' );

function zoho_flow_api_key_table(){
	$service_id = $_POST['service_id'];
	$api_keys_table = new Zoho_Flow_API_Key_List_Table();
	$api_keys_table->set_service_id($service_id);
	$api_keys_table->prepare_items();
	$api_keys_table->display();
	wp_die();
}
add_action( 'wp_ajax_zoho_flow_api_key_table', 'zoho_flow_api_key_table' );

function zoho_flow_test_tls(){
	$resp = wp_remote_get("https://tlstest.zoho.com/api");
	if(is_wp_error($resp)){
		return $resp;
	}
	$json = json_decode($resp['body'], true);
	return $json;
}

function zoho_flow_review_banner(){
	wp_enqueue_script('zoho-flow-review-notice', plugins_url('../assets/js/zoho-flow-review-notice.js', __FILE__), array('jquery'),null, false);
	global $pagenow;
	$review_allowed_pages = array(
		"index.php",
		"plugins.php",
	);
	if ( current_user_can('administrator') ) {
		$zoho_flow_services_obj = Zoho_Flow_Services::get_instance();
		if($zoho_flow_services_obj->is_current_user_has_api_keys()){
			$option_slug = "zoho_flow_next_review_date_".get_current_user_id();
			$next_review_notice_date = get_option($option_slug);
			if(empty($next_review_notice_date)){
				add_option( $option_slug, date_add(date_create(),date_interval_create_from_date_string("1 day")), '', 'yes' );
			}
			else if(date_timestamp_get(date_create()) > date_timestamp_get($next_review_notice_date)){
				if(in_array($pagenow,$review_allowed_pages)){
				?>
					<div id= "flow-review-notice" style="border: 5px solid transparent;border-bottom: 0;border-left: 0;border-right: 0;padding: 10px;border-image: url('<?php echo plugins_url('../assets/images/zoho-colors.gif', __FILE__); ?>') 100% 1 stretch;" class="notice notice-info is-dismissible">
				<?php
				}
				else if((($pagenow=='admin.php')) && (!empty($_REQUEST['page'])) && (($_REQUEST['page']=='zoho_flow') || ($_REQUEST['page']=='zoho_flow_system_info'))){
				?>
					<div id= "flow-review-notice" style="padding: 10px;" class="notice notice-info is-dismissible">
				<?php
				}
				else{
					return true;
				}
				?>
						<div style="display:flex">
							<div style="min-width:8%;margin-top: auto;margin-bottom: auto;padding: 0px 15px;">
								<img style="max-height: 64px;" src="<?php echo plugins_url('../assets/images/Flow-logo-animation.gif', __FILE__); ?>"/>
							</div>
							<div style="margin-left: auto;margin-right: auto;padding-right: 121px;">
								<div style="font-size: 17px;padding: 15px;text-align: center;font-weight: 600;">
									<?php
									echo sprintf(
										esc_html__('Happy with %1$s? Please give us a review!'),sprintf('<strong>%s</strong>',esc_html__('Zoho Flow'))
									);
									?>
								</div>
								<div style="font-size: 15px;padding: 10px;padding-top: 5px;text-align: center;">
									<?php
									echo sprintf(
										esc_html__('Your feedback matters. Please leave a %1$s to help us improve. Thank you for being a part of the %2$s community.'), '<a href="https://wordpress.org/support/plugin/zoho-flow/reviews?rate=5#new-post" target="_blank" class="zoho-flow-rating-link" >review</a>',sprintf('<strong>%s</strong>',esc_html__('Zoho Flow'))
									);
									?>
								</div>
								<div style="text-align:center;">
									<a id="notice-review-botton" class="button button-primary" style="margin:5px;" href="https://wordpress.org/support/plugin/zoho-flow/reviews?rate=5#new-post" target="_blank"><?php echo 'Review' ?></a>
									<a id="notice-later-botton" class="button button-secondary" style="margin:5px;"><?php echo 'Maybe later' ?></a>
									<a id="notice-donot-botton" class="button button-secondary" style="margin:5px;"><?php echo 'Do not show again' ?></a>
								</div>
							</div>
						</div>
					</div>
				<?php
			}
		}
	}

}
add_action('zoho-flow-review-notice', 'zoho_flow_review_banner');
add_action( 'admin_notices', 'zoho_flow_review_banner' );

function zoho_flow_change_next_review_date(){
	if(!empty($_POST['days_to_increase']) && (is_numeric($_POST['days_to_increase']))){
		$option_slug = "zoho_flow_next_review_date_".get_current_user_id();
		update_option( $option_slug, date_add(date_create(),date_interval_create_from_date_string($_POST['days_to_increase']." days")), '', 'yes' );
	}
}
add_action( 'wp_ajax_zoho_flow_change_next_review_date', 'zoho_flow_change_next_review_date' );

function zoho_flow_integration_suggestion_banner(){
	wp_enqueue_script('zoho-flow-suggestion-notice', plugins_url('../assets/js/zoho-flow-suggestion-notice.js', __FILE__), array('jquery'),null, false);
	if ( current_user_can('administrator') ) {
		if( ! class_exists( 'Zoho_Flow_Service_Suggestion' ) ) {
				require_once( WP_ZOHO_FLOW_PLUGIN_DIR . '/includes/zoho-flow-service-suggestion.php' );
		}
		$service_suggestion = new Zoho_Flow_Service_Suggestion();
		if($service_suggestion->permission_to_show()){
			$next_suggestion_notice_date = get_option($service_suggestion->get_option_slug());
			if((empty($next_suggestion_notice_date)) || (date_timestamp_get(date_create()) > date_timestamp_get($next_suggestion_notice_date))){
				$service_suggestion->display();
			}

		}
	}
}
add_action('zoho-flow-suggestion-notice', 'zoho_flow_suggestion_banner');
add_action( 'admin_notices', 'zoho_flow_integration_suggestion_banner' );

function zoho_flow_change_next_suggestion_date(){
	if(!empty($_POST['days_to_increase']) && (is_numeric($_POST['days_to_increase'])) && (!empty($_POST['flow_service_id']))){
		$option_slug = "zoho_flow_next_suggestion_date_".$_POST['flow_service_id']."_".get_current_user_id();
		if(empty(get_option($option_slug))){
			add_option( $option_slug, date_add(date_create(),date_interval_create_from_date_string($_POST['days_to_increase']." days")), '', 'yes' );
		}
		update_option( $option_slug, date_add(date_create(),date_interval_create_from_date_string($_POST['days_to_increase']." days")), '', 'yes' );
	}
}
add_action( 'wp_ajax_zoho_flow_change_next_suggestion_date', 'zoho_flow_change_next_suggestion_date' );

add_action('admin_notices',function() {
			global $pagenow;
			if((($pagenow=='admin.php')) && (($_REQUEST['page']=='zoho_flow') || ($_REQUEST['page']=='zoho_flow_system_info'))){
				remove_all_actions( 'admin_notices' );
			}
	},
	1);

function zoho_flow_show_admin_page(){
	zoho_flow_enqueue_scripts();
	$zoho_gif = esc_attr(esc_url(plugins_url('../assets/images/Flow-logo-animation.gif', __FILE__)));
?>
<div id="loader" style="position:fixed; width:100%; height:100%; z-index:9999; background: url('<?php echo $zoho_gif ?>') 45% 45% no-repeat white;background-size: 64px 64px, cover;"></div>
<div class="wrap">

<?php
	if ( ! empty( $_REQUEST['service'] ) ) {
		$service_param = sanitize_key($_REQUEST['service']);
		$zoho_flow_services = Zoho_Flow_Services::get_instance();
		$service = $zoho_flow_services->get_service($service_param);
		if(is_null($service)){
			zoho_flow_show_unavailable_service();
		}
		else{
			zoho_flow_show_service_details($service);
		}
		return;
	}
	else{
		zoho_flow_show_service_grid();
	}
?>
</div>
<?php

}

function zoho_flow_show_service_grid(){
	?>
		<div class="zoho-flow-header">
			<h1 class="wp-heading-inline">
				<img class="zflow-logo" src="<?php echo plugins_url('../assets/images/flow-256.png', __FILE__); ?>"/>
				<?php echo esc_html__('Zoho Flow', 'zoho-flow') ?>
			</h1>
			<h3><?php echo esc_html__('Integrate your WordPress plugins with other business applications', 'zoho-flow') ?></h3>
		</div>
	<?php
		echo zoho_flow_review_banner();
		if ( !get_option('permalink_structure')){
	?>
	<div id="permalink-warning-notice" class="notice inline notice-warning">
		<h2><?php echo esc_html__( 'Permalink is not enabled' ) ?></h2>
			<p><?php echo esc_html__( 'It appears that permalink has not been enabled for this site. For this plugin to work properly, you need to enable permalinks. ', 'zoho-flow' )?><a href="<?php echo admin_url('options-permalink.php'); ?>">Enable</a></p>
	</div>
	<?php
		}
		$resp = zoho_flow_test_tls();
		if(is_wp_error($resp)){
	?>
	<div id="url-call-fail-notice" class="notice inline notice-warning">
		<h2><?php echo esc_html__( 'External url call failed' ) ?></h2>
		<p><?php echo esc_html__( 'This plugin needs to call external url for its functionality. However, it did not work due to the below error.', 'zoho-flow' ) ?></p>
			<p style="font-weight:bold;"><?php printf(esc_html( '%s' ), $resp->get_error_message())?></p>
	</div>
	<?php
		}
		else{
			$message = $resp['message'];
			$version = $resp['version'];
			if($message != 'Connection Success'){
	?>
	<div id="tls-connection-fail-notice" class="notice inline notice-warning">
		<h2><?php echo esc_html__( 'TLS connection failed' ) ?></h2>

			<p>
				<?php
					// translators: %s refers to the minimum TLS version
					printf(esc_html__( 'This plugins requires at least %s to work properly. Kindly upgrade the TLS version of your wordpress setup.', 'zoho-flow' ), $version)
				?>
			</p>
	</div>
	<?php
		}
	}
	?>
	<div id="service_details_popup" style="display:none;">
		<div id="service_details_popup_content" >
			<h1><?php echo 'service_name' ?></h1>
			<p><?php echo 'service_description' ?></p>
		</div>
	</div>
	<div class="plugin-gallery-wrapper">
		<div class="plugin-gallery" style="max-width:75%">
			<h2><?php echo esc_html__( 'WordPress plugin integrations available in Zoho Flow', 'zoho-flow' )?></h2>
			<p><?php echo esc_html__( 'Click the WordPress plugins to access their API keys and other connection requirements.', 'zoho-flow' )?></p>
			<div class="table services-list-grid">
					<?php
						if( ! class_exists( 'Zoho_Flow_Services_Grid' ) ) {
								require_once( WP_ZOHO_FLOW_PLUGIN_DIR . '/includes/zoho-flow-services-grid.php' );
						}
						$servicesTable = new Zoho_Flow_Services_Grid();
						$servicesTable->display();
					?>
			</div>
		</div>
		<div class="plugin-gallery-right-side" style="max-width:25%">
			<div class="why-flow-section">
				<h2><img class="zflow-logo" src="<?php echo plugins_url('../assets/images/flow-256.png', __FILE__); ?>"><?php echo esc_html__('About Zoho Flow', 'zoho-flow') ?></h2>
				<p><?php echo esc_html__('Zoho Flow is an integration platform that lets you connect different applications without writing code. It also lets you integrate popular WordPress plugins. Zoho Flow offers a dynamic drag-and-drop builder that can help you build integrations and automated workflows within minutes.') ?></p>
				<p class="zflow-cta">
					<a target="_blank" href="//www.zoho.com/flow/features.html?utm_source=wordpress&utm_medium=link&utm_campaign=zoho-flow-plugin"><?php echo esc_html__('Features', 'zoho-flow') ?></a> |
					<a target="_blank" href="//www.zoho.com/flow/help/?utm_source=wordpress&utm_medium=link&utm_campaign=zoho-flow-plugin"><?php echo esc_html__('Help', 'zoho-flow') ?></a> |
					<a target="_blank" href="//www.zoho.com/flow/pricing.html?utm_source=wordpress&utm_medium=link&utm_campaign=zoho-flow-plugin"><?php echo esc_html__('Pricing', 'zoho-flow') ?></a>
				</p>
			</div>
			<div class="flow-gallery-section">
				<h2><?php echo esc_html__('Gallery') ?></h2>
				<p><?php echo esc_html__('Check out Zoho Flow\'s gallery page to integrate your favorite applications and build automated workflows.') ?></p>
				<p>
					<a target="_blank" href="//www.zohoflow.com/apps/?utm_source=wordpress&utm_medium=link&utm_campaign=zoho-flow-plugin"><?php echo esc_html__('Access Gallery', 'zoho-flow') ?></a>
				</p>
			</div>
			<div class="flow-video-section">
				<h2><?php echo esc_html__('Watch Zoho Flow in action') ?></h2>
				<div class="zvideo-thumb-wrap zcpopup-controller">
					<a id="open-video-popup"  href="https://www.youtube.com/embed/68JFXlpm6iI?autoplay=1&TB_iframe=true" class="thickbox" title="Getting started with Zoho Flow">
					<img src="<?php echo plugins_url('../assets/images/flow-video-thumb.png', __FILE__); ?>">
					<span class="zarrow"></span>
					</a>
				</div>
			</div>
			<div class="zflow-req-app">
				<div class="zicon zicon1"></div>
				<h2><?php echo esc_html__('Can\'t find an app or plugin? ', 'zoho-flow') ?></h2>
				<p><?php echo esc_html__('If you aren\'t able to find the app or plugin that you need in our gallery, let us know and we\'ll try our best to add them!', 'zoho-flow') ?></p>
				<p>
					<a href="https://creatorapp.zohopublic.com/zohointranet/zoho-flow/form-embed/Request_an_App/qqePxZq7ZkzdWKGCYvntEk14O9YqjUGHJUZJHYsMA5zOK6XEC8b6Gh7mrdz2TnYu4AUVBRwu1YzKVU8KAwbn2OurBsJ66FqkT8Rm?zc_BdrClr=ffffff&zc_Header=false&TB_iframe=true&width=320&height=440" class="thickbox" title="Integration request"><?php echo esc_html__('Request app/plugin', 'zoho-flow') ?></a>
				</p>
			</div>
			<div class="flow-webinar-section">
				<h2><?php echo esc_html__('Register for live webinar') ?></h2>
				<div class="zwebinar-registration-wrap">
					<a href="https://www.zoho.com/flow/webinars.html?utm_source=wordpress&utm_medium=link&utm_campaign=zoho-flow-plugin" target="_blank">
						 <img src="<?php echo plugins_url('../assets/images/zoho-flow-webinar.png', __FILE__); ?>">
					</a>
				</div>
			</div>
			<div class="flow-social-media-section">
				<p style="margin:0px !important;height: 20px !important;"><?php echo esc_html__('Follow us on') ?></p>
				<div class="social-media-icons">
					<a href="https://www.linkedin.com/company/zoho-flow" style="text-decoration:none !important; outline: 0 !important;" target="_blank">
						<img src="<?php echo plugins_url('../assets/images/logos/linkedin.png', __FILE__); ?>" style="height: 20px;">
					</a>
					<a href="https://twitter.com/ZohoFlow" style="text-decoration:none !important; outline: 0 !important;" target="_blank">
						<img src="<?php echo plugins_url('../assets/images/logos/twitter.png', __FILE__); ?>" style="height: 20px;">
					</a>
					<a href="https://www.facebook.com/ZohoFlow/" style="text-decoration:none !important; outline: 0 !important;" target="_blank">
						<img src="<?php echo plugins_url('../assets/images/logos/facebook.png', __FILE__); ?>" style="height: 20px;">
					</a>
				</div>
			</div>
		</div>
	</div>
	<div style="padding-top: 4px;">
	   	<script type="text/javascript" src="https://flow.zoho.com/embed/flow-widget.js?services=zoho_flow&limit=3"></script>
	</div>
	<?php

}

function zoho_flow_show_unavailable_service(){
?>
	<div class="unavailable-zoho-flow-service">
		<img src="<?php echo plugins_url('../assets/images/no-result-found.svg', __FILE__); ?>"/>
		<h2><?php echo esc_html__( 'The plugin was not found!', 'zoho-flow' )?></h2>
	</div>
<?php
}

function service_not_available_popup($service_name, $service_description){
	?>
	<div id="service_details_popup" style="display:none;">
		<h1><?php echo $service_name ?></h1>
		<p><?php echo $service_description ?></p>
	</div>
<?php
}

function zoho_flow_api_key_html($current_service){
	$service_id = $current_service->get_service_id();
	$button_label = esc_html__( 'Generate', 'zoho-flow' );
	$ok_button_label = esc_html__( 'Ok', 'zoho-flow' );
?>
<div id="api_key_details" style="display:none;">
	<form id="generate-api-key-form" method="post" style="padding:10px 0">
		<?php wp_nonce_field( 'generate_api_key', 'api_key_generation_nonce' );?>
		<input type="hidden" name="action" value="zoho_flow_generate_api_key">
		<input type="hidden" name="service_id" value="<?php echo esc_attr($service_id) ?>">
		<label style="font-size: 13px;" for="description"><?php echo esc_html__('Provide a label for this API key') ?></label>
		<input id="api-key-description" type="text" maxlength="100" name="description" style="display: block;
		    margin: 10px 0;
		    height: 40px;
		    width: 95%;"/>
		<span>
			<input type="button" id="generate-api-key" class="button button-primary" value="<?php echo $button_label ?>"></button>
			<span style="display:none;"><?php echo esc_html__('Generating API key...', 'zoho-flow') ?></span>
		</span>
	</form>
	<div id="api-key-div" style="display:none; font-size: 13px;">
		<h3 style="margin-bottom:0px;"><?php echo esc_html('API Key', 'zoho-flow') ?></h3>
		<p style="margin:0px;padding-bottom:0px"><?php echo esc_html__('Copy the API key and keep it safe. The API key can\'t be copied or retrieved again.', 'zoho-flow') ?></p>
		<div style="margin:10px 0">
			<span id="api-key-span"></span>
			<a id="copy-api-key" style="padding: 6px 0px;" class="dashicons dashicons-admin-page"></a>
		</div>
		<h3><?php echo esc_html('Base URL', 'zoho-flow') ?></h3>
		<div style="margin:10px 0">
			<span id="site-url-span"><?php echo esc_html(site_url(),'zoho-flow')?></span>
			<a id="copy-site-url" style="padding: 6px 0px;" class="dashicons dashicons-admin-page"></a>
		</div>
		<p id="api-key-ok-button">
			<button id="ok-api-key-popup" class="button button-primary"><?php echo $ok_button_label ?></button>
			<span id='copy-sucess'>Copied!</span>
		</p>
	</div>
</div>
<?php
}

function zoho_flow_show_service_details($service){
	update_option( 'zoho_flow_admin_footer_text_rated', 0 );
	$services = Zoho_Flow_Services::get_instance()->get_services();
	$current_service = Zoho_Flow_Services::get_instance()->get_service($service['id'])['instance'];
	$service_name = $current_service->get_service_name();
	$gallery_app_link = $service['gallery_app_link'];
	$app_documentation_link = $service['app_documentation_link'];
	$embed_link = $service['embed_link'];
	$service_name
?>
	<span>
		<a style="text-decoration:none;box-shadow: none;" href="<?php echo menu_page_url( 'zoho_flow', false ); ?>">&larr; <?php echo esc_html__( 'Back', 'zoho-flow' )?></a>
	</span>
	<div class="service-details-header-wrapper">
		<div style="display:grid;">
			<div class="service-details-logo-wrapper">
				<div style="display:inline-flex;">
					<img class="service-details-logo" src="<?php echo plugins_url('../assets/images/flow-256.png', __FILE__); ?>"/>
					<div class="service-details-logo-arrow" >&#8644;</div>
					<img class="service-details-logo" src="<?php echo plugins_url('../assets/images/logos/' . $service['icon_file'], __FILE__); ?>"/>
				</div>
			</div>
			<div class="service-details-title">
				<?php
					echo $service['name']." Integration";
				?>
			</div>
			<div>
				<div class="service-details-description">
					<?php
						echo $service['description'];
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="service-details-wrapper">
		<div class="service-details-content-wrapper">
			<div class="service_details_content">
				<div>
					<h3 style="display:inline;vertical-align:middle;"><?php echo esc_html__( 'API Keys', 'zoho-flow' )?></h3>
					<a style="vertical-align:middle;margin-left:5px;" id="open-api-key-generation-popup" href="<?php echo esc_attr('#TB_inline?width=600&height=150&inlineId=api_key_details') ?>" class="button button-primary thickbox" title="<?php echo esc_attr($service_name) ?> : <?php echo esc_attr__('Generate new API key', 'zoho-flow') ?>"><?php echo esc_html__('+ New API Key', 'zoho-flow') ?></a>
					<span id="notice" class="notice" style="display:none;margin-left:10px;padding:5px;vertical-align:middle;"></span>
					<p>
						<?php
							// translators: %s refers to the plugin name
							printf(esc_html__( 'You can use any of the active API keys to connect %s to Zoho Flow. Generate one if you don\'t have any.', 'zoho-flow' ), $service_name)
						?>
					</p>

					<form id="remove-api-key-form" method="post">
						<?php wp_nonce_field( 'remove_api_key', 'api_key_removal_nonce' );?>
						<input type="hidden" name="action" value="zoho_flow_remove_api_key">
						<input type="hidden" name="service_id" value="<?php echo esc_attr($current_service->get_service_id()) ?>">
						<div id="api-key-list-display" class="table api-key-table">
					<?php
						$api_keys_table = new Zoho_Flow_API_Key_List_Table();
						$api_keys_table->set_service_id($current_service->get_service_id());
						$api_keys_table->prepare_items();
						$api_keys_table->display();
					?>
						</div>
					</form>
					<?php zoho_flow_api_key_html($current_service) ?>
				</div>
			</div>
		</div>
		<div class="service-details-right-panel">
			<h3 style="width:100%;">
				<?php
					// translators: %s refers to the plugin name
					printf(esc_html__('How to integrate %s with other apps via Zoho Flow?', 'zoho-flow') , $service_name)
				?>
			</h3>
			<h4><?php echo esc_html__('Step 1', 'zoho-flow') ?></h4>
			<p><a href="https://www.zoho.com/flow/signup.html?utm_source=wordpress&utm_medium=link&utm_campaign=zoho_flow_<?php echo $gallery_app_link ?>" target="_blank"><?php echo esc_html__('Register', 'zoho-flow') ?></a><?php echo esc_html__(' for a Zoho Flow account or log in if you already have one.', 'zoho-flow') ?></p>
			<h4><?php echo esc_html__('Step 2', 'zoho-flow') ?></h4>
			<p><a id= "open-api-key-generation-popup-from-right-panel" href="#TB_inline?width=600&height=150&inlineId=api_key_details" class="thickbox" title="<?php echo esc_attr($service_name) ?> - <?php echo esc_attr__('Generate new API key', 'zoho-flow') ?>"><?php echo esc_html__('Generate', 'zoho-flow') ?></a><?php echo esc_html__(' an API Key for the plugin. Keep it safe as it can\'t be retrieved again.', 'zoho-flow') ?></p>
			<h4><?php echo esc_html__('Step 3', 'zoho-flow') ?></h4>
			<p><?php echo esc_html__('Open Zoho Flow and create a new flow. Note that each plugin or app has specific triggers and actions. Select the ones that suit your desired workflow.', 'zoho-flow') ?></p>
			<h4><?php echo esc_html__('Step 4', 'zoho-flow') ?></h4>
			<p><?php echo esc_html__('When prompted for a connection, enter the previously generated API key for the plugin and provide the Base URL of your WordPress website.', 'zoho-flow') ?></p>
			<h4><?php echo esc_html__('Step 5', 'zoho-flow') ?></h4>
			<p><?php echo esc_html__('Configure the trigger and actions of your workflow. Once set up, switch the flow on.', 'zoho-flow') ?></p>
			<p>
				<strong>
						<?php echo esc_html__('Note:') ?>
				</strong>
				<?php
					echo __(' If there\'s a specific trigger, action, or app/plugin you\'d like to connect that isn\'t currently available, or if you have feedback about the existing integrations, please contact Zoho Flow support. We\'re always looking to enhance our platform based on user feedback.', 'zoho-flow' )
				?>
			</p>
			<h3 style="width:100%;"><?php printf(esc_html__('More information')) ?></h3>
			<p>
			<a target="_blank" href="https://www.zohoflow.com/apps/<?php echo $gallery_app_link ?>/integrations/?utm_source=wordpress&utm_medium=link&utm_campaign=zoho_flow_<?php echo $gallery_app_link ?>">
				<?php
					// translators; %s refers to the plugin name
					printf(esc_html__('%s integrations', 'zoho-flow') , $service_name)
				?>
			</a></p>
			<p>
			<a target="_blank" href="https://help.zoho.com/portal/en/kb/flow/user-guide/app-specific-documentation/articles/<?php echo $app_documentation_link ?>/?utm_source=wordpress&utm_medium=link&utm_campaign=zoho_flow_<?php echo $gallery_app_link ?>">
				<?php
				if(!empty($app_documentation_link)){
					// translators; %s refers to the plugin name
					printf(esc_html__('%s app documentation', 'zoho-flow') , $service_name) ;
				}
				?>
			</a></p>
			<p><a target="_blank" href="https://www.zoho.com/flow/help/?utm_source=wordpress&utm_medium=link&utm_campaign=zoho_flow_<?php echo $gallery_app_link ?>">Help resources</a>
			<h3 style="width:100%;"><?php printf(esc_html__('Support')) ?></h3>
			<p><a href="mailto:support@zohoflow.com">support@zohoflow.com</a></p>
			<p><a href="mailto:support@zohoflow.eu">support@zohoflow.eu</a> (for EU Customers)</p>
		</div>
	</div>
	<div style="padding-top: 4px;">
       	<script type="text/javascript" src="https://flow.zoho.com/embed/flow-widget.js?services=<?php echo esc_attr($embed_link) ?>&limit=3"></script>
    </div>
<?php


}

function zoho_flow_show_system_info_page(){
	zoho_flow_enqueue_scripts();
	wp_enqueue_script('zoho-flow-system-info', plugins_url('../assets/js/zoho-flow-system-info.js', __FILE__), array('jquery'),null, false);
	$zoho_gif = esc_attr(esc_url(plugins_url('../assets/images/Flow-logo-animation.gif', __FILE__)));
?>
<div id="loader" style="position:fixed; width:100%; height:100%; z-index:9999; background: url('<?php echo $zoho_gif ?>') 45% 45% no-repeat white;background-size: 64px 64px, cover;"></div>
<div class="wrap">

<?php
	?>
		<div class="zoho-flow-header">
			<h1 class="wp-heading-inline">
				<img class="zflow-logo" src="<?php echo plugins_url('../assets/images/flow-256.png', __FILE__); ?>"/>
				<?php echo esc_html__('Zoho Flow', 'zoho-flow') ?>
			</h1>
			<!--<h3><?php echo esc_html__('Integrate your WordPress plugins with other business applications', 'zoho-flow') ?></h3>-->
		</div>
	<?php
		echo zoho_flow_review_banner();
	?>
		<div class="system-info-wrapper">
			<h2><?php echo esc_html__('System info', 'zoho-flow') ?></h2>

				<?php
					$site_info_table = new Zoho_Flow_System_Info_Menue();
					$site_info_table->prepare_items();
					$site_info_table->display();
				?>

		</div>
	<?php
}
