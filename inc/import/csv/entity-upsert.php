<?php
/**
 * Upsert WordPress posts by stable CSV code.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find or create a post by code meta and set its title.
 *
 * @param callable(int $post_id): void|null $on_update Runs before title update on existing posts.
 * @return int Post ID, or 0 on failure.
 */
function MRT_csv_upsert_post_by_code(
	string $code,
	string $post_type,
	string $meta_key,
	string $title,
	?callable $on_update = null
): int {
	$id = MRT_csv_find_post_by_code( $code, $post_type, $meta_key );
	if ( $id <= 0 ) {
		$result = wp_insert_post(
			array(
				'post_type'   => $post_type,
				'post_title'  => $title,
				'post_status' => 'publish',
			)
		);
		if ( ! $result || $result instanceof WP_Error ) {
			return 0;
		}
		return (int) $result;
	}

	if ( $on_update !== null ) {
		$on_update( $id );
	}
	$result = wp_update_post(
		array(
			'ID'         => $id,
			'post_title' => $title,
		)
	);
	if ( $result instanceof WP_Error ) {
		return 0;
	}
	return (int) $id;
}
