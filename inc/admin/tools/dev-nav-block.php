<?php
/**
 * Block theme wp_navigation sync for development menus.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Serialized navigation-link block for block themes.
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

	$nav_id = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => 'wp_navigation',
				'post_status'  => 'publish',
				'post_title'   => MRT_dev_nav_menu_title(),
				'post_name'    => 'museum-railway-development',
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
 * Sync block-theme navigation from the classic dev menu.
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
