<?php
/**
 * Stop time mode columns for PHPUnit fixtures (schema v3).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

/**
 * Four mode columns plus in_service_timetable for DB / display rows.
 *
 * @return array<string, mixed>
 */
function MRT_test_stop_mode_columns( string $pickup = 'scheduled', string $dropoff = 'scheduled' ): array {
	return array(
		'ank_pickup_mode'      => $pickup,
		'ank_dropoff_mode'     => $dropoff,
		'avg_pickup_mode'      => $pickup,
		'avg_dropoff_mode'     => $dropoff,
		'in_service_timetable' => 1,
	);
}

/** @return array<string, mixed> pickup=1, dropoff=0 (P only) */
function MRT_test_stop_modes_pickup_only(): array {
	return MRT_test_stop_mode_columns( 'on_request', 'none' );
}

/** @return array<string, mixed> pickup=0, dropoff=1 (A only) */
function MRT_test_stop_modes_dropoff_only(): array {
	return MRT_test_stop_mode_columns( 'none', 'on_request' );
}

/** @return array<string, mixed> both allowed with scheduled times */
function MRT_test_stop_modes_both_scheduled(): array {
	return MRT_test_stop_mode_columns( 'scheduled', 'scheduled' );
}

/** @return array<string, mixed> both allowed, no time (X) */
function MRT_test_stop_modes_both_on_request(): array {
	return MRT_test_stop_mode_columns( 'on_request', 'on_request' );
}

/** @return array<string, mixed> pass-through (|) */
function MRT_test_stop_modes_none(): array {
	return MRT_test_stop_mode_columns( 'none', 'none' );
}

/**
 * Mode columns from CSV / fixture row (stoptimes.csv v3).
 *
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function MRT_test_stop_mode_columns_from_fixture( array $row ): array {
	return array(
		'ank_pickup_mode'      => (string) ( $row['ank_pickup_mode'] ?? 'scheduled' ),
		'ank_dropoff_mode'     => (string) ( $row['ank_dropoff_mode'] ?? 'scheduled' ),
		'avg_pickup_mode'      => (string) ( $row['avg_pickup_mode'] ?? 'scheduled' ),
		'avg_dropoff_mode'     => (string) ( $row['avg_dropoff_mode'] ?? 'scheduled' ),
		'approximate_time'     => (int) ( $row['approximate_time'] ?? 0 ),
		'in_service_timetable' => (int) ( $row['in_service_timetable'] ?? 1 ),
	);
}
