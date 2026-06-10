<?php
/**
 * Stop time pickup/dropoff modes (schema v3).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @return array<int, string> */
function MRT_stop_time_mode_values(): array {
	return array( 'none', 'scheduled', 'on_request' );
}

/** @param mixed $mode Raw mode value. */
function MRT_stop_time_mode_is_valid( $mode ): bool {
	return is_string( $mode ) && in_array( $mode, MRT_stop_time_mode_values(), true );
}

/**
 * @param mixed  $mode    Raw mode value.
 * @param string $default Fallback when invalid.
 */
function MRT_stop_time_mode_sanitize( $mode, string $default = 'none' ): string {
	if ( ! MRT_stop_time_mode_is_valid( $mode ) ) {
		return $default;
	}
	return (string) $mode;
}

/**
 * @param mixed $mode Raw mode value.
 */
function MRT_stop_time_mode_rank( $mode ): int {
	$ranks = array(
		'none'        => 0,
		'scheduled'   => 1,
		'on_request'  => 2,
	);
	$key = MRT_stop_time_mode_sanitize( $mode, 'none' );
	return $ranks[ $key ];
}

/**
 * @param array<string, mixed> $row Stop row with optional mode columns.
 */
function MRT_stop_time_effective_pickup( array $row ): string {
	$avg = MRT_stop_time_mode_sanitize( $row['avg_pickup_mode'] ?? 'none', 'none' );
	$ank = MRT_stop_time_mode_sanitize( $row['ank_pickup_mode'] ?? 'none', 'none' );
	return MRT_stop_time_mode_rank( $avg ) >= MRT_stop_time_mode_rank( $ank ) ? $avg : $ank;
}

/**
 * @param array<string, mixed> $row Stop row with optional mode columns.
 */
function MRT_stop_time_effective_dropoff( array $row ): string {
	$avg = MRT_stop_time_mode_sanitize( $row['avg_dropoff_mode'] ?? 'none', 'none' );
	$ank = MRT_stop_time_mode_sanitize( $row['ank_dropoff_mode'] ?? 'none', 'none' );
	return MRT_stop_time_mode_rank( $avg ) >= MRT_stop_time_mode_rank( $ank ) ? $avg : $ank;
}

/**
 * @param array<string, mixed> $row Stop row.
 */
function MRT_stop_time_allows_pickup( array $row ): bool {
	return MRT_stop_time_effective_pickup( $row ) !== 'none';
}

/**
 * @param array<string, mixed> $row Stop row.
 */
function MRT_stop_time_allows_dropoff( array $row ): bool {
	return MRT_stop_time_effective_dropoff( $row ) !== 'none';
}

/**
 * @param array<string, mixed> $row Stop row.
 */
function MRT_stop_time_on_request_pickup( array $row ): bool {
	return MRT_stop_time_effective_pickup( $row ) === 'on_request';
}

/**
 * @param array<string, mixed> $row Stop row.
 */
function MRT_stop_time_on_request_dropoff( array $row ): bool {
	return MRT_stop_time_effective_dropoff( $row ) === 'on_request';
}

/**
 * Apply in_service_timetable = 0 ⇒ approximate_time = 1.
 *
 * @param array<string, mixed> $row Prepared stop row.
 * @return array<string, mixed>
 */
function MRT_stop_time_apply_in_service_rules( array $row ): array {
	if ( empty( $row['in_service_timetable'] ) ) {
		$row['approximate_time'] = 1;
	}
	return $row;
}

/**
 * Expand effective modes to four stored columns (admin simplified edit).
 *
 * @param array<string, mixed> $row Row with pickup_mode / dropoff_mode or effective already set.
 * @return array<string, mixed>
 */
function MRT_stop_time_expand_effective_modes( array $row ): array {
	$pickup  = MRT_stop_time_mode_sanitize(
		$row['pickup_mode'] ?? MRT_stop_time_effective_pickup( $row ),
		'scheduled'
	);
	$dropoff = MRT_stop_time_mode_sanitize(
		$row['dropoff_mode'] ?? MRT_stop_time_effective_dropoff( $row ),
		'scheduled'
	);
	$row['ank_pickup_mode']   = $pickup;
	$row['ank_dropoff_mode']  = $dropoff;
	$row['avg_pickup_mode']   = $pickup;
	$row['avg_dropoff_mode']  = $dropoff;
	unset( $row['pickup_mode'], $row['dropoff_mode'] );
	return $row;
}

/**
 * @param array<string, mixed> $row DB or CSV row.
 * @return array<string, mixed>
 */
