<?php
/**
 * REST: UL-like disruption feed (sources A+B, extended horizon).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/traffic-notices/disruption-feed.php';

/**
 * Register public disruption feed routes.
 */
function MRT_rest_register_traffic_disruptions_public_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/traffic-disruptions/feed',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_traffic_disruptions_feed_handler',
			'permission_callback' => 'MRT_rest_can_read_public',
			'args'                => array(
				'date'          => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'horizon_days'  => array(
					'type'              => 'integer',
					'required'          => false,
					'default'           => MRT_DISRUPTION_FEED_DEFAULT_HORIZON,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_traffic_disruptions_feed_handler( WP_REST_Request $request ) {
	$date = (string) $request->get_param( 'date' );
	if ( $date === '' ) {
		$date = MRT_get_current_datetime()['date'];
	}
	$horizon_days = (int) $request->get_param( 'horizon_days' );

	$result = MRT_disruption_feed_build( $date, $horizon_days );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}
