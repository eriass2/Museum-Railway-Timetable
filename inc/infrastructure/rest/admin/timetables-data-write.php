<?php
/**
 * REST timetable data: mutations
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	if ( isset( $body['type'] ) ) {
		$type    = sanitize_text_field( (string) $body['type'] );
		$allowed = array( 'green', 'red', 'yellow', 'orange', '' );
		if ( in_array( $type, $allowed, true ) ) {
			update_post_meta( (int) $id, 'mrt_timetable_type', $type );
		}
	}
	return (int) $id;
}

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

function MRT_rest_add_timetable_service( int $timetable_id, array $body ) {
	$parsed = MRT_parse_trip_input( $body );
	if ( is_wp_error( $parsed ) ) {
		return $parsed;
	}

	$service_id = wp_insert_post(
		array(
			'post_type'   => MRT_POST_TYPE_SERVICE,
			'post_title'  => MRT_build_service_auto_title(
				$parsed['route_id'],
				$parsed['end_station_id'],
				$parsed['direction']
			),
			'post_status' => 'publish',
		)
	);
	if ( $service_id instanceof WP_Error ) {
		return $service_id;
	}

	update_post_meta( $service_id, 'mrt_service_timetable_id', $timetable_id );
	MRT_persist_trip_fields( (int) $service_id, $parsed, $body );

	return MRT_build_add_service_response(
		(int) $service_id,
		$parsed['route_id'],
		$parsed['train_type_id'],
		$parsed['end_station_id'],
		$parsed['direction']
	);
}

function MRT_rest_update_timetable_service( int $timetable_id, int $service_id, array $body ) {
	$post = MRT_get_timetable_service_post( $timetable_id, $service_id );
	if ( is_wp_error( $post ) ) {
		return $post;
	}
	$result = MRT_apply_timetable_service_update( $service_id, $body );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	$rows = MRT_rest_format_timetable_services( $timetable_id );
	foreach ( $rows as $row ) {
		if ( (int) ( $row['id'] ?? 0 ) === $service_id ) {
			return $row;
		}
	}
	return new WP_Error( 'not_found', __( 'Service not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
}
