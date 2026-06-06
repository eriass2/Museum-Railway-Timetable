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

/**
 * CSV files included in the empty starter template (header row only).
 *
 * @return list<string>
 */
function MRT_csv_template_files(): array {
	return array(
		'stations.csv',
		'train_types.csv',
		'routes.csv',
		'route_stations.csv',
		'timetables.csv',
		'timetable_dates.csv',
		'services.csv',
		'service_train_types.csv',
		'stoptimes.csv',
	);
}

/**
 * Write empty CSV template files to a directory.
 *
 * @return string|WP_Error Output directory path
 */
function MRT_csv_export_template_package( string $target_dir ) {
	if ( ! wp_mkdir_p( $target_dir ) ) {
		return new WP_Error( 'mrt_csv_template', 'Could not create template directory.' );
	}
	$manifest = array(
		'format_version' => MRT_csv_format_version(),
		'exported_at'    => gmdate( 'c' ),
		'plugin_version' => defined( 'MRT_VERSION' ) ? MRT_VERSION : '0.0.0',
		'locale'         => function_exists( 'determine_locale' ) ? determine_locale() : 'sv_SE',
		'includes'       => array(
			'stations',
			'train_types',
			'routes',
			'timetables',
			'services',
			'stoptimes',
		),
		'template'       => true,
	);
	if ( ! MRT_csv_write_manifest( $target_dir, $manifest ) ) {
		return new WP_Error( 'mrt_csv_template', 'Could not write manifest.' );
	}
	$headers = MRT_csv_export_column_headers();
	foreach ( MRT_csv_template_files() as $file ) {
		if ( ! isset( $headers[ $file ] ) ) {
			continue;
		}
		if ( ! MRT_csv_write_file( trailingslashit( $target_dir ) . $file, $headers[ $file ], array() ) ) {
			return new WP_Error( 'mrt_csv_template', "Could not write {$file}." );
		}
	}
	return $target_dir;
}

/**
 * Export an empty CSV template package as zip.
 *
 * @return string|WP_Error Zip path
 */
function MRT_csv_export_template_zip( string $zip_path ) {
	$temp_base = function_exists( 'get_temp_dir' ) ? get_temp_dir() : sys_get_temp_dir();
	$tmpdir    = trailingslashit( $temp_base ) . 'mrt-template-' . wp_generate_password( 8, false );
	$dir       = MRT_csv_export_template_package( $tmpdir );
	if ( is_wp_error( $dir ) ) {
		return $dir;
	}
	$ok = MRT_csv_zip_directory( $tmpdir, $zip_path );
	MRT_csv_remove_dir( $tmpdir );
	if ( ! $ok ) {
		return new WP_Error( 'mrt_csv_template', 'Could not create zip archive.' );
	}
	return $zip_path;
}

/**
 * Format validation errors for admin display.
 *
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_format_validation_errors( array $errors, int $limit = 6 ): string {
	if ( $errors === array() ) {
		return __( 'CSV validation failed.', 'museum-railway-timetable' );
	}
	$lines = array();
	foreach ( array_slice( $errors, 0, $limit ) as $error ) {
		$file = (string) ( $error['file'] ?? 'unknown' );
		$line = (int) ( $error['line'] ?? 0 );
		$msg  = (string) ( $error['message'] ?? '' );
		if ( $line > 0 ) {
			/* translators: 1: CSV filename, 2: line number, 3: error message */
			$lines[] = sprintf( __( '%1$s rad %2$d: %3$s', 'museum-railway-timetable' ), $file, $line, $msg );
			continue;
		}
		/* translators: 1: file or manifest name, 2: error message */
		$lines[] = sprintf( __( '%1$s: %2$s', 'museum-railway-timetable' ), $file, $msg );
	}
	if ( count( $errors ) > $limit ) {
		/* translators: %d: number of additional validation errors */
		$lines[] = sprintf( __( '… och %d fler fel.', 'museum-railway-timetable' ), count( $errors ) - $limit );
	}
	return implode( "\n", $lines );
}
