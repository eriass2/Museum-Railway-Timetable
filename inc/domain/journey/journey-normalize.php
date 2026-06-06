<?php
/**
 * Normalize journey results for JSON API / frontends
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Door-to-door minutes from first leg departure to last leg arrival.
 *
 * @param array<int, array<string, mixed>> $legs Leg payloads
 * @return int|null
 */
function MRT_normalize_total_duration_from_legs( array $legs ) {
	if ( $legs === array() ) {
		return 0;
	}
	$first = $legs[0];
	$last  = $legs[ count( $legs ) - 1 ];
	$dep   = (string) ( $first['from_departure'] ?? '' );
	$arr   = (string) ( $last['to_arrival'] ?? '' );
	return MRT_format_duration_minutes( $dep, $arr );
}

/**
 * Short train-type label for multi-leg (one value if all same, else "a / b")
 *
 * @param array<int, array<string, mixed>> $legs Leg payloads
 * @return string
 */
function MRT_journey_multi_leg_train_type_label( array $legs ) {
	$tts = array();
	foreach ( $legs as $leg ) {
		$t = (string) ( $leg['train_type'] ?? '' );
		if ( $t !== '' ) {
			$tts[] = $t;
		}
	}
	$tts = array_values( array_unique( $tts ) );
	if ( count( $tts ) <= 1 ) {
		return (string) ( $tts[0] ?? '' );
	}
	return implode( ' / ', $tts );
}

/**
 * Human-readable label for a multi-leg journey.
 *
 * @param array<string, mixed>             $item Multi-leg bundle
 * @param array<int, array<string, mixed>> $legs Leg payloads
 * @return string
 */
function MRT_journey_multi_leg_service_label( array $item, array $legs ) {
	$titles = array();
	foreach ( $legs as $leg ) {
		$sid = (int) ( $leg['service_id'] ?? 0 );
		if ( $sid > 0 ) {
			$titles[] = get_the_title( $sid ) ?: ( '#' . $sid );
		}
	}
	if ( count( $legs ) === 2 ) {
		$transfer_id = (int) ( $item['transfer_station_id'] ?? 0 );
		$hub         = $transfer_id > 0 ? get_the_title( $transfer_id ) : '';
		if ( $hub !== '' && isset( $titles[0], $titles[1] ) ) {
			return sprintf(
				/* translators: 1: first service name, 2: transfer station name, 3: second service name */
				__( '%1$s · Change at %2$s · %3$s', 'museum-railway-timetable' ),
				$titles[0],
				$hub,
				$titles[1]
			);
		}
	}
	if ( count( $legs ) > 2 ) {
		$parts     = array();
		$leg_count = count( $legs );
		for ( $i = 0; $i < $leg_count; $i++ ) {
			if ( $i > 0 ) {
				$hub_id = (int) ( $legs[ $i - 1 ]['to_station_id'] ?? 0 );
				$hub    = $hub_id > 0 ? get_the_title( $hub_id ) : '';
				if ( $hub !== '' ) {
					$parts[] = sprintf(
						/* translators: %s: transfer station name */
						__( 'Change at %s', 'museum-railway-timetable' ),
						$hub
					);
				}
			}
			if ( isset( $titles[ $i ] ) ) {
				$parts[] = $titles[ $i ];
			}
		}
		return implode( ' · ', $parts );
	}
	return implode( ' · ', $titles );
}

/**
 * Build segments / notice for one service connection
 *
 * @param int    $service_id Service
 * @param int    $from_id From station
 * @param int    $to_id To station
 * @param string $dateYmd Date
 * @return array<string, mixed>
 */
function MRT_normalize_segments_single_service( $service_id, $from_id, $to_id, $dateYmd ) {
	$detail = MRT_get_connection_journey_detail( $service_id, $from_id, $to_id );
	$notice = MRT_get_service_notice( $service_id, $dateYmd );
	return array(
		'segments'         => $detail['stops'],
		'duration_minutes' => $detail['duration_minutes'],
		'notice'           => $notice,
		'is_cancelled'     => MRT_notice_indicates_cancelled( $notice ),
	);
}

