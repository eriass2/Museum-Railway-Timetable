<?php
/**
 * REST: public timetable calendar endpoints.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register public timetable REST routes.
 */
function MRT_rest_register_timetable_public_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/timetables/day',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_timetable_day_handler',
			'permission_callback' => 'MRT_rest_can_read_public',
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/timetables/month',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_timetable_month_handler',
			'permission_callback' => 'MRT_rest_can_read_public',
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_timetable_day_handler( WP_REST_Request $request ) {
	$date       = sanitize_text_field( (string) $request->get_param( 'date' ) );
	$train_type = sanitize_text_field( (string) $request->get_param( 'train_type' ) );
	if ( $date === '' || ! MRT_validate_date( $date ) ) {
		return new WP_Error(
			'mrt_journey_date',
			__( 'Please select a valid date.', 'museum-railway-timetable' ),
			array( 'status' => 400 )
		);
	}
	$data = MRT_get_timetable_day_data( $date, $train_type );
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	return rest_ensure_response( array( 'overview' => $data ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_timetable_month_handler( WP_REST_Request $request ) {
	$year       = (int) $request->get_param( 'year' );
	$month      = (int) $request->get_param( 'month' );
	$train_type = sanitize_text_field( (string) $request->get_param( 'train_type' ) );
	$service    = sanitize_text_field( (string) $request->get_param( 'service' ) );
	$start_mon  = $request->get_param( 'start_monday' );
	$atts       = array(
		'train_type'   => $train_type,
		'service'      => $service,
		'start_monday' => $start_mon === null || $start_mon === '' || (bool) $start_mon,
	);

	$data = MRT_month_calendar_data_for_month( $year, $month, $atts );
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	return rest_ensure_response( $data );
}
