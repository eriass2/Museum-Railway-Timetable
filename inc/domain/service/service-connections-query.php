<?php
/**
 * SQL-backed departures and station-to-station connections.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map stoptime rows to departure result format.
 *
 * @param array<int, array<string, mixed>> $rows Raw DB rows.
 * @return array<int, array<string, mixed>> Formatted departure data.
 */
function MRT_map_departure_rows_to_result( $rows ) {
	$out = array();
	foreach ( $rows as $r ) {
		$service_id       = intval( $r['service_post_id'] );
		$destination_data = MRT_get_service_destination( $service_id );
		$out[]            = array(
			'service_id'     => $service_id,
			'service_name'   => get_the_title( $service_id ) ?: ( '#' . $service_id ),
			'arrival_time'   => $r['arrival_time'],
			'departure_time' => $r['departure_time'],
			'destination'    => $destination_data['destination'],
			'direction'      => $destination_data['direction'],
		);
	}
	return $out;
}

/**
 * Get next departures from a station after a given time.
 *
 * @param int    $station_id  Station post ID.
 * @param array  $service_ids Array of service post IDs.
 * @param string $timeHHMM    Time in HH:MM format.
 * @param int    $limit       Maximum number of departures to return.
 * @param bool   $with_arrival Whether to include arrival times.
 * @return array<int, array<string, mixed>> Array of departure data.
 */
function MRT_next_departures_for_station( $station_id, $service_ids, $timeHHMM, $limit = 5, $with_arrival = false ) {
	global $wpdb;
	if ( ! $service_ids || $station_id <= 0 || $limit <= 0 ) {
		return array();
	}
	if ( empty( $timeHHMM ) || ! MRT_validate_time_hhmm( $timeHHMM ) ) {
		return array();
	}

	$table    = $wpdb->prefix . 'mrt_stoptimes';
	$in       = implode( ',', array_map( 'intval', $service_ids ) );
	$col_time = $with_arrival ? 'COALESCE(departure_time, arrival_time)' : 'departure_time';

	$sql = $wpdb->prepare(
		"
        SELECT s.service_post_id, s.arrival_time, s.departure_time, s.stop_sequence
        FROM $table s
        WHERE s.station_post_id = %d
          AND s.service_post_id IN ($in)
          AND (
              (s.departure_time IS NOT NULL AND s.departure_time >= %s)
              OR (s.departure_time IS NULL AND s.arrival_time IS NOT NULL AND s.arrival_time >= %s)
          )
        ORDER BY $col_time ASC
        LIMIT %d
    ",
		$station_id,
		$timeHHMM,
		$timeHHMM,
		$limit
	);

	$rows = $wpdb->get_results( $sql, ARRAY_A );
	if ( MRT_check_db_error( 'MRT_next_departures_for_station' ) || ! $rows ) {
		return array();
	}

	return MRT_map_departure_rows_to_result( $rows );
}

/**
 * Map connection DB rows to result format.
 *
 * @param array<int, array<string, mixed>> $rows    Raw DB rows.
 * @param string                           $dateYmd Date in YYYY-MM-DD format.
 * @return array<int, array<string, mixed>> Formatted connection data.
 */
function MRT_map_connection_rows_to_result( $rows, $dateYmd ) {
	$connections = array();
	foreach ( $rows as $r ) {
		$service_id       = intval( $r['service_post_id'] );
		$destination_data = MRT_get_service_destination( $service_id );
		$train_type       = MRT_get_service_train_type_for_date( $service_id, $dateYmd );
		$route_id         = get_post_meta( $service_id, 'mrt_service_route_id', true );
		$connections[]    = array(
			'service_id'     => $service_id,
			'service_name'   => get_the_title( $service_id ) ?: ( '#' . $service_id ),
			'route_name'     => $route_id ? get_the_title( $route_id ) : '',
			'destination'    => $destination_data['destination'],
			'direction'      => $destination_data['direction'],
			'train_type'     => $train_type ? $train_type->name : '',
			'from_departure' => $r['from_departure'] ?: '',
			'from_arrival'   => $r['from_arrival'] ?: '',
			'to_arrival'     => $r['to_arrival'] ?: '',
			'to_departure'   => $r['to_departure'] ?: '',
			'from_sequence'  => intval( $r['from_sequence'] ),
			'to_sequence'    => intval( $r['to_sequence'] ),
		);
	}
	return $connections;
}

