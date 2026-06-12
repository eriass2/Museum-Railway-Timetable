<?php
/**
 * Shared timetable group view preparation (JSON + legacy callers).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, mixed> $a
 * @param array<string, mixed> $b
 */
function MRT_sort_timetable_groups_source_order( array $a, array $b ): int {
	$a_stations = $a['stations'] ?? array();
	$b_stations = $b['stations'] ?? array();
	$a_first    = is_array( $a_stations ) && $a_stations !== array() ? (int) $a_stations[0] : 0;
	$b_first    = is_array( $b_stations ) && $b_stations !== array() ? (int) $b_stations[0] : 0;
	$a_order    = $a_first ? (int) get_post_meta( $a_first, 'mrt_display_order', true ) : 0;
	$b_order    = $b_first ? (int) get_post_meta( $b_first, 'mrt_display_order', true ) : 0;
	return $a_order <=> $b_order;
}

/**
 * @return array<int, WP_Post>
 */
function MRT_get_services_for_timetable( int $timetable_id ): array {
	return get_posts(
		array(
			'post_type'      => 'mrt_service',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'mrt_service_timetable_id',
					'value'   => $timetable_id,
					'compare' => '=',
				),
			),
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'all',
		)
	);
}

/**
 * @param array<int> $service_ids
 * @return array<int, WP_Post>
 */
function MRT_get_services_by_post_ids( array $service_ids ): array {
	if ( $service_ids === array() ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'      => 'mrt_service',
			'post__in'       => $service_ids,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'all',
		)
	);
}

/**
 * @param array<int, array<string, mixed>> $services_list
 * @return array<int, array<string, mixed>>
 */
function MRT_sort_timetable_services_by_first_station_time( array $services_list, int $first_station_id ): array {
		usort(
			$services_list,
			function ( $a, $b ) use ( $first_station_id ) {
				$a_stop = $a['stop_times'][ $first_station_id ] ?? array();
				$b_stop = $b['stop_times'][ $first_station_id ] ?? array();
				$a_time = MRT_stop_effective_departure( is_array( $a_stop ) ? $a_stop : array() );
				$b_time = MRT_stop_effective_departure( is_array( $b_stop ) ? $b_stop : array() );
				if ( $a_time === '' && $b_time === '' ) {
					return 0;
				}
				if ( $a_time === '' ) {
					return 1;
				}
				if ( $b_time === '' ) {
					return -1;
				}
				return strcmp( $a_time, $b_time );
			}
		);
	return $services_list;
}

/**
 * @param array<int, WP_Post> $regular_stations
 */
function MRT_timetable_grid_direction( array $regular_stations ): string {
	if ( $regular_stations === array() ) {
		return '';
	}
	$first = $regular_stations[0]->post_title ?? '';
	return $first === 'Moga' ? 'inbound' : 'outbound';
}

/**
 * @param array<int, array<string, mixed>> $services_list
 */
function MRT_station_row_has_arrival_departure_split( int $station_id, array $services_list ): bool {
	foreach ( $services_list as $service_data ) {
		$stop_time = $service_data['stop_times'][ $station_id ] ?? null;
		if ( ! $stop_time ) {
			continue;
		}
		$arrival   = $stop_time['arrival_time'] ?? '';
		$departure = $stop_time['departure_time'] ?? '';
		if ( $arrival !== '' && $departure !== '' && $arrival !== $departure ) {
			return true;
		}
	}
	return false;
}

/**
 * Stop row for "Från" (departure time).
 *
 * @param array<string, mixed>|null $stop_time
 * @return array<string, mixed>|null
 */
function MRT_get_from_row_display_stop_time( $stop_time ) {
	if ( ! is_array( $stop_time ) || $stop_time === array() ) {
		return null;
	}
	$time_to_show = ! empty( $stop_time['departure_time'] )
		? (string) $stop_time['departure_time']
		: (string) ( $stop_time['arrival_time'] ?? '' );
	if ( $time_to_show === '' ) {
		return $stop_time;
	}
	$row                   = MRT_stop_time_row_with_defaults( $stop_time );
	$row['arrival_time']   = '';
	$row['departure_time'] = MRT_format_time_display( $time_to_show );
	return $row;
}

/**
 * Stop row for "Till" (arrival time).
 *
 * @param array<string, mixed>|null $stop_time
 * @return array<string, mixed>|null
 */
function MRT_get_to_row_display_stop_time( $stop_time ) {
	if ( ! is_array( $stop_time ) || $stop_time === array() ) {
		return null;
	}
	$time_to_show = ! empty( $stop_time['arrival_time'] )
		? (string) $stop_time['arrival_time']
		: (string) ( $stop_time['departure_time'] ?? '' );
	if ( $time_to_show === '' ) {
		return $stop_time;
	}
	$row                   = MRT_stop_time_row_with_defaults( $stop_time );
	$row['arrival_time']   = MRT_format_time_display( $time_to_show );
	$row['departure_time'] = '';
	return $row;
}

/**
 * @param array<string, mixed> $group Route group from MRT_group_services_by_route.
 * @return array<string, mixed>
 */
function MRT_prepare_timetable_group_view( $group, $dateYmd ) {
	$route         = $group['route'];
	$direction     = $group['direction'];
	$stations      = $group['stations'];
	$services_list = $group['services'];

	$station_posts = array();
	if ( ! empty( $stations ) ) {
		$station_posts = get_posts(
			array(
				'post_type'      => 'mrt_station',
				'post__in'       => $stations,
				'posts_per_page' => -1,
				'orderby'        => 'post__in',
				'fields'         => 'all',
			)
		);
	}

	$route_label  = MRT_get_route_label( $route, $direction, $services_list, $station_posts );
	$from_station = ! empty( $station_posts ) ? $station_posts[0] : null;
	$to_station   = ! empty( $station_posts ) ? end( $station_posts ) : null;
	if ( $from_station ) {
		$services_list = MRT_sort_timetable_services_by_first_station_time( $services_list, (int) $from_station->ID );
	}

	$prepared = MRT_prepare_service_info( $services_list, $dateYmd );

	return array(
		'route_label'      => $route_label,
		'from_station'     => $from_station,
		'to_station'       => $to_station,
		'station_posts'    => $station_posts,
		'services_list'    => $services_list,
		'service_classes'  => $prepared['service_classes'],
		'service_info'     => $prepared['service_info'],
		'all_connections'  => $prepared['all_connections'],
		'service_count'    => count( $services_list ),
		'group_heading_id' => wp_unique_id( 'mrtgrh' ),
	);
}
