<?php
/**
 * AJAX handlers for journey search and calendar (frontend)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * JSON success when no services run on the selected date
 *
 * @param string $trip_type single|return
 * @return void
 */
function MRT_journey_ajax_send_no_services_response( $trip_type ) {
	wp_send_json_success(
		array(
			'trip_type'   => $trip_type,
			'connections' => array(),
		)
	);
}

/**
 * Normalized connections for AJAX search (single vs return).
 *
 * @param array<string, mixed> $params From MRT_journey_ajax_parse_trip_search_params
 * @return array<int, array<string, mixed>>
 */
function MRT_journey_ajax_find_connections( array $params ): array {
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

function MRT_ajax_search_journey() {
	if ( ! MRT_journey_ajax_verify_nonce() ) {
		wp_send_json_error(
			array(
				'message' => __( 'Security check failed. Please refresh the page.', 'museum-railway-timetable' ),
			)
		);
	}
	$params = MRT_journey_ajax_parse_trip_search_params();
	if ( is_wp_error( $params ) ) {
		wp_send_json_error( array( 'message' => $params->get_error_message() ) );
	}
	$services_on_date = MRT_services_running_on_date( $params['date'] );
	if ( empty( $services_on_date ) ) {
		MRT_journey_ajax_send_no_services_response( $params['trip_type'] );
	}

	$connections = MRT_journey_ajax_find_connections( $params );
	wp_send_json_success(
		array(
			'trip_type'   => $params['trip_type'],
			'connections' => $connections,
		)
	);
}

/**
 * Calendar month cell states for a station pair (frontend)
 *
 * POST: nonce, from_station, to_station, year, month (1–12)
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_journey_calendar_month() {
	if ( ! MRT_journey_ajax_verify_nonce() ) {
		wp_send_json_error(
			array(
				'message' => __( 'Security check failed. Please refresh the page.', 'museum-railway-timetable' ),
			)
		);
	}
	$parsed = MRT_journey_ajax_parse_calendar_month_params();
	if ( is_wp_error( $parsed ) ) {
		wp_send_json_error( array( 'message' => $parsed->get_error_message() ) );
	}
	$days = MRT_get_journey_calendar_month(
		$parsed['from'],
		$parsed['to'],
		$parsed['year'],
		$parsed['month']
	);
	wp_send_json_success(
		array(
			'year'  => $parsed['year'],
			'month' => $parsed['month'],
			'days'  => $days,
		)
	);
}

/**
 * Stops and duration for one service between two stations (frontend wizard / API)
 *
 * POST: nonce, from_station, to_station, service_id
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_journey_connection_detail() {
	if ( ! MRT_journey_ajax_verify_nonce() ) {
		wp_send_json_error(
			array(
				'message' => __( 'Security check failed. Please refresh the page.', 'museum-railway-timetable' ),
			)
		);
	}
	$parsed = MRT_journey_ajax_parse_connection_detail_params();
	if ( is_wp_error( $parsed ) ) {
		wp_send_json_error( array( 'message' => $parsed->get_error_message() ) );
	}
	$detail = MRT_get_connection_journey_detail(
		$parsed['service_id'],
		$parsed['from'],
		$parsed['to']
	);
	$notice = MRT_get_service_notice( $parsed['service_id'], null );
	wp_send_json_success(
		array(
			'detail' => $detail,
			'notice' => $notice,
		)
	);
}
