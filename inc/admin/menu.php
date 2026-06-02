<?php
/**
 * Admin menu registration (single Vue app entry + optional dev pages).
 *
 * Navigation lives inside the Vue app (AdminNav). Avoid WordPress submenus that reload
 * admin.php with different page slugs — they break hash routing.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Top-level menu and optional dev-only utility pages.
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

	MRT_register_admin_menu_demo_submenu();
}

/**
 * Remove WP auto-duplicate submenu (same label as parent).
 */
function MRT_admin_remove_native_app_submenu(): void {
	remove_submenu_page( MRT_ADMIN_APP_SLUG, MRT_ADMIN_APP_SLUG );
}

/**
 * Component demo admin screen (dev only), hidden from WP sidebar.
 *
 * Linked from Vue AdminNav (componentDemoAdminUrl). Parent null avoids hijacking
 * the Tidtabell top-level link when it would become the only visible submenu.
 */
function MRT_register_admin_menu_demo_submenu(): void {
	if ( ! MRT_is_development_mode() ) {
		return;
	}
	$demo_slug = MRT_components_demo_menu_slug();
	// WordPress accepts null parent to register a hidden submenu page.
	// @phpstan-ignore argument.type
	add_submenu_page(
		null,
		__( 'Komponentdemo', 'museum-railway-timetable' ),
		__( 'Komponentdemo', 'museum-railway-timetable' ),
		'manage_options',
		$demo_slug,
		'MRT_render_components_demo_admin_page'
	);
}

add_action( 'admin_menu', 'MRT_register_admin_menus' );
add_action( 'admin_menu', 'MRT_admin_remove_native_app_submenu', 999 );

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
	$target = MRT_is_development_mode()
		? MRT_admin_app_url( '/dev-tools' )
		: MRT_admin_app_url( '/dashboard' );
	wp_safe_redirect( $target );
	exit;
}

add_action( 'admin_init', 'MRT_admin_redirect_legacy_settings_page', 5 );
