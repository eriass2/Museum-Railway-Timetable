<?php

declare(strict_types=1);

/**
 * Utility helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verify meta box save: nonce, autosave, permissions
 * Call at start of save_post_* handlers.
 *
 * @param int    $post_id Post ID
 * @param string $nonce_name $_POST key for nonce
 * @param string $nonce_action wp_verify_nonce action
 * @return bool True if save should proceed, false to abort
 */
function MRT_verify_meta_box_save( int $post_id, string $nonce_name, string $nonce_action ): bool {
	if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_name ] ) ), $nonce_action ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	return true;
}

/**
 * Verify AJAX permission to edit a specific post.
 *
 * @param int $post_id Post ID
 * @return void
 */
function MRT_verify_ajax_edit_post_permission( int $post_id ): void {
	if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', MRT_TEXT_DOMAIN ) ) );
	}
}

/**
 * Verify AJAX permission to delete a specific post.
 *
 * @param int $post_id Post ID
 * @return void
 */
function MRT_verify_ajax_delete_post_permission( int $post_id ): void {
	if ( $post_id <= 0 || ! current_user_can( 'delete_post', $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', MRT_TEXT_DOMAIN ) ) );
	}
}

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
	$type          = in_array( $type, $allowed_types ) ? $type : 'error';
	$classes       = 'mrt-alert mrt-alert-' . $type;
	if ( ! empty( $extra_classes ) ) {
		$classes .= ' ' . esc_attr( $extra_classes );
	}
	$role = ( $type === 'info' ) ? 'status' : 'alert';

	return '<div class="' . $classes . '" role="' . esc_attr( $role ) . '">' . esc_html( $message ) . '</div>';
}

/**
 * Render info box (title + content)
 *
 * @param string $title Box title (will be escaped)
 * @param string $content HTML content (sanitized with wp_kses_post for safe HTML)
 * @param string $extra_classes Optional extra CSS classes (e.g. 'mrt-mb-1')
 * @return void Outputs HTML
 */
function MRT_render_info_box( string $title, string $content, string $extra_classes = '' ): void {
	$classes = 'mrt-alert mrt-alert-info mrt-info-box';
	if ( ! empty( $extra_classes ) ) {
		$classes .= ' ' . esc_attr( $extra_classes );
	}
	echo '<div class="' . esc_attr( $classes ) . '">';
	echo '<p><strong>' . esc_html( $title ) . '</strong></p>';
	echo wp_kses_post( $content );
	echo '</div>';
}

/**
 * Get post by title and post type
 *
 * @param string $title Post title
 * @param string $post_type Post type (e.g., 'mrt_station', 'mrt_service')
 * @return WP_Post|null Post object or null if not found
 */
function MRT_get_post_by_title( string $title, string $post_type ): ?WP_Post {
	if ( empty( $title ) || empty( $post_type ) ) {
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
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$message = 'MRT: Database error';
			if ( $context ) {
				$message .= ' in ' . $context;
			}
			$message .= ': ' . $wpdb->last_error;
			error_log( $message );
		}
		return true;
	}
	return false;
}

/**
 * Log error message if WP_DEBUG is enabled
 *
 * @param string $message Error message to log
 * @return void
 */
function MRT_log_error( string $message ): void {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'MRT: ' . $message );
	}
}

/**
 * Supported train-type icon keys (assets/icons/train-types/{key}.png).
 *
 * @return array<int, string>
 */
function MRT_train_type_icon_keys(): array {
	return array( 'steam', 'diesel', 'railbus', 'bus' );
}

/**
 * Public URL for a train-type icon PNG.
 *
 * @param string $key steam|diesel|railbus|bus
 */
function MRT_train_type_icon_url( string $key ): string {
	if ( ! in_array( $key, MRT_train_type_icon_keys(), true ) ) {
		$key = 'diesel';
	}
	$relative = 'icons/train-types/' . $key . '.png';
	if ( ! file_exists( MRT_PATH . 'assets/' . $relative ) ) {
		$relative = 'icons/train-types/diesel.png';
	}
	return MRT_URL . 'assets/' . $relative;
}

/**
 * Icon URLs keyed by symbol (for script localization).
 *
 * @return array<string, string>
 */
function MRT_train_type_icon_urls(): array {
	$urls = array();
	foreach ( MRT_train_type_icon_keys() as $key ) {
		$urls[ $key ] = MRT_train_type_icon_url( $key );
	}
	return $urls;
}

/**
 * Resolve icon key from train type name and slug.
 */
