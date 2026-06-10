<?php
/**
 * Timetable trip (service) creation helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/route/direction-labels.php';

/**
 * Build auto title for new service.
 *
 * @param int    $route_id Route post ID.
 * @param int    $end_station_id End station post ID.
 * @param string $direction Direction ('dit' or 'från').
 */
function MRT_build_service_auto_title( $route_id, $end_station_id, $direction ): string {
	$route      = get_post( $route_id );
	$route_name = $route ? $route->post_title : __( 'Route', 'museum-railway-timetable' ) . ' #' . $route_id;
	$dest       = '';
	if ( $end_station_id > 0 ) {
		$s    = get_post( $end_station_id );
		$dest = $s ? ' → ' . $s->post_title : '';
	} else {
		$dest = MRT_service_direction_title_suffix( (string) $direction );
	}
	return $route_name . $dest;
}

/**
 * Resolve destination display for add-service response.
 *
 * @param int    $end_station_id End station post ID.
 * @param string $direction Direction ('dit' or 'från').
 */
function MRT_service_destination_display_label( int $end_station_id, string $direction ): string {
	if ( $end_station_id > 0 ) {
		$s = get_post( $end_station_id );
		return $s ? $s->post_title : '—';
	}
	return MRT_service_direction_label_or_dash( $direction );
}

/**
 * Build response data for add-service success.
 *
 * @param int    $service_id Service post ID.
 * @param int    $route_id Route post ID.
 * @param int    $train_type_id Train type term ID.
 * @param int    $end_station_id End station post ID.
 * @param string $direction Direction ('dit' or 'från').
 * @return array<string, mixed>
 */
function MRT_build_add_service_response( $service_id, $route_id, $train_type_id, $end_station_id, $direction ): array {
	$service    = get_post( $service_id );
	$route      = get_post( $route_id );
	$train_type = $train_type_id > 0 ? get_term( $train_type_id, 'mrt_train_type' ) : null;
	$dest_name  = MRT_service_destination_display_label( (int) $end_station_id, (string) $direction );
	return array(
		'service_id'      => $service_id,
		'service_title'   => $service ? $service->post_title : '',
		'route_name'      => $route ? $route->post_title : '—',
		'train_type_name' => $train_type ? $train_type->name : '—',
		'destination'     => $dest_name,
		'direction'       => MRT_service_direction_label_or_dash( (string) $direction ),
		'edit_url'        => get_edit_post_link( $service_id, 'raw' ),
	);
}
