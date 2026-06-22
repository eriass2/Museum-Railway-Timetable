<?php
/**
 * Enqueue traffic info feed design tokens (UL layout + optional Lennakatten profile).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether to load Lennakatten traffic feed token overrides.
 *
 * Uses MRT_LENNAKATTEN_BRAND even when colour tokens come from CSV import
 * (import disables MRT_use_lennakatten_brand_tokens() static pack only).
 */
function MRT_use_lennakatten_traffic_info_tokens(): bool {
	$filtered = apply_filters( 'mrt_use_lennakatten_traffic_info_tokens', null );
	if ( $filtered !== null ) {
		return (bool) $filtered;
	}
	if ( function_exists( 'MRT_is_lennakatten_brand_enabled' ) && MRT_is_lennakatten_brand_enabled() ) {
		return true;
	}
	return function_exists( 'MRT_use_lennakatten_brand_tokens' ) && MRT_use_lennakatten_brand_tokens();
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
	MRT_enqueue_lennakatten_traffic_info_tokens();
}

/**
 * Lennakatten profile overrides (--mrt-tf-* + active category contrast).
 */
function MRT_enqueue_lennakatten_traffic_info_tokens(): void {
	if ( ! MRT_use_lennakatten_traffic_info_tokens() ) {
		return;
	}

	$color_deps = array( 'mrt-traffic-info-tokens' );
	if ( function_exists( 'MRT_has_imported_brand_tokens' ) && MRT_has_imported_brand_tokens() ) {
		// Imported CSV tokens on :root — no static color pack required.
	} elseif ( wp_style_is( 'mrt-brand-lennakatten-colors', 'enqueued' )
		|| wp_style_is( 'mrt-brand-lennakatten-colors', 'done' ) ) {
		$color_deps[] = 'mrt-brand-lennakatten-colors';
	} else {
		wp_enqueue_style(
			'mrt-brand-lennakatten-colors',
			MRT_URL . 'assets/brand/lennakatten-color-tokens.css',
			array(),
			MRT_VERSION
		);
		$color_deps[] = 'mrt-brand-lennakatten-colors';
	}

	wp_enqueue_style(
		'mrt-brand-lennakatten-traffic-info',
		MRT_URL . 'assets/brand/lennakatten-traffic-info-tokens.css',
		$color_deps,
		MRT_VERSION
	);
}
