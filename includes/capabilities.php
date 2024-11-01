<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_filter( 'map_meta_cap', 'zoho_flow_map_meta_cap', 10, 4 );

function zoho_flow_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'zoho_flow_admin_page'=> 'activate_plugins',
	);

	$meta_caps = apply_filters( 'zoho_flow_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) ) {
		$caps[] = $meta_caps[$cap];
	}

	return $caps;
}
