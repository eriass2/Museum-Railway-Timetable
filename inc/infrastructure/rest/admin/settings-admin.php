<?php
/**
 * REST: plugin settings and price matrix (admin).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register settings REST routes.
 */
function MRT_rest_register_settings_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/settings',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_get_settings_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'MRT_rest_save_settings_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/settings/prices',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_get_prices_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'MRT_rest_save_prices_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_get_settings_handler( WP_REST_Request $request ) {
	unset( $request );
	$opts = MRT_get_plugin_settings();
	return rest_ensure_response(
		array(
			'enabled'                            => ! empty( $opts['enabled'] ),
			'note'                               => (string) ( $opts['note'] ?? '' ),
			'operator_name'                      => (string) ( $opts['operator_name'] ?? '' ),
			'ticket_url'                         => (string) ( $opts['ticket_url'] ?? '' ),
			'hero_background_url'                => (string) ( $opts['hero_background_url'] ?? '' ),
			'min_transfer_minutes'               => (int) ( $opts['min_transfer_minutes'] ?? 0 ),
			'max_transfer_minutes'               => (int) ( $opts['max_transfer_minutes'] ?? 120 ),
			'max_transfers'                      => (int) ( $opts['max_transfers'] ?? 2 ),
			'afternoon_return_threshold_minutes' => MRT_afternoon_return_threshold_minutes(),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_save_settings_handler( WP_REST_Request $request ) {
	$body   = (array) $request->get_json_params();
	$saved  = MRT_sanitize_plugin_settings(
		array(
			'enabled'                            => ! empty( $body['enabled'] ) ? '1' : '',
			'note'                               => $body['note'] ?? '',
			'operator_name'                      => $body['operator_name'] ?? null,
			'ticket_url'                       => $body['ticket_url'] ?? null,
			'hero_background_url'              => $body['hero_background_url'] ?? null,
			'min_transfer_minutes'             => $body['min_transfer_minutes'] ?? null,
			'max_transfer_minutes'             => $body['max_transfer_minutes'] ?? null,
			'max_transfers'                    => $body['max_transfers'] ?? null,
			'afternoon_return_threshold_minutes' => $body['afternoon_return_threshold_minutes'] ?? null,
		)
	);
	update_option( 'mrt_settings', $saved );
	return MRT_rest_get_settings_handler( $request );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_get_prices_handler( WP_REST_Request $request ) {
	unset( $request );
	return rest_ensure_response(
		array(
			'matrix'           => MRT_get_price_matrix(),
			'ticket_types'     => MRT_price_ticket_type_labels(),
			'categories'       => MRT_price_category_labels(),
			'zones'            => MRT_price_zone_keys(),
			'zone_cap'         => MRT_price_zone_cap(),
			'afternoon_return' => MRT_get_afternoon_return_prices(),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_save_prices_handler( WP_REST_Request $request ) {
	$body = (array) $request->get_json_params();
	if (
		isset( $body['ticket_types'], $body['categories'], $body['zones'] )
		&& is_array( $body['ticket_types'] )
		&& is_array( $body['categories'] )
		&& is_array( $body['zones'] )
	) {
		$schema = MRT_sanitize_price_schema_from_admin_maps(
			$body['ticket_types'],
			$body['categories'],
			array_map( 'intval', $body['zones'] ),
			array(
				'zone_cap'         => $body['zone_cap'] ?? null,
				'afternoon_return' => isset( $body['afternoon_return'] ) && is_array( $body['afternoon_return'] )
					? $body['afternoon_return']
					: array(),
			)
		);
		update_option( 'mrt_price_schema', $schema );
	}
	$matrix = isset( $body['matrix'] ) && is_array( $body['matrix'] ) ? $body['matrix'] : array();
	$clean  = MRT_sanitize_price_matrix( $matrix );
	update_option( 'mrt_price_matrix', $clean );
	return MRT_rest_get_prices_handler( $request );
}