/**
 * One-leg wrapped direct → flat connection row for normalizer
 *
 * @param array<string, mixed> $item Wrapped direct multi
 * @return array<string, mixed>|null
 */
function MRT_flatten_wrapped_direct_connection( array $item ) {
	if ( ( $item['connection_type'] ?? '' ) !== 'direct' || empty( $item['legs'][0] ) ) {
		return null;
	}
	$leg = $item['legs'][0];
	$sid = (int) ( $leg['service_id'] ?? 0 );
	if ( $sid <= 0 ) {
		return null;
	}
	$route_id = get_post_meta( $sid, 'mrt_service_route_id', true );
	$dest     = MRT_get_service_destination( $sid );
	return array(
		'service_id'     => $sid,
		'service_name'   => get_the_title( $sid ) ?: ( '#' . $sid ),
		'route_name'     => $route_id ? get_the_title( (int) $route_id ) : '',
		'destination'    => $dest['destination'],
		'direction'      => $dest['direction'],
		'train_type'     => (string) ( $leg['train_type'] ?? '' ),
		'from_departure' => (string) ( $leg['from_departure'] ?? '' ),
		'from_arrival'   => '',
		'to_arrival'     => (string) ( $leg['to_arrival'] ?? '' ),
		'to_departure'   => '',
		'from_sequence'  => 0,
		'to_sequence'    => 0,
	);
}

/**
 * Normalize multi-leg bundle for API
 *
 * @param array<string, mixed> $item Must contain legs[]
 * @param string               $dateYmd Date
 * @return array<string, mixed>
 */
function MRT_normalize_multi_leg_for_api( array $item, $dateYmd ) {
	$legs     = $item['legs'];
	$duration = MRT_normalize_total_duration_from_legs( $legs );
	$notices  = array();
	$cancelled = false;
	foreach ( $legs as $leg ) {
		$nid = isset( $leg['service_id'] ) ? (int) $leg['service_id'] : 0;
		if ( $nid <= 0 ) {
			continue;
		}
		$n = MRT_get_service_notice( $nid, $dateYmd );
		if ( MRT_notice_indicates_cancelled( $n ) ) {
			$cancelled = true;
		}
		if ( $n !== '' ) {
			$notices[] = $n;
		}
	}
	$last      = count( $legs ) - 1;
	$dep_first = (string) ( $legs[0]['from_departure'] ?? '' );
	$arr_last  = (string) ( $legs[ $last ]['to_arrival'] ?? '' );
	$transfer_wait = null;
	if ( count( $legs ) > 1 ) {
		$transfer_wait = MRT_journey_transfer_wait_minutes(
			(string) ( $legs[0]['to_arrival'] ?? '' ),
			(string) ( $legs[1]['from_departure'] ?? '' )
		);
	}
	return array(
		'connection_type'     => $item['connection_type'] ?? 'transfer',
		'transfer_station_id' => $item['transfer_station_id'] ?? null,
		'transfer_wait_minutes' => $transfer_wait,
		'legs'                => $legs,
		'duration_minutes'    => $duration,
		'segments'            => array(),
		'notice'              => implode( "\n", array_unique( $notices ) ),
		'is_cancelled'        => $cancelled,
		'service_id'          => isset( $legs[0]['service_id'] ) ? (int) $legs[0]['service_id'] : 0,
		'departure'           => $dep_first,
		'arrival'             => $arr_last,
		'from_departure'      => $dep_first,
		'to_arrival'          => $arr_last,
		'service_name'        => MRT_journey_multi_leg_service_label( $item, $legs ),
		'train_type'          => MRT_journey_multi_leg_train_type_label( $legs ),
	);
}

/**
 * Unified connection payload (direct row or multi-leg bundle)
 *
 * @param array<string, mixed> $item Either flat connection or multi-leg array
 * @param string               $dateYmd Date
 * @param int                  $from_station_id Search from
 * @param int                  $to_station_id Search to
 * @return array<string, mixed>
 */
