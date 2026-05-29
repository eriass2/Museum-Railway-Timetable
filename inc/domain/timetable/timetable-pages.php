<?php
/**
 * Public WordPress pages for timetables (index + one page per timetable).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option: post ID for the timetables index page */
define( 'MRT_OPTION_TIMETABLES_INDEX_PAGE_ID', 'mrt_timetables_index_page_id' );

/** Post meta on mrt_timetable: linked public page ID */
define( 'MRT_META_TIMETABLE_PAGE_ID', 'mrt_timetable_page_id' );

/**
 * @return WP_Post[]
 */
function MRT_get_published_timetables(): array {
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_TIMETABLE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	return is_array( $posts ) ? $posts : array();
}

/**
 * Human-readable label for lists (title + traffic-day hint).
 */
function MRT_timetable_public_label( WP_Post $timetable ): string {
	$timetable_id = (int) $timetable->ID;
	$title        = get_the_title( $timetable );
	if ( $title === '' ) {
		$title = __( 'Timetable', 'museum-railway-timetable' ) . ' #' . $timetable_id;
	}
	$dates = MRT_get_timetable_dates( $timetable_id );
	if ( $dates === array() ) {
		return $title;
	}
	$first = date_i18n( get_option( 'date_format' ), strtotime( $dates[0] ) );
	if ( count( $dates ) === 1 ) {
		return $title . ' (' . $first . ')';
	}
	return $title . ' (' . $first . ' + ' . ( count( $dates ) - 1 ) . ' ' . __( 'more', 'museum-railway-timetable' ) . ')';
}

/**
 * URL slug for a single-timetable public page.
 */
function MRT_timetable_public_page_slug( int $timetable_id, string $title ): string {
	$code = (string) get_post_meta( $timetable_id, 'mrt_timetable_code', true );
	$base = $code !== '' ? $code : $title;
	$slug = sanitize_title( $base );
	if ( $slug === '' ) {
		$slug = 'tidtabell-' . $timetable_id;
	}
	return 'tidtabell-' . $slug;
}

/**
 * Linked public page ID for one timetable.
 */
function MRT_timetable_public_page_id( int $timetable_id ): int {
	if ( $timetable_id <= 0 ) {
		return 0;
	}
	return (int) get_post_meta( $timetable_id, MRT_META_TIMETABLE_PAGE_ID, true );
}

/**
 * Permalink for a timetable public page, or empty string.
 */
function MRT_timetable_public_page_url( int $timetable_id ): string {
	$page_id = MRT_timetable_public_page_id( $timetable_id );
	if ( $page_id <= 0 || ! get_post( $page_id ) ) {
		return '';
	}
	$url = get_permalink( $page_id );
	return is_string( $url ) ? $url : '';
}

/**
 * Index page post content.
 */
function MRT_timetables_index_page_content(): string {
	return '[museum_timetable_index]';
}

/**
 * Single timetable page post content.
 */
function MRT_timetable_single_page_content( int $timetable_id ): string {
	return sprintf( '[museum_timetable_overview timetable_id="%d"]', $timetable_id );
}

/**
 * Create or update a published page stored in an option key.
 *
 * @return int|WP_Error
 */
function MRT_ensure_option_backed_page(
	string $option_key,
	string $title,
	string $content,
	string $post_name = ''
) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'mrt_cap', __( 'Permission denied.', 'museum-railway-timetable' ) );
	}

	$post_id = (int) get_option( $option_key, 0 );
	$postarr = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_content' => $content,
	);
	if ( $post_name !== '' ) {
		$postarr['post_name'] = $post_name;
	}

	if ( $post_id > 0 && get_post( $post_id ) && get_post_type( $post_id ) === 'page' ) {
		$postarr['ID'] = $post_id;
		$result        = wp_update_post( wp_slash( $postarr ), true );
	} else {
		$result = wp_insert_post( wp_slash( $postarr ), true );
		if ( ! is_wp_error( $result ) ) {
			update_option( $option_key, (int) $result );
		}
	}

	return $result;
}

/**
 * Create or update the public page for one timetable.
 *
 * @return int|WP_Error
 */
function MRT_ensure_timetable_public_page( WP_Post $timetable ) {
	$timetable_id = (int) $timetable->ID;
	if ( $timetable_id <= 0 || $timetable->post_type !== MRT_POST_TYPE_TIMETABLE ) {
		return new WP_Error( 'mrt_invalid', __( 'Invalid timetable.', 'museum-railway-timetable' ) );
	}

	$page_id  = MRT_timetable_public_page_id( $timetable_id );
	$postarr  = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => get_the_title( $timetable ),
		'post_name'    => MRT_timetable_public_page_slug( $timetable_id, get_the_title( $timetable ) ),
		'post_content' => MRT_timetable_single_page_content( $timetable_id ),
	);
	if ( $page_id > 0 && get_post( $page_id ) && get_post_type( $page_id ) === 'page' ) {
		$postarr['ID'] = $page_id;
		$result        = wp_update_post( wp_slash( $postarr ), true );
	} else {
		$result = wp_insert_post( wp_slash( $postarr ), true );
	}
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	update_post_meta( $timetable_id, MRT_META_TIMETABLE_PAGE_ID, (int) $result );
	return (int) $result;
}

/**
 * Create/update index page and one page per published timetable.
 *
 * @return array{index_page_id: int, timetable_page_ids: array<int, int>, errors: WP_Error[]}|WP_Error
 */
function MRT_sync_timetable_public_pages() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'mrt_cap', __( 'Permission denied.', 'museum-railway-timetable' ) );
	}

	$errors = array();
	$index  = MRT_ensure_option_backed_page(
		MRT_OPTION_TIMETABLES_INDEX_PAGE_ID,
		__( 'Tidtabeller', 'museum-railway-timetable' ),
		MRT_timetables_index_page_content(),
		'tidtabeller'
	);
	if ( is_wp_error( $index ) ) {
		return $index;
	}

	$timetable_page_ids = array();
	foreach ( MRT_get_published_timetables() as $timetable ) {
		$result = MRT_ensure_timetable_public_page( $timetable );
		if ( is_wp_error( $result ) ) {
			$errors[] = $result;
			continue;
		}
		$timetable_page_ids[ (int) $timetable->ID ] = (int) $result;
	}

	if ( function_exists( 'MRT_append_timetables_index_to_nav_menu' ) ) {
		MRT_append_timetables_index_to_nav_menu( (int) $index );
	}

	return array(
		'index_page_id'       => (int) $index,
		'timetable_page_ids'  => $timetable_page_ids,
		'errors'              => $errors,
	);
}

/**
 * Delete index and per-timetable public pages created by the plugin.
 */
function MRT_clear_timetable_public_pages(): void {
	$index_id = (int) get_option( MRT_OPTION_TIMETABLES_INDEX_PAGE_ID, 0 );
	if ( $index_id > 0 && get_post( $index_id ) ) {
		wp_delete_post( $index_id, true );
	}
	delete_option( MRT_OPTION_TIMETABLES_INDEX_PAGE_ID );

	foreach ( MRT_get_published_timetables() as $timetable ) {
		$page_id = MRT_timetable_public_page_id( (int) $timetable->ID );
		if ( $page_id > 0 && get_post( $page_id ) ) {
			wp_delete_post( $page_id, true );
		}
		delete_post_meta( (int) $timetable->ID, MRT_META_TIMETABLE_PAGE_ID );
	}
}
