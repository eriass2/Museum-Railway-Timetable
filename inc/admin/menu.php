<?php
/**
 * Admin menu registration (Vue app + utility pages).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vue admin submenus (same render callback, different initial route).
 */
function MRT_register_admin_vue_submenus(): void {
	$pages = array(
		MRT_ADMIN_APP_SLUG        => __( 'Dashboard', 'museum-railway-timetable' ),
		'mrt_app_timetables'      => __( 'Timetables', 'museum-railway-timetable' ),
		'mrt_app_stations_routes' => __( 'Stations & routes', 'museum-railway-timetable' ),
	);
	$first = true;
	foreach ( $pages as $slug => $label ) {
		add_submenu_page(
			MRT_ADMIN_APP_SLUG,
			$label,
			$first ? $label : $label,
			'edit_posts',
			$slug,
			'MRT_render_admin_app'
		);
		$first = false;
	}
}

/**
 * Component demo submenu + hook fallback.
 */
function MRT_register_admin_menu_demo_submenu(): void {
	if ( ! MRT_is_development_mode() ) {
		return;
	}
	$demo_slug = MRT_components_demo_menu_slug();
	add_submenu_page(
		MRT_ADMIN_APP_SLUG,
		__( 'Component demo page', 'museum-railway-timetable' ),
		__( 'Component demo page', 'museum-railway-timetable' ),
		'manage_options',
		$demo_slug,
		'MRT_render_components_demo_admin_page'
	);

	$demo_hook = get_plugin_page_hookname( $demo_slug, MRT_ADMIN_APP_SLUG );
	if ( is_string( $demo_hook ) && $demo_hook !== '' && ! has_action( $demo_hook ) ) {
		add_action( $demo_hook, 'MRT_render_components_demo_admin_page' );
	}
}

/**
 * Top-level menu and submenus.
 */
function MRT_register_admin_menus(): void {
	add_menu_page(
		__( 'Museum Railway Timetable', 'museum-railway-timetable' ),
		__( 'Railway Timetable', 'museum-railway-timetable' ),
		'edit_posts',
		MRT_ADMIN_APP_SLUG,
		'MRT_render_admin_app',
		'dashicons-calendar-alt'
	);

	MRT_register_admin_vue_submenus();
	MRT_register_admin_legacy_tools_submenu();
	MRT_register_admin_menu_demo_submenu();
}

/**
 * Legacy PHP tools (settings, import) until fully migrated to Vue.
 */
function MRT_register_admin_legacy_tools_submenu(): void {
	add_submenu_page(
		MRT_ADMIN_APP_SLUG,
		__( 'Settings & tools', 'museum-railway-timetable' ),
		__( 'Settings & tools', 'museum-railway-timetable' ),
		'manage_options',
		'mrt_settings',
		'MRT_render_admin_page'
	);
}

add_action( 'admin_menu', 'MRT_register_admin_menus' );

/**
 * Repoint legacy settings slug to Vue dashboard.
 */
function MRT_admin_menu_legacy_redirect(): void {
	remove_submenu_page( MRT_ADMIN_APP_SLUG, MRT_ADMIN_APP_SLUG );
	add_submenu_page(
		MRT_ADMIN_APP_SLUG,
		__( 'Dashboard', 'museum-railway-timetable' ),
		__( 'Dashboard', 'museum-railway-timetable' ),
		'edit_posts',
		MRT_ADMIN_APP_SLUG,
		'MRT_render_admin_app'
	);
}

add_action( 'admin_menu', 'MRT_admin_menu_legacy_redirect', 999 );
