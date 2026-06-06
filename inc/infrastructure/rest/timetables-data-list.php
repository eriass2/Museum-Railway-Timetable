<?php
/**
 * REST timetable data: read serializers
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		$train_type_term = $train_type_id > 0 ? get_term( $train_type_id, MRT_TAXONOMY_TRAIN_TYPE ) : null;
		$service_number  = (string) get_post_meta( $service->ID, 'mrt_service_number', true );
		if ( $service_number === '' ) {
			$service_number = (string) $service->ID;
		}
		$end_station_id  = (int) get_post_meta( $service->ID, 'mrt_service_end_station_id', true );
		$highlight       = MRT_get_service_highlight( (int) $service->ID );
		$rows[]          = array(
			'id'                  => (int) $service->ID,
			'title'               => (string) $service->post_title,
			'service_number'      => $service_number,
			'end_station_id'      => $end_station_id,
			'route_id'            => $route_id,
			'route_name'          => $route_id > 0 ? (string) get_the_title( $route_id ) : '',
			'train_type_id'       => $train_type_id,
			'train_type_name'     => ( $train_type_term instanceof WP_Term ) ? (string) $train_type_term->name : '',
			'train_type_icon_key' => ( $train_type_term instanceof WP_Term )
				? MRT_get_train_type_symbol_key( $train_type_term )
				: '',
			'destination'         => ! empty( $dest['destination'] ) ? (string) $dest['destination'] : '',
			'highlight_label'     => $highlight !== null ? (string) $highlight['label'] : '',
			'highlight_color'     => $highlight !== null ? (string) $highlight['color'] : '',
			'highlight_note'      => $highlight !== null ? (string) $highlight['note'] : '',
		);
	}
	return $rows;
}

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
			'id'       => (int) $term->term_id,
			'name'     => (string) $term->name,
			'icon_key' => MRT_get_train_type_symbol_key( $term ),
		);
	}
	return $rows;
}
