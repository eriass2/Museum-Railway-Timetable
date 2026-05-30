<?php
/**
 * REST API permission callbacks.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read-only admin REST (dashboard, lists, preview).
 */
function MRT_rest_can_read(): bool {
	return current_user_can( 'edit_posts' ) || current_user_can( 'manage_options' );
}

/**
 * Full timetable data management (stations, routes, timetables, stop times bulk).
 */
function MRT_rest_can_manage(): bool {
	return current_user_can( 'manage_options' );
}

/**
 * Deviations and limited departure edits (edit_posts role).
 */
function MRT_rest_can_edit_operations(): bool {
	return MRT_rest_can_manage() || current_user_can( 'edit_posts' );
}

/**
 * Verify user may edit a specific post.
 *
 * @param int $post_id Post ID.
 */
function MRT_rest_can_edit_post( int $post_id ): bool {
	if ( $post_id <= 0 ) {
		return false;
	}
	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}
	return current_user_can( 'edit_post', $post_id );
}
