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
	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return false;
	}
	foreach ( $items as $item ) {
		if ( $item->object === 'page' && (int) $item->object_id === $page_id ) {
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
	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return 0;
	}
	$removed = 0;
	foreach ( $items as $item ) {
		if ( $item->object !== 'page' ) {
			continue;
		}
		$page_id = (int) $item->object_id;
		if ( $page_id > 0 && get_post( $page_id ) ) {
			continue;
		}
		if ( wp_delete_post( (int) $item->ID, true ) ) {
			++$removed;
		}
	}
	return $removed;
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
	$items  = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return 0;
	}
	$removed = 0;
	foreach ( $items as $item ) {
		if ( $item->object !== 'page' ) {
			continue;
		}
		if ( ! isset( $lookup[ (int) $item->object_id ] ) ) {
			continue;
		}
		if ( wp_delete_post( (int) $item->ID, true ) ) {
			++$removed;
		}
	}
	return $removed;
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
	$items  = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return 0;
	}
	$seen    = array();
	$removed = 0;
	foreach ( $items as $item ) {
		if ( $item->object !== 'page' ) {
			continue;
		}
		$page_id = (int) $item->object_id;
		if ( ! isset( $lookup[ $page_id ] ) ) {
			continue;
		}
		if ( isset( $seen[ $page_id ] ) ) {
			if ( wp_delete_post( (int) $item->ID, true ) ) {
				++$removed;
			}
			continue;
		}
		$seen[ $page_id ] = true;
	}
	return $removed;
}

/**
 * Append smoke pages to a nav menu (idempotent).
 *
 * @return int Number of items added.
 */
function MRT_append_smoke_pages_to_nav_menu( int $menu_id ): int {
	$added = 0;
	foreach ( MRT_dev_smoke_page_specs() as $spec ) {
		$page_id = (int) get_option( $spec['option'], 0 );
		if ( $page_id <= 0 || ! get_post( $page_id ) ) {
			continue;
		}
		if ( MRT_nav_menu_contains_page( $menu_id, $page_id ) ) {
			continue;
		}
		$result = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $spec['menu_label'],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
		if ( ! is_wp_error( $result ) ) {
			++$added;
		}
	}
	return $added;
}

/**
 * Page IDs for per-component debug screens (not linked in front menu).
 *
 * @return int[]
 */
function MRT_debug_page_ids(): array {
	$ids = array();
	if ( ! function_exists( 'MRT_component_debug_page_specs' ) ) {
		return $ids;
	}
	foreach ( MRT_component_debug_page_specs() as $spec ) {
		$page_id = (int) get_option( $spec['option'], 0 );
		if ( $page_id > 0 && get_post( $page_id ) ) {
			$ids[] = $page_id;
		}
	}
	return $ids;
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

	$menu_name = __( 'Museijärnväg (utveckling)', 'museum-railway-timetable' );
	$menu_id   = wp_create_nav_menu( $menu_name );
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
