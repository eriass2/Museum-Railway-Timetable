<?php
/**
 * Admin menu registration (Vue app + utility pages).
 *
 * Submenu capabilities mirror Vue AdminNav: edit_posts sees operate pages only;
 * manage_options sees settings, prices, train types, import/export, and dev tools.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vue admin submenu definitions (slug => label + capability).
 *
 * @return array<string, array{label: string, cap: string}>
 */
function MRT_admin_vue_submenu_pages(): array {
	$pages = array(
		MRT_ADMIN_APP_SLUG        => array(
			'label' => __( 'Översikt', 'museum-railway-timetable' ),
			'cap'   => 'edit_posts',
		),
		'mrt_app_timetables'      => array(
			'label' => __( 'Tidtabeller', 'museum-railway-timetable' ),
			'cap'   => 'edit_posts',
		),
		'mrt_app_stations_routes' => array(
			'label' => __( 'Stationer & rutter', 'museum-railway-timetable' ),
			'cap'   => 'edit_posts',
		),
		'mrt_app_help'            => array(
			'label' => __( 'Hjälp', 'museum-railway-timetable' ),
			'cap'   => 'edit_posts',
		),
		'mrt_app_train_types'     => array(
			'label' => __( 'Tågtyper', 'museum-railway-timetable' ),
			'cap'   => 'manage_options',
		),
		'mrt_app_settings'        => array(
			'label' => __( 'Inställningar', 'museum-railway-timetable' ),
			'cap'   => 'manage_options',
		),
		'mrt_app_prices'          => array(
			'label' => __( 'Priser', 'museum-railway-timetable' ),
			'cap'   => 'manage_options',
		),
		'mrt_app_import_export'   => array(
			'label' => __( 'Import / export', 'museum-railway-timetable' ),
			'cap'   => 'manage_options',
		),
	);
	if ( MRT_is_development_mode() ) {
		$pages['mrt_app_dev_tools'] = array(
			'label' => __( 'Utvecklingsverktyg', 'museum-railway-timetable' ),
			'cap'   => 'manage_options',
		);
	}
	return $pages;
}

/**
 * Vue admin submenus (same render callback, different initial route).
 */
function MRT_register_admin_vue_submenus(): void {
	foreach ( MRT_admin_vue_submenu_pages() as $slug => $page ) {
		add_submenu_page(
			MRT_ADMIN_APP_SLUG,
			$page['label'],
			$page['label'],
			$page['cap'],
			$slug,
			'MRT_render_admin_app'
		);
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
		__( 'Komponentdemo', 'museum-railway-timetable' ),
		__( 'Komponentdemo', 'museum-railway-timetable' ),
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
		__( 'Tidtabell', 'museum-railway-timetable' ),
		'edit_posts',
		MRT_ADMIN_APP_SLUG,
		'MRT_render_admin_app',
		'dashicons-calendar-alt'
	);

	MRT_register_admin_vue_submenus();
	MRT_register_admin_menu_demo_submenu();
}

add_action( 'admin_menu', 'MRT_register_admin_menus' );

/**
 * Repoint duplicate top-level slug to Översikt submenu (WordPress default).
 */
function MRT_admin_menu_legacy_redirect(): void {
	remove_submenu_page( MRT_ADMIN_APP_SLUG, MRT_ADMIN_APP_SLUG );
	add_submenu_page(
		MRT_ADMIN_APP_SLUG,
		__( 'Översikt', 'museum-railway-timetable' ),
		__( 'Översikt', 'museum-railway-timetable' ),
		'edit_posts',
		MRT_ADMIN_APP_SLUG,
		'MRT_render_admin_app'
	);
}

add_action( 'admin_menu', 'MRT_admin_menu_legacy_redirect', 999 );

/**
 * Redirect legacy mrt_settings URL to Vue dev tools.
 */
function MRT_admin_redirect_legacy_settings_page(): void {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';
	if ( $page !== 'mrt_settings' ) {
		return;
	}
	$target = MRT_is_development_mode() ? 'mrt_app_dev_tools' : MRT_ADMIN_APP_SLUG;
	wp_safe_redirect( admin_url( 'admin.php?page=' . $target ) );
	exit;
}

add_action( 'admin_init', 'MRT_admin_redirect_legacy_settings_page', 5 );
