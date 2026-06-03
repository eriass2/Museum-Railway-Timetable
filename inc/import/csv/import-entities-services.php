<?php
/**
 * Import timetables, services, stoptimes, settings, prices.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/service/highlight.php';

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_import_timetables( array $files, array &$maps ): int {
	$meta  = MRT_csv_code_meta_keys()['timetables'];
	$dates = MRT_csv_group_by_field( $files, 'timetable_dates.csv', 'timetable_code' );
	$count = 0;
	foreach ( (array) ( $files['timetables.csv'] ?? array() ) as $row ) {
		$code = MRT_csv_row_code( $row, 'timetable_code', 'title' );
		$id   = MRT_csv_find_post_by_code( $code, MRT_POST_TYPE_TIMETABLE, $meta );
		if ( $id <= 0 ) {
			$id = wp_insert_post(
				array(
					'post_type'   => MRT_POST_TYPE_TIMETABLE,
					'post_title'  => $row['title'],
					'post_status' => 'publish',
				)
			);
		} else {
			wp_update_post(
				array(
					'ID' => $id,
					'post_title' => $row['title'],
				)
			);
		}
		if ( ! $id || $id instanceof WP_Error ) {
			continue;
		}
		$date_list = array();
		foreach ( $dates[ $code ] ?? array() as $drow ) {
			$date_list[] = $drow['date'];
		}
		sort( $date_list );
		update_post_meta( $id, 'mrt_timetable_dates', $date_list );
		update_post_meta( $id, 'mrt_timetable_type', sanitize_text_field( $row['colour_type'] ?? '' ) );
		MRT_csv_save_post_code( (int) $id, $meta, $code );
		$maps['timetable'][ $code ] = (int) $id;
		++$count;
	}
	return $count;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_import_services( array $files, array &$maps ): int {
	$meta       = MRT_csv_code_meta_keys()['services'];
	$train_rows = (array) ( $files['service_train_types.csv'] ?? array() );
	$by_service = array();
	foreach ( $train_rows as $trow ) {
		$by_service[ $trow['service_code'] ?? '' ][] = $trow['train_type_slug'] ?? '';
	}
	$count = 0;
	foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
		$code = MRT_csv_resolve_service_code( $row );
		$id   = MRT_csv_find_post_by_code( $code, MRT_POST_TYPE_SERVICE, $meta );
		$title = MRT_csv_service_title( $row, $maps );
		if ( $id <= 0 ) {
			$id = wp_insert_post(
				array(
					'post_type'   => MRT_POST_TYPE_SERVICE,
					'post_title'  => $title,
					'post_status' => 'publish',
				)
			);
		} else {
			wp_update_post(
				array(
					'ID' => $id,
					'post_title' => $title,
				)
			);
			MRT_csv_clear_service_stoptimes( (int) $id );
		}
		if ( ! $id || $id instanceof WP_Error ) {
			continue;
		}
		$route_code = $row['route_code'] ?? '';
		$tt_code    = $row['timetable_code'] ?? '';
		$end_code   = $row['end_station_code'] ?? '';
		update_post_meta( $id, 'mrt_service_route_id', (int) ( $maps['route'][ $route_code ] ?? 0 ) );
		update_post_meta( $id, 'mrt_service_timetable_id', (int) ( $maps['timetable'][ $tt_code ] ?? 0 ) );
		update_post_meta( $id, 'mrt_service_end_station_id', (int) ( $maps['station'][ $end_code ] ?? 0 ) );
		update_post_meta( $id, 'mrt_service_number', sanitize_text_field( $row['service_number'] ?? '' ) );
		MRT_csv_update_service_highlight_from_row( (int) $id, $row );
		MRT_csv_assign_service_train_types( (int) $id, $by_service[ $code ] ?? array() );
		MRT_csv_save_post_code( (int) $id, $meta, $code );
		$maps['service'][ $code ] = (int) $id;
		++$count;
	}
	return $count;
}

/**
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_service_title( array $row, array $maps ): string {
	$custom = trim( $row['title'] ?? '' );
	if ( $custom !== '' ) {
		return $custom;
	}
	$route_id = (int) ( $maps['route'][ $row['route_code'] ?? '' ] ?? 0 );
	$route    = $route_id > 0 ? get_post( $route_id ) : null;
	$prefix   = $route instanceof WP_Post ? $route->post_title : ( $row['route_code'] ?? 'Service' );
	$num      = $row['service_number'] ?? '';
	return trim( $prefix . ' ' . $num );
}

function MRT_csv_resolve_service_code( array $row ): string {
	$code = trim( $row['service_code'] ?? '' );
	if ( $code !== '' ) {
		return $code;
	}
	return MRT_csv_slugify(
		( $row['timetable_code'] ?? 'tt' ) . '-' . ( $row['service_number'] ?? 'x' ) . '-' . ( $row['end_station_code'] ?? 'end' )
	);
}

/**
 * @param array<int, string> $slugs
 */
