<?php
/**
 * Delete plugin entities (REST admin).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/timetable/view/group-view.php';
require_once MRT_PATH . 'inc/domain/route/routes.php';
require_once MRT_PATH . 'inc/domain/timetable/timetable-pages.php';

/**
 * Remove all stop time rows for a service.
 */
function MRT_delete_service_stoptimes( int $service_id ): void {
	global $wpdb;
	if ( $service_id <= 0 ) {
		return;
	}
	$table = $wpdb->prefix . 'mrt_stoptimes';
	$wpdb->delete( $table, array( 'service_post_id' => $service_id ), array( '%d' ) );
}

/**
 * Permanently delete a service and its stop times.
 *
 * @return true|WP_Error
 */
function MRT_delete_service_post( int $service_id ) {
	if ( $service_id <= 0 ) {
		return new WP_Error( 'invalid', __( 'Invalid service.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$post = get_post( $service_id );
	if ( ! $post instanceof WP_Post || $post->post_type !== MRT_POST_TYPE_SERVICE ) {
		return new WP_Error( 'not_found', __( 'Service not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	MRT_delete_service_stoptimes( $service_id );
	$deleted = wp_delete_post( $service_id, true );
	if ( ! $deleted ) {
		return new WP_Error( 'delete_failed', __( 'Could not delete service.', 'museum-railway-timetable' ), array( 'status' => 500 ) );
	}
	return true;
}

/**
 * Delete linked public WP page for one timetable.
 */
function MRT_delete_timetable_public_page( int $timetable_id ): void {
	$page_id = MRT_timetable_public_page_id( $timetable_id );
	if ( $page_id > 0 && get_post( $page_id ) ) {
		wp_delete_post( $page_id, true );
	}
	delete_post_meta( $timetable_id, MRT_META_TIMETABLE_PAGE_ID );
}

/**
 * Permanently delete a timetable, its services, and public page.
 *
 * @return true|WP_Error
 */
function MRT_delete_timetable_post( int $timetable_id ) {
	if ( $timetable_id <= 0 ) {
		return new WP_Error( 'invalid', __( 'Invalid timetable.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$post = get_post( $timetable_id );
	if ( ! $post instanceof WP_Post || $post->post_type !== MRT_POST_TYPE_TIMETABLE ) {
		return new WP_Error( 'not_found', __( 'Timetable not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	foreach ( MRT_get_services_for_timetable( $timetable_id ) as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$result = MRT_delete_service_post( (int) $service->ID );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
	}
	MRT_delete_timetable_public_page( $timetable_id );
	$deleted = wp_delete_post( $timetable_id, true );
	if ( ! $deleted ) {
		return new WP_Error( 'delete_failed', __( 'Could not delete timetable.', 'museum-railway-timetable' ), array( 'status' => 500 ) );
	}
	return true;
}

/**
 * Route IDs that reference a station.
 *
 * @return int[]
 */
function MRT_station_referencing_route_ids( int $station_id ): array {
	if ( $station_id <= 0 ) {
		return array();
	}
	$routes = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_ROUTE,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	$matches = array();
	foreach ( $routes as $route ) {
		$route_id = $route instanceof WP_Post ? (int) $route->ID : (int) $route;
		if ( $route_id <= 0 ) {
			continue;
		}
		$ids = array_map( 'intval', MRT_get_route_stations( $route_id ) );
		if ( in_array( $station_id, $ids, true ) ) {
			$matches[] = $route_id;
		}
	}
	return $matches;
}

/**
 * Service IDs using a station as trip destination.
 *
 * @return int[]
 */
function MRT_station_referencing_service_ids( int $station_id ): array {
	if ( $station_id <= 0 ) {
		return array();
	}
	$ids = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_SERVICE,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'mrt_service_end_station_id',
					'value'   => $station_id,
					'compare' => '=',
				),
			),
		)
	);
	return array_map( 'intval', $ids );
}

/**
 * Whether a station is referenced by routes, services, or stop times.
 */
function MRT_station_is_in_use( int $station_id ): bool {
	if ( $station_id <= 0 ) {
		return false;
	}
	if ( MRT_station_referencing_route_ids( $station_id ) !== array() ) {
		return true;
	}
	if ( MRT_station_referencing_service_ids( $station_id ) !== array() ) {
		return true;
	}
	global $wpdb;
	$table = $wpdb->prefix . 'mrt_stoptimes';
	$count = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE station_post_id = %d",
			$station_id
		)
	);
	return $count > 0;
}

/**
 * Permanently delete a station when unused.
 *
 * @return true|WP_Error
 */
function MRT_delete_station_post( int $station_id ) {
	if ( $station_id <= 0 ) {
		return new WP_Error( 'invalid', __( 'Invalid station.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$post = get_post( $station_id );
	if ( ! $post instanceof WP_Post || $post->post_type !== MRT_POST_TYPE_STATION ) {
		return new WP_Error( 'not_found', __( 'Station not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	if ( MRT_station_is_in_use( $station_id ) ) {
		return new WP_Error(
			'in_use',
			__( 'Station is used by a route, trip, or stop times and cannot be deleted.', 'museum-railway-timetable' ),
			array( 'status' => 409 )
		);
	}
	$deleted = wp_delete_post( $station_id, true );
	if ( ! $deleted ) {
		return new WP_Error( 'delete_failed', __( 'Could not delete station.', 'museum-railway-timetable' ), array( 'status' => 500 ) );
	}
	return true;
}

/**
 * Service IDs linked to a route.
 *
 * @return int[]
 */
function MRT_route_referencing_service_ids( int $route_id ): array {
	if ( $route_id <= 0 ) {
		return array();
	}
	$ids = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_SERVICE,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'mrt_service_route_id',
					'value'   => $route_id,
					'compare' => '=',
				),
			),
		)
	);
	return array_map( 'intval', $ids );
}

/**
 * Permanently delete a route when no trips reference it.
 *
 * @return true|WP_Error
 */
function MRT_delete_route_post( int $route_id ) {
	if ( $route_id <= 0 ) {
		return new WP_Error( 'invalid', __( 'Invalid route.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$post = get_post( $route_id );
	if ( ! $post instanceof WP_Post || $post->post_type !== MRT_POST_TYPE_ROUTE ) {
		return new WP_Error( 'not_found', __( 'Route not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	if ( MRT_route_referencing_service_ids( $route_id ) !== array() ) {
		return new WP_Error(
			'in_use',
			__( 'Route is used by trips and cannot be deleted.', 'museum-railway-timetable' ),
			array( 'status' => 409 )
		);
	}
	$deleted = wp_delete_post( $route_id, true );
	if ( ! $deleted ) {
		return new WP_Error( 'delete_failed', __( 'Could not delete route.', 'museum-railway-timetable' ), array( 'status' => 500 ) );
	}
	return true;
}
