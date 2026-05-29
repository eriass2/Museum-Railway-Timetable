<?php

declare(strict_types=1);

/**
 * Prepare service information for timetable rendering
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param WP_Post      $service
 * @param WP_Term|null $train_type
 * @return array{classes: array<int, string>, is_special: bool, special_name: string, highlight_label: string, highlight_color: string, highlight_note: string, service_number: string|int}
 */
function MRT_prepare_service_train_display( WP_Post $service, $train_type ): array {
	$service_number = get_post_meta( $service->ID, 'mrt_service_number', true );
	if ( empty( $service_number ) ) {
		$service_number = $service->ID;
	}

	$classes = array( 'mrt-service-col' );
	if ( $train_type && $train_type->slug === 'buss' ) {
		$classes[] = 'mrt-service-bus';
	}

	$highlight = MRT_get_service_highlight( (int) $service->ID );
	if ( $highlight !== null ) {
		$classes[] = 'mrt-service-highlight';
	}

	return array(
		'classes'          => $classes,
		'is_special'         => $highlight !== null,
		'special_name'       => $highlight['label'] ?? '',
		'highlight_label'    => $highlight['label'] ?? '',
		'highlight_color'    => $highlight['color'] ?? '',
		'highlight_note'     => $highlight['note'] ?? '',
		'service_number'     => $service_number,
	);
}

/**
 * Connections after this service at end station (for overview footnotes).
 *
 * @param array<string, mixed> $service_stop_times
 * @param array<string, mixed> $destination_data
 * @return array<int, mixed>
 */
function MRT_prepare_service_end_connections( WP_Post $service, $service_stop_times, $destination_data, string $dateYmd ): array {
	$connections = array();
	if ( empty( $destination_data['end_station_id'] ) ) {
		return $connections;
	}
	$end_station_id = $destination_data['end_station_id'];
	if ( ! isset( $service_stop_times[ $end_station_id ] ) ) {
		return $connections;
	}
	$end_stop    = $service_stop_times[ $end_station_id ];
	$end_arrival = $end_stop['arrival_time'] ?? '';
	if ( $end_arrival && $dateYmd !== '' ) {
		$connections = MRT_find_connecting_services( $end_station_id, $service->ID, $end_arrival, $dateYmd, 2 );
	}
	return $connections;
}

/**
 * Prepare service information and CSS classes for timetable rendering
 *
 * @param array<int, array<string, mixed>> $services_list From MRT_group_services_by_route
 * @return array{service_classes: array<int, array<int, string>>, service_info: array<int, array<string, mixed>>, all_connections: array<int, mixed>}
 */
function MRT_prepare_service_info( array $services_list, string $dateYmd ): array {
	$service_classes = array();
	$service_info    = array();
	$all_connections = array();

	foreach ( $services_list as $idx => $service_data ) {
		$service                 = $service_data['service'];
		$train_type              = $service_data['train_type'];
		$disp                    = MRT_prepare_service_train_display( $service, $train_type );
		$service_classes[ $idx ] = $disp['classes'];

		$service_stop_times = $service_data['stop_times'] ?? array();
		$destination_data   = MRT_get_service_destination( $service->ID );
		$connections        = MRT_prepare_service_end_connections( $service, $service_stop_times, $destination_data, $dateYmd );

		if ( $connections !== array() ) {
			$all_connections[ $idx ] = $connections;
		}

		$default_train_type = MRT_get_service_default_train_type( (int) $service->ID );
		$is_deviation       = $dateYmd !== '' && MRT_service_has_train_type_deviation( (int) $service->ID, $dateYmd );
		$deviation_notice   = $dateYmd !== '' ? MRT_get_service_notice_for_date( (int) $service->ID, $dateYmd ) : '';

		$service_info[ $idx ] = array(
			'service'                => $service,
			'train_type'             => $train_type,
			'default_train_type'     => $default_train_type,
			'is_deviation'           => $is_deviation,
			'deviation_notice'       => $deviation_notice,
			'service_number'         => $disp['service_number'],
			'is_special'             => $disp['is_special'],
			'special_name'           => $disp['special_name'],
			'highlight_label'        => $disp['highlight_label'],
			'highlight_color'        => $disp['highlight_color'],
			'highlight_note'         => $disp['highlight_note'],
			'destination'            => $destination_data['destination'] ?? '',
		);
	}

	return array(
		'service_classes' => $service_classes,
		'service_info'    => $service_info,
		'all_connections' => $all_connections,
	);
}
