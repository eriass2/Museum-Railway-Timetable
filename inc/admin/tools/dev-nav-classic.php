<?php
/**
 * Classic theme nav menu helpers for development smoke pages.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme location slugs to try for the site menu (classic themes).
 *
 * @return string[]
 */
function MRT_dev_nav_location_candidates(): array {
	return array( 'primary', 'menu-1', 'header', 'main' );
}

/**
 * Nav menu ID assigned to the first matching theme location.
 *
 * @return int 0 if none.
 */
function MRT_get_assigned_nav_menu_id_for_theme(): int {
	$locations = get_nav_menu_locations();
	if ( ! is_array( $locations ) ) {
		return 0;
	}
	foreach ( MRT_dev_nav_location_candidates() as $loc ) {
		if ( ! empty( $locations[ $loc ] ) ) {
			return (int) $locations[ $loc ];
		}
	}
	return 0;
}

/**
 * Whether a nav menu already links to a page.
 */
function MRT_nav_menu_contains_page( int $menu_id, int $page_id ): bool {
	foreach ( MRT_get_nav_menu_page_items( $menu_id ) as $item ) {
		if ( (int) $item->object_id === $page_id ) {
			return true;
		}
	}
	return false;
}

/**
 * Remove classic menu links to pages that no longer exist.
 *
 * @return int Items removed.
 */
function MRT_remove_broken_nav_menu_page_items( int $menu_id ): int {
	return MRT_remove_nav_menu_page_items_where(
		$menu_id,
		static function ( int $page_id ): bool {
			return $page_id <= 0 || ! get_post( $page_id );
		}
	);
}

/**
 * Remove nav menu items that point at given pages.
 *
 * @param int[] $page_ids Page post IDs.
 * @return int Items removed.
 */
function MRT_remove_pages_from_nav_menu( int $menu_id, array $page_ids ): int {
	if ( $page_ids === array() ) {
		return 0;
	}
	$lookup = array_fill_keys( array_map( 'intval', $page_ids ), true );
	return MRT_remove_nav_menu_page_items_where(
		$menu_id,
		static function ( int $page_id ) use ( $lookup ): bool {
			return isset( $lookup[ $page_id ] );
		}
	);
}

/**
 * Keep one menu item per page; drop duplicate links.
 *
 * @param int[] $page_ids Page post IDs.
 * @return int Duplicates removed.
 */
function MRT_dedupe_nav_menu_page_links( int $menu_id, array $page_ids ): int {
	if ( $page_ids === array() ) {
		return 0;
	}
	$lookup = array_fill_keys( array_map( 'intval', $page_ids ), true );
	$seen   = array();
	return MRT_remove_nav_menu_page_items_where(
		$menu_id,
		static function ( int $page_id ) use ( $lookup, &$seen ): bool {
			if ( ! isset( $lookup[ $page_id ] ) ) {
				return false;
			}
			if ( isset( $seen[ $page_id ] ) ) {
				return true;
			}
			$seen[ $page_id ] = true;
			return false;
		}
	);
}

/**
 * Page IDs for per-component debug screens (not linked in front menu).
 *
 * @return int[]
 */
function MRT_debug_page_ids(): array {
	if ( ! function_exists( 'MRT_component_debug_page_specs' ) ) {
		return array();
	}
	return MRT_option_backed_page_ids_from_specs( MRT_component_debug_page_specs() );
}

/**
 * Create a dev nav menu and assign it to primary when no menu is assigned.
 *
 * @return int|WP_Error Menu term ID.
 */
function MRT_get_or_create_dev_nav_menu() {
	$stored = (int) get_option( MRT_OPTION_DEV_NAV_MENU_ID, 0 );
	if ( $stored > 0 && wp_get_nav_menu_object( $stored ) ) {
		return $stored;
	}

	$menu_id = wp_create_nav_menu( MRT_dev_nav_menu_title() );
	if ( is_wp_error( $menu_id ) ) {
		return $menu_id;
	}
	update_option( MRT_OPTION_DEV_NAV_MENU_ID, (int) $menu_id );
	return (int) $menu_id;
}

/**
 * Assign menu to primary theme location when empty.
 */
function MRT_assign_dev_menu_to_primary_if_unassigned( int $menu_id ): void {
	if ( MRT_get_assigned_nav_menu_id_for_theme() > 0 ) {
		return;
	}
	$locations = get_nav_menu_locations();
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}
