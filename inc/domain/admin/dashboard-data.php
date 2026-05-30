<?php
/**
 * Dashboard aggregate data for Vue admin.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/admin/dashboard-warnings.php';

/**
 * Dashboard statistics (same counts as legacy PHP dashboard).
 *
 * @return array<string, int>
 */
function MRT_dashboard_stats(): array {
	$train_types_count = wp_count_terms(
		array(
			'taxonomy'   => MRT_TAXONOMY_TRAIN_TYPE,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $train_types_count ) ) {
		$train_types_count = 0;
	}
	return array(
		'stations'    => (int) wp_count_posts( MRT_POST_TYPE_STATION )->publish,
		'routes'      => (int) wp_count_posts( MRT_POST_TYPE_ROUTE )->publish,
		'timetables'  => (int) wp_count_posts( MRT_POST_TYPE_TIMETABLE )->publish,
		'services'    => (int) wp_count_posts( MRT_POST_TYPE_SERVICE )->publish,
		'train_types' => (int) $train_types_count,
	);
}

/**
 * Collect informational data-quality warnings.
 *
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_collect_dashboard_warnings(): array {
	return array_merge(
		MRT_dashboard_warnings_empty_timetable_dates(),
		MRT_dashboard_warnings_timetables_without_trips(),
		MRT_dashboard_warnings_trips_without_stoptimes(),
		MRT_dashboard_warnings_routes_without_stations()
	);
}

/**
 * Next upcoming traffic days across timetables.
 *
 * @param int $limit Max rows.
 * @return array<int, array{date: string, timetable_id: int, title: string}>
 */
function MRT_dashboard_next_traffic( int $limit = 5 ): array {
	$datetime = MRT_get_current_datetime();
	$today    = gmdate( 'Y-m-d', $datetime['timestamp'] );
	$by_date  = array();

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
		$dates = MRT_get_timetable_dates( (int) $post->ID );
		if ( ! is_array( $dates ) ) {
			continue;
		}
		foreach ( $dates as $date ) {
			if ( ! is_string( $date ) || $date < $today ) {
				continue;
			}
			if ( ! isset( $by_date[ $date ] ) ) {
				$by_date[ $date ] = array(
					'date'          => $date,
					'timetable_id'  => (int) $post->ID,
					'title'         => (string) $post->post_title,
				);
			}
		}
	}
	$rows = array_values( $by_date );
	usort(
		$rows,
		static function ( array $a, array $b ): int {
			return strcmp( $a['date'], $b['date'] );
		}
	);
	return array_slice( $rows, 0, max( 0, $limit ) );
}

/**
 * Quick-start and external links for dashboard.
 *
 * @return array<string, string>
 */
function MRT_dashboard_links(): array {
	$links = array(
		'timetables' => admin_url( 'admin.php?page=mrt_app_timetables' ),
		'stations'   => admin_url( 'admin.php?page=mrt_app_stations_routes' ),
		'front'      => home_url( '/' ),
	);
	if ( MRT_is_development_mode() ) {
		$links['wizard_smoke'] = home_url( '/wizard-smoke-test/' );
		$links['component_demo'] = home_url( '/museum-railway-timetable-component-demo/' );
	}
	return $links;
}

/**
 * Full dashboard REST payload.
 *
 * @return array<string, mixed>
 */
function MRT_get_dashboard_payload(): array {
	return array(
		'stats'        => MRT_dashboard_stats(),
		'warnings'     => MRT_collect_dashboard_warnings(),
		'next_traffic' => MRT_dashboard_next_traffic(),
		'links'        => MRT_dashboard_links(),
		'can_manage'   => current_user_can( 'manage_options' ),
		'can_operate'  => current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ),
	);
}
