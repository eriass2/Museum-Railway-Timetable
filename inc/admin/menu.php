<?php
/**
 * Admin menu registration (top-level Railway Timetable + CPT links).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT and taxonomy links under the plugin menu.
 */
function MRT_register_admin_menu_cpt_submenus(): void {
	add_submenu_page(
		'mrt_settings',
		__( 'Timetables', 'museum-railway-timetable' ),
		__( 'Timetables', 'museum-railway-timetable' ),
		'edit_posts',
		'edit.php?post_type=mrt_timetable'
	);

	add_submenu_page(
		'mrt_settings',
		__( 'Stations', 'museum-railway-timetable' ),
		__( 'Stations', 'museum-railway-timetable' ),
		'edit_posts',
		'edit.php?post_type=mrt_station'
	);

	add_submenu_page(
		'mrt_settings',
		__( 'Routes', 'museum-railway-timetable' ),
		__( 'Routes', 'museum-railway-timetable' ),
		'edit_posts',
		'edit.php?post_type=mrt_route'
	);

	add_submenu_page(
		'mrt_settings',
		__( 'Train Types', 'museum-railway-timetable' ),
		__( 'Train Types', 'museum-railway-timetable' ),
		'manage_categories',
		'edit-tags.php?taxonomy=mrt_train_type&post_type=mrt_service'
	);
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
		'mrt_settings',
		__( 'Component demo page', 'museum-railway-timetable' ),
		__( 'Component demo page', 'museum-railway-timetable' ),
		'manage_options',
		$demo_slug,
		'MRT_render_components_demo_admin_page'
	);

	$demo_hook = get_plugin_page_hookname( $demo_slug, 'mrt_settings' );
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
		'manage_options',
		'mrt_settings',
		'MRT_render_admin_page',
		'dashicons-calendar-alt'
	);

	MRT_register_admin_menu_cpt_submenus();
	MRT_register_admin_menu_demo_submenu();
}

add_action( 'admin_menu', 'MRT_register_admin_menus' );
