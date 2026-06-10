<?php
/**
 * Slugify text for CSV stable codes.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build a URL-safe code from a label.
 */
function MRT_csv_slugify( string $text ): string {
	$text = trim( $text );
	if ( $text === '' ) {
		return '';
	}
	if ( function_exists( 'remove_accents' ) ) {
		$text = remove_accents( $text );
	} else {
		$text = MRT_csv_slugify_ascii_fallback( $text );
	}
	$text = strtolower( $text );
	$text = (string) preg_replace( '/[^a-z0-9]+/', '-', $text );
	$text = trim( $text, '-' );
	return (string) preg_replace( '/-+/', '-', $text );
}

/**
 * Basic accent folding when WordPress is not loaded.
 */
function MRT_csv_slugify_ascii_fallback( string $text ): string {
	$map = array(
		'å' => 'a',
		'ä' => 'a',
		'ö' => 'o',
		'Å' => 'a',
		'Ä' => 'a',
		'Ö' => 'o',
		'é' => 'e',
	);
	return strtr( $text, $map );
}
