<?php
/**
 * Operator brand CSS (optional Lennakatten pack or CSV-imported tokens).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/brand-tokens-data.php';

/**
 * Whether Lennakatten branding is enabled (constant + filter only).
 *
 * Does not consider CSV-imported tokens; use MRT_use_lennakatten_brand_tokens()
 * for static pack enqueue.
 */
function MRT_is_lennakatten_brand_enabled(): bool {
	$default = defined( 'MRT_LENNAKATTEN_BRAND' ) && MRT_LENNAKATTEN_BRAND;
	return (bool) apply_filters( 'mrt_use_lennakatten_brand_tokens', $default );
}

/**
 * Whether to enqueue Lennakatten static brand CSS (when no CSV tokens stored).
 *
 * Default: MRT_LENNAKATTEN_BRAND constant in wp-config.php.
 * Filter: mrt_use_lennakatten_brand_tokens
 */
function MRT_use_lennakatten_brand_tokens(): bool {
	if ( MRT_has_imported_brand_tokens() ) {
		return false;
	}
	return MRT_is_lennakatten_brand_enabled();
}

/**
 * Enqueue brand overrides after the Vue public bundle CSS.
 *
 * @param string $after_handle Style handle to load after (last Vue CSS chunk).
 */
function MRT_enqueue_brand_token_overrides( string $after_handle = '' ): void {
	if ( MRT_has_imported_brand_tokens() ) {
		MRT_enqueue_imported_brand_tokens( $after_handle );
		return;
	}
	if ( ! MRT_use_lennakatten_brand_tokens() ) {
		return;
	}
	MRT_enqueue_lennakatten_brand_pack( $after_handle );
}

/**
 * @param string $after_handle Style dependency.
 */
function MRT_enqueue_imported_brand_tokens( string $after_handle ): void {
	$fonts_url = MRT_get_brand_google_fonts_url();
	if ( $fonts_url !== '' ) {
		wp_enqueue_style(
			'mrt-brand-google-fonts',
			$fonts_url,
			array(),
			null
		);
	}
	$css = MRT_brand_tokens_inline_css();
	if ( $css === '' ) {
		return;
	}
	$deps = $fonts_url !== '' ? array( 'mrt-brand-google-fonts' ) : ( $after_handle !== '' ? array( $after_handle ) : array() );
	wp_register_style( 'mrt-brand-imported-tokens', false, $deps, MRT_VERSION );
	wp_enqueue_style( 'mrt-brand-imported-tokens' );
	wp_add_inline_style( 'mrt-brand-imported-tokens', $css );
}

/**
 * @param string $after_handle Style dependency.
 */
function MRT_enqueue_lennakatten_brand_pack( string $after_handle ): void {
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
