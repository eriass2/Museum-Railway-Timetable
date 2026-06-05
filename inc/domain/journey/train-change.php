<?php
/**
 * Timetable train-change rows (e.g. ångtåg 71 → dieseltåg 61 at Marielund).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Passenger-facing "mot …" label for a leg alighting station.
 */
function MRT_journey_leg_destination_label( int $to_station_id ): string {
	if ( $to_station_id <= 0 ) {
		return '';
	}
	$title = get_the_title( $to_station_id );
	return is_string( $title ) ? $title : '';
}

/**
 * Train-change mappings keyed by transfer station title.
 *
 * @return array<string, array<string, array{typeName: string, serviceNumber: string}>>
 */
function MRT_journey_train_change_by_station(): array {
	return array(
		'Marielund' => array(
			'71' => array(
				'typeName'      => 'Dieseltåg',
				'serviceNumber' => '61',
			),
			'63' => array(
				'typeName'      => 'Rälsbuss',
				'serviceNumber' => '97',
			),
			'60' => array(
				'typeName'      => 'Ångtåg',
				'serviceNumber' => '74',
			),
			'96' => array(
				'typeName'      => 'Dieseltåg',
				'serviceNumber' => '64',
			),
		),
	);
}

/**
 * Continuation vehicle after a scheduled train change, if any.
 *
 * @return array{typeName: string, serviceNumber: string}|null
 */
function MRT_journey_train_change_continuation( string $station_title, string $service_number ): ?array {
	$map = MRT_journey_train_change_by_station();
	if ( ! isset( $map[ $station_title ][ $service_number ] ) ) {
		return null;
	}
	return $map[ $station_title ][ $service_number ];
}

/**
 * Whether a trip on one service passes through a change station between from and to.
 */
function MRT_journey_service_spans_station(
	int $service_id,
	int $from_station_id,
	int $to_station_id,
	int $via_station_id
): bool {
	$ordered = MRT_get_service_stop_times_ordered( $service_id );
	if ( $ordered === array() ) {
		return false;
	}
	$from_i = MRT_journey_find_stop_index( $ordered, $from_station_id );
	$to_i   = MRT_journey_find_stop_index( $ordered, $to_station_id );
	$via_i  = MRT_journey_find_stop_index( $ordered, $via_station_id );
	if ( $from_i === null || $to_i === null || $via_i === null ) {
		return false;
	}
	return $from_i < $via_i && $via_i < $to_i;
}

/**
 * Resolve change-station ID on a service route by post title.
 */
function MRT_journey_train_change_station_id_on_service( int $service_id, string $station_title ): int {
	foreach ( MRT_get_service_stop_times_ordered( $service_id ) as $row ) {
		$station_id = (int) ( $row['station_post_id'] ?? 0 );
		if ( $station_id > 0 && get_the_title( $station_id ) === $station_title ) {
			return $station_id;
		}
	}
	return 0;
}

/**
 * Apply display train numbers/types after a PDF-style train change.
 *
 * @param array<string, mixed> $leg Leg payload (mutated)
 */
function MRT_journey_apply_train_change_leg_display( array &$leg, array $continuation ): void {
	$leg['service_number'] = $continuation['serviceNumber'];
	$leg['train_type']       = $continuation['typeName'];
	$term                    = MRT_get_train_type_term_by_label( $continuation['typeName'] );
	if ( $term ) {
		$leg['train_type_slug'] = $term->slug;
		$leg['train_type_icon'] = MRT_get_train_type_symbol_key( $term );
	}
}

/**
 * Split a wrapped direct connection when it crosses a known train change.
 *
 * @param array<string, mixed> $item Wrapped direct multi-leg bundle
 * @return array<string, mixed>
 */
function MRT_journey_split_train_change_legs(
	array $item,
	string $dateYmd,
	int $from_station_id,
	int $to_station_id
): array {
	if ( ( $item['connection_type'] ?? '' ) !== 'direct' ) {
		return $item;
	}
	$legs = $item['legs'] ?? array();
	if ( ! is_array( $legs ) || count( $legs ) !== 1 ) {
		return $item;
	}
	$service_id = (int) ( $legs[0]['service_id'] ?? 0 );
	if ( $service_id <= 0 ) {
		return $item;
	}
	$service_number = (string) get_post_meta( $service_id, 'mrt_service_number', true );
	foreach ( MRT_journey_train_change_by_station() as $station_title => $changes ) {
		if ( ! isset( $changes[ $service_number ] ) ) {
			continue;
		}
		$change_id = MRT_journey_train_change_station_id_on_service( $service_id, $station_title );
		if ( $change_id <= 0 ) {
			continue;
		}
		if ( ! MRT_journey_service_spans_station( $service_id, $from_station_id, $to_station_id, $change_id ) ) {
			continue;
		}
		$leg1 = MRT_journey_build_leg_segment( $service_id, $from_station_id, $change_id, $dateYmd );
		$leg2 = MRT_journey_build_leg_segment( $service_id, $change_id, $to_station_id, $dateYmd );
		if ( $leg1 === null || $leg2 === null ) {
			return $item;
		}
		MRT_journey_apply_train_change_leg_display( $leg2, $changes[ $service_number ] );
		return array(
			'connection_type'     => 'transfer',
			'transfer_station_id' => $change_id,
			'legs'                => array( $leg1, $leg2 ),
		);
	}
	return $item;
}
