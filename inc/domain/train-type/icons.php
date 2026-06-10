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
 * Icon keys (steam|diesel|railbus|bus) differ from WP train type slugs
 * (angtag|ralsbuss|dieseltag|buss). Slug → icon mapping: MRT_train_type_slug_icon_map().
 * Vue mirror: frontend/vue/src/shared/trainTypeIcons.ts
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
 * Lennakatten / standard train type slug → icon file key.
 *
 * @return array<string, string>
 */
function MRT_train_type_slug_icon_map(): array {
	return array(
		'angtag'     => 'steam',
		'ralsbuss'   => 'railbus',
		'dieseltag'  => 'diesel',
		'buss'       => 'bus',
		'ang-diesel' => 'diesel',
	);
}

/**
 * Resolve icon key from train type name and slug.
 */
function MRT_resolve_train_type_symbol_key( string $name, string $slug ): string {
	$slug_lower = strtolower( $slug );
	$map        = MRT_train_type_slug_icon_map();
	if ( isset( $map[ $slug_lower ] ) ) {
		return $map[ $slug_lower ];
	}

	$name_lower = strtolower( $name );
	if ( str_contains( $name_lower, 'rälsbuss' ) || str_contains( $name_lower, 'railbus' ) ) {
		return 'railbus';
	}
	if ( $name_lower === 'buss' || $slug_lower === 'buss' ) {
		return 'bus';
	}
	if ( str_contains( $name_lower, 'ång' ) && ! str_contains( $name_lower, 'diesel' ) ) {
		return 'steam';
	}
	if ( str_contains( $name_lower, 'diesel' ) || str_contains( $name_lower, 'elektrisk' ) ) {
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
	$stored = get_term_meta( (int) $train_type->term_id, 'mrt_icon_key', true );
	if ( is_string( $stored ) && $stored !== '' && in_array( $stored, MRT_train_type_icon_keys(), true ) ) {
		return $stored;
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
 * @return WP_Term|null
 */
function MRT_get_train_type_term_by_slug( string $slug ): ?WP_Term {
	$term = get_term_by( 'slug', $slug, 'mrt_train_type' );
	if ( ! $term || is_wp_error( $term ) ) {
		return null;
	}
	return $term;
}

/**
 * Resolve a printed label (e.g. Dieseltåg) to a train type term.
 *
 * @return WP_Term|null
 */
function MRT_get_train_type_term_by_label( string $label ): ?WP_Term {
	$slug_map = array(
		'Dieseltåg'  => 'dieseltag',
		'Rälsbuss'   => 'ralsbuss',
		'Ångtåg'     => 'angtag',
		'Ång/diesel' => 'ang-diesel',
		'Buss'       => 'buss',
	);
	$slug = $slug_map[ $label ] ?? '';
	if ( $slug === '' ) {
		return null;
	}
	return MRT_get_train_type_term_by_slug( $slug );
}
