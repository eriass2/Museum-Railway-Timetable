<?php
/**
 * Stop time bulk persist helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * When only arrival or departure is set, use the same time for both (A6).
 *
 * @return array{0: string, 1: string}
 */
function MRT_mirror_stoptime_arrival_departure( string $arrival, string $departure ): array {
	if ( $arrival !== '' && $departure === '' ) {
		$departure = $arrival;
	} elseif ( $departure !== '' && $arrival === '' ) {
		$arrival = $departure;
	}
	return array( $arrival, $departure );
}

/**
 * First stop shows departure only; last stop arrival only (matches anslagstidtabell).
 *
 * @param array<string, mixed> $row Prepared stop row.
 */
function MRT_trim_stoptime_row_endpoint_times( array $row, bool $is_first, bool $is_last ): array {
	if ( $is_first && $is_last ) {
		return $row;
	}
	if ( $is_first ) {
		$row['arrival_time'] = null;
	}
	if ( $is_last ) {
		$row['departure_time'] = null;
	}
	return $row;
}

/**
 * Normalize one submitted stop time row for save_all.
 *
 * @param array<string, mixed> $stop Stop data.
 * @param int                  $sequence Stop sequence.
 * @return array<string, mixed>|WP_Error|null Null when the row is intentionally omitted.
 */
function MRT_normalize_stoptime_for_save_all( array $stop, int $sequence ) {
	$station_id = intval( $stop['station_id'] ?? 0 );
	$stops_here = isset( $stop['stops_here'] ) && $stop['stops_here'] == '1';
	if ( ! $stops_here ) {
		return null;
	}
	if ( $station_id <= 0 ) {
		return new WP_Error( 'invalid_station', __( 'Invalid station in stop times.', MRT_TEXT_DOMAIN ) );
	}
	$arrival   = sanitize_text_field( $stop['arrival'] ?? '' );
	$departure = sanitize_text_field( $stop['departure'] ?? '' );
	list( $arrival, $departure ) = MRT_mirror_stoptime_arrival_departure( $arrival, $departure );
	if ( ( $arrival && ! MRT_validate_time_hhmm( $arrival ) ) || ( $departure && ! MRT_validate_time_hhmm( $departure ) ) ) {
		return new WP_Error( 'invalid_time', __( 'Invalid time format in stop times. Use HH:MM.', MRT_TEXT_DOMAIN ) );
	}
	$pickup  = sanitize_text_field( $stop['pickup_mode'] ?? '' );
	$dropoff = sanitize_text_field( $stop['dropoff_mode'] ?? '' );
	$approx  = ! empty( $stop['approximate'] ) || ! empty( $stop['approximate_time'] ) ? 1 : 0;
	$in_svc  = isset( $stop['in_service_timetable'] ) ? (int) $stop['in_service_timetable'] : 1;

	$modes = MRT_stop_time_modes_from_input(
		array(
			'pickup_mode'            => $pickup !== '' ? $pickup : 'scheduled',
			'dropoff_mode'           => $dropoff !== '' ? $dropoff : 'scheduled',
			'approximate_time'       => $approx,
			'in_service_timetable'   => $in_svc,
			'ank_pickup_mode'        => $stop['ank_pickup_mode'] ?? null,
			'ank_dropoff_mode'       => $stop['ank_dropoff_mode'] ?? null,
			'avg_pickup_mode'        => $stop['avg_pickup_mode'] ?? null,
			'avg_dropoff_mode'       => $stop['avg_dropoff_mode'] ?? null,
		)
	);
	if ( is_wp_error( $modes ) ) {
		return $modes;
	}

	return array_merge(
		array(
			'station_post_id'  => $station_id,
			'stop_sequence'    => $sequence,
			'arrival_time'     => $arrival ?: null,
			'departure_time'   => $departure ?: null,
		),
		MRT_stop_time_mode_db_fields( $modes )
	);
}

/**
 * @param array<int, array<string, mixed>> $stops Stop data.
 * @return array<int, array<string, mixed>>|WP_Error
 */
function MRT_prepare_stoptimes_for_save_all( array $stops ) {
	$prepared = array();
	$sequence = 1;
	foreach ( $stops as $stop ) {
		if ( ! is_array( $stop ) ) {
			return new WP_Error( 'invalid_stop', __( 'Invalid stops data.', MRT_TEXT_DOMAIN ) );
		}
		$row = MRT_normalize_stoptime_for_save_all( $stop, $sequence );
		if ( is_wp_error( $row ) ) {
			return $row;
		}
		if ( $row !== null ) {
			$prepared[] = $row;
			++$sequence;
		}
	}
	$count = count( $prepared );
	if ( $count > 0 ) {
		foreach ( $prepared as $index => $row ) {
			$prepared[ $index ] = MRT_trim_stoptime_row_endpoint_times(
				$row,
				$index === 0,
				$index === $count - 1
			);
		}
	}
	return $prepared;
}

/**
 * @param wpdb  $wpdb WordPress DB object.
 * @param array $row Prepared stop row.
 * @param int   $service_id Service ID.
 * @return int|false Inserted row ID, or false on failure.
 */
function MRT_insert_prepared_stoptime_for_save_all( $wpdb, array $row, int $service_id ) {
	$table  = $wpdb->prefix . 'mrt_stoptimes';
	$result = $wpdb->insert(
		$table,
		array_merge(
			array(
				'service_post_id' => $service_id,
				'station_post_id' => $row['station_post_id'],
				'stop_sequence'   => $row['stop_sequence'],
				'arrival_time'    => $row['arrival_time'],
				'departure_time'  => $row['departure_time'],
			),
			MRT_stop_time_mode_db_fields( $row )
		),
		array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' )
	);
	if ( $result === false ) {
		MRT_check_db_error( 'MRT_save_service_stoptimes_bulk' );
		return false;
	}
	return (int) $wpdb->insert_id;
}

/**
 * @param wpdb       $wpdb WordPress DB object.
 * @param array<int> $inserted_ids Row IDs to delete.
 */
function MRT_cleanup_inserted_stoptimes_for_save_all( $wpdb, array $inserted_ids ): void {
	$table = $wpdb->prefix . 'mrt_stoptimes';
	foreach ( $inserted_ids as $id ) {
		$wpdb->delete( $table, array( 'id' => (int) $id ), array( '%d' ) );
	}
}

/**
 * @param wpdb       $wpdb WordPress DB object.
 * @param int        $service_id Service ID.
 * @param array<int> $replacement_ids Replacement row IDs.
 */
function MRT_delete_old_stoptimes_after_save_all( $wpdb, int $service_id, array $replacement_ids ): bool {
	$table = $wpdb->prefix . 'mrt_stoptimes';
	if ( $replacement_ids === array() ) {
		return $wpdb->delete( $table, array( 'service_post_id' => $service_id ), array( '%d' ) ) !== false;
	}

	$in  = implode( ',', array_map( 'intval', $replacement_ids ) );
	$sql = $wpdb->prepare(
		"DELETE FROM $table WHERE service_post_id = %d AND id NOT IN ($in)",
		$service_id
	);
	return $wpdb->query( $sql ) !== false;
}