function MRT_stop_time_row_with_defaults( array $row ): array {
	$defaults = array(
		'ank_pickup_mode'        => 'none',
		'ank_dropoff_mode'       => 'none',
		'avg_pickup_mode'        => 'scheduled',
		'avg_dropoff_mode'       => 'scheduled',
		'approximate_time'       => 0,
		'in_service_timetable'   => 1,
	);
	foreach ( $defaults as $key => $value ) {
		if ( ! array_key_exists( $key, $row ) || $row[ $key ] === '' || $row[ $key ] === null ) {
			$row[ $key ] = $value;
		}
	}
	foreach ( array( 'ank_pickup_mode', 'ank_dropoff_mode', 'avg_pickup_mode', 'avg_dropoff_mode' ) as $col ) {
		$row[ $col ] = MRT_stop_time_mode_sanitize( $row[ $col ], 'none' );
	}
	$row['approximate_time']     = ! empty( $row['approximate_time'] ) ? 1 : 0;
	$row['in_service_timetable'] = ! empty( $row['in_service_timetable'] ) ? 1 : 0;
	return MRT_stop_time_apply_in_service_rules( $row );
}

/**
 * Build DB insert/update columns for stop modes.
 *
 * @param array<string, mixed> $row Prepared row.
 * @return array<string, mixed>
 */
function MRT_stop_time_mode_db_fields( array $row ): array {
	$row = MRT_stop_time_row_with_defaults( $row );
	return array(
		'ank_pickup_mode'        => $row['ank_pickup_mode'],
		'ank_dropoff_mode'       => $row['ank_dropoff_mode'],
		'avg_pickup_mode'        => $row['avg_pickup_mode'],
		'avg_dropoff_mode'       => $row['avg_dropoff_mode'],
		'approximate_time'       => (int) $row['approximate_time'],
		'in_service_timetable'   => (int) $row['in_service_timetable'],
	);
}

/**
 * @param array<string, mixed> $row Stop row.
 * @return array<string, mixed>
 */
function MRT_stop_time_editor_api_row( array $row ): array {
	$row = MRT_stop_time_row_with_defaults( $row );
	return array(
		'pickup_mode'            => MRT_stop_time_effective_pickup( $row ),
		'dropoff_mode'           => MRT_stop_time_effective_dropoff( $row ),
		'ank_pickup_mode'        => $row['ank_pickup_mode'],
		'ank_dropoff_mode'       => $row['ank_dropoff_mode'],
		'avg_pickup_mode'        => $row['avg_pickup_mode'],
		'avg_dropoff_mode'       => $row['avg_dropoff_mode'],
		'approximate_time'       => ! empty( $row['approximate_time'] ),
		'in_service_timetable'   => ! empty( $row['in_service_timetable'] ),
	);
}

/**
 * Parse CSV / API mode columns into a prepared row fragment.
 *
 * @param array<string, mixed> $source Raw input.
 * @return array<string, mixed>|WP_Error
 */
function MRT_stop_time_modes_from_input( array $source ) {
	$has_modes = isset( $source['avg_pickup_mode'] ) || isset( $source['ank_pickup_mode'] );
	if ( $has_modes ) {
		$row = array(
			'ank_pickup_mode'      => $source['ank_pickup_mode'] ?? 'none',
			'ank_dropoff_mode'     => $source['ank_dropoff_mode'] ?? 'none',
			'avg_pickup_mode'      => $source['avg_pickup_mode'] ?? 'none',
			'avg_dropoff_mode'     => $source['avg_dropoff_mode'] ?? 'none',
			'approximate_time'     => (int) ( $source['approximate_time'] ?? 0 ),
			'in_service_timetable' => (int) ( $source['in_service_timetable'] ?? 1 ),
		);
	} else {
		$row = array(
			'pickup_mode'            => $source['pickup_mode'] ?? $source['pickup'] ?? 'scheduled',
			'dropoff_mode'           => $source['dropoff_mode'] ?? $source['dropoff'] ?? 'scheduled',
			'approximate_time'       => ! empty( $source['approximate'] ) || ! empty( $source['approximate_time'] ) ? 1 : 0,
			'in_service_timetable'   => (int) ( $source['in_service_timetable'] ?? 1 ),
		);
		$row = MRT_stop_time_expand_effective_modes( $row );
	}
	foreach ( array( 'ank_pickup_mode', 'ank_dropoff_mode', 'avg_pickup_mode', 'avg_dropoff_mode' ) as $col ) {
		if ( ! MRT_stop_time_mode_is_valid( $row[ $col ] ?? '' ) ) {
			return new WP_Error(
				'invalid_mode',
				sprintf(
					/* translators: %s: column name */
					__( 'Invalid stop time mode in %s.', MRT_TEXT_DOMAIN ),
					$col
				)
			);
		}
	}
	return MRT_stop_time_row_with_defaults( $row );
}
