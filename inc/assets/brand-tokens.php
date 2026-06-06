<?php
/**
 * Optional operator brand CSS (Lennakatten profile).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether to enqueue Lennakatten colour/typography overrides on public UI.
 *
 * Default: MRT_LENNAKATTEN_BRAND constant in wp-config.php.
 * Filter: mrt_use_lennakatten_brand_tokens
 */
function MRT_use_lennakatten_brand_tokens(): bool {
	$default = defined( 'MRT_LENNAKATTEN_BRAND' ) && MRT_LENNAKATTEN_BRAND;
	return (bool) apply_filters( 'mrt_use_lennakatten_brand_tokens', $default );
}

/**
 * Enqueue optional brand token overrides after the Vue public bundle CSS.
 *
 * @param string $after_handle Style handle to load after (last Vue CSS chunk).
 */
function MRT_enqueue_brand_token_overrides( string $after_handle = '' ): void {
	if ( ! MRT_use_lennakatten_brand_tokens() ) {
		return;
	}
	$deps = $after_handle !== '' ? array( $after_handle ) : array();
	wp_enqueue_style(
		'mrt-brand-lennakatten-colors',
		MRT_URL . 'assets/brand/lennakatten-color-tokens.css',
		$deps,
		MRT_VERSION
	);
	wp_enqueue_style(
		'mrt-brand-lennakatten-typography',
		MRT_URL . 'assets/brand/lennakatten-typography.css',
		array( 'mrt-brand-lennakatten-colors' ),
		MRT_VERSION
	);
}
