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

/**
 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}>|null $display_columns
 * @param array<int, WP_Post>|null $station_posts
 */
function MRT_timetable_row_times_json(
	string $kind,
	string $label,
	int $station_id,
	array $services,
	array $info,
	bool $use_from_display,
	bool $use_to_display,
	?array $display_columns = null,
	?array $station_posts = null
): array {
	$cells = array();
	if ( $display_columns === null || $station_posts === null ) {
		foreach ( $services as $service_data ) {
			$stop    = $service_data['stop_times'][ $station_id ] ?? null;
			$cells[] = MRT_timetable_time_cell_json( $stop, $use_from_display, $use_to_display, $kind );
		}
	} else {
		foreach ( $display_columns as $column ) {
			$idx          = MRT_timetable_display_column_service_idx( $column, $station_id, $kind, $station_posts );
			$service_data = $services[ $idx ] ?? array();
			$stop         = $service_data['stop_times'][ $station_id ] ?? null;
			$cell         = MRT_timetable_time_cell_json( $stop, $use_from_display, $use_to_display, $kind );
			$service_id   = MRT_timetable_service_id_from_data( $service_data );
			if ( $service_id > 0 ) {
				$cell['serviceId'] = $service_id;
			}
			$cells[] = $cell;
		}
	}

	return array(
		'kind'      => $kind,
		'label'     => $label,
		'stationId' => $station_id,
		'cells'     => $cells,
	);
}

function MRT_timetable_time_cell_json(
	$stop,
	bool $use_from_display = false,
	bool $use_to_display = false,
	string $row_kind = ''
): array {
	$cell = array(
		'text'            => MRT_timetable_time_cell_text( $stop, $use_from_display, $use_to_display, $row_kind ),
		'approximateTime' => false,
	);
	if ( ! is_array( $stop ) ) {
		$cell['edit'] = array(
			'arrival'         => '',
			'departure'       => '',
			'stopsHere'       => false,
			'pickupMode'      => 'scheduled',
			'dropoffMode'     => 'scheduled',
			'approximateTime' => false,
		);
		return $cell;
	}
	$cell['approximateTime'] = ! empty( $stop['approximate_time'] );
	$cell['edit']            = array(
		'arrival'         => (string) ( $stop['arrival_time'] ?? '' ),
		'departure'       => (string) ( $stop['departure_time'] ?? '' ),
		'stopsHere'       => true,
		'pickupMode'      => MRT_stop_time_effective_pickup( $stop ),
		'dropoffMode'     => MRT_stop_time_effective_dropoff( $stop ),
		'approximateTime' => ! empty( $stop['approximate_time'] ),
	);
	return $cell;
}

function MRT_timetable_time_cell_text(
	$stop,
	bool $use_from_display,
	bool $use_to_display,
	string $row_kind = ''
): string {
	if ( ! is_array( $stop ) ) {
		return '—';
	}
	if ( $use_from_display ) {
		$display = MRT_get_from_row_display_stop_time( $stop );
		return MRT_format_stop_time_display( $display ?? $stop, $row_kind );
	}
	if ( $use_to_display ) {
		$display = MRT_get_to_row_display_stop_time( $stop );
		return MRT_format_stop_time_display( $display ?? $stop, $row_kind );
	}
	return MRT_format_stop_time_display( $stop, $row_kind );
}

/**
 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}>|null $display_columns
 */
function MRT_timetable_train_change_row_json(
	WP_Post $station,
	array $services,
	array $info,
	?array $display_columns = null
): ?array {
	$map = MRT_get_station_train_change_map( (int) $station->ID, $station );
	if ( $map === array() ) {
		return null;
	}

	$cells = array();
	if ( $display_columns === null ) {
		foreach ( $services as $idx => $service_data ) {
			unset( $service_data );
			$number   = (string) ( $info[ $idx ]['service_number'] ?? '' );
			$transfer = $map[ $number ] ?? null;
			$cells[]  = array(
				'vehicles' => $transfer ? array( MRT_timetable_vehicle_json( $transfer['typeName'], $transfer['serviceNumber'] ) ) : array(),
			);
		}
	} else {
		foreach ( $display_columns as $column ) {
			$idx      = (int) $column['primary_idx'];
			$number   = (string) ( $info[ $idx ]['service_number'] ?? '' );
			$transfer = $map[ $number ] ?? null;
			$cells[]  = array(
				'vehicles' => $transfer ? array( MRT_timetable_vehicle_json( $transfer['typeName'], $transfer['serviceNumber'] ) ) : array(),
			);
		}
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
