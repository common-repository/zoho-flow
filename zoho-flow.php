<?php
/*
Plugin Name: Zoho Flow
Plugin URI: https://www.zohoflow.com/?utm_source=wordpress&utm_campaign=plugin-uri&utm_medium=link
Description: Zoho Flow helps you integrate your favorite Wordpress plugins with hundreds of popular business apps like Zoho CRM, Mailchimp, HubSpot, and Eventbrite. Save hours of time at work by automatically syncing data between the plugins and the apps you use.
Author: Zoho Flow
Author URI: https://www.zohoflow.com/?utm_source=wordpress&utm_campaign=author-uri&utm_medium=link
Text Domain: zoho-flow
Requires at least: 4.4
Requires PHP: 7.0.0
Domain Path: /languages/
Version: 2.10.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WP_ZOHO_FLOW_PLUGIN', __FILE__ );

define( 'WP_ZOHO_FLOW_PLUGIN_DIR', untrailingslashit( dirname( WP_ZOHO_FLOW_PLUGIN ) ) );

define( 'WP_ZOHO_FLOW_PLUGIN_BASENAME', plugin_basename( WP_ZOHO_FLOW_PLUGIN ) );

define( 'WP_ZOHO_FLOW_PLUGIN_NAME', trim( dirname( WP_ZOHO_FLOW_PLUGIN_BASENAME ), '/' ) );

define ( 'WP_ZOHO_FLOW_WEBHOOK_POST_TYPE', 'zoho_flow_webhooks');

define ( 'WP_ZOHO_FLOW_API_KEY_POST_TYPE', 'zoho_flow_api_keys');

define ( 'WP_ZOHO_FLOW_DEBUG', false);

require_once WP_ZOHO_FLOW_PLUGIN_DIR . '/settings.php';
