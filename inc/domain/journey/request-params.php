<?php
/**
 * Journey request parameter parsing (REST body).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate two station IDs for a journey.
 *
 * @param int $from_station_id From station post ID.
 * @param int $to_station_id To station post ID.
 * @return WP_Error|null Null when valid.
 */
function MRT_journey_validate_station_pair_ids( $from_station_id, $to_station_id ) {
	$from = (int) $from_station_id;
	$to   = (int) $to_station_id;
	if ( $from <= 0 || $to <= 0 ) {
		return new WP_Error(
			'mrt_journey_stations',
			__( 'Please select both departure and arrival stations.', 'museum-railway-timetable' )
		);
	}
	if ( $from === $to ) {
		return new WP_Error(
			'mrt_journey_same',
			__( 'Please select different stations for departure and arrival.', 'museum-railway-timetable' )
		);
	}

	return null;
}

/**
 * Parse from_station and to_station from input array.
 *
 * @param array<string, mixed> $input Request body.
 * @return array<string, int>|WP_Error
 */
function MRT_journey_parse_stations_pair( array $input ) {
	$from = intval( $input['from_station'] ?? 0 );
	$to   = intval( $input['to_station'] ?? 0 );
	$err  = MRT_journey_validate_station_pair_ids( $from, $to );
	if ( $err !== null ) {
		return $err;
	}

	return array(
		'from' => $from,
		'to'   => $to,
	);
}

/**
 * @param array<string, mixed> $input Request body.
 * @return array<string, mixed>|WP_Error
 */
function MRT_journey_parse_from_to_date( array $input ) {
	$pair = MRT_journey_parse_stations_pair( $input );
	if ( is_wp_error( $pair ) ) {
		return $pair;
	}
	$date = isset( $input['date'] ) ? sanitize_text_field( (string) $input['date'] ) : '';
	if ( $date === '' || ! MRT_validate_date( $date ) ) {
		return new WP_Error(
			'mrt_journey_date',
			__( 'Please select a valid date.', 'museum-railway-timetable' )
		);
	}
	return array_merge( $pair, array( 'date' => $date ) );
}

/**
 * @param array<string, mixed> $input Request body.
 * @return array<string, mixed>|WP_Error
 */
function MRT_journey_parse_trip_search_params( array $input ) {
	$base = MRT_journey_parse_from_to_date( $input );
	if ( is_wp_error( $base ) ) {
		return $base;
	}
	$trip_raw          = isset( $input['trip_type'] ) ? sanitize_text_field( (string) $input['trip_type'] ) : 'single';
	$trip_type         = ( $trip_raw === 'return' ) ? 'return' : 'single';
	$base['trip_type'] = $trip_type;
	if ( $trip_type !== 'return' ) {
		return $base;
	}
	$arrival = isset( $input['outbound_arrival'] ) ? sanitize_text_field( (string) $input['outbound_arrival'] ) : '';
	if ( $arrival === '' || ! MRT_validate_time_hhmm( $arrival ) ) {
		return new WP_Error(
			'mrt_journey_return_arrival',
			__( 'Please provide a valid outbound arrival time for return search.', 'museum-railway-timetable' )
		);
	}
	$base['outbound_arrival']       = $arrival;
	$base['outbound_service_id']    = isset( $input['outbound_service_id'] ) ? intval( $input['outbound_service_id'] ) : 0;
	$base['min_turnaround_minutes'] = isset( $input['min_turnaround_minutes'] )
		? max( 0, intval( $input['min_turnaround_minutes'] ) )
		: MRT_journey_min_transfer_minutes();
	return $base;
}

/**
 * @param array<string, mixed> $input Request body.
 * @return array<string, int>|WP_Error
 */
function MRT_journey_parse_calendar_month_params( array $input ) {
	$pair = MRT_journey_parse_stations_pair( $input );
	if ( is_wp_error( $pair ) ) {
		return $pair;
	}
	$year  = intval( $input['year'] ?? 0 );
	$month = intval( $input['month'] ?? 0 );
	if ( $year < 1970 || $year > 2100 || $month < 1 || $month > 12 ) {
		return new WP_Error(
			'mrt_calendar_month_range',
			__( 'Please select a valid month.', 'museum-railway-timetable' )
		);
	}
	return array_merge(
		$pair,
		array(
			'year'  => $year,
			'month' => $month,
		)
	);
}

/**
 * @param array<string, mixed> $input Request body.
 * @return array<string, int>|WP_Error
 */
function MRT_journey_parse_connection_detail_params( array $input ) {
	$pair = MRT_journey_parse_stations_pair( $input );
	if ( is_wp_error( $pair ) ) {
		return $pair;
	}
	$service_id = intval( $input['service_id'] ?? 0 );
	if ( $service_id <= 0 ) {
		return new WP_Error(
			'mrt_journey_service',
			__( 'Invalid service.', 'museum-railway-timetable' )
		);
	}
	return array_merge( $pair, array( 'service_id' => $service_id ) );
}
