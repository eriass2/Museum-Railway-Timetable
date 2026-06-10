<?php
/**
 * Look up and store stable CSV codes on posts.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find a post by stored CSV code meta.
 */
function MRT_csv_find_post_by_code( string $code, string $post_type, string $meta_key ): int {
	if ( $code === '' ) {
		return 0;
	}
	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => $meta_key,
					'value' => $code,
				),
			),
		)
	);
	return isset( $posts[0] ) ? (int) $posts[0] : 0;
}

/**
 * Collect all codes currently stored for one entity type.
 *
 * @return array<string, bool>
 */
function MRT_csv_collect_db_codes( string $post_type, string $meta_key ): array {
	$ids = get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	$out = array();
	foreach ( $ids as $id ) {
		$code = (string) get_post_meta( (int) $id, $meta_key, true );
		if ( $code !== '' ) {
			$out[ $code ] = true;
		}
	}
	return $out;
}

/**
 * Build existing code map for partial CSV validation.
 *
 * @return array<string, array<string, bool>>
 */
function MRT_csv_existing_codes_from_db(): array {
	$keys = MRT_csv_code_meta_keys();
	return array(
		'stations'   => MRT_csv_collect_db_codes( MRT_POST_TYPE_STATION, $keys['stations'] ),
		'routes'     => MRT_csv_collect_db_codes( MRT_POST_TYPE_ROUTE, $keys['routes'] ),
		'timetables' => MRT_csv_collect_db_codes( MRT_POST_TYPE_TIMETABLE, $keys['timetables'] ),
		'services'   => MRT_csv_collect_db_codes( MRT_POST_TYPE_SERVICE, $keys['services'] ),
	);
}

/**
 * Persist code meta on a post.
 */
function MRT_csv_save_post_code( int $post_id, string $meta_key, string $code ): void {
	if ( $code !== '' ) {
		update_post_meta( $post_id, $meta_key, $code );
	}
}
