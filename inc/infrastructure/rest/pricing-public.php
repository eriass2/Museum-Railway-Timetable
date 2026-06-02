<?php
/**
 * REST: public trip price lookup for journey wizard.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register public pricing REST routes.
 */
function MRT_rest_register_pricing_public_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/prices/trip',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_trip_prices_handler',
			'permission_callback' => 'MRT_rest_can_read_public',
			'args'                => array(
				'from_id'             => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
				'to_id'               => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
				'trip_type'           => array(
					'type'              => 'string',
					'default'           => 'single',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'outbound_departure'  => array(
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'inbound_departure'   => array(
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'include_day'         => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function MRT_rest_trip_prices_handler( WP_REST_Request $request ) {
	$from_id = (int) $request->get_param( 'from_id' );
	$to_id   = (int) $request->get_param( 'to_id' );
	if ( $from_id <= 0 || $to_id <= 0 ) {
		return new WP_Error( 'mrt_prices_invalid', __( 'Invalid station.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}

	$trip_type = (string) $request->get_param( 'trip_type' );
	if ( $trip_type !== 'return' ) {
		$trip_type = 'single';
	}

	$include_day = filter_var( $request->get_param( 'include_day' ), FILTER_VALIDATE_BOOLEAN );

	$result = MRT_trip_prices_response(
		$from_id,
		$to_id,
		$trip_type,
		(string) $request->get_param( 'outbound_departure' ),
		(string) $request->get_param( 'inbound_departure' ),
		$include_day
	);

	return rest_ensure_response( $result );
}