function MRT_normalize_connection_for_api( $item, $dateYmd, $from_station_id, $to_station_id ) {
	$flat = MRT_flatten_wrapped_direct_connection( $item );
	if ( $flat !== null ) {
		$item = $flat;
	}
	if ( isset( $item['legs'] ) && is_array( $item['legs'] ) && count( $item['legs'] ) > 1 ) {
		return MRT_normalize_multi_leg_for_api( $item, $dateYmd );
	}
	$conn  = $item;
	$sid   = intval( $conn['service_id'] ?? 0 );
	$dep   = MRT_connection_row_departure_at_from( $conn );
	$arr   = ! empty( $conn['to_arrival'] ) ? (string) $conn['to_arrival'] : (string) ( $conn['to_departure'] ?? '' );
	$tt    = $sid > 0 ? MRT_get_service_train_type_for_date( $sid, $dateYmd ) : null;
	$num   = $sid > 0 ? get_post_meta( $sid, 'mrt_service_number', true ) : '';
	$extra = MRT_normalize_segments_single_service( $sid, $from_station_id, $to_station_id, $dateYmd );
	$dur   = $extra['duration_minutes'];
	if ( $dur === null ) {
		$dur = MRT_format_duration_minutes( $dep, $arr );
	}
	$leg   = $sid > 0 ? MRT_journey_build_leg_segment( $sid, $from_station_id, $to_station_id, $dateYmd ) : null;
	$legs  = $leg !== null ? array( $leg ) : array();
	return array(
		'connection_type'     => 'direct',
		'transfer_station_id' => null,
		'legs'                => $legs,
		'service_id'          => $sid,
		'departure'           => $dep,
		'arrival'             => $arr,
		'from_departure'      => $dep,
		'to_arrival'          => $arr,
		'duration_minutes'    => $dur,
		'train_type'          => $tt ? $tt->name : (string) ( $conn['train_type'] ?? '' ),
		'train_type_slug'     => $tt ? $tt->slug : '',
		'train_type_icon'     => $tt
			? MRT_get_train_type_symbol_key( $tt )
			: MRT_get_train_type_symbol_key_from_label( (string) ( $conn['train_type'] ?? '' ) ),
		'service_name'        => (string) ( $conn['service_name'] ?? '' ),
		'service_number'      => $num !== '' && $num !== null ? (string) $num : ( $sid > 0 ? (string) $sid : '' ),
		'route_name'          => (string) ( $conn['route_name'] ?? '' ),
		'destination'         => $leg !== null
			? (string) ( $leg['destination'] ?? '' )
			: MRT_journey_leg_destination_label( $to_station_id ),
		'direction'           => (string) ( $conn['direction'] ?? '' ),
		'segments'            => $extra['segments'],
		'notice'              => $extra['notice'],
		'is_cancelled'        => ! empty( $extra['is_cancelled'] ),
	);
}

/**
 * Reduce wizard results: hub transfers only, drop redundant options.
 *
 * @param array<int, array<string, mixed>> $connections Normalized API connections
 * @return array<int, array<string, mixed>>
 */
function MRT_journey_filter_wizard_connections( array $connections ): array {
	if ( $connections === array() ) {
		return $connections;
	}
	$directs     = array();
	$transfers   = array();
	$direct_deps = array();
	foreach ( $connections as $conn ) {
		if ( (string) ( $conn['connection_type'] ?? '' ) === 'direct' ) {
			$directs[] = $conn;
			$dep       = MRT_journey_normalized_departure_hhmm( $conn );
			if ( $dep !== '' ) {
				$direct_deps[ $dep ] = true;
			}
			continue;
		}
		$transfers[] = $conn;
	}
	$kept = MRT_journey_filter_transfer_connections( $transfers, $direct_deps, $directs );
	$out  = array_merge( $directs, $kept );
	return (array) apply_filters( 'mrt_journey_wizard_connections', $out, $connections );
}

/**
 * @param array<int, array<string, mixed>> $transfers
 * @param array<string, true>              $direct_deps
 * @param array<int, array<string, mixed>> $directs
 * @return array<int, array<string, mixed>>
 */
