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

require_once MRT_PATH . 'inc/domain/line/line-csv.php';
require_once MRT_PATH . 'inc/domain/line/line-route-resolve.php';
require_once MRT_PATH . 'inc/domain/service/highlight.php';
require_once MRT_PATH . 'inc/domain/service/overview-column.php';
require_once MRT_PATH . 'inc/domain/service/stop-time-modes.php';
require_once MRT_PATH . 'inc/import/csv/fixture-read.php';

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
		$id   = MRT_csv_upsert_post_by_code( $code, MRT_POST_TYPE_TIMETABLE, $meta, $row['title'] );
		if ( $id <= 0 ) {
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
	$routes_branch = MRT_csv_routes_branch_from_file( $files );
	$count         = 0;
	foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
		$code       = MRT_csv_resolve_service_code( $row );
		$route_code = MRT_csv_resolve_service_route_code( $row, $files, $routes_branch );
		if ( $route_code === '' ) {
			continue;
		}
		$row['_resolved_route_code'] = $route_code;
		$title = MRT_csv_service_title( $row, $maps );
		$id    = MRT_csv_upsert_post_by_code(
			$code,
			MRT_POST_TYPE_SERVICE,
			$meta,
			$title,
			static function ( int $service_id ): void {
				MRT_csv_clear_service_stoptimes( $service_id );
			}
		);
		if ( $id <= 0 ) {
			continue;
		}
		$tt_code  = $row['timetable_code'] ?? '';
		$end_code = $row['end_station_code'] ?? '';
		update_post_meta( $id, 'mrt_service_route_id', (int) ( $maps['route'][ $route_code ] ?? 0 ) );
		update_post_meta( $id, 'mrt_service_timetable_id', (int) ( $maps['timetable'][ $tt_code ] ?? 0 ) );
		update_post_meta( $id, 'mrt_service_end_station_id', (int) ( $maps['station'][ $end_code ] ?? 0 ) );
		update_post_meta( $id, 'mrt_service_number', sanitize_text_field( $row['service_number'] ?? '' ) );
		$line_code = MRT_csv_resolve_service_line_code( $row, $routes_branch );
		if ( $line_code !== '' ) {
			update_post_meta( $id, MRT_service_line_code_meta_key(), $line_code );
		} else {
			delete_post_meta( $id, MRT_service_line_code_meta_key() );
		}
		MRT_csv_apply_service_overview_display_from_line( (int) $id, $line_code, $row );
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
	$route_code = trim( (string) ( $row['route_code'] ?? '' ) );
	if ( $route_code === '' && isset( $row['_resolved_route_code'] ) ) {
		$route_code = (string) $row['_resolved_route_code'];
	}
	$route_id = (int) ( $maps['route'][ $route_code ] ?? 0 );
	$route    = $route_id > 0 ? get_post( $route_id ) : null;
	$prefix   = $route instanceof WP_Post ? $route->post_title : ( $route_code !== '' ? $route_code : 'Service' );
	$num      = $row['service_number'] ?? '';
	return trim( $prefix . ' ' . $num );
}

/**
 * Pattern lines get a standalone overview column; legacy overview_column CSV still supported.
 *
 * @param array<string, mixed> $row
 */
function MRT_csv_apply_service_overview_display_from_line( int $service_id, string $line_code, array $row ): void {
	$csv_flag = ! empty( $row['overview_column'] ) && (string) $row['overview_column'] !== '0';
	if ( $csv_flag ) {
		update_post_meta( $service_id, 'mrt_service_overview_column', 1 );
		delete_post_meta( $service_id, 'mrt_service_overview_pass_from_station_id' );
		return;
	}
	if ( $line_code !== '' && MRT_line_is_direct_pattern( $line_code ) ) {
		update_post_meta( $service_id, 'mrt_service_overview_column', 1 );
		delete_post_meta( $service_id, 'mrt_service_overview_pass_from_station_id' );
		return;
	}
	delete_post_meta( $service_id, 'mrt_service_overview_column' );
	delete_post_meta( $service_id, 'mrt_service_overview_pass_from_station_id' );
}

/**
 * @return array<string, string>
 */
