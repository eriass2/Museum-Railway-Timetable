<?php
/**
 * Stored operator brand tokens (CSS variables + optional Google Fonts URL).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option key for imported brand tokens. */
define( 'MRT_OPTION_BRAND_TOKENS', 'mrt_brand_tokens' );

/**
 * @return array{google_fonts: string, tokens: array<string, string>}
 */
function MRT_default_brand_tokens_storage(): array {
	return array(
		'google_fonts' => '',
		'tokens'       => array(),
	);
}

/**
 * @return array{google_fonts: string, tokens: array<string, string>}
 */
function MRT_get_brand_tokens_storage(): array {
	$stored = get_option( MRT_OPTION_BRAND_TOKENS, array() );
	if ( ! is_array( $stored ) ) {
		return MRT_default_brand_tokens_storage();
	}
	return MRT_sanitize_brand_tokens_storage( $stored );
}

/**
 * @return array<string, string>
 */
function MRT_get_brand_css_tokens(): array {
	return MRT_get_brand_tokens_storage()['tokens'];
}

/**
 * @return string
 */
function MRT_get_brand_google_fonts_url(): string {
	return MRT_get_brand_tokens_storage()['google_fonts'];
}

/**
 * Whether CSV-imported brand overrides are active.
 */
function MRT_has_imported_brand_tokens(): bool {
	$storage = MRT_get_brand_tokens_storage();
	return $storage['google_fonts'] !== '' || $storage['tokens'] !== array();
}

/**
 * @param array<string, mixed> $input Raw storage.
 * @return array{google_fonts: string, tokens: array<string, string>}
 */
function MRT_sanitize_brand_tokens_storage( array $input ): array {
	$tokens = array();
	if ( isset( $input['tokens'] ) && is_array( $input['tokens'] ) ) {
		foreach ( $input['tokens'] as $token => $value ) {
			$clean_token = MRT_sanitize_brand_token_key( (string) $token );
			$clean_value = MRT_sanitize_brand_token_value( $clean_token, (string) $value );
			if ( $clean_token !== '' && $clean_value !== '' ) {
				$tokens[ $clean_token ] = $clean_value;
			}
		}
	}
	$url = isset( $input['google_fonts'] ) ? MRT_sanitize_brand_google_fonts_url( (string) $input['google_fonts'] ) : '';
	return array(
		'google_fonts' => $url,
		'tokens'       => $tokens,
	);
}

/**
 * @param string $token Raw token name (with or without leading --).
 */
function MRT_sanitize_brand_token_key( string $token ): string {
	$token = trim( $token );
	if ( $token === 'google_fonts' ) {
		return '';
	}
	if ( str_starts_with( $token, '--' ) ) {
		$token = substr( $token, 2 );
	}
	if ( ! preg_match( '/^mrt-[a-z0-9-]{1,48}$/', $token ) ) {
		return '';
	}
	return '--' . $token;
}

/**
 * @param string $token CSS custom property including -- prefix.
 * @param string $value Raw value.
 */
function MRT_sanitize_brand_token_value( string $token, string $value ): string {
	$value = trim( wp_strip_all_tags( $value ) );
	if ( $value === '' || strlen( $value ) > 240 ) {
		return '';
	}
	if ( preg_match( '/[{}<>\\\\]/', $value ) ) {
		return '';
	}
	if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $value ) ) {
		return strtolower( $value );
	}
	if ( preg_match( '/^var\\(--mrt-[a-z0-9-]+\\)$/', $value ) ) {
		return $value;
	}
	if ( preg_match( '/^[0-9]{1,3}$/', $value ) && (int) $value <= 900 ) {
		return $value;
	}
	if ( preg_match( '/^rgba\\([^)]+\\)$/', $value ) ) {
		return $value;
	}
	if ( str_starts_with( $token, '--mrt-font-' ) ) {
		if ( preg_match( '/^["\'a-zA-Z0-9 ,.-]+$/', $value ) ) {
			return $value;
		}
	}
	return '';
}

/**
 * @param string $url Google Fonts CSS URL.
 */
function MRT_sanitize_brand_google_fonts_url( string $url ): string {
	$url = esc_url_raw( trim( $url ) );
	if ( $url === '' ) {
		return '';
	}
	if ( ! preg_match( '#^https://fonts\\.googleapis\\.com/css2#', $url ) ) {
		return '';
	}
	return $url;
}

/**
 * Build :root { ... } CSS from stored tokens.
 */
function MRT_brand_tokens_inline_css(): string {
	$tokens = MRT_get_brand_css_tokens();
	if ( $tokens === array() ) {
		return '';
	}
	$lines = array();
	foreach ( $tokens as $token => $value ) {
		$lines[] = sprintf( "\t%s: %s;", $token, $value );
	}
	return ":root {\n" . implode( "\n", $lines ) . "\n}";
}
