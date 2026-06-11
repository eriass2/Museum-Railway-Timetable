<?php
/**
 * Admin edit hints for disruption feed items.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/traffic-notices/disruption-feed.php';

/**
 * @return array<string, mixed>|WP_Error
 */
function MRT_disruption_feed_for_admin( string $reference_date, int $horizon_days = MRT_DISRUPTION_FEED_DEFAULT_HORIZON ) {
	$feed = MRT_disruption_feed_build( $reference_date, $horizon_days );
	if ( is_wp_error( $feed ) ) {
		return $feed;
	}
	return MRT_disruption_feed_with_admin_edit( $feed );
}

/**
 * @param array<string, mixed> $feed
 * @return array<string, mixed>
 */
function MRT_disruption_feed_with_admin_edit( array $feed ): array {
	foreach ( array( 'ongoing', 'upcoming' ) as $section ) {
		$items = isset( $feed[ $section ] ) && is_array( $feed[ $section ] ) ? $feed[ $section ] : array();
		$feed[ $section ] = array_map( 'MRT_disruption_feed_item_with_admin_edit', $items );
	}
	return $feed;
}

/**
 * @param array<string, mixed> $item
 * @return array<string, mixed>
 */
function MRT_disruption_feed_item_with_admin_edit( array $item ): array {
	$item['edit'] = MRT_disruption_feed_admin_edit_for_item( $item );
	return $item;
}

/**
 * @param array<string, mixed> $item
 * @return array{path: string, label: string, query?: array<string, string>}
 */
function MRT_disruption_feed_admin_edit_for_item( array $item ): array {
	if ( (string) ( $item['source'] ?? '' ) === 'general' ) {
		return array(
			'path'  => '/traffic-notices',
			'label' => __( 'Redigera meddelanden', 'museum-railway-timetable' ),
		);
	}
	$service_ids   = isset( $item['service_ids'] ) && is_array( $item['service_ids'] ) ? $item['service_ids'] : array();
	$timetable_id  = MRT_disruption_feed_first_timetable_id_for_services( $service_ids );
	if ( $timetable_id > 0 ) {
		return array(
			'path'  => '/timetables/' . $timetable_id,
			'query' => array( 'tab' => 'deviations' ),
			'label' => __( 'Redigera avvikelser', 'museum-railway-timetable' ),
		);
	}
	return array(
		'path'  => '/timetables',
		'label' => __( 'Öppna tidtabeller', 'museum-railway-timetable' ),
	);
}

/**
 * @param list<mixed> $service_ids
 */
function MRT_disruption_feed_first_timetable_id_for_services( array $service_ids ): int {
	foreach ( $service_ids as $service_id ) {
		$timetable_id = (int) get_post_meta( (int) $service_id, 'mrt_service_timetable_id', true );
		if ( $timetable_id > 0 ) {
			return $timetable_id;
		}
	}
	return 0;
}
