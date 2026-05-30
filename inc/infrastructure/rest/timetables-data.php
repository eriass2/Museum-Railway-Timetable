<?php
/**
 * REST timetable serializers and mutations.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/infrastructure/ajax/timetable-services.php';
require_once MRT_PATH . 'inc/infrastructure/ajax/route-destinations.php';

/**
 * @return array<int, array{id: int, title: string, dates_count: int, trips_count: int}>
 */
function MRT_rest_list_timetables(): array {
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_TIMETABLE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	$rows = array();
	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$dates    = MRT_get_timetable_dates( (int) $post->ID );
		$services = MRT_get_services_for_timetable( (int) $post->ID );
		$rows[]   = array(
			'id'           => (int) $post->ID,
			'title'        => (string) $post->post_title,
			'dates_count'  => is_array( $dates ) ? count( $dates ) : 0,
			'trips_count'  => count( $services ),
		);
	}
	return $rows;
}

/**
 * @return array<string, mixed>|WP_Error
 */
function MRT_rest_get_timetable_detail( int $timetable_id ) {
	$post = get_post( $timetable_id );
	if ( ! $post instanceof WP_Post || $post->post_type !== MRT_POST_TYPE_TIMETABLE ) {
		return new WP_Error( 'not_found', __( 'Timetable not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	$dates    = MRT_get_timetable_dates( $timetable_id );
	$services = MRT_rest_format_timetable_services( $timetable_id );
	$routes   = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_ROUTE,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	$train_types = get_terms(
		array(
			'taxonomy'   => MRT_TAXONOMY_TRAIN_TYPE,
			'hide_empty' => false,
		)
	);
	return array(
		'id'          => $timetable_id,
		'title'       => (string) $post->post_title,
		'type'        => (string) get_post_meta( $timetable_id, 'mrt_timetable_type', true ),
		'dates'       => is_array( $dates ) ? array_values( $dates ) : array(),
		'services'    => $services,
		'routes'      => MRT_rest_format_route_options( $routes ),
		'train_types' => MRT_rest_format_train_type_options( $train_types ),
	);
}

/**
 * @return array<int, array<string, mixed>>
 */
function MRT_rest_format_timetable_services( int $timetable_id ): array {
	$services = MRT_get_services_for_timetable( $timetable_id );
	$rows     = array();
	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$route_id      = (int) get_post_meta( $service->ID, 'mrt_service_route_id', true );
		$train_types   = wp_get_post_terms( $service->ID, MRT_TAXONOMY_TRAIN_TYPE, array( 'fields' => 'ids' ) );
		$train_type_id = is_array( $train_types ) && $train_types !== array() ? (int) $train_types[0] : 0;
		$dest          = MRT_get_service_destination( (int) $service->ID );
		$rows[]        = array(
			'id'              => (int) $service->ID,
			'title'           => (string) $service->post_title,
			'route_id'        => $route_id,
			'route_name'      => $route_id > 0 ? (string) get_the_title( $route_id ) : '',
			'train_type_id'   => $train_type_id,
			'train_type_name' => $train_type_id > 0 ? (string) ( get_term( $train_type_id, MRT_TAXONOMY_TRAIN_TYPE )->name ?? '' ) : '',
			'destination'     => ! empty( $dest['destination'] ) ? (string) $dest['destination'] : '',
		);
	}
	return $rows;
}

/**
 * @param array<int, WP_Post> $routes Routes.
 * @return array<int, array{id: int, title: string}>
 */
function MRT_rest_format_route_options( array $routes ): array {
	$rows = array();
	foreach ( $routes as $route ) {
		if ( ! $route instanceof WP_Post ) {
			continue;
		}
		$rows[] = array(
			'id'    => (int) $route->ID,
			'title' => (string) $route->post_title,
		);
	}
	return $rows;
}

/**
 * @param array<int, WP_Term>|WP_Term[] $terms Terms.
 * @return array<int, array{id: int, name: string}>
 */
function MRT_rest_format_train_type_options( $terms ): array {
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$rows = array();
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$rows[] = array(
			'id'   => (int) $term->term_id,
			'name' => (string) $term->name,
		);
	}
	return $rows;
}

/**
 * @param array<string, mixed> $body Request body.
 * @return int|WP_Error
 */
function MRT_rest_create_timetable( array $body ) {
	$title = isset( $body['title'] ) ? sanitize_text_field( (string) $body['title'] ) : '';
	if ( $title === '' ) {
		return new WP_Error( 'invalid_title', __( 'Title is required.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$id = wp_insert_post(
		array(
			'post_type'   => MRT_POST_TYPE_TIMETABLE,
			'post_title'  => $title,
			'post_status' => 'publish',
		)
	);
	if ( $id instanceof WP_Error ) {
		return $id;
	}
	return (int) $id;
}

/**
 * @param array<string, mixed> $body Request body.
 * @return true|WP_Error
 */
function MRT_rest_update_timetable( int $timetable_id, array $body ) {
	$post = get_post( $timetable_id );
	if ( ! $post instanceof WP_Post || $post->post_type !== MRT_POST_TYPE_TIMETABLE ) {
		return new WP_Error( 'not_found', __( 'Timetable not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	if ( isset( $body['title'] ) ) {
		wp_update_post(
			array(
				'ID'         => $timetable_id,
				'post_title' => sanitize_text_field( (string) $body['title'] ),
			)
		);
	}
	if ( isset( $body['type'] ) ) {
		$type    = sanitize_text_field( (string) $body['type'] );
		$allowed = array( 'green', 'red', 'yellow', 'orange', '' );
		if ( in_array( $type, $allowed, true ) ) {
			update_post_meta( $timetable_id, 'mrt_timetable_type', $type );
		}
	}
	if ( isset( $body['dates'] ) && is_array( $body['dates'] ) ) {
		MRT_rest_save_timetable_dates( $timetable_id, $body['dates'] );
	}
	return true;
}

/**
 * @param array<int, string> $dates Raw dates.
 */
function MRT_rest_save_timetable_dates( int $timetable_id, array $dates ): void {
	$clean = array();
	foreach ( $dates as $date ) {
		$date = sanitize_text_field( (string) $date );
		if ( MRT_validate_date( $date ) ) {
			$clean[] = $date;
		}
	}
	$clean = array_values( array_unique( $clean ) );
	sort( $clean );
	if ( $clean !== array() ) {
		update_post_meta( $timetable_id, 'mrt_timetable_dates', $clean );
		delete_post_meta( $timetable_id, 'mrt_timetable_date' );
	} else {
		delete_post_meta( $timetable_id, 'mrt_timetable_dates' );
	}
}

/**
 * @param array<string, mixed> $body Trip fields.
 * @return array<string, mixed>|WP_Error
 */
function MRT_rest_add_timetable_service( int $timetable_id, array $body ) {
	$input = array(
		'timetable_id'   => $timetable_id,
		'route_id'       => (int) ( $body['route_id'] ?? 0 ),
		'train_type_id'  => (int) ( $body['train_type_id'] ?? 0 ),
		'end_station_id' => (int) ( $body['end_station_id'] ?? 0 ),
		'direction'      => '',
	);
	if ( $input['route_id'] <= 0 ) {
		return new WP_Error( 'route', __( 'Route is required.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	if ( $input['end_station_id'] > 0 ) {
		$input['direction'] = MRT_calculate_direction_from_end_station( $input['route_id'], $input['end_station_id'] );
	}
	$auto_title = MRT_build_service_auto_title( $input['route_id'], $input['end_station_id'], $input['direction'] );
	$service_id = wp_insert_post(
		array(
			'post_type'   => MRT_POST_TYPE_SERVICE,
			'post_title'  => $auto_title,
			'post_status' => 'publish',
		)
	);
	if ( $service_id instanceof WP_Error ) {
		return $service_id;
	}
	update_post_meta( $service_id, 'mrt_service_timetable_id', $timetable_id );
	update_post_meta( $service_id, 'mrt_service_route_id', $input['route_id'] );
	if ( $input['end_station_id'] > 0 ) {
		update_post_meta( $service_id, 'mrt_service_end_station_id', $input['end_station_id'] );
		if ( $input['direction'] !== '' ) {
			update_post_meta( $service_id, 'mrt_direction', $input['direction'] );
		}
	}
	if ( $input['train_type_id'] > 0 ) {
		wp_set_object_terms( $service_id, array( $input['train_type_id'] ), MRT_TAXONOMY_TRAIN_TYPE );
	}
	return MRT_build_add_service_response(
		(int) $service_id,
		$input['route_id'],
		$input['train_type_id'],
		$input['end_station_id'],
		$input['direction']
	);
}
