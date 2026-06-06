<?php
/**
 * Admin helpers for public timetable pages (nav menu sync).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/timetable/timetable-pages.php';

/**
 * Add index page to primary nav menu when missing (idempotent).
 */
function MRT_append_timetables_index_to_nav_menu( int $page_id ): int {
	if ( $page_id <= 0 || ! function_exists( 'MRT_get_assigned_nav_menu_id_for_theme' ) ) {
		return 0;
	}
	$menu_id = MRT_get_assigned_nav_menu_id_for_theme();
	if ( $menu_id <= 0 ) {
		return 0;
	}
	if ( function_exists( 'MRT_remove_broken_nav_menu_page_items' ) ) {
		MRT_remove_broken_nav_menu_page_items( $menu_id );
	}
	$added = 0;
	if ( function_exists( 'MRT_append_nav_menu_page_if_missing' )
		&& MRT_append_nav_menu_page_if_missing( $menu_id, $page_id, __( 'Tidtabeller', 'museum-railway-timetable' ) )
	) {
		$added = 1;
	}
	if ( function_exists( 'MRT_sync_block_navigation_from_menu' ) ) {
		MRT_sync_block_navigation_from_menu( $menu_id );
	}
	return $added;
}
