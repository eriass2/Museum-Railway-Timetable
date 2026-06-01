<?php
/**
 * Admin menu registration (Vue app + utility pages).
 *
 * Submenu order matches setup workflow (stationer → tidtabeller). Capabilities mirror
 * Vue AdminNav (mobile only): edit_posts sees operate pages; manage_options sees settings.
 * manage_options sees settings, prices, train types, import/export, and dev tools.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vue admin submenu definitions (slug => label + capability), excluding dashboard.
 *
 * @return array<string, array{label: string, cap: string}>
 */
function MRT_admin_vue_submenu_pages(): array {
	$pages = array(
		'mrt_app_stations_routes' => array(
			'label' => __( 'Stationer & rutter', 'museum-railway-timetable' ),
			'cap'   => 'edit_posts',
		),
		'mrt_app_timetables'      => array(
			'label' => __( 'Tidtabeller', 'museum-railway-timetable' ),
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
 * Register one Vue admin submenu.
 */
function MRT_admin_add_vue_submenu( string $slug, string $label, string $cap ): void {
	add_submenu_page(
		MRT_ADMIN_APP_SLUG,
		$label,
		$label,
		$cap,
		$slug,
		'MRT_render_admin_app'
	);
}

/**
 * Vue admin submenus in display order.
 */
function MRT_register_admin_vue_submenus(): void {
	// First submenu must use parent slug — replaces WP auto-duplicate with «Översikt».
	MRT_admin_add_vue_submenu(
		MRT_ADMIN_APP_SLUG,
		__( 'Översikt', 'museum-railway-timetable' ),
		'edit_posts'
	);

	foreach ( MRT_admin_vue_submenu_pages() as $slug => $page ) {
		MRT_admin_add_vue_submenu( $slug, $page['label'], $page['cap'] );
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
