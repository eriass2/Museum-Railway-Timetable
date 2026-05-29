<?php
/**
 * Export plugin timetable data to a CSV package.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export to a directory (created if needed).
 *
 * @param array<string, bool> $options include_prices, include_settings
 * @return string|WP_Error Output directory path
 */
function MRT_csv_export_package( string $target_dir, array $options = array() ) {
	$include_prices   = ! empty( $options['include_prices'] );
	$include_settings = ! empty( $options['include_settings'] );
	$tables           = MRT_csv_collect_export_tables( $include_prices, $include_settings );
	$includes         = array_keys(
		array_filter(
			array(
				'stations'    => true,
				'train_types' => true,
				'routes'      => true,
				'timetables'  => true,
				'services'    => true,
				'stoptimes'   => true,
				'settings'    => $include_settings,
				'prices'      => $include_prices,
			)
		)
	);
	$manifest = array(
		'format_version' => MRT_csv_format_version(),
		'exported_at'    => gmdate( 'c' ),
		'plugin_version' => defined( 'MRT_VERSION' ) ? MRT_VERSION : '0.0.0',
		'locale'         => determine_locale(),
		'includes'       => $includes,
	);
	if ( ! wp_mkdir_p( $target_dir ) ) {
		return new WP_Error( 'mrt_csv_export', 'Could not create export directory.' );
	}
	if ( ! MRT_csv_write_manifest( $target_dir, $manifest ) ) {
		return new WP_Error( 'mrt_csv_export', 'Could not write manifest.' );
	}
	$headers = MRT_csv_fixture_column_headers();
	$headers['settings.csv'] = array( 'key', 'value' );
	$headers['prices.csv']   = array( 'ticket_type', 'category', 'zone', 'amount_sek' );
	foreach ( $tables as $file => $rows ) {
		if ( ! MRT_csv_write_file( trailingslashit( $target_dir ) . $file, $headers[ $file ], $rows ) ) {
			return new WP_Error( 'mrt_csv_export', "Could not write {$file}." );
		}
	}
	return $target_dir;
}

/**
 * Export directory as zip file path.
 *
 * @param array<string, bool> $options
 * @return string|WP_Error Zip path
 */
function MRT_csv_export_zip( string $zip_path, array $options = array() ) {
	$tmpdir = trailingslashit( get_temp_dir() ) . 'mrt-export-' . wp_generate_password( 8, false );
	$dir    = MRT_csv_export_package( $tmpdir, $options );
	if ( is_wp_error( $dir ) ) {
		return $dir;
	}
	$ok = MRT_csv_zip_directory( $dir, $zip_path );
	MRT_csv_remove_dir( $tmpdir );
	if ( ! $ok ) {
		return new WP_Error( 'mrt_csv_export', 'Could not create zip archive.' );
	}
	return $zip_path;
}

/**
 * @return array<string, array<int, array<string, string>>>
 */
function MRT_csv_collect_export_tables( bool $include_prices, bool $include_settings ): array {
	$maps = array( 'station' => array(), 'route' => array(), 'timetable' => array(), 'service' => array() );
	$tables = array(
		'stations.csv'            => MRT_csv_export_stations( $maps ),
		'train_types.csv'         => MRT_csv_export_train_types(),
		'routes.csv'              => array(),
		'route_stations.csv'      => array(),
		'timetables.csv'          => array(),
		'timetable_dates.csv'     => array(),
		'services.csv'            => array(),
		'service_train_types.csv' => array(),
		'stoptimes.csv'           => array(),
	);
	MRT_csv_export_routes( $tables, $maps );
	MRT_csv_export_timetables( $tables, $maps );
	MRT_csv_export_services( $tables, $maps );
	$tables['stoptimes.csv'] = MRT_csv_export_stoptimes( $maps );
	if ( $include_settings ) {
		$tables['settings.csv'] = MRT_csv_export_settings();
	}
	if ( $include_prices ) {
		$tables['prices.csv'] = MRT_csv_export_prices();
	}
	return $tables;
}

/**
 * @param array<string, array<string, int>> $maps
 * @return array<int, array<string, string>>
 */
function MRT_csv_export_stations( array &$maps ): array {
	$meta = MRT_csv_code_meta_keys()['stations'];
	$rows = array();
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_STATION,
			'posts_per_page' => -1,
			'orderby'        => 'meta_value_num',
			'meta_key'       => 'mrt_display_order',
			'order'          => 'ASC',
		)
	);
	foreach ( $posts as $post ) {
		$code = (string) get_post_meta( $post->ID, $meta, true );
		if ( $code === '' ) {
			$code = MRT_csv_slugify( $post->post_title );
			MRT_csv_save_post_code( $post->ID, $meta, $code );
		}
		$maps['station'][ $code ] = $post->ID;
		$rows[] = array(
			'station_code'    => $code,
			'name'            => $post->post_title,
			'station_type'    => (string) get_post_meta( $post->ID, 'mrt_station_type', true ),
			'display_order'   => (string) (int) get_post_meta( $post->ID, 'mrt_display_order', true ),
			'bus_stop_marker' => get_post_meta( $post->ID, 'mrt_station_bus_suffix', true ) === '1' ? '1' : '0',
			'lat'             => (string) get_post_meta( $post->ID, 'mrt_lat', true ),
			'lng'             => (string) get_post_meta( $post->ID, 'mrt_lng', true ),
		);
	}
	return $rows;
}
