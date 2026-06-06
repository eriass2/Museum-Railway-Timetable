<?php
/**
 * Development smoke pages and front-end navigation setup.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option: wizard-only smoke page ID */
define( 'MRT_OPTION_WIZARD_SMOKE_PAGE_ID', 'mrt_wizard_smoke_page_id' );

/** Option: nav menu ID used for dev links (may match site primary) */
define( 'MRT_OPTION_DEV_NAV_MENU_ID', 'mrt_dev_nav_menu_id' );

/** Option: block-theme navigation post (wp_navigation) synced from the dev menu */
define( 'MRT_OPTION_DEV_WP_NAVIGATION_ID', 'mrt_dev_wp_navigation_id' );

/**
 * Smoke page definitions (content callback for component demo).
 *
 * @return array<int, array{option: string, title: string, menu_label: string, content: string|callable}>
 */
function MRT_dev_smoke_page_specs(): array {
	$tt = function_exists( 'MRT_demo_lennakatten_timetable_title' )
		? MRT_demo_lennakatten_timetable_title()
		: 'GRÖN TIDTABELL 2026';

	return array(
		array(
			'option'     => MRT_OPTION_COMPONENTS_DEMO_PAGE_ID,
			'title'      => __( 'Museijärnvägens tidtabell – komponentdemo', 'museum-railway-timetable' ),
			'menu_label' => __( 'Komponentdemo', 'museum-railway-timetable' ),
			'content'    => static function (): string {
				return MRT_get_components_demo_page_content();
			},
		),
		array(
			'option'     => MRT_OPTION_WIZARD_SMOKE_PAGE_ID,
			'title'      => __( 'Wizard-smoketest', 'museum-railway-timetable' ),
			'menu_label' => __( 'Wizard-smoketest', 'museum-railway-timetable' ),
			'content'    => sprintf(
				'[museum_journey_wizard timetable="%s"]',
				esc_attr( $tt )
			),
		),
	);
}

/**
 * Create or update all development smoke pages.
 *
 * @return array{page_ids: int[], errors: WP_Error[]}
 */
function MRT_ensure_dev_smoke_pages(): array {
	$page_ids = array();
	$errors   = array();

	foreach ( MRT_dev_smoke_page_specs() as $spec ) {
		$result = MRT_ensure_option_backed_page( $spec['option'], $spec['title'], $spec['content'] );
		if ( is_wp_error( $result ) ) {
			$errors[] = $result;
			continue;
		}
		$page_ids[] = (int) $result;
	}

	$debug_pages = MRT_ensure_component_debug_pages();
	foreach ( $debug_pages['errors'] as $error ) {
		$errors[] = $error;
	}
	$page_ids = array_merge( $page_ids, $debug_pages['page_ids'] );

	return array(
		'page_ids' => $page_ids,
		'errors'   => $errors,
	);
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
 *
 * @param int $menu_id Nav menu term ID.
 * @param int $page_id Page ID.
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
 * Remove classic menu links to pages that no longer exist (e.g. after dev reset).
 *
 * @param int $menu_id Nav menu term ID.
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
 * Serialized navigation-link block for block themes (Twenty Twenty-Five header).
 */
function MRT_build_navigation_link_block( int $page_id, string $label ): string {
	$url = get_permalink( $page_id );
	if ( ! is_string( $url ) || $url === '' ) {
		return '';
	}
	$attrs = array(
		'className'     => ' menu-item menu-item-type-post_type menu-item-object-page',
		'description'   => '',
		'id'            => (string) $page_id,
		'kind'          => 'post-type',
		'label'         => $label,
		'opensInNewTab' => false,
		'rel'           => null,
		'title'         => '',
		'type'          => 'page',
		'url'           => $url,
	);
	$json = wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	return '<!-- wp:navigation-link ' . $json . ' /-->';
}

/**
 * Build wp_navigation post content from a classic nav menu.
 */
function MRT_build_block_navigation_content_from_menu( int $menu_id ): string {
	$items = wp_get_nav_menu_items( $menu_id, array( 'orderby' => 'menu_order' ) );
	if ( ! is_array( $items ) ) {
		return '';
	}
	$blocks = array();
	foreach ( $items as $item ) {
		if ( $item->type !== 'post_type' || $item->object !== 'page' ) {
			continue;
		}
		$page_id = (int) $item->object_id;
		if ( $page_id <= 0 || ! get_post( $page_id ) ) {
			continue;
		}
		$block = MRT_build_navigation_link_block( $page_id, (string) $item->title );
		if ( $block !== '' ) {
			$blocks[] = $block;
		}
	}
	return implode( '', $blocks );
}

/**
 * @return int|WP_Error wp_navigation post ID.
 */
function MRT_get_or_create_dev_wp_navigation_post() {
	$stored = (int) get_option( MRT_OPTION_DEV_WP_NAVIGATION_ID, 0 );
	if ( $stored > 0 && get_post( $stored ) && get_post_type( $stored ) === 'wp_navigation' ) {
		return $stored;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'wp_navigation',
			'name'           => 'museum-railway-development',
			'posts_per_page' => 1,
			'post_status'    => array( 'publish', 'draft' ),
		)
	);
	if ( $existing !== array() ) {
		$nav_id = (int) $existing[0]->ID;
		update_option( MRT_OPTION_DEV_WP_NAVIGATION_ID, $nav_id );
		return $nav_id;
	}

	$menu_name = __( 'Museijärnväg (utveckling)', 'museum-railway-timetable' );
	$nav_id    = wp_insert_post(
		wp_slash(
			array(
				'post_type'   => 'wp_navigation',
				'post_status' => 'publish',
				'post_title'  => $menu_name,
				'post_name'   => 'museum-railway-development',
				'post_content' => '',
			)
		),
		true
	);
	if ( is_wp_error( $nav_id ) ) {
		return $nav_id;
	}
	update_option( MRT_OPTION_DEV_WP_NAVIGATION_ID, (int) $nav_id );
	return (int) $nav_id;
}

