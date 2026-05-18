<?php

declare(strict_types=1);

/**
 * Train type domain helpers.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
