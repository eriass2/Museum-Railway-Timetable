<?php
/**
 * Trip price rules: zones
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_pricing_zone_count( int $zones ): int {
	if ( $zones <= 0 ) {
		return MRT_price_zone_cap();
	}
	return max( 1, min( MRT_price_zone_cap(), $zones ) );
}

function MRT_parse_trip_clock_minutes( string $hhmm ): ?int {
	if ( ! preg_match( '/^(\d{1,2}):(\d{2})$/', trim( $hhmm ), $matches ) ) {
		return null;
	}
	return ( (int) $matches[1] * 60 ) + (int) $matches[2];
}

function MRT_qualifies_for_afternoon_return(
	string $trip_type,
	string $outbound_departure,
	string $inbound_departure,
	int $threshold_minutes = 900
): bool {
	if ( $trip_type !== 'return' ) {
		return false;
	}
	$out_minutes = MRT_parse_trip_clock_minutes( $outbound_departure );
	$in_minutes  = MRT_parse_trip_clock_minutes( $inbound_departure );
	if ( $out_minutes === null || $in_minutes === null ) {
		return false;
	}
	return $out_minutes >= $threshold_minutes && $in_minutes >= $threshold_minutes;
}

function MRT_zones_for_station_pair( int $from_id, int $to_id, array $station_zones_map ): int {
	$from_zones = $station_zones_map[ $from_id ] ?? array();
	$to_zones   = $station_zones_map[ $to_id ] ?? array();
	$best       = MRT_price_zone_cap();
	if ( $from_zones === array() || $to_zones === array() ) {
		return $best;
	}
	foreach ( $from_zones as $from_zone ) {
		foreach ( $to_zones as $to_zone ) {
			$span = MRT_zones_pair_span( (int) $from_zone, (int) $to_zone );
			$best = min( $best, $span );
		}
	}
	return MRT_pricing_zone_count( $best );
}

function MRT_zones_pair_span( int $from_zone, int $to_zone ): int {
	if ( $from_zone === $to_zone ) {
		return 1;
	}
	return max( 2, abs( $to_zone - $from_zone ) );
}

function MRT_zones_distinct_on_path( array $station_ids, array $station_zones_map ): int {
	$seen = array();
	foreach ( $station_ids as $station_id ) {
		$sid = (int) $station_id;
		if ( $sid <= 0 ) {
			continue;
		}
		foreach ( $station_zones_map[ $sid ] ?? array() as $zone ) {
			$seen[ (int) $zone ] = true;
		}
	}
	return count( $seen );
}

function MRT_zones_min_range_on_path( array $station_ids, array $station_zones_map ): int {
	if ( $station_ids === array() ) {
		return 0;
	}
	$first_id    = (int) $station_ids[0];
	$first_zones = $station_zones_map[ $first_id ] ?? array();
	if ( $first_zones === array() ) {
		return 0;
	}
	/** @var array<string, array{min: int, max: int}> $states */
	$states = array();
	foreach ( $first_zones as $zone ) {
		$zone = (int) $zone;
		$states[ $zone . ',' . $zone ] = array(
			'min' => $zone,
			'max' => $zone,
		);
	}
	for ( $i = 1, $count = count( $station_ids ); $i < $count; $i++ ) {
		$sid          = (int) $station_ids[ $i ];
		$zone_options = $station_zones_map[ $sid ] ?? array();
		if ( $zone_options === array() ) {
			return 0;
		}
		$next = array();
		foreach ( $states as $state ) {
			foreach ( $zone_options as $zone ) {
				$zone   = (int) $zone;
				$min_z  = min( $state['min'], $zone );
				$max_z  = max( $state['max'], $zone );
				$key    = $min_z . ',' . $max_z;
				$next[ $key ] = array(
					'min' => $min_z,
					'max' => $max_z,
				);
			}
		}
		$states = $next;
	}
	$best = MRT_price_zone_cap();
	foreach ( $states as $state ) {
		$best = min( $best, MRT_zones_pair_span( $state['min'], $state['max'] ) );
	}
	return $best;
}

function MRT_zones_for_station_path( array $station_ids, array $station_zones_map ): int {
	$distinct = MRT_zones_distinct_on_path( $station_ids, $station_zones_map );
	if ( $distinct === 0 ) {
		return MRT_price_zone_cap();
	}
	$range = MRT_zones_min_range_on_path( $station_ids, $station_zones_map );
	if ( $range <= 0 ) {
		return MRT_price_zone_cap();
	}
	return MRT_pricing_zone_count( min( $range, $distinct ) );
}

function MRT_collect_journey_leg_station_ids( array $legs ): array {
	$ids   = array();
	$seen  = array();
	foreach ( $legs as $leg ) {
		if ( ! is_array( $leg ) ) {
			continue;
		}
		$service_id = (int) ( $leg['service_id'] ?? 0 );
		$from_id    = (int) ( $leg['from_station_id'] ?? 0 );
		$to_id      = (int) ( $leg['to_station_id'] ?? 0 );
		if ( $service_id <= 0 || $from_id <= 0 || $to_id <= 0 ) {
			continue;
		}
		$detail = MRT_get_connection_journey_detail( $service_id, $from_id, $to_id );
		foreach ( (array) ( $detail['stops'] ?? array() ) as $stop ) {
			$sid = (int) ( $stop['station_id'] ?? 0 );
			if ( $sid <= 0 || isset( $seen[ $sid ] ) ) {
				continue;
			}
			$seen[ $sid ] = true;
			$ids[]        = $sid;
		}
	}
	return $ids;
}

function MRT_zones_for_journey_legs( array $legs, ?array $station_zones_map = null ): int {
	$station_zones_map = $station_zones_map ?? MRT_get_station_price_zones_map();
	$station_ids       = MRT_collect_journey_leg_station_ids( $legs );
	if ( $station_ids === array() ) {
		return MRT_price_zone_cap();
	}
	return MRT_zones_for_station_path( $station_ids, $station_zones_map );
}

function MRT_zones_for_trip_price(
	int $from_id,
	int $to_id,
	?array $outbound_legs = null,
	?array $inbound_legs = null
): int {
	if ( $outbound_legs !== null && $outbound_legs !== array() ) {
		return MRT_pricing_zone_count( MRT_zones_for_journey_legs( $outbound_legs ) );
	}
	if ( $inbound_legs !== null && $inbound_legs !== array() ) {
		return MRT_pricing_zone_count( MRT_zones_for_journey_legs( $inbound_legs ) );
	}
	return MRT_zones_for_station_pair_ids( $from_id, $to_id );
}

function MRT_parse_trip_price_legs_param( string $raw ): ?array {
	$raw = trim( $raw );
	if ( $raw === '' ) {
		return null;
	}
	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		return null;
	}
	$legs = array();
	foreach ( $decoded as $leg ) {
		if ( ! is_array( $leg ) ) {
			continue;
		}
		$service_id = (int) ( $leg['service_id'] ?? 0 );
		$from_id    = (int) ( $leg['from_station_id'] ?? 0 );
		$to_id      = (int) ( $leg['to_station_id'] ?? 0 );
		if ( $service_id <= 0 || $from_id <= 0 || $to_id <= 0 ) {
			continue;
		}
		$legs[] = array(
			'service_id'       => $service_id,
			'from_station_id'  => $from_id,
			'to_station_id'    => $to_id,
		);
	}
	return $legs === array() ? null : $legs;
}

function MRT_zones_for_station_pair_ids( int $from_id, int $to_id ): int {
	return MRT_zones_for_station_pair( $from_id, $to_id, MRT_get_station_price_zones_map() );
}