function MRT_csv_assign_service_train_types( int $service_id, array $slugs ): void {
	$term_ids = array();
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, MRT_TAXONOMY_TRAIN_TYPE );
		if ( $term && ! is_wp_error( $term ) ) {
			$term_ids[] = (int) $term->term_id;
		}
	}
	if ( $term_ids !== array() ) {
		wp_set_object_terms( $service_id, $term_ids, MRT_TAXONOMY_TRAIN_TYPE );
	}
}

function MRT_csv_clear_service_stoptimes( int $service_id ): void {
	global $wpdb;
	$table = $wpdb->prefix . 'mrt_stoptimes';
	$wpdb->delete( $table, array( 'service_post_id' => $service_id ), array( '%d' ) );
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_import_stoptimes( array $files, array $maps ): void {
	global $wpdb;
	$table = $wpdb->prefix . 'mrt_stoptimes';
	foreach ( (array) ( $files['stoptimes.csv'] ?? array() ) as $row ) {
		$svc_code = $row['service_code'] ?? '';
		$svc_id   = (int) ( $maps['service'][ $svc_code ] ?? 0 );
		$st_code  = $row['station_code'] ?? '';
		$st_id    = (int) ( $maps['station'][ $st_code ] ?? 0 );
		if ( $svc_id <= 0 || $st_id <= 0 ) {
			continue;
		}
		$wpdb->insert(
			$table,
			array(
				'service_post_id' => $svc_id,
				'station_post_id' => $st_id,
				'stop_sequence'   => (int) ( $row['sequence'] ?? 0 ),
				'arrival_time'    => MRT_csv_nullable_time( $row['arrival_time'] ?? '' ),
				'departure_time'  => MRT_csv_nullable_time( $row['departure_time'] ?? '' ),
				'pickup_allowed'  => (int) ( $row['pickup_allowed'] ?? 1 ),
				'dropoff_allowed' => (int) ( $row['dropoff_allowed'] ?? 1 ),
			),
			array( '%d', '%d', '%d', '%s', '%s', '%d', '%d' )
		);
	}
}

function MRT_csv_nullable_time( string $time ): ?string {
	$time = trim( $time );
	return $time === '' ? null : $time;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 */
function MRT_csv_import_settings( array $files ): void {
	$allowed = array( 'enabled', 'note', 'min_transfer_minutes', 'max_transfer_minutes' );
	$current = MRT_get_plugin_settings();
	foreach ( (array) ( $files['settings.csv'] ?? array() ) as $row ) {
		$key = $row['key'] ?? '';
		if ( ! in_array( $key, $allowed, true ) ) {
			continue;
		}
		$current[ $key ] = MRT_csv_cast_setting_value( $key, $row['value'] ?? '' );
	}
	update_option( 'mrt_settings', $current );
}

function MRT_csv_cast_setting_value( string $key, string $value ) {
	if ( $key === 'enabled' ) {
		return in_array( strtolower( $value ), array( '1', 'true', 'yes' ), true );
	}
	if ( str_contains( $key, '_minutes' ) ) {
		return (int) $value;
	}
	return sanitize_text_field( $value );
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 */
function MRT_csv_import_prices( array $files ): void {
	if ( ! function_exists( 'MRT_get_default_price_matrix' ) ) {
		require_once MRT_PATH . 'inc/domain/pricing/prices.php';
	}
	$matrix = MRT_get_default_price_matrix();
	foreach ( (array) ( $files['prices.csv'] ?? array() ) as $row ) {
		$t = $row['ticket_type'] ?? '';
		$c = $row['category'] ?? '';
		$z = (int) ( $row['zone'] ?? 0 );
		if ( ! isset( $matrix[ $t ][ $c ][ $z ] ) ) {
			continue;
		}
		$amount = trim( $row['amount_sek'] ?? '' );
		$matrix[ $t ][ $c ][ $z ] = $amount === '' ? null : (int) $amount;
	}
	update_option( 'mrt_price_matrix', $matrix );
}

/**
 * @return array<string, array<int, array<string, string>>>
 */
function MRT_csv_group_by_field( array $files, string $file, string $field ): array {
	$groups = array();
	foreach ( (array) ( $files[ $file ] ?? array() ) as $row ) {
		$key = $row[ $field ] ?? '';
		if ( $key !== '' ) {
			$groups[ $key ][] = $row;
		}
	}
	return $groups;
}
