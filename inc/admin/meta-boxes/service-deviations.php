<?php
/**
 * Service deviation helpers (data layer for timetable deviations tab).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Traffic days that have a deviation on this trip.
 *
 * @return string[] YYYY-MM-DD, sorted.
 */
function MRT_service_deviation_dates( int $service_id ): array {
	$types   = get_post_meta( $service_id, 'mrt_service_train_types_by_date', true );
	$notices = get_post_meta( $service_id, 'mrt_service_notices_by_date', true );
	if ( ! is_array( $types ) ) {
		$types = array();
	}
	if ( ! is_array( $notices ) ) {
		$notices = array();
	}
	$dates = array_unique( array_merge( array_keys( $types ), array_keys( $notices ) ) );
	sort( $dates );
	return $dates;
}

/**
 * Number of traffic days with a deviation on this trip.
 */
function MRT_count_service_deviations( int $service_id ): int {
	return count( MRT_service_deviation_dates( $service_id ) );
}

/**
 * Dates on the timetable not yet used for deviations on this trip.
 *
 * @param string[] $timetable_dates
 * @param string[] $used_dates
 * @return string[]
 */
function MRT_service_deviation_available_dates( array $timetable_dates, array $used_dates ): array {
	$used = array_flip( $used_dates );
	$out  = array();
	foreach ( $timetable_dates as $date ) {
		if ( ! isset( $used[ $date ] ) ) {
			$out[] = $date;
		}
	}
	return $out;
}

/**
 * Train types for deviation UI (id + name).
 *
 * @param array<int, WP_Term> $all_train_types
 * @return array<int, array{id: int, name: string}>
 */
function MRT_service_deviation_train_type_options( array $all_train_types ): array {
	$out = array();
	foreach ( $all_train_types as $term ) {
		$out[] = array(
			'id'   => (int) $term->term_id,
			'name' => (string) $term->name,
		);
	}
	return $out;
}