function MRT_journey_filter_transfer_connections( array $transfers, array $direct_deps, array $directs ): array {
	$earliest_direct = MRT_journey_earliest_departure_hhmm( $directs );
	$kept            = array();
	$best_by_dep     = array();
	foreach ( $transfers as $conn ) {
		$dep = MRT_journey_normalized_departure_hhmm( $conn );
		if ( $dep !== '' && isset( $direct_deps[ $dep ] ) ) {
			continue;
		}
		$arr = MRT_journey_normalized_arrival_hhmm( $conn );
		if ( $dep === '' || $arr === '' ) {
			$kept[] = $conn;
			continue;
		}
		if ( ! isset( $best_by_dep[ $dep ] ) ) {
			$best_by_dep[ $dep ] = count( $kept );
			$kept[]              = $conn;
			continue;
		}
		$idx     = $best_by_dep[ $dep ];
		$current = $kept[ $idx ];
		if ( MRT_compare_hhmm( $arr, MRT_journey_normalized_arrival_hhmm( $current ) ) < 0 ) {
			$kept[ $idx ] = $conn;
		}
	}
	return array_values(
		array_filter(
			$kept,
			static function ( array $conn ) use ( $directs, $earliest_direct ): bool {
				return ! MRT_journey_transfer_dominated_by_direct( $conn, $directs, $earliest_direct );
			}
		)
	);
}

/**
 * Earliest direct departure HH:MM, or empty when none.
 *
 * @param array<int, array<string, mixed>> $directs
 */
function MRT_journey_earliest_departure_hhmm( array $directs ): string {
	$earliest = '';
	foreach ( $directs as $direct ) {
		$dep = MRT_journey_normalized_departure_hhmm( $direct );
		if ( $dep === '' ) {
			continue;
		}
		if ( $earliest === '' || MRT_compare_hhmm( $dep, $earliest ) < 0 ) {
			$earliest = $dep;
		}
	}
	return $earliest;
}

/**
 * Transfer is redundant when a direct arrives same or earlier after a later departure.
 */
function MRT_journey_transfer_dominated_by_direct(
	array $transfer,
	array $directs,
	string $earliest_direct_dep
): bool {
	$t_dep = MRT_journey_normalized_departure_hhmm( $transfer );
	$t_arr = MRT_journey_normalized_arrival_hhmm( $transfer );
	if ( $t_dep === '' || $t_arr === '' ) {
		return false;
	}
	if ( $earliest_direct_dep !== '' && MRT_compare_hhmm( $t_dep, $earliest_direct_dep ) < 0 ) {
		return false;
	}
	foreach ( $directs as $direct ) {
		$d_dep = MRT_journey_normalized_departure_hhmm( $direct );
		$d_arr = MRT_journey_normalized_arrival_hhmm( $direct );
		if ( $d_dep === '' || $d_arr === '' ) {
			continue;
		}
		if ( MRT_compare_hhmm( $d_arr, $t_arr ) > 0 ) {
			continue;
		}
		if ( MRT_compare_hhmm( $d_dep, $t_dep ) <= 0 ) {
			continue;
		}
		return true;
	}
	return false;
}

/**
 * One-way journey search: normalized API connections for wizard REST.
 *
 * @return array<int, array<string, mixed>>
 */
function MRT_journey_find_normalized_connections( int $from_station_id, int $to_station_id, string $dateYmd ): array {
	$min_xfer   = MRT_journey_min_transfer_minutes();
	$raw_multi  = MRT_find_multi_leg_connections(
		$from_station_id,
		$to_station_id,
		$dateYmd,
		$min_xfer,
		true
	);
	$normalized = array();
	foreach ( $raw_multi as $item ) {
		$normalized[] = MRT_normalize_connection_for_api(
			$item,
			$dateYmd,
			$from_station_id,
			$to_station_id
		);
	}

	$normalized = MRT_journey_filter_wizard_connections( $normalized );

	return MRT_journey_sort_outbound_connections(
		$normalized,
		$from_station_id,
		$to_station_id,
		$dateYmd
	);
}
