<?php
/**
 * Rail ↔ bus connection row assembly.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<int, array<string, mixed>> $rail_services
 * @param array<int, array<string, mixed>> $bus_services
 * @return array<int, array{train: array{service_number: string, time_display: string}, buses: array<int, array{service_number: string, time_display: string, destination: string}>}>
 */
function MRT_build_train_to_bus_connection_rows(
	array $rail_services,
	array $bus_services,
	int $junction_id,
	string $direction
): array {
	$rows = array();
	foreach ( $rail_services as $train_data ) {
		$stop = $train_data['stop_times'][ $junction_id ] ?? null;
		$rows[] = array(
			'train' => is_array( $stop ) ? MRT_build_train_link_entry( $train_data, $stop ) : array(
				'service_number' => MRT_connection_service_number( $train_data ),
				'time_display'   => '—',
			),
			'buses' => MRT_buses_for_train_at_junction(
				is_array( $stop ) ? $stop : null,
				$direction,
				$junction_id,
				$bus_services
			),
		);
	}
	return $rows;
}

/**
 * @param array<int, array<string, mixed>> $bus_services
 * @param array<int, array<string, mixed>> $rail_services
 * @param array<string, mixed>             $branch_group
 * @return array<int, array{bus: array{service_number: string, time_display: string, destination: string}, trains: array<int, array{service_number: string, time_display: string}>}>
 */
function MRT_build_bus_to_train_connection_rows(
	array $bus_services,
	array $rail_services,
	array $branch_group,
	int $junction_id,
	string $direction
): array {
	$rows = array();
	foreach ( $bus_services as $bus_data ) {
		$from_id = (int) ( $branch_group['stations'][0] ?? 0 );
		$stop    = $bus_data['stop_times'][ $junction_id ] ?? $bus_data['stop_times'][ $from_id ] ?? null;
		$rows[]  = array(
			'bus'    => is_array( $stop )
				? array_merge(
					MRT_build_bus_link_entry( $bus_data, $stop ),
					array( 'destination' => MRT_get_bus_connection_destination_label( $bus_data ) )
				)
				: array(
					'service_number' => MRT_connection_service_number( $bus_data ),
					'time_display'   => '—',
					'destination'    => MRT_get_bus_connection_destination_label( $bus_data ),
				),
			'trains' => is_array( $stop )
				? MRT_trains_for_bus_at_junction( $stop, $direction, $junction_id, $rail_services )
				: array(),
		);
	}
	return $rows;
}
