<?php
/**
 * REST: public traffic notices.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/traffic-notices/aggregate.php';

/**
 * Register public traffic notices routes.
 */
function MRT_rest_register_traffic_notices_public_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/traffic-notices',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_traffic_notices_public_handler',
			'permission_callback' => 'MRT_rest_can_read_public',
			'args'                => array(
				'date'             => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'days'             => array(
					'type'              => 'integer',
					'required'          => false,
					'default'           => 1,
					'sanitize_callback' => 'absint',
				),
				'show_general'     => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'show_deviations'  => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_traffic_notices_public_handler( WP_REST_Request $request ) {
	$date = (string) $request->get_param( 'date' );
	if ( $date === '' ) {
		$date = MRT_get_current_datetime()['date'];
	}
	$days            = max( 1, min( 2, (int) $request->get_param( 'days' ) ) );
	$show_general    = rest_sanitize_boolean( $request->get_param( 'show_general' ) );
	$show_deviations = rest_sanitize_boolean( $request->get_param( 'show_deviations' ) );

	$result = MRT_traffic_notices_aggregate( $date, $days, $show_general, $show_deviations );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}
