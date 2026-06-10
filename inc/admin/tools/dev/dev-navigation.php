<?php
/**
 * Development smoke pages and front-end navigation setup.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dev_nav_dir = __DIR__ . '/';
require_once $dev_nav_dir . 'dev-nav-constants.php';
require_once $dev_nav_dir . 'dev-smoke-pages.php';
require_once $dev_nav_dir . 'dev-nav-classic.php';
require_once $dev_nav_dir . 'dev-nav-menu.php';
require_once $dev_nav_dir . 'dev-nav-block.php';

/**
 * Ensure smoke pages exist and add them to the site navigation menu.
 *
 * @return array{menu_id: int, added: int, page_ids: int[]}|WP_Error
 */
function MRT_setup_development_navigation() {
	if ( ! MRT_dev_cli_allowed() ) {
		return new WP_Error(
			'mrt_not_dev',
			__( 'Utvecklingsmeny är endast tillgänglig i utvecklingsläge (WP_DEBUG eller MRT_DEVELOPMENT) eller WP-CLI.', 'museum-railway-timetable' )
		);
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'mrt_cap', __( 'Åtkomst nekad.', 'museum-railway-timetable' ) );
	}

	MRT_ensure_pretty_permalinks();

	$pages = MRT_ensure_dev_smoke_pages();
	if ( $pages['errors'] !== array() ) {
		return $pages['errors'][0];
	}

	$menu_id = MRT_get_assigned_nav_menu_id_for_theme();
	if ( $menu_id <= 0 ) {
		$created = MRT_get_or_create_dev_nav_menu();
		if ( is_wp_error( $created ) ) {
			return $created;
		}
		$menu_id = (int) $created;
		MRT_assign_dev_menu_to_primary_if_unassigned( $menu_id );
	}

	$added = MRT_sync_dev_smoke_pages_to_nav_menu( $menu_id );
	MRT_sync_block_navigation_from_menu( $menu_id );
	update_option( MRT_OPTION_DEV_NAV_MENU_ID, $menu_id );

	return array(
		'menu_id'  => $menu_id,
		'added'    => $added,
		'page_ids' => $pages['page_ids'],
	);
}