/**
 * First departure time at "from" leg of a connection row.
 *
 * @param array<string, mixed> $conn Row from MRT_find_connections.
 * @return string HH:MM or empty.
 */
function MRT_connection_row_departure_at_from( array $conn ) {
	if ( ! empty( $conn['from_departure'] ) ) {
		return (string) $conn['from_departure'];
	}
	return ! empty( $conn['from_arrival'] ) ? (string) $conn['from_arrival'] : '';
}

/**
 * Find connections (services) from one station to another on a specific date.
 *
 * @param int    $from_station_id From station post ID.
 * @param int    $to_station_id   To station post ID.
 * @param string $dateYmd         Date in YYYY-MM-DD format.
 * @return array<int, array<string, mixed>> Array of connection data.
 */
function MRT_find_connections( $from_station_id, $to_station_id, $dateYmd ) {
	global $wpdb;
	if ( $from_station_id <= 0 || $to_station_id <= 0 || $from_station_id === $to_station_id ) {
		return array();
	}
	if ( ! MRT_validate_date( $dateYmd ) ) {
		return array();
	}

	$service_ids = MRT_services_running_on_date( $dateYmd );
	if ( empty( $service_ids ) ) {
		return array();
	}

	$table = $wpdb->prefix . 'mrt_stoptimes';
	$in    = implode( ',', array_map( 'intval', $service_ids ) );
	$sql   = $wpdb->prepare(
		"
        SELECT from_st.service_post_id, from_st.departure_time as from_departure,
            from_st.arrival_time as from_arrival, from_st.stop_sequence as from_sequence,
            to_st.arrival_time as to_arrival, to_st.departure_time as to_departure,
            to_st.stop_sequence as to_sequence
        FROM $table from_st
        INNER JOIN $table to_st ON from_st.service_post_id = to_st.service_post_id
        WHERE from_st.station_post_id = %d AND to_st.station_post_id = %d
          AND from_st.service_post_id IN ($in) AND from_st.stop_sequence < to_st.stop_sequence
          AND from_st.pickup_allowed = 1
          AND to_st.dropoff_allowed = 1
        ORDER BY COALESCE(from_st.departure_time, from_st.arrival_time) ASC, from_st.stop_sequence ASC
    ",
		$from_station_id,
		$to_station_id
	);

	$rows = $wpdb->get_results( $sql, ARRAY_A );
	if ( MRT_check_db_error( 'MRT_find_connections' ) || ! $rows ) {
		return array();
	}

	return MRT_map_connection_rows_to_result( $rows, $dateYmd );
}

/**
 * Connections where first leg departs not before a minimum time (same day).
 *
 * @param int    $from_station_id From station.
 * @param int    $to_station_id   To station.
 * @param string $dateYmd         Date YYYY-MM-DD.
 * @param string $earliest_hhmm   Minimum departure at from station.
 * @return array<int, array<string, mixed>>
 */
function MRT_find_connections_departing_not_before( $from_station_id, $to_station_id, $dateYmd, $earliest_hhmm ) {
	if ( $from_station_id <= 0 || $to_station_id <= 0 || $from_station_id === $to_station_id ) {
		return array();
	}
	if ( ! MRT_validate_date( $dateYmd ) || ! MRT_validate_time_hhmm( $earliest_hhmm ) ) {
		return array();
	}
	$filtered = array();
	foreach ( MRT_find_connections( $from_station_id, $to_station_id, $dateYmd ) as $row ) {
		$dep = MRT_connection_row_departure_at_from( $row );
		if ( $dep !== '' && MRT_validate_time_hhmm( $dep ) && MRT_compare_hhmm( $dep, $earliest_hhmm ) >= 0 ) {
			$filtered[] = $row;
		}
	}
	return $filtered;
}
