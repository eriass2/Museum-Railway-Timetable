<?php
/**
 * Pre-warm journey wizard server transients (calendar months).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Station-code pairs to warm (filterable).
 *
 * @return array<int, array{from: string, to: string}>
 */
function MRT_journey_cache_warm_station_code_pairs(): array {
	$default = array(
		array(
			'from' => 'uppsala-ostra',
			'to'   => 'fjallnora',
		),
		array(
			'from' => 'uppsala-ostra',
			'to'   => 'linnes-hammarby',
		),
		array(
			'from' => 'faringe',
			'to'   => 'uppsala-ostra',
		),
	);
	$pairs = apply_filters( 'mrt_journey_cache_warm_station_code_pairs', $default );
	return is_array( $pairs ) ? $pairs : $default;
}

/**
 * Resolve warm pairs to station post IDs.
 *
 * @return array<int, array{from: int, to: int}>
 */
function MRT_journey_cache_warm_station_id_pairs(): array {
	if ( ! function_exists( 'MRT_station_post_id_from_station_code' ) ) {
		require_once MRT_PATH . 'inc/domain/line/line-rest-format.php';
	}
	$out = array();
	foreach ( MRT_journey_cache_warm_station_code_pairs() as $pair ) {
		if ( ! is_array( $pair ) ) {
			continue;
		}
		$from = MRT_station_post_id_from_station_code( (string) ( $pair['from'] ?? '' ) );
		$to   = MRT_station_post_id_from_station_code( (string) ( $pair['to'] ?? '' ) );
		if ( $from > 0 && $to > 0 && $from !== $to ) {
			$out[] = array(
				'from' => $from,
				'to'   => $to,
			);
		}
	}
	return $out;
}

/**
 * Month offsets relative to anchor month (filterable).
 *
 * @return array<int, int>
 */
function MRT_journey_cache_warm_month_offsets(): array {
	$offsets = apply_filters( 'mrt_journey_cache_warm_month_offsets', array( -1, 0, 1 ) );
	if ( ! is_array( $offsets ) ) {
		return array( -1, 0, 1 );
	}
	return array_values( array_map( 'intval', $offsets ) );
}

/**
 * Build one warmed calendar month (no-op when already cached).
 */
function MRT_journey_cache_warm_calendar_month(
	int $from_station_id,
	int $to_station_id,
	int $year,
	int $month,
	string $trip_type
): bool {
	if ( $from_station_id <= 0 || $to_station_id <= 0 ) {
		return false;
	}
	$trip_type = $trip_type === 'return' ? 'return' : 'single';
	$params    = array(
		'from'      => (string) $from_station_id,
		'to'        => (string) $to_station_id,
		'year'      => (string) $year,
		'month'     => (string) $month,
		'trip_type' => $trip_type,
	);
	if ( MRT_journey_cache_get( 'calendar.month', $params ) !== null ) {
		return false;
	}
	$built = MRT_get_journey_calendar_month( $from_station_id, $to_station_id, $year, $month, $trip_type );
	return $built !== array();
}

/**
 * Warm single + return for one route and anchor month (incl. neighbour months).
 */
function MRT_journey_cache_warm_route_months(
	int $from_station_id,
	int $to_station_id,
	int $year,
	int $month
): int {
	$warmed = 0;
	foreach ( MRT_journey_cache_warm_month_offsets() as $offset ) {
		$shifted = MRT_journey_cache_shift_month( $year, $month, (int) $offset );
		foreach ( array( 'single', 'return' ) as $trip_type ) {
			if ( MRT_journey_cache_warm_calendar_month(
				$from_station_id,
				$to_station_id,
				$shifted['year'],
				$shifted['month'],
				$trip_type
			) ) {
				++$warmed;
			}
		}
	}
	return $warmed;
}

/**
 * Shift calendar month by delta months.
 *
 * @return array{year: int, month: int}
 */
function MRT_journey_cache_shift_month( int $year, int $month, int $delta ): array {
	$month += $delta;
	while ( $month < 1 ) {
		$month += 12;
		--$year;
	}
	while ( $month > 12 ) {
		$month -= 12;
		++$year;
	}
	return array(
		'year'  => $year,
		'month' => $month,
	);
}

/**
 * Warm popular routes for current + next calendar month.
 *
 * @return array{warmed: int, pairs: int, year: int, month: int}
 */
function MRT_journey_cache_warm_popular_routes( ?int $year = null, ?int $month = null ): array {
	$year  = $year ?? (int) gmdate( 'Y' );
	$month = $month ?? (int) gmdate( 'n' );
	$pairs = MRT_journey_cache_warm_station_id_pairs();
	$total = 0;
	foreach ( $pairs as $pair ) {
		$total += MRT_journey_cache_warm_route_months( (int) $pair['from'], (int) $pair['to'], $year, $month );
		$next   = MRT_journey_cache_shift_month( $year, $month, 1 );
		$total += MRT_journey_cache_warm_route_months(
			(int) $pair['from'],
			(int) $pair['to'],
			$next['year'],
			$next['month']
		);
	}
	return array(
		'warmed' => $total,
		'pairs'  => count( $pairs ),
		'year'   => $year,
		'month'  => $month,
	);
}
