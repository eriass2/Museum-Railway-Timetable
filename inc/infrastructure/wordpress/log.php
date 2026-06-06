<?php
/**
 * Development logging helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether plugin debug logging is enabled.
 *
 * True when WP_DEBUG or WP_DEBUG_LOG is on. Filter: mrt_should_log.
 */
function MRT_should_log(): bool {
	$enabled = ( defined( 'WP_DEBUG' ) && WP_DEBUG )
		|| ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG );

	return (bool) apply_filters( 'mrt_should_log', $enabled );
}

/**
 * Write a line to the PHP error log when debug logging is enabled.
 *
 * @param array<string, mixed> $context
 */
function MRT_log( string $message, array $context = array(), string $level = 'error' ): void {
	if ( ! MRT_should_log() ) {
		return;
	}

	$level = sanitize_key( $level );
	if ( $level === '' ) {
		$level = 'error';
	}

	$line = 'MRT [' . $level . '] ' . $message;
	if ( $context !== array() ) {
		$encoded = wp_json_encode( $context, JSON_UNESCAPED_UNICODE );
		if ( is_string( $encoded ) ) {
			$line .= ' ' . $encoded;
		}
	}

	error_log( $line );
}

/**
 * Log a WP_Error with code and data payload.
 */
function MRT_log_wp_error( string $where, WP_Error $error, string $level = 'error' ): void {
	MRT_log(
		$where . ': ' . $error->get_error_message(),
		array(
			'code' => $error->get_error_code(),
			'data' => $error->get_error_data(),
		),
		$level
	);
}
