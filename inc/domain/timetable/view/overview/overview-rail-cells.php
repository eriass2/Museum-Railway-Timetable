<?php
/**
 * Timetable overview rail JSON: time cells
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_timetable_row_times_json(
	string $kind,
	string $label,
	int $station_id,
	array $services,
	array $info,
	bool $use_from_display,
	bool $use_to_display
): array {
	$cells = array();
	foreach ( $services as $idx => $service_data ) {
		unset( $idx, $info );
		$stop    = $service_data['stop_times'][ $station_id ] ?? null;
		$cells[] = MRT_timetable_time_cell_json( $stop, $use_from_display, $use_to_display );
	}

	return array(
		'kind'       => $kind,
		'label'      => $label,
		'stationId'  => $station_id,
		'cells'      => $cells,
	);
}

function MRT_timetable_time_cell_json( $stop, bool $use_from_display = false, bool $use_to_display = false ): array {
	$cell = array(
		'text'            => MRT_timetable_time_cell_text( $stop, $use_from_display, $use_to_display ),
		'approximateTime' => false,
	);
	if ( ! is_array( $stop ) ) {
		$cell['edit'] = array(
			'arrival'         => '',
			'departure'       => '',
			'stopsHere'       => false,
			'pickupAllowed'   => true,
			'dropoffAllowed'  => true,
			'approximateTime' => false,
		);
		return $cell;
	}
	$cell['approximateTime'] = ! empty( $stop['approximate_time'] );
	$cell['edit']            = array(
		'arrival'         => (string) ( $stop['arrival_time'] ?? '' ),
		'departure'       => (string) ( $stop['departure_time'] ?? '' ),
		'stopsHere'       => true,
		'pickupAllowed'   => ! empty( $stop['pickup_allowed'] ),
		'dropoffAllowed'  => ! empty( $stop['dropoff_allowed'] ),
		'approximateTime' => ! empty( $stop['approximate_time'] ),
	);
	return $cell;
}

function MRT_timetable_time_cell_text( $stop, bool $use_from_display, bool $use_to_display ): string {
	if ( ! is_array( $stop ) ) {
		return '—';
	}
	if ( $use_from_display ) {
		$display = MRT_get_from_row_display_stop_time( $stop );
		return MRT_format_stop_time_display( $display ?? $stop );
	}
	if ( $use_to_display ) {
		$display = MRT_get_to_row_display_stop_time( $stop );
		return MRT_format_stop_time_display( $display ?? $stop );
	}
	return MRT_format_stop_time_display( $stop );
}

function MRT_timetable_train_change_row_json(
	WP_Post $station,
	array $services,
	array $info
): ?array {
	$map = MRT_get_station_train_change_map( (int) $station->ID, $station );
	if ( $map === array() ) {
		return null;
	}

	$cells = array();
	foreach ( $services as $idx => $service_data ) {
		$number   = (string) ( $info[ $idx ]['service_number'] ?? '' );
		$transfer = $map[ $number ] ?? null;
		$cells[]  = array(
			'vehicles' => $transfer ? array( MRT_timetable_vehicle_json( $transfer['typeName'], $transfer['serviceNumber'] ) ) : array(),
		);
	}

	return array(
		'kind'  => 'trainChange',
		'label' => __( 'Tågbyte:', 'museum-railway-timetable' ),
		'cells' => $cells,
	);
}

function MRT_timetable_vehicle_json( string $type_name, string $service_number, string $detail = '' ): array {
	$term = MRT_get_train_type_term_by_label( $type_name );
	return array(
		'typeName'      => $type_name,
		'serviceNumber' => $service_number,
		'iconKey'       => $term ? MRT_get_train_type_symbol_key( $term ) : 'diesel',
		'detail'        => $detail,
	);
}