function MRT_csv_routes_branch_from_file( array $files ): array {
	require_once MRT_PATH . 'inc/domain/line/line-route-definitions.php';
	$branch = array();
	foreach ( MRT_csv_line_derived_route_rows( $files ) as $code => $row ) {
		$branch[ $code ] = trim( (string) ( $row['branch_code'] ?? '' ) );
	}
	foreach ( (array) ( $files['routes.csv'] ?? array() ) as $row ) {
		$code = trim( (string) ( $row['route_code'] ?? '' ) );
		if ( $code === '' ) {
			continue;
		}
		$branch[ $code ] = trim( (string) ( $row['branch_code'] ?? '' ) );
	}
	return $branch;
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
		$modes = MRT_stop_time_modes_from_input( $row );
		if ( is_wp_error( $modes ) ) {
			continue;
		}
		$wpdb->insert(
			$table,
			array_merge(
				array(
					'service_post_id' => $svc_id,
					'station_post_id' => $st_id,
					'stop_sequence'   => (int) ( $row['sequence'] ?? 0 ),
					'arrival_time'    => MRT_csv_nullable_time( $row['arrival_time'] ?? '' ),
					'departure_time'  => MRT_csv_nullable_time( $row['departure_time'] ?? '' ),
				),
				MRT_stop_time_mode_db_fields( $modes )
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' )
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
	$allowed = array(
		'enabled',
		'note',
		'operator_name',
		'ticket_url',
		'hero_background_url',
		'wizard_beta_enabled',
		'wizard_feedback_enabled',
		'min_transfer_minutes',
		'max_transfer_minutes',
		'max_transfers',
		'afternoon_return_threshold_minutes',
	);
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

/**
 * Resolve hero background URL from absolute URL or plugin-relative testdata path.
 */
function MRT_csv_resolve_hero_background_url( string $value ): string {
	$value = trim( $value );
	if ( $value === '' ) {
		return '';
	}
	if ( str_starts_with( $value, 'http://' ) || str_starts_with( $value, 'https://' ) ) {
		return esc_url_raw( $value );
	}
	$url = MRT_testdata_asset_url( $value );
	return $url !== '' ? esc_url_raw( $url ) : '';
}

function MRT_csv_cast_setting_value( string $key, string $value ) {
	if ( in_array( $key, array( 'enabled', 'wizard_beta_enabled', 'wizard_feedback_enabled' ), true ) ) {
		return in_array( strtolower( $value ), array( '1', 'true', 'yes' ), true );
	}
	if ( $key === 'ticket_url' ) {
		return esc_url_raw( $value );
	}
	if ( $key === 'hero_background_url' ) {
		return MRT_csv_resolve_hero_background_url( $value );
	}
	if ( $key === 'max_transfers' || $key === 'afternoon_return_threshold_minutes' || str_contains( $key, '_minutes' ) ) {
		return (int) $value;
	}
	return sanitize_text_field( $value );
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 */
function MRT_csv_import_price_schema( array $files ): void {
	if ( ! function_exists( 'MRT_sanitize_price_schema' ) ) {
		require_once MRT_PATH . 'inc/domain/pricing/price-schema.php';
	}
	$rows = (array) ( $files['price_schema.csv'] ?? array() );
	if ( $rows === array() ) {
		return;
	}
	$current      = MRT_get_price_schema();
	$ticket_types = $current['ticket_types'];
	$categories   = $current['categories'];
	$zones        = $current['zones'];
	$zone_cap     = $current['zone_cap'];
	$afternoon    = $current['afternoon_return'];
	$has_tickets  = false;
	$has_cats     = false;
	$has_zones    = false;
	$has_cap      = false;
	$has_afternoon = false;
	foreach ( $rows as $row ) {
		$kind = sanitize_key( (string) ( $row['kind'] ?? '' ) );
		$key  = sanitize_key( (string) ( $row['key'] ?? '' ) );
		switch ( $kind ) {
			case 'ticket_type':
				if ( ! $has_tickets ) {
					$ticket_types = array();
					$has_tickets  = true;
				}
				if ( $key !== '' ) {
					$ticket_types[] = array(
						'key'   => $key,
						'label' => sanitize_text_field( (string) ( $row['label'] ?? $key ) ),
					);
				}
				break;
			case 'category':
				if ( ! $has_cats ) {
					$categories = array();
					$has_cats   = true;
				}
				if ( $key !== '' ) {
					$categories[] = array(
						'key'   => $key,
						'label' => sanitize_text_field( (string) ( $row['label'] ?? $key ) ),
					);
				}
				break;
			case 'zone':
				if ( ! $has_zones ) {
					$zones     = array();
					$has_zones = true;
				}
				$zone = (int) ( $row['value'] ?? $row['key'] ?? 0 );
				if ( $zone >= 1 && $zone <= 99 ) {
					$zones[] = $zone;
				}
				break;
			case 'zone_cap':
				$zone_cap = (int) ( $row['value'] ?? 0 );
				$has_cap  = true;
				break;
			case 'afternoon_return':
				if ( ! $has_afternoon ) {
					$afternoon     = array();
					$has_afternoon = true;
				}
				if ( $key !== '' ) {
					$afternoon[ $key ] = (int) ( $row['value'] ?? 0 );
				}
				break;
		}
	}
	update_option(
		'mrt_price_schema',
		MRT_sanitize_price_schema(
			array(
				'ticket_types'     => $ticket_types,
				'categories'       => $categories,
				'zones'            => $zones,
				'zone_cap'         => $has_cap ? $zone_cap : $current['zone_cap'],
				'afternoon_return' => $has_afternoon ? $afternoon : $current['afternoon_return'],
			)
		)
	);
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 */
function MRT_csv_import_prices( array $files ): void {
	if ( ! function_exists( 'MRT_get_price_matrix' ) ) {
		require_once MRT_PATH . 'inc/domain/pricing/prices.php';
	}
	$matrix        = MRT_get_price_matrix();
	$ticket_keys   = MRT_price_ticket_type_keys();
	$category_keys = MRT_price_category_keys();
	$zone_keys     = MRT_price_zone_keys();
	foreach ( (array) ( $files['prices.csv'] ?? array() ) as $row ) {
		$t = sanitize_key( (string) ( $row['ticket_type'] ?? '' ) );
		$c = sanitize_key( (string) ( $row['category'] ?? '' ) );
		$z = (int) ( $row['zone'] ?? 0 );
		if (
			! in_array( $t, $ticket_keys, true )
			|| ! in_array( $c, $category_keys, true )
			|| ! in_array( $z, $zone_keys, true )
		) {
			continue;
		}
		$amount = trim( (string) ( $row['amount_sek'] ?? '' ) );
		$matrix[ $t ][ $c ][ $z ] = $amount === '' ? null : (int) $amount;
	}
	update_option( 'mrt_price_matrix', MRT_sanitize_price_matrix( $matrix ) );
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
