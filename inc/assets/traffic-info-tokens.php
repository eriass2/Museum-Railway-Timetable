<?php
/**
 * Enqueue traffic info feed design tokens (UL layout).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $after_handle Optional style handle to load after.
 */
function MRT_enqueue_traffic_info_tokens( string $after_handle = '' ): void {
	$deps = $after_handle !== '' ? array( $after_handle ) : array();
	wp_enqueue_style(
		'mrt-traffic-info-tokens',
		MRT_URL . 'assets/mrt-traffic-info-tokens.css',
		$deps,
		MRT_VERSION
	);
	wp_enqueue_style(
		'mrt-traffic-info-layout',
		MRT_URL . 'assets/mrt-traffic-info-layout.css',
		array( 'mrt-traffic-info-tokens' ),
		MRT_VERSION
	);
}
