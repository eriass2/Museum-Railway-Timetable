<?php
/**
 * CSV import/export for operator brand tokens (colors + typography).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 */
function MRT_csv_import_brand_tokens( array $files ): void {
	require_once MRT_PATH . 'inc/assets/brand-tokens-data.php';
	if ( ! isset( $files['brand_tokens.csv'] ) ) {
		return;
	}
	$google = '';
	$tokens = array();
	foreach ( (array) $files['brand_tokens.csv'] as $row ) {
		$raw_token = trim( (string) ( $row['token'] ?? '' ) );
		$value     = (string) ( $row['value'] ?? '' );
		if ( $raw_token === 'google_fonts' ) {
			$google = MRT_sanitize_brand_google_fonts_url( $value );
			continue;
		}
		$token = MRT_sanitize_brand_token_key( $raw_token );
		$clean = MRT_sanitize_brand_token_value( $token, $value );
		if ( $token !== '' && $clean !== '' ) {
			$tokens[ $token ] = $clean;
		}
	}
	update_option(
		MRT_OPTION_BRAND_TOKENS,
		MRT_sanitize_brand_tokens_storage(
			array(
				'google_fonts' => $google,
				'tokens'       => $tokens,
			)
		)
	);
}

/**
 * @return array<int, array<string, string>>
 */
function MRT_csv_export_brand_tokens(): array {
	require_once MRT_PATH . 'inc/assets/brand-tokens-data.php';
	$storage = MRT_get_brand_tokens_storage();
	$rows    = array();
	if ( $storage['google_fonts'] !== '' ) {
		$rows[] = array(
			'token' => 'google_fonts',
			'value' => $storage['google_fonts'],
		);
	}
	$tokens = $storage['tokens'];
	ksort( $tokens );
	foreach ( $tokens as $token => $value ) {
		$rows[] = array(
			'token' => $token,
			'value' => $value,
		);
	}
	return $rows;
}
