<?php
/**
 * Legacy Import Lennakatten admin URL → Vue dev tools.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Legacy admin page slug (no menu item). */
define( 'MRT_LEGACY_IMPORT_LENNAKATTEN_SLUG', 'mrt_import_lennakatten' );

/**
 * Redirect old Import Lennakatten screen to Vue dev tools.
 */
function MRT_admin_redirect_legacy_import_lennakatten_page(): void {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';
	if ( $page !== MRT_LEGACY_IMPORT_LENNAKATTEN_SLUG ) {
		return;
	}
	if ( ! MRT_is_development_mode() ) {
		wp_safe_redirect( admin_url( 'admin.php?page=' . MRT_ADMIN_APP_SLUG ) );
		exit;
	}
	wp_safe_redirect( MRT_admin_app_url( '/dev-tools' ) );
	exit;
}

add_action( 'admin_init', 'MRT_admin_redirect_legacy_import_lennakatten_page', 5 );
