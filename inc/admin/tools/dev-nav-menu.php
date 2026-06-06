<?php
/**
 * Shared classic nav menu helpers (development smoke pages).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display title for the development navigation menu / wp_navigation post.
 */
function MRT_dev_nav_menu_title(): string {
	return __( 'Museijärnväg (utveckling)', 'museum-railway-timetable' );
}

/**
 * Resolve a live page ID from an option key.
 */
function MRT_resolve_option_backed_page_id( string $option_key ): int {
	$page_id = (int) get_option( $option_key, 0 );
	if ( $page_id > 0 && get_post( $page_id ) ) {
		return $page_id;
	}
	return 0;
}

/**
 * @param array<int, array{option: string}> $specs
 * @return int[]
 */
function MRT_option_backed_page_ids_from_specs( array $specs ): array {
	$ids = array();
	foreach ( $specs as $spec ) {
		$page_id = MRT_resolve_option_backed_page_id( $spec['option'] );
		if ( $page_id > 0 ) {
			$ids[] = $page_id;
		}
	}
	return $ids;
}

/**
 * @return array<int, object>
 */
function MRT_get_nav_menu_page_items( int $menu_id ): array {
	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return array();
	}
	$page_items = array();
	foreach ( $items as $item ) {
		if ( $item->object === 'page' ) {
			$page_items[] = $item;
		}
	}
	return $page_items;
}

/**
 * @param callable(int $page_id, object $item): bool $should_remove
 * @return int Items removed.
 */
function MRT_remove_nav_menu_page_items_where( int $menu_id, callable $should_remove ): int {
	$removed = 0;
	foreach ( MRT_get_nav_menu_page_items( $menu_id ) as $item ) {
		$page_id = (int) $item->object_id;
		if ( ! $should_remove( $page_id, $item ) ) {
			continue;
		}
		if ( wp_delete_post( (int) $item->ID, true ) ) {
			++$removed;
		}
	}
	return $removed;
}

/**
 * Prune broken links, debug pages, and duplicate smoke links (single pass).
 *
 * @param int[] $smoke_page_ids Pages that may appear once in the menu.
 * @param int[] $exclude_page_ids Pages that must not appear in the menu.
 * @return int Items removed.
 */
function MRT_normalize_dev_nav_menu( int $menu_id, array $smoke_page_ids, array $exclude_page_ids ): int {
	$smoke_lookup   = array_fill_keys( array_map( 'intval', $smoke_page_ids ), true );
	$exclude_lookup = array_fill_keys( array_map( 'intval', $exclude_page_ids ), true );
	$seen_smoke     = array();

	return MRT_remove_nav_menu_page_items_where(
		$menu_id,
		static function ( int $page_id ) use ( $smoke_lookup, $exclude_lookup, &$seen_smoke ): bool {
			if ( $page_id <= 0 || ! get_post( $page_id ) ) {
				return true;
			}
			if ( isset( $exclude_lookup[ $page_id ] ) ) {
				return true;
			}
			if ( ! isset( $smoke_lookup[ $page_id ] ) ) {
				return false;
			}
			if ( isset( $seen_smoke[ $page_id ] ) ) {
				return true;
			}
			$seen_smoke[ $page_id ] = true;
			return false;
		}
	);
}

/**
 * Append a page link when missing.
 */
function MRT_append_nav_menu_page_if_missing( int $menu_id, int $page_id, string $label ): bool {
	if ( $page_id <= 0 || ! get_post( $page_id ) ) {
		return false;
	}
	if ( MRT_nav_menu_contains_page( $menu_id, $page_id ) ) {
		return false;
	}
	$result = wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => $label,
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
	return ! is_wp_error( $result );
}

/**
 * Normalize dev menu and append smoke pages (idempotent).
 *
 * @return int Number of items added.
 */
function MRT_sync_dev_smoke_pages_to_nav_menu( int $menu_id ): int {
	$smoke_ids = MRT_dev_smoke_page_ids();
	MRT_normalize_dev_nav_menu( $menu_id, $smoke_ids, MRT_debug_page_ids() );

	$added = 0;
	foreach ( MRT_dev_smoke_page_specs() as $spec ) {
		$page_id = MRT_resolve_option_backed_page_id( $spec['option'] );
		if ( MRT_append_nav_menu_page_if_missing( $menu_id, $page_id, $spec['menu_label'] ) ) {
			++$added;
		}
	}
	return $added;
}
