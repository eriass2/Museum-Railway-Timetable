<?php
/**
 * CSV manifest inference and template packages.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Entity types listed in manifest.json → includes (import order).
 *
 * @return list<string>
 */
function MRT_csv_manifest_include_order(): array {
	return array(
		'stations',
		'train_types',
		'routes',
		'timetables',
		'services',
		'stoptimes',
		'settings',
		'prices',
	);
}

/**
 * Map a CSV filename to a manifest includes entry.
 */
function MRT_csv_file_to_include_entity( string $filename ): ?string {
	$map = array(
		'stations.csv'            => 'stations',
		'train_types.csv'         => 'train_types',
		'routes.csv'              => 'routes',
		'route_stations.csv'      => 'routes',
		'timetables.csv'          => 'timetables',
		'timetable_dates.csv'     => 'timetables',
		'services.csv'            => 'services',
		'service_train_types.csv' => 'services',
		'stoptimes.csv'           => 'stoptimes',
		'settings.csv'            => 'settings',
		'prices.csv'              => 'prices',
		'price_schema.csv'        => 'prices',
	);
	return $map[ $filename ] ?? null;
}

/**
 * Detect which entity types are present in a package directory.
 *
 * @return list<string>
 */
function MRT_csv_infer_includes_from_dir( string $dir ): array {
	$dir   = trailingslashit( $dir );
	$found = array();
	$paths = glob( $dir . '*.csv' );
	if ( ! is_array( $paths ) ) {
		return array();
	}
	foreach ( $paths as $path ) {
		$entity = MRT_csv_file_to_include_entity( basename( (string) $path ) );
		if ( $entity !== null ) {
			$found[ $entity ] = true;
		}
	}
	$includes = array();
	foreach ( MRT_csv_manifest_include_order() as $entity ) {
		if ( ! empty( $found[ $entity ] ) ) {
			$includes[] = $entity;
		}
	}
	return $includes;
}

/**
 * Whether a directory looks like a CSV package (manifest or CSV files).
 */
function MRT_csv_dir_has_package_markers( string $dir ): bool {
	$dir = trailingslashit( $dir );
	if ( is_file( $dir . 'manifest.json' ) ) {
		return true;
	}
	return MRT_csv_infer_includes_from_dir( $dir ) !== array();
}

/**
 * Build manifest.json content from CSV files in a directory.
 *
 * @return array<string, mixed>
 */
function MRT_csv_build_manifest_from_dir( string $dir ): array {
	return array(
		'format_version' => MRT_csv_format_version(),
		'exported_at'    => gmdate( 'c' ),
		'plugin_version' => defined( 'MRT_VERSION' ) ? MRT_VERSION : '0.0.0',
		'locale'         => function_exists( 'determine_locale' ) ? determine_locale() : 'sv_SE',
		'includes'       => MRT_csv_infer_includes_from_dir( $dir ),
		'generated'      => true,
	);
}
