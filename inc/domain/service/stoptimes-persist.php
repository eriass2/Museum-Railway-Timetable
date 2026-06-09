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
	$pickup  = isset( $stop['pickup'] ) && $stop['pickup'] == '1' ? 1 : 0;
	$dropoff = isset( $stop['dropoff'] ) && $stop['dropoff'] == '1' ? 1 : 0;

	return array(
		'station_post_id' => $station_id,
		'stop_sequence'   => $sequence,
		'arrival_time'    => $arrival ?: null,
		'departure_time'  => $departure ?: null,
		'pickup_allowed'  => $pickup,
		'dropoff_allowed' => $dropoff,
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
		array(
			'service_post_id' => $service_id,
			'station_post_id' => $row['station_post_id'],
			'stop_sequence'   => $row['stop_sequence'],
			'arrival_time'    => $row['arrival_time'],
			'departure_time'  => $row['departure_time'],
			'pickup_allowed'  => $row['pickup_allowed'],
			'dropoff_allowed' => $row['dropoff_allowed'],
		),
		array( '%d', '%d', '%d', '%s', '%s', '%d', '%d' )
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
