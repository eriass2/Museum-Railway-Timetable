<?php
/**
 * Remove plugin entities not present in an override import.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, mixed> $package
 */
function MRT_csv_delete_orphans( array $package ): void {
	$includes = (array) ( $package['manifest']['includes'] ?? array() );
	$files    = (array) ( $package['files'] ?? array() );
	$codes    = MRT_csv_collect_package_codes( $files );

	if ( in_array( 'services', $includes, true ) || in_array( 'stoptimes', $includes, true ) ) {
		MRT_csv_delete_orphan_posts( MRT_POST_TYPE_SERVICE, MRT_csv_code_meta_keys()['services'], $codes['services'] ?? array() );
	}
	if ( in_array( 'timetables', $includes, true ) ) {
		MRT_csv_delete_orphan_posts( MRT_POST_TYPE_TIMETABLE, MRT_csv_code_meta_keys()['timetables'], $codes['timetables'] ?? array() );
	}
	if ( in_array( 'routes', $includes, true ) ) {
		MRT_csv_delete_orphan_posts( MRT_POST_TYPE_ROUTE, MRT_csv_code_meta_keys()['routes'], $codes['routes'] ?? array() );
	}
	if ( in_array( 'stations', $includes, true ) ) {
		MRT_csv_delete_orphan_posts( MRT_POST_TYPE_STATION, MRT_csv_code_meta_keys()['stations'], $codes['stations'] ?? array() );
	}
	if ( in_array( 'train_types', $includes, true ) ) {
		MRT_csv_delete_orphan_train_types( $codes['train_types'] ?? array() );
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @return array<string, array<string, bool>>
 */
function MRT_csv_collect_package_codes( array $files ): array {
	$out = array(
		'stations'    => array(),
		'routes'      => array(),
		'timetables'  => array(),
		'services'    => array(),
		'train_types' => array(),
	);
	foreach ( (array) ( $files['stations.csv'] ?? array() ) as $row ) {
		$code = MRT_csv_row_code( $row, 'station_code', 'name' );
		$out['stations'][ $code ] = true;
	}
	foreach ( (array) ( $files['routes.csv'] ?? array() ) as $row ) {
		$code = MRT_csv_row_code( $row, 'route_code', 'title' );
		$out['routes'][ $code ] = true;
	}
	foreach ( (array) ( $files['timetables.csv'] ?? array() ) as $row ) {
		$code = MRT_csv_row_code( $row, 'timetable_code', 'title' );
		$out['timetables'][ $code ] = true;
	}
	foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
		$out['services'][ MRT_csv_resolve_service_code( $row ) ] = true;
	}
	foreach ( (array) ( $files['train_types.csv'] ?? array() ) as $row ) {
		$slug = $row['slug'] ?? '';
		if ( $slug !== '' ) {
			$out['train_types'][ $slug ] = true;
		}
	}
	return $out;
}

/**
 * @param array<string, bool> $keep_codes
 */
function MRT_csv_delete_orphan_posts( string $post_type, string $meta_key, array $keep_codes ): void {
	$ids = get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $ids as $id ) {
		$code = (string) get_post_meta( (int) $id, $meta_key, true );
		if ( $code === '' || ! isset( $keep_codes[ $code ] ) ) {
			wp_delete_post( (int) $id, true );
		}
	}
}

/**
 * @param array<string, bool> $keep_slugs
 */
function MRT_csv_delete_orphan_train_types( array $keep_slugs ): void {
	$terms = get_terms(
		array(
			'taxonomy'   => MRT_TAXONOMY_TRAIN_TYPE,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) ) {
		return;
	}
	foreach ( $terms as $term ) {
		if ( ! isset( $keep_slugs[ $term->slug ] ) ) {
			wp_delete_term( (int) $term->term_id, MRT_TAXONOMY_TRAIN_TYPE );
		}
	}
}
