<?php
/**
 * Public journey search handlers (REST).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/journey/request-params.php';

/**
 * Normalized connections for search (single vs return).
 *
 * @param array<string, mixed> $params Parsed trip search params.
 * @return array<int, array<string, mixed>>
 */
function MRT_journey_find_connections( array $params ): array {
	if ( $params['trip_type'] === 'return' ) {
		return MRT_find_return_connections(
			(int) $params['from'],
			(int) $params['to'],
			$params['date'],
			$params['outbound_arrival'],
			(int) $params['min_turnaround_minutes']
		);
	}

	return MRT_journey_find_normalized_connections(
		(int) $params['from'],
		(int) $params['to'],
		$params['date']
	);
}

/**
 * Search journey connections for a station pair and date.
 *
 * @param array<string, mixed> $input Request body.
 * @return array<string, mixed>|WP_Error
 */
function MRT_journey_search_response( array $input ) {
	$params = MRT_journey_parse_trip_search_params( $input );
	if ( is_wp_error( $params ) ) {
		return $params;
	}
	$cache_params = array(
		'from'             => (string) (int) $params['from'],
		'to'               => (string) (int) $params['to'],
		'date'             => (string) $params['date'],
		'trip_type'        => (string) $params['trip_type'],
		'outbound_arrival' => (string) ( $params['outbound_arrival'] ?? '' ),
		'min_turnaround'   => (string) (int) ( $params['min_turnaround_minutes'] ?? 0 ),
	);
	$cached = MRT_journey_cache_get( 'journey.search', $cache_params );
	if ( is_array( $cached ) && isset( $cached['trip_type'], $cached['connections'] ) ) {
		return $cached;
	}
	$services_on_date = MRT_services_running_on_date( $params['date'] );
	if ( empty( $services_on_date ) ) {
		$empty = array(
			'trip_type'   => $params['trip_type'],
			'connections' => array(),
		);
		MRT_journey_cache_set( 'journey.search', $cache_params, $empty );
		return $empty;
	}
	$response = array(
		'trip_type'   => $params['trip_type'],
		'connections' => MRT_journey_find_connections( $params ),
	);
	MRT_journey_cache_set( 'journey.search', $cache_params, $response );
	return $response;
}

/**
 * Calendar month cell states for a station pair.
 *
 * @param array<string, mixed> $input Request body.
 * @return array<string, mixed>|WP_Error
 */
function MRT_journey_calendar_response( array $input ) {
	$parsed = MRT_journey_parse_calendar_month_params( $input );
	if ( is_wp_error( $parsed ) ) {
		return $parsed;
	}
	$days = MRT_get_journey_calendar_month(
		$parsed['from'],
		$parsed['to'],
		$parsed['year'],
		$parsed['month'],
		(string) $parsed['trip_type']
	);
	return array(
		'year'  => $parsed['year'],
		'month' => $parsed['month'],
		'days'  => $days,
	);
}

/**
 * Stops and duration for one service between two stations.
 *
 * @param array<string, mixed> $input Request body.
 * @return array<string, mixed>|WP_Error
 */
function MRT_journey_connection_detail_response( array $input ) {
	$parsed = MRT_journey_parse_connection_detail_params( $input );
	if ( is_wp_error( $parsed ) ) {
		return $parsed;
	}
	$detail = MRT_get_connection_journey_detail(
		$parsed['service_id'],
		$parsed['from'],
		$parsed['to']
	);
	$date   = (string) ( $parsed['date'] ?? '' );
	$notice = MRT_get_service_notice(
		$parsed['service_id'],
		$date !== '' ? $date : null
	);
	return array(
		'detail'       => $detail,
		'notice'       => $notice,
		'is_cancelled' => MRT_notice_indicates_cancelled( $notice ),
	);
}
