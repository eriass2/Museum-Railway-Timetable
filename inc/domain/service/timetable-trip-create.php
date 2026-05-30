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
	} elseif ( $direction === 'dit' ) {
		$dest = ' - ' . __( 'Dit', 'museum-railway-timetable' );
	} elseif ( $direction === 'från' ) {
		$dest = ' - ' . __( 'Från', 'museum-railway-timetable' );
	}
	return $route_name . $dest;
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
	$dest_name  = '—';
	if ( $end_station_id > 0 ) {
		$s         = get_post( $end_station_id );
		$dest_name = $s ? $s->post_title : '—';
	} elseif ( $direction === 'dit' ) {
		$dest_name = __( 'Dit', 'museum-railway-timetable' );
	} elseif ( $direction === 'från' ) {
		$dest_name = __( 'Från', 'museum-railway-timetable' );
	}
	return array(
		'service_id'      => $service_id,
		'service_title'   => $service ? $service->post_title : '',
		'route_name'      => $route ? $route->post_title : '—',
		'train_type_name' => $train_type ? $train_type->name : '—',
		'destination'     => $dest_name,
		'direction'       => $direction === 'dit' ? __( 'Dit', 'museum-railway-timetable' ) : ( $direction === 'från' ? __( 'Från', 'museum-railway-timetable' ) : '—' ),
		'edit_url'        => get_edit_post_link( $service_id, 'raw' ),
	);
}
