<?php
/**
 * REST: stations and routes.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/infrastructure/ajax/route-stations.php';

/**
 * Register stations/routes routes.
 */
function MRT_rest_register_stations_routes_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/stations',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_list_stations_handler',
				'permission_callback' => 'MRT_rest_can_read',
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'MRT_rest_create_station_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/stations/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'MRT_rest_update_station_handler',
			'permission_callback' => 'MRT_rest_can_manage',
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/routes',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_list_routes_handler',
				'permission_callback' => 'MRT_rest_can_read',
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'MRT_rest_create_route_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/routes/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'MRT_rest_update_route_handler',
			'permission_callback' => 'MRT_rest_can_manage',
		)
	);
}

/**
 * @return array<int, array<string, mixed>>
 */
function MRT_rest_list_stations_payload(): array {
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_STATION,
			'posts_per_page' => -1,
			'orderby'        => 'meta_value_num title',
			'meta_key'       => 'mrt_display_order',
			'order'          => 'ASC',
		)
	);
	$rows = array();
	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$rows[] = MRT_rest_format_station( $post );
	}
	return $rows;
}

/**
 * @return array<string, mixed>
 */
function MRT_rest_format_station( WP_Post $post ): array {
	return array(
		'id'            => (int) $post->ID,
		'title'         => (string) $post->post_title,
		'station_type'  => (string) get_post_meta( $post->ID, 'mrt_station_type', true ),
		'bus_suffix'    => get_post_meta( $post->ID, 'mrt_station_bus_suffix', true ) === '1',
		'lat'           => (string) get_post_meta( $post->ID, 'mrt_lat', true ),
		'lng'           => (string) get_post_meta( $post->ID, 'mrt_lng', true ),
		'display_order' => (int) get_post_meta( $post->ID, 'mrt_display_order', true ),
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_list_stations_handler( WP_REST_Request $request ) {
	unset( $request );
	return rest_ensure_response( array( 'items' => MRT_rest_list_stations_payload() ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_create_station_handler( WP_REST_Request $request ) {
	$body  = (array) $request->get_json_params();
	$title = isset( $body['title'] ) ? sanitize_text_field( (string) $body['title'] ) : '';
	if ( $title === '' ) {
		return new WP_Error( 'invalid', __( 'Title is required.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$id = wp_insert_post(
		array(
			'post_type'   => MRT_POST_TYPE_STATION,
			'post_title'  => $title,
			'post_status' => 'publish',
		)
	);
	if ( $id instanceof WP_Error ) {
		return $id;
	}
	MRT_rest_apply_station_meta( (int) $id, $body );
	$post = get_post( (int) $id );
	return rest_ensure_response( MRT_rest_format_station( $post instanceof WP_Post ? $post : get_post( (int) $id ) ) );
}

/**
 * @param array<string, mixed> $body Fields.
 */
function MRT_rest_apply_station_meta( int $station_id, array $body ): void {
	if ( isset( $body['station_type'] ) ) {
		update_post_meta( $station_id, 'mrt_station_type', sanitize_text_field( (string) $body['station_type'] ) );
	}
	if ( array_key_exists( 'bus_suffix', $body ) ) {
		$body['bus_suffix'] ? update_post_meta( $station_id, 'mrt_station_bus_suffix', '1' ) : delete_post_meta( $station_id, 'mrt_station_bus_suffix' );
	}
	if ( isset( $body['lat'] ) ) {
		update_post_meta( $station_id, 'mrt_lat', sanitize_text_field( (string) $body['lat'] ) );
	}
	if ( isset( $body['lng'] ) ) {
		update_post_meta( $station_id, 'mrt_lng', sanitize_text_field( (string) $body['lng'] ) );
	}
	if ( isset( $body['display_order'] ) ) {
		update_post_meta( $station_id, 'mrt_display_order', (int) $body['display_order'] );
	}
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_update_station_handler( WP_REST_Request $request ) {
	$id   = (int) $request['id'];
	$post = get_post( $id );
	if ( ! $post instanceof WP_Post || $post->post_type !== MRT_POST_TYPE_STATION ) {
		return new WP_Error( 'not_found', __( 'Station not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	$body = (array) $request->get_json_params();
	if ( isset( $body['title'] ) ) {
		wp_update_post(
			array(
				'ID'         => $id,
				'post_title' => sanitize_text_field( (string) $body['title'] ),
			)
		);
	}
	MRT_rest_apply_station_meta( $id, $body );
	$updated = get_post( $id );
	return rest_ensure_response( MRT_rest_format_station( $updated instanceof WP_Post ? $updated : $post ) );
}

/**
 * @return array<int, array<string, mixed>>
 */
function MRT_rest_list_routes_payload(): array {
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_ROUTE,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	$rows = array();
	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$rows[] = MRT_rest_format_route( $post );
	}
	return $rows;
}

/**
 * @return array<string, mixed>
 */
function MRT_rest_format_route( WP_Post $post ): array {
	$station_ids = MRT_get_route_stations( (int) $post->ID );
	$stations    = array();
	foreach ( $station_ids as $sid ) {
		$stations[] = array(
			'id'   => (int) $sid,
			'name' => (string) get_the_title( (int) $sid ),
		);
	}
	$ends = MRT_get_route_end_stations( (int) $post->ID );
	return array(
		'id'             => (int) $post->ID,
		'title'          => (string) $post->post_title,
		'start_station'  => $ends['start'],
		'end_station'    => $ends['end'],
		'station_ids'    => array_map( 'intval', $station_ids ),
		'stations'       => $stations,
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_list_routes_handler( WP_REST_Request $request ) {
	unset( $request );
	return rest_ensure_response( array( 'items' => MRT_rest_list_routes_payload() ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_create_route_handler( WP_REST_Request $request ) {
	$body  = (array) $request->get_json_params();
	$title = isset( $body['title'] ) ? sanitize_text_field( (string) $body['title'] ) : '';
	if ( $title === '' ) {
		return new WP_Error( 'invalid', __( 'Title is required.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$id = wp_insert_post(
		array(
			'post_type'   => MRT_POST_TYPE_ROUTE,
			'post_title'  => $title,
			'post_status' => 'publish',
		)
	);
	if ( $id instanceof WP_Error ) {
		return $id;
	}
	MRT_rest_apply_route_meta( (int) $id, $body );
	$post = get_post( (int) $id );
	return rest_ensure_response( MRT_rest_format_route( $post instanceof WP_Post ? $post : get_post( (int) $id ) ) );
}

/**
 * @param array<string, mixed> $body Fields.
 */
function MRT_rest_apply_route_meta( int $route_id, array $body ): void {
	if ( isset( $body['start_station'] ) ) {
		MRT_update_route_terminus_station_meta( $route_id, (int) $body['start_station'], 'mrt_route_start_station' );
	}
	if ( isset( $body['end_station'] ) ) {
		MRT_update_route_terminus_station_meta( $route_id, (int) $body['end_station'], 'mrt_route_end_station' );
	}
	if ( isset( $body['station_ids'] ) && is_array( $body['station_ids'] ) ) {
		$ids = array_values( array_filter( array_map( 'intval', $body['station_ids'] ) ) );
		update_post_meta( $route_id, 'mrt_route_stations', $ids );
	}
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_update_route_handler( WP_REST_Request $request ) {
	$id   = (int) $request['id'];
	$post = get_post( $id );
	if ( ! $post instanceof WP_Post || $post->post_type !== MRT_POST_TYPE_ROUTE ) {
		return new WP_Error( 'not_found', __( 'Route not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	$body = (array) $request->get_json_params();
	if ( isset( $body['title'] ) ) {
		wp_update_post(
			array(
				'ID'         => $id,
				'post_title' => sanitize_text_field( (string) $body['title'] ),
			)
		);
	}
	MRT_rest_apply_route_meta( $id, $body );
	$updated = get_post( $id );
	return rest_ensure_response( MRT_rest_format_route( $updated instanceof WP_Post ? $updated : $post ) );
}
