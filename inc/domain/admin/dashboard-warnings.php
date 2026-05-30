<?php
/**
 * Dashboard warning collectors.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One dashboard warning row.
 *
 * @param string $code    Machine code.
 * @param string $message Human message (Swedish).
 * @param string $route   Vue hash route.
 * @return array{code: string, message: string, route: string}
 */
function MRT_dashboard_warning_row( string $code, string $message, string $route ): array {
	return array(
		'code'    => $code,
		'message' => $message,
		'route'   => $route,
	);
}

/**
 * Warnings for empty timetable dates.
 *
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_dashboard_warnings_empty_timetable_dates(): array {
	$warnings   = array();
	$timetables = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_TIMETABLE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		)
	);
	foreach ( $timetables as $tid ) {
		$dates = MRT_get_timetable_dates( (int) $tid );
		if ( ! is_array( $dates ) || $dates === array() ) {
			$title      = get_the_title( (int) $tid );
			$warnings[] = MRT_dashboard_warning_row(
				'timetable_no_dates',
				sprintf( 'Tidtabellen "%s" saknar trafikdagar.', $title ?: '#' . $tid ),
				'#/timetables/' . (int) $tid
			);
		}
	}
	return $warnings;
}

/**
 * Warnings for timetables without trips.
 *
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_dashboard_warnings_timetables_without_trips(): array {
	$warnings   = array();
	$timetables = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_TIMETABLE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'all',
		)
	);
	foreach ( $timetables as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$services = MRT_get_services_for_timetable( (int) $post->ID );
		if ( $services === array() ) {
			$warnings[] = MRT_dashboard_warning_row(
				'timetable_no_trips',
				sprintf( 'Tidtabellen "%s" har inga turer.', $post->post_title ),
				'#/timetables/' . (int) $post->ID
			);
		}
	}
	return $warnings;
}

/**
 * Warnings for trips with route but no stop times.
 *
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_dashboard_warnings_trips_without_stoptimes(): array {
	global $wpdb;
	$warnings = array();
	$services = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_SERVICE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'all',
		)
	);
	$table = $wpdb->prefix . 'mrt_stoptimes';
	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$route_id = (int) get_post_meta( $service->ID, 'mrt_service_route_id', true );
		if ( $route_id <= 0 ) {
			continue;
		}
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE service_post_id = %d",
				(int) $service->ID
			)
		);
		if ( $count > 0 ) {
			continue;
		}
		$timetable_id = (int) get_post_meta( $service->ID, 'mrt_service_timetable_id', true );
		$route        = $timetable_id > 0 ? '#/timetables/' . $timetable_id : '#/timetables';
		$warnings[]   = MRT_dashboard_warning_row(
			'trip_no_stoptimes',
			sprintf( 'Turen "%s" saknar stopptider.', $service->post_title ),
			$route
		);
	}
	return $warnings;
}

/**
 * Warnings for routes without stations.
 *
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_dashboard_warnings_routes_without_stations(): array {
	$warnings = array();
	$routes   = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_ROUTE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'all',
		)
	);
	foreach ( $routes as $route ) {
		if ( ! $route instanceof WP_Post ) {
			continue;
		}
		$stations = MRT_get_route_stations( (int) $route->ID );
		if ( $stations !== array() ) {
			continue;
		}
		$warnings[] = MRT_dashboard_warning_row(
			'route_no_stations',
			sprintf( 'Rutten "%s" har inga stationer.', $route->post_title ),
			'#/stations-routes'
		);
	}
	return $warnings;
}
