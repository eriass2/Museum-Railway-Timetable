<?php
/**
 * Shared junction transfer matching for rail ↔ bus grid connections.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<int, array{entry: array<string, mixed>, wait: int}> $matches
 * @return array<int, array<string, mixed>>
 */
function MRT_junction_ranked_entries( array $matches, int $limit ): array {
	usort( $matches, fn( $a, $b ) => $a['wait'] <=> $b['wait'] );
	return array_map(
		fn( $row ) => $row['entry'],
		array_slice( $matches, 0, $limit )
	);
}

/**
 * @param array<string, mixed> $stop
 */
function MRT_junction_try_bus_inbound_match( string $anchor, array $stop ): ?array {
	$bus_arr = MRT_stop_effective_arrival( $stop );
	if ( $bus_arr === '' || strcmp( $bus_arr, $anchor ) > 0 ) {
		return null;
	}
	$wait = MRT_journey_transfer_wait_minutes( $bus_arr, $anchor );
	if ( $wait === null || ! MRT_journey_transfer_wait_is_valid( $bus_arr, $anchor ) ) {
		return null;
	}
	return array( 'wait' => $wait );
}

/**
 * @param array<string, mixed> $stop
 */
function MRT_junction_try_bus_outbound_match( string $anchor, array $stop ): ?array {
	$bus_dep = MRT_stop_effective_departure( $stop );
	if ( $bus_dep === '' || strcmp( $bus_dep, $anchor ) < 0 ) {
		return null;
	}
	$wait = MRT_journey_transfer_wait_minutes( $anchor, $bus_dep );
	if ( $wait === null || ! MRT_journey_transfer_wait_is_valid( $anchor, $bus_dep ) ) {
		return null;
	}
	return array( 'wait' => $wait );
}

/**
 * @param array<string, mixed> $train_stop
 */
function MRT_junction_try_train_inbound_match( string $bus_arr, array $train_stop ): ?array {
	$train_dep = MRT_stop_effective_departure( $train_stop );
	if ( $train_dep === '' || strcmp( $bus_arr, $train_dep ) > 0 ) {
		return null;
	}
	$wait = MRT_journey_transfer_wait_minutes( $bus_arr, $train_dep );
	if ( $wait === null || ! MRT_journey_transfer_wait_is_valid( $bus_arr, $train_dep ) ) {
		return null;
	}
	return array( 'wait' => $wait );
}

/**
 * @param array<string, mixed> $train_stop
 */
function MRT_junction_try_train_outbound_match( string $bus_dep, array $train_stop ): ?array {
	$anchor = MRT_train_connection_anchor_time( $train_stop );
	if ( $anchor === '' || strcmp( $bus_dep, $anchor ) < 0 ) {
		return null;
	}
	$wait = MRT_journey_transfer_wait_minutes( $anchor, $bus_dep );
	if ( $wait === null || ! MRT_journey_transfer_wait_is_valid( $anchor, $bus_dep ) ) {
		return null;
	}
	return array( 'wait' => $wait );
}

/**
 * @param array<string, mixed>|null $train_stop
 * @param array<int, array<string, mixed>> $bus_services
 * @return array<int, array{service_number: string, time_display: string, destination: string}>
 */
function MRT_buses_for_train_at_junction(
	?array $train_stop,
	string $grid_direction,
	int $junction_id,
	array $bus_services,
	int $limit = 6
): array {
	if ( ! $train_stop || $bus_services === array() ) {
		return array();
	}

	$matches = array();
	if ( $grid_direction === 'inbound' ) {
		$anchor = MRT_stop_effective_departure( $train_stop );
		if ( $anchor === '' ) {
			$anchor = MRT_stop_effective_arrival( $train_stop );
		}
		foreach ( $bus_services as $bus_data ) {
			$stop = $bus_data['stop_times'][ $junction_id ] ?? null;
			if ( ! is_array( $stop ) ) {
				continue;
			}
			$hit = MRT_junction_try_bus_inbound_match( $anchor, $stop );
			if ( $hit === null ) {
				continue;
			}
			$matches[] = array(
				'entry' => MRT_build_bus_link_entry( $bus_data, $stop ),
				'wait'  => $hit['wait'],
			);
		}
	} else {
		$anchor = MRT_train_connection_anchor_time( $train_stop );
		if ( $anchor === '' ) {
			return array();
		}
		foreach ( $bus_services as $bus_data ) {
			$stop = $bus_data['stop_times'][ $junction_id ] ?? null;
			if ( ! is_array( $stop ) ) {
				continue;
			}
			$hit = MRT_junction_try_bus_outbound_match( $anchor, $stop );
			if ( $hit === null ) {
				continue;
			}
			$matches[] = array(
				'entry' => MRT_build_bus_link_entry( $bus_data, $stop ),
				'wait'  => $hit['wait'],
			);
		}
	}

	return MRT_junction_ranked_entries( $matches, $limit );
}

/**
 * @param array<string, mixed> $bus_stop
 * @param array<int, array<string, mixed>> $rail_services
 * @return array<int, array{service_number: string, time_display: string}>
 */
function MRT_trains_for_bus_at_junction(
	array $bus_stop,
	string $grid_direction,
	int $junction_id,
	array $rail_services,
	int $limit = 6
): array {
	if ( $rail_services === array() ) {
		return array();
	}

	$matches = array();
	if ( $grid_direction === 'inbound' ) {
		$bus_arr = MRT_stop_effective_arrival( $bus_stop );
		if ( $bus_arr === '' ) {
			return array();
		}
		foreach ( $rail_services as $train_data ) {
			$train_stop = $train_data['stop_times'][ $junction_id ] ?? null;
			if ( ! is_array( $train_stop ) ) {
				continue;
			}
			$hit = MRT_junction_try_train_inbound_match( $bus_arr, $train_stop );
			if ( $hit === null ) {
				continue;
			}
			$matches[] = array(
				'entry' => MRT_build_train_link_entry( $train_data, $train_stop ),
				'wait'  => $hit['wait'],
			);
		}
	} else {
		$bus_dep = MRT_stop_effective_departure( $bus_stop );
		if ( $bus_dep === '' ) {
			return array();
		}
		foreach ( $rail_services as $train_data ) {
			$train_stop = $train_data['stop_times'][ $junction_id ] ?? null;
			if ( ! is_array( $train_stop ) ) {
				continue;
			}
			$hit = MRT_junction_try_train_outbound_match( $bus_dep, $train_stop );
			if ( $hit === null ) {
				continue;
			}
			$matches[] = array(
				'entry' => MRT_build_train_link_entry( $train_data, $train_stop ),
				'wait'  => $hit['wait'],
			);
		}
	}

	return MRT_junction_ranked_entries( $matches, $limit );
}
