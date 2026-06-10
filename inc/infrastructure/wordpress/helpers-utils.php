<?php

declare(strict_types=1);

/**
 * Generic WordPress infrastructure helpers.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/train-type/icons.php';

/**
 * Render alert HTML (error, info, warning)
 *
 * @param string $message Message text (will be escaped)
 * @param string $type 'error'|'info'|'warning'
 * @param string $extra_classes Optional extra CSS classes (e.g. 'mrt-empty')
 * @return string HTML
 */
function MRT_render_alert( string $message, string $type = 'error', string $extra_classes = '' ): string {
	$allowed_types = array( 'error', 'info', 'warning' );
	$type          = in_array( $type, $allowed_types, true ) ? $type : 'error';
	$classes       = 'mrt-alert mrt-alert-' . $type;
	if ( $extra_classes !== '' ) {
		$classes .= ' ' . esc_attr( $extra_classes );
	}
	$role = ( $type === 'info' ) ? 'status' : 'alert';

	return '<div class="' . $classes . '" role="' . esc_attr( $role ) . '">' . esc_html( $message ) . '</div>';
}

/**
 * Get post by title and post type
 *
 * @param string $title Post title
 * @param string $post_type Post type (e.g., 'mrt_station', 'mrt_service')
 * @return WP_Post|null Post object or null if not found
 */
function MRT_get_post_by_title( string $title, string $post_type ): ?WP_Post {
	if ( $title === '' || $post_type === '' ) {
		return null;
	}
	$query = new WP_Query(
		array(
			'post_type'              => $post_type,
			'title'                  => sanitize_text_field( $title ),
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		)
	);
	if ( ! $query->have_posts() ) {
		return null;
	}
	return $query->posts[0];
}

/**
 * Check for database errors and log if WP_DEBUG is enabled
 *
 * @param string $context Context string for error logging (e.g., function name)
 * @return bool True if error occurred, false otherwise
 */
function MRT_check_db_error( string $context = '' ): bool {
	global $wpdb;
	if ( $wpdb->last_error ) {
		$message = 'Database error';
		if ( $context !== '' ) {
			$message .= ' in ' . $context;
		}
		$message .= ': ' . $wpdb->last_error;
		MRT_log( $message );
		return true;
	}
	return false;
}
