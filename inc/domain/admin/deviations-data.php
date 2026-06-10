<?php
/**
 * Deviation row helpers (domain data, no admin UI).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

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
 * @param string[] $timetable_dates Timetable dates.
 * @param string[] $used_dates Used deviation dates.
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
 * One deviation row for the timetable table.
 *
 * @return array{service_id: int, date: string, trip_label: string, train_type_id: int, notice: string}
 */
function MRT_timetable_deviation_row_data( WP_Post $service, string $date ): array {
	$types   = get_post_meta( $service->ID, 'mrt_service_train_types_by_date', true );
	$notices = get_post_meta( $service->ID, 'mrt_service_notices_by_date', true );
	if ( ! is_array( $types ) ) {
		$types = array();
	}
	if ( ! is_array( $notices ) ) {
		$notices = array();
	}

	return array(
		'service_id'    => (int) $service->ID,
		'date'          => $date,
		'trip_label'    => (string) $service->post_title,
		'train_type_id' => isset( $types[ $date ] ) ? (int) $types[ $date ] : 0,
		'notice'        => isset( $notices[ $date ] ) ? (string) $notices[ $date ] : '',
	);
}

/**
 * All deviation rows for a timetable, sorted by date then trip.
 *
 * @param WP_Post[] $services Service posts.
 * @return array<int, array{service_id: int, date: string, trip_label: string, train_type_id: int, notice: string}>
 */
function MRT_collect_timetable_deviation_rows( array $services ): array {
	$rows = array();
	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		foreach ( MRT_service_deviation_dates( (int) $service->ID ) as $date ) {
			$rows[] = MRT_timetable_deviation_row_data( $service, $date );
		}
	}
	usort(
		$rows,
		static function ( array $a, array $b ): int {
			$cmp = strcmp( $a['date'], $b['date'] );
			return $cmp !== 0 ? $cmp : strcmp( $a['trip_label'], $b['trip_label'] );
		}
	);
	return $rows;
}