/**
 * Sync block-theme navigation (wp_navigation) from the classic dev menu.
 *
 * @return bool True when content was updated.
 */
function MRT_sync_block_navigation_from_menu( int $menu_id ): bool {
	if ( $menu_id <= 0 || ! post_type_exists( 'wp_navigation' ) ) {
		return false;
	}
	$content = MRT_build_block_navigation_content_from_menu( $menu_id );
	if ( $content === '' ) {
		return false;
	}
	$nav_post = MRT_get_or_create_dev_wp_navigation_post();
	if ( is_wp_error( $nav_post ) ) {
		return false;
	}
	$result = wp_update_post(
		wp_slash(
			array(
				'ID'           => (int) $nav_post,
				'post_content' => $content,
			)
		),
		true
	);
	return ! is_wp_error( $result );
}

/**
 * Append smoke pages to a nav menu (idempotent). Debug pages stay admin-only.
 *
 * @param int $menu_id Nav menu term ID.
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
 * Remove nav menu items that point at given pages.
 *
 * @param int   $menu_id  Nav menu term ID.
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
 * Keep one menu item per page; drop duplicate links (e.g. after re-create).
 *
 * @param int   $menu_id  Nav menu term ID.
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
 * Smoke page IDs used for front-menu sync.
 *
 * @return int[]
 */
function MRT_dev_smoke_page_ids(): array {
	$ids = array();
	foreach ( MRT_dev_smoke_page_specs() as $spec ) {
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
 *
 * @param int $menu_id Nav menu term ID.
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

	$smoke_ids = MRT_dev_smoke_page_ids();
	MRT_remove_broken_nav_menu_page_items( $menu_id );
	MRT_remove_pages_from_nav_menu( $menu_id, MRT_debug_page_ids() );
	MRT_dedupe_nav_menu_page_links( $menu_id, $smoke_ids );

	$added = MRT_append_smoke_pages_to_nav_menu( $menu_id );
	MRT_sync_block_navigation_from_menu( $menu_id );
	update_option( MRT_OPTION_DEV_NAV_MENU_ID, $menu_id );

	return array(
		'menu_id'  => $menu_id,
		'added'    => $added,
		'page_ids' => $pages['page_ids'],
	);
}

/**
 * Delete plugin-owned smoke + debug pages.
 */
function MRT_clear_dev_smoke_pages(): void {
	$keys = array(
		MRT_OPTION_COMPONENTS_DEMO_PAGE_ID,
		MRT_OPTION_WIZARD_SMOKE_PAGE_ID,
	);
	if ( function_exists( 'MRT_component_debug_page_specs' ) ) {
		foreach ( MRT_component_debug_page_specs() as $spec ) {
			$keys[] = $spec['option'];
		}
	}
	foreach ( $keys as $key ) {
		$page_id = (int) get_option( $key, 0 );
		if ( $page_id > 0 && get_post( $page_id ) && get_post_type( $page_id ) === 'page' ) {
			wp_delete_post( $page_id, true );
		}
		delete_option( $key );
	}
}
