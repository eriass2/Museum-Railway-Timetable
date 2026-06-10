<?php
/**
 * Aggregate general notices and trip deviations for public display.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/traffic-notices/public-notices.php';
require_once MRT_PATH . 'inc/domain/admin/deviations-data.php';
require_once MRT_PATH . 'inc/domain/timetable/timetable-pages.php';
require_once MRT_PATH . 'inc/domain/timetable/view/group-view.php';
require_once MRT_PATH . 'inc/domain/journey/journey-notice.php';

/**
 * Build traffic notices payload for REST and shortcode fallback.
 *
 * @return array<string, mixed>|WP_Error
 */
function MRT_traffic_notices_aggregate(
	string $reference_date,
	int $days = 1,
	bool $show_general = true,
	bool $show_deviations = true
) {
	if ( ! MRT_validate_date( $reference_date ) ) {
		return new WP_Error( 'mrt_traffic_date', __( 'Ogiltigt datum.', 'museum-railway-timetable' ) );
	}
	$days = max( 1, min( 2, $days ) );
	$dates = MRT_traffic_notices_date_range( $reference_date, $days );

	$general = array();
	if ( $show_general ) {
		$seen = array();
		foreach ( $dates as $date ) {
			foreach ( MRT_public_notices_active_for_date( $date ) as $item ) {
				if ( isset( $seen[ $item['id'] ] ) ) {
					continue;
				}
				$seen[ $item['id'] ] = true;
				$general[]           = $item;
			}
		}
	}

	$by_date = array();
	if ( $show_deviations ) {
		foreach ( $dates as $date ) {
			$deviations = MRT_traffic_notices_deviations_for_date( $date );
			if ( $deviations === array() ) {
				continue;
			}
			$by_date[] = array(
				'date'       => $date,
				'date_label' => MRT_traffic_notices_date_label( $date, $reference_date ),
				'deviations' => $deviations,
			);
		}
	}

	$is_empty = $general === array() && $by_date === array();

	return array(
		'reference_date' => $reference_date,
		'days'           => $days,
		'general'        => $general,
		'by_date'        => $by_date,
		'is_empty'       => $is_empty,
	);
}

/**
 * @return list<string> YYYY-MM-DD
 */
function MRT_traffic_notices_date_range( string $start_ymd, int $days ): array {
	$out = array();
	for ( $i = 0; $i < $days; $i++ ) {
		$ts = strtotime( $start_ymd . ' +' . $i . ' days' );
		if ( $ts === false ) {
			break;
		}
		$out[] = gmdate( 'Y-m-d', $ts );
	}
	return $out;
}

/**
 * Human-readable date label for grouped deviations.
 */
function MRT_traffic_notices_date_label( string $date_ymd, string $reference_date ): string {
	if ( $date_ymd === $reference_date ) {
		return __( 'Idag', 'museum-railway-timetable' );
	}
	$tomorrow = gmdate( 'Y-m-d', strtotime( $reference_date . ' +1 day' ) );
	if ( $date_ymd === $tomorrow ) {
		return __( 'Imorgon', 'museum-railway-timetable' );
	}
	$ts = strtotime( $date_ymd );
	if ( $ts === false ) {
		return $date_ymd;
	}
	$weekday = wp_date( 'l j F', $ts );
	return is_string( $weekday ) ? $weekday : $date_ymd;
}

/**
 * @return list<array{service_id: int, service_number: string, route_label: string, trip_label: string, notice: string, is_cancelled: bool, train_type_id: int}>
 */
function MRT_traffic_notices_deviations_for_date( string $date_ymd ): array {
	if ( ! MRT_validate_date( $date_ymd ) ) {
		return array();
	}
	$rows = array();
	foreach ( MRT_get_published_timetables() as $timetable ) {
		if ( ! $timetable instanceof WP_Post ) {
			continue;
		}
		$services = MRT_get_services_for_timetable( (int) $timetable->ID );
		foreach ( MRT_collect_timetable_deviation_rows( $services ) as $row ) {
			if ( (string) $row['date'] !== $date_ymd ) {
				continue;
			}
			$service_id = (int) $row['service_id'];
			$service    = get_post( $service_id );
			if ( ! $service instanceof WP_Post ) {
				continue;
			}
			$notice = (string) $row['notice'];
			if ( $notice === '' && (int) $row['train_type_id'] <= 0 ) {
				continue;
			}
			$formatted = MRT_traffic_notice_format_deviation( $service, $row );
			if ( $formatted !== null ) {
				$rows[] = $formatted;
			}
		}
	}
	usort(
		$rows,
		static function ( array $a, array $b ): int {
			$cmp = strcmp( (string) $a['trip_label'], (string) $b['trip_label'] );
			return $cmp !== 0 ? $cmp : (int) $a['service_id'] <=> (int) $b['service_id'];
		}
	);
	return $rows;
}

/**
 * @param array{service_id: int, date: string, trip_label: string, train_type_id: int, notice: string} $row
 * @return array{service_id: int, service_number: string, route_label: string, trip_label: string, notice: string, is_cancelled: bool, train_type_id: int}|null
 */
function MRT_traffic_notice_format_deviation( WP_Post $service, array $row ): ?array {
	$notice = trim( (string) $row['notice'] );
	if ( $notice === '' && (int) $row['train_type_id'] <= 0 ) {
		return null;
	}
	$number = (string) get_post_meta( $service->ID, 'mrt_service_number', true );
	if ( $number === '' ) {
		$number = (string) $service->ID;
	}
	$dest      = MRT_get_service_destination( (int) $service->ID );
	$dest_name = ! empty( $dest['destination'] ) ? (string) $dest['destination'] : '';
	$route_id  = (int) get_post_meta( $service->ID, 'mrt_service_route_id', true );
	$route_label = $route_id > 0 ? (string) get_the_title( $route_id ) : $dest_name;
	$trip_label  = $dest_name !== '' ? $number . ' → ' . $dest_name : $number;

	return array(
		'service_id'     => (int) $service->ID,
		'service_number' => $number,
		'route_label'    => $route_label,
		'trip_label'     => $trip_label,
		'notice'         => $notice,
		'is_cancelled'   => MRT_notice_indicates_cancelled( $notice ),
		'train_type_id'  => (int) $row['train_type_id'],
	);
}

/**
 * Format one deviation line for HTML fallback.
 */
function MRT_traffic_notice_deviation_line_text( array $deviation ): string {
	$notice = trim( (string) ( $deviation['notice'] ?? '' ) );
	$number = (string) ( $deviation['service_number'] ?? '' );
	$route  = (string) ( $deviation['route_label'] ?? '' );
	if ( $notice === '' ) {
		return sprintf(
			/* translators: 1: service number, 2: route label */
			__( 'Tåg %1$s, %2$s', 'museum-railway-timetable' ),
			$number,
			$route
		);
	}
	return sprintf(
		/* translators: 1: notice text, 2: service number, 3: route label */
		__( '%1$s — Tåg %2$s, %3$s', 'museum-railway-timetable' ),
		$notice,
		$number,
		$route
	);
}
