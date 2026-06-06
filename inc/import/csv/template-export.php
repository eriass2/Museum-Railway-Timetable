<?php
/**
 * Empty CSV template package export.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
 * @return array<string, mixed>
 */
function MRT_csv_template_manifest(): array {
	return array(
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
	if ( ! MRT_csv_write_manifest( $target_dir, MRT_csv_template_manifest() ) ) {
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
