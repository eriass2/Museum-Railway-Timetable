<?php
/**
 * Export routes, timetables, services, prices.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int, array<string, string>>
 */
function MRT_csv_export_train_types(): array {
	$rows  = array();
	$terms = get_terms(
		array(
			'taxonomy'   => MRT_TAXONOMY_TRAIN_TYPE,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) ) {
		return $rows;
	}
	foreach ( $terms as $term ) {
		$icon_key = MRT_get_train_type_symbol_key( $term );
		$rows[]   = array(
			'slug'      => $term->slug,
			'name'      => $term->name,
			'icon_file' => $icon_key !== '' ? 'icons/' . $icon_key . '.png' : '',
		);
	}
	return $rows;
}

/**
 * @param array<string, array<int, array<string, string>>> $tables
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_export_routes( array &$tables, array &$maps ): void {
	$meta  = MRT_csv_code_meta_keys()['routes'];
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_ROUTE,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	foreach ( $posts as $post ) {
		$code = (string) get_post_meta( $post->ID, $meta, true );
		if ( $code === '' ) {
			$code = MRT_csv_slugify( $post->post_title );
			MRT_csv_save_post_code( $post->ID, $meta, $code );
		}
		$maps['route'][ $code ] = $post->ID;
		$station_ids = (array) get_post_meta( $post->ID, 'mrt_route_stations', true );
		$start       = (string) MRT_csv_id_to_station_code( (int) ( $station_ids[0] ?? 0 ), $maps );
		$end         = (string) MRT_csv_id_to_station_code( (int) ( end( $station_ids ) ?: 0 ), $maps );
		$tables['routes.csv'][] = array(
			'route_code'         => $code,
			'title'              => $post->post_title,
			'start_station_code' => $start,
			'end_station_code'   => $end,
		);
		$seq = 1;
		foreach ( $station_ids as $sid ) {
			$tables['route_stations.csv'][] = array(
				'route_code'   => $code,
				'sequence'     => (string) $seq,
				'station_code' => MRT_csv_id_to_station_code( (int) $sid, $maps ),
			);
			++$seq;
		}
	}
}

/**
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_id_to_station_code( int $station_id, array $maps ): string {
	if ( $station_id <= 0 ) {
		return '';
	}
	foreach ( $maps['station'] as $code => $id ) {
		if ( (int) $id === $station_id ) {
			return $code;
		}
	}
	$post = get_post( $station_id );
	return $post instanceof WP_Post ? MRT_csv_slugify( $post->post_title ) : '';
}

/**
 * @param array<string, array<int, array<string, string>>> $tables
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_export_timetables( array &$tables, array &$maps ): void {
	$meta  = MRT_csv_code_meta_keys()['timetables'];
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_TIMETABLE,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	foreach ( $posts as $post ) {
		$code = (string) get_post_meta( $post->ID, $meta, true );
		if ( $code === '' ) {
			$code = MRT_csv_slugify( $post->post_title );
			MRT_csv_save_post_code( $post->ID, $meta, $code );
		}
		$maps['timetable'][ $code ] = $post->ID;
		$tables['timetables.csv'][] = array(
			'timetable_code' => $code,
			'title'          => $post->post_title,
			'colour_type'    => (string) get_post_meta( $post->ID, 'mrt_timetable_type', true ),
		);
		$dates = (array) get_post_meta( $post->ID, 'mrt_timetable_dates', true );
		foreach ( $dates as $date ) {
			$tables['timetable_dates.csv'][] = array(
				'timetable_code' => $code,
				'date'           => (string) $date,
			);
		}
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $tables
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_export_services( array &$tables, array &$maps ): void {
	$meta  = MRT_csv_code_meta_keys()['services'];
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_SERVICE,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	foreach ( $posts as $post ) {
		$code = (string) get_post_meta( $post->ID, $meta, true );
		if ( $code === '' ) {
			$code = MRT_csv_slugify( $post->post_title );
			MRT_csv_save_post_code( $post->ID, $meta, $code );
		}
		$maps['service'][ $code ] = $post->ID;
		$route_id = (int) get_post_meta( $post->ID, 'mrt_service_route_id', true );
		$tt_id    = (int) get_post_meta( $post->ID, 'mrt_service_timetable_id', true );
		$end_id   = (int) get_post_meta( $post->ID, 'mrt_service_end_station_id', true );
		$tables['services.csv'][] = array(
			'service_code'     => $code,
			'timetable_code'   => MRT_csv_id_to_timetable_code( $tt_id, $maps ),
			'route_code'       => MRT_csv_id_to_route_code( $route_id, $maps ),
			'service_number'   => (string) get_post_meta( $post->ID, 'mrt_service_number', true ),
			'end_station_code' => MRT_csv_id_to_station_code( $end_id, $maps ),
			'title'            => $post->post_title,
			'highlight_label'  => (string) get_post_meta( $post->ID, 'mrt_service_highlight_label', true ),
			'highlight_color'  => (string) get_post_meta( $post->ID, 'mrt_service_highlight_color', true ),
			'highlight_note'   => (string) get_post_meta( $post->ID, 'mrt_service_highlight_note', true ),
		);
		$terms = wp_get_object_terms( $post->ID, MRT_TAXONOMY_TRAIN_TYPE );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$tables['service_train_types.csv'][] = array(
					'service_code'    => $code,
					'train_type_slug' => $term->slug,
				);
			}
		}
	}
}

function MRT_csv_id_to_route_code( int $id, array $maps ): string {
	foreach ( $maps['route'] as $code => $rid ) {
		if ( (int) $rid === $id ) {
			return $code;
		}
	}
	return '';
}

function MRT_csv_id_to_timetable_code( int $id, array $maps ): string {
	foreach ( $maps['timetable'] as $code => $tid ) {
		if ( (int) $tid === $id ) {
			return $code;
		}
	}
	return '';
}

/**
 * @param array<string, array<string, int>> $maps
 * @return array<int, array<string, string>>
 */