function MRT_resolve_train_type_symbol_key( string $name, string $slug ): string {
	$name_lower = strtolower( $name );
	$slug_lower = strtolower( $slug );

	if ( str_contains( $name_lower, 'rälsbuss' ) || str_contains( $name_lower, 'railbus' ) || str_contains( $slug_lower, 'ralsbuss' ) ) {
		return 'railbus';
	}
	if ( $slug_lower === 'buss' || $name_lower === 'buss' ) {
		return 'bus';
	}
	if ( str_contains( $name_lower, 'ång' ) || str_contains( $slug_lower, 'steam' ) || str_contains( $slug_lower, 'ang' ) ) {
		return 'steam';
	}
	if ( str_contains( $name_lower, 'diesel' ) || str_contains( $slug_lower, 'diesel' ) ) {
		return 'diesel';
	}
	if ( str_contains( $name_lower, 'elektrisk' ) || str_contains( $name_lower, 'electric' ) || str_contains( $slug_lower, 'electric' ) ) {
		return 'diesel';
	}

	return 'diesel';
}

/**
 * Icon key for a train type term.
 */
function MRT_get_train_type_symbol_key( ?WP_Term $train_type ): string {
	if ( ! $train_type ) {
		return '';
	}
	return MRT_resolve_train_type_symbol_key( $train_type->name, $train_type->slug );
}

/**
 * Icon key from a free-text label (e.g. journey results).
 */
function MRT_get_train_type_symbol_key_from_label( string $label ): string {
	if ( $label === '' ) {
		return 'diesel';
	}
	return MRT_resolve_train_type_symbol_key( $label, sanitize_title( $label ) );
}

/**
 * <img> markup for a train-type icon.
 *
 * @param string $key    steam|diesel|railbus|bus
 * @param string $alt    Accessible label (empty when decorative)
 */
function MRT_train_type_icon_img( string $key, string $alt = '' ): string {
	if ( $key === '' ) {
		return '';
	}
	$class = 'mrt-train-type-icon-img mrt-train-type-icon-img--' . sanitize_html_class( $key );
	return sprintf(
		'<img src="%s" class="%s" width="48" height="24" decoding="async" alt="%s" />',
		esc_url( MRT_train_type_icon_url( $key ) ),
		esc_attr( $class ),
		esc_attr( $alt )
	);
}

/**
 * Icon HTML for a train type term (timetable grids, admin).
 *
 * @param WP_Term|null $train_type Train type term object
 * @return string Icon HTML or empty string
 */
function MRT_get_train_type_icon( ?WP_Term $train_type ): string {
	if ( ! $train_type ) {
		return '';
	}
	$key = MRT_get_train_type_symbol_key( $train_type );
	return MRT_train_type_icon_img( $key, $train_type->name );
}

/**
 * Convert time format from HH:MM to HH.MM
 *
 * @param string|null $time Time in HH:MM format or null
 * @return string Time in HH.MM format or empty string
 */
function MRT_format_time_display( ?string $time ): string {
	if ( empty( $time ) ) {
		return '';
	}
	return str_replace( ':', '.', $time );
}

/**
 * Format time display for a stop time
 * Determines the appropriate symbol (P, A, X, |) and formats the time
 *
 * @param array|null $stop_time Stop time data array with keys: arrival_time, departure_time, pickup_allowed, dropoff_allowed
 * @return string Formatted time display (e.g., "10.13", "P 10.13", "X", "|", "—")
 */
/**
 * Prefix (P/A) and time fragment for a stopping row.
 *
 * @return array{0: string, 1: string} [symbol_prefix, time_str]
 */
function MRT_stop_time_prefix_and_time_parts( array $stop_time ): array {
	$arrival         = $stop_time['arrival_time'] ?? '';
	$departure       = $stop_time['departure_time'] ?? '';
	$pickup_allowed  = ! empty( $stop_time['pickup_allowed'] );
	$dropoff_allowed = ! empty( $stop_time['dropoff_allowed'] );

	$symbol_prefix = '';
	if ( $pickup_allowed && ! $dropoff_allowed ) {
		$symbol_prefix = 'P ';
	} elseif ( ! $pickup_allowed && $dropoff_allowed ) {
		$symbol_prefix = 'A ';
	}

	if ( $departure ) {
		$time_str = $departure;
	} elseif ( $arrival ) {
		$time_str = $arrival;
	} elseif ( $pickup_allowed && $dropoff_allowed ) {
		return array( '', 'X' );
	} else {
		$time_str = '';
	}

	if ( $time_str !== '' && $time_str !== 'X' ) {
		$time_str = MRT_format_time_display( $time_str );
	}

	return array( $symbol_prefix, $time_str );
}

function MRT_format_stop_time_display( ?array $stop_time ): string {
	if ( ! $stop_time ) {
		return '—';
	}

	$pickup_allowed  = ! empty( $stop_time['pickup_allowed'] );
	$dropoff_allowed = ! empty( $stop_time['dropoff_allowed'] );
	$stops_here      = $pickup_allowed || $dropoff_allowed;

	if ( ! $stops_here ) {
		return '|';
	}

	[$symbol_prefix, $time_str] = MRT_stop_time_prefix_and_time_parts( $stop_time );

	return $symbol_prefix . $time_str;
}