function MRT_csv_export_stoptimes( array $maps ): array {
	global $wpdb;
	$table = $wpdb->prefix . 'mrt_stoptimes';
	$rows  = array();
	$results = $wpdb->get_results(
		"SELECT * FROM {$table} ORDER BY service_post_id, stop_sequence",
		ARRAY_A
	);
	if ( ! is_array( $results ) ) {
		return $rows;
	}
	$code_by_id = array();
	foreach ( $maps['service'] as $code => $id ) {
		$code_by_id[ (int) $id ] = $code;
	}
	foreach ( $results as $st ) {
		$svc_id = (int) ( $st['service_post_id'] ?? 0 );
		$code   = (string) ( $code_by_id[ $svc_id ] ?? '' );
		if ( $code === '' ) {
			continue;
		}
		$rows[] = array(
			'service_code'    => $code,
			'sequence'        => (string) (int) ( $st['stop_sequence'] ?? 0 ),
			'station_code'    => MRT_csv_id_to_station_code( (int) ( $st['station_post_id'] ?? 0 ), $maps ),
			'arrival_time'    => (string) ( $st['arrival_time'] ?? '' ),
			'departure_time'  => (string) ( $st['departure_time'] ?? '' ),
			'pickup_allowed'    => (string) (int) ( $st['pickup_allowed'] ?? 1 ),
			'dropoff_allowed'   => (string) (int) ( $st['dropoff_allowed'] ?? 1 ),
			'approximate_time'  => (string) (int) ( $st['approximate_time'] ?? 0 ),
		);
	}
	return $rows;
}

/**
 * @return array<int, array<string, string>>
 */
function MRT_csv_export_settings(): array {
	$settings = MRT_get_plugin_settings();
	$rows     = array();
	foreach (
		array(
			'enabled',
			'note',
			'operator_name',
			'ticket_url',
			'hero_background_url',
			'wizard_beta_enabled',
			'min_transfer_minutes',
			'max_transfer_minutes',
			'max_transfers',
			'afternoon_return_threshold_minutes',
		) as $key
	) {
		$val = $settings[ $key ] ?? '';
		if ( is_bool( $val ) ) {
			$val = $val ? '1' : '0';
		}
		$rows[] = array(
			'key' => $key,
			'value' => (string) $val,
		);
	}
	return $rows;
}

/**
 * @return array<int, array<string, string>>
 */
function MRT_csv_export_price_schema(): array {
	if ( ! function_exists( 'MRT_get_price_schema' ) ) {
		require_once MRT_PATH . 'inc/domain/pricing/price-schema.php';
	}
	$schema = MRT_get_price_schema();
	$rows   = array();
	foreach ( $schema['ticket_types'] as $row ) {
		$rows[] = array(
			'kind'  => 'ticket_type',
			'key'   => $row['key'],
			'label' => $row['label'],
			'value' => '',
		);
	}
	foreach ( $schema['categories'] as $row ) {
		$rows[] = array(
			'kind'  => 'category',
			'key'   => $row['key'],
			'label' => $row['label'],
			'value' => '',
		);
	}
	foreach ( $schema['zones'] as $zone ) {
		$rows[] = array(
			'kind'  => 'zone',
			'key'   => '',
			'label' => '',
			'value' => (string) $zone,
		);
	}
	$rows[] = array(
		'kind'  => 'zone_cap',
		'key'   => '',
		'label' => '',
		'value' => (string) $schema['zone_cap'],
	);
	foreach ( $schema['afternoon_return'] as $key => $amount ) {
		$rows[] = array(
			'kind'  => 'afternoon_return',
			'key'   => (string) $key,
			'label' => '',
			'value' => (string) (int) $amount,
		);
	}
	return $rows;
}

/**
 * @return array<int, array<string, string>>
 */
function MRT_csv_export_prices(): array {
	if ( ! function_exists( 'MRT_get_price_matrix' ) ) {
		require_once MRT_PATH . 'inc/domain/pricing/prices.php';
	}
	$matrix = MRT_get_price_matrix();
	$rows   = array();
	foreach ( $matrix as $ticket => $cats ) {
		foreach ( $cats as $cat => $zones ) {
			foreach ( $zones as $zone => $amount ) {
				$rows[] = array(
					'ticket_type' => (string) $ticket,
					'category'    => (string) $cat,
					'zone'        => (string) $zone,
					'amount_sek'  => $amount === null ? '' : (string) (int) $amount,
				);
			}
		}
	}
	return $rows;
}
