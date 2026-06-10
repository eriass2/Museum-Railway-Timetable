<?php
/**
 * Timetable overview payload for Vue (JSON).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/timetable/view/grid/grid-merge.php';
require_once __DIR__ . '/overview-print-key.php';
require_once __DIR__ . '/overview-bus-rows.php';
require_once __DIR__ . '/overview-rail-rows.php';
require_once __DIR__ . '/overview-branch-group.php';

/**
 * @param array<int, WP_Post> $services
 * @param array<string, mixed> $meta timetableId, title, timetableType, scope, typeBanner|null
 * @return array<string, mixed>|WP_Error
 */
function MRT_build_timetable_overview_payload( array $services, string $dateYmd, array $meta ) {
	if ( $services === array() ) {
		$message = isset( $meta['emptyMessage'] ) ? (string) $meta['emptyMessage'] : __( 'No trips found.', 'museum-railway-timetable' );
		return new WP_Error( 'empty', $message );
	}

	$grouped = MRT_group_services_by_route( $services, $dateYmd );
	if ( empty( $grouped ) ) {
		$message = isset( $meta['emptyMessage'] ) ? (string) $meta['emptyMessage'] : __( 'No valid trips found.', 'museum-railway-timetable' );
		return new WP_Error( 'empty', $message );
	}

	$grouped = MRT_timetable_groups_link_branch_pairs( $grouped );
	usort( $grouped, 'MRT_sort_timetable_groups_source_order' );

	$groups = array();
	foreach ( $grouped as $group ) {
		if ( MRT_timetable_group_is_branch_shuttle( $group ) && ! empty( $group['paired_rail'] ) ) {
			continue;
		}
		$groups[] = MRT_timetable_overview_group_to_json( $group, $dateYmd );
	}

	$tt     = (string) ( $meta['timetableType'] ?? '' );
	$banner = $meta['typeBanner'] ?? null;
	if ( $banner === null && $tt !== '' ) {
		$banner = MRT_timetable_type_banner_text( $tt );
	}

	return array(
		'scope'         => (string) ( $meta['scope'] ?? 'timetable' ),
		'timetableId'   => (int) ( $meta['timetableId'] ?? 0 ),
		'title'         => (string) ( $meta['title'] ?? '' ),
		'dateYmd'       => $dateYmd,
		'timetableType' => $tt,
		'typeBanner'    => is_array( $banner ) ? $banner : array( 'label' => '' ),
		'printKey'      => MRT_timetable_print_key_data( $services, $dateYmd ),
		'iconUrls'      => MRT_train_type_icon_urls(),
		'groups'        => $groups,
	);
}

/**
 * @return array<string, mixed>|WP_Error
 */
function MRT_get_timetable_overview_data( int $timetable_id, ?string $dateYmd = null ) {
	if ( $timetable_id <= 0 ) {
		return new WP_Error( 'invalid_timetable', __( 'Invalid timetable.', 'museum-railway-timetable' ) );
	}

	if ( $dateYmd === null ) {
		$datetime = MRT_get_current_datetime();
		$dateYmd  = $datetime['date'];
	}

	$services = MRT_get_services_for_timetable( $timetable_id );
	$tt       = (string) get_post_meta( $timetable_id, 'mrt_timetable_type', true );

	return MRT_build_timetable_overview_payload(
		$services,
		$dateYmd,
		array(
			'scope'         => 'timetable',
			'timetableId'   => $timetable_id,
			'title'         => get_the_title( $timetable_id ),
			'timetableType' => $tt,
			'emptyMessage'  => __( 'No trips in this timetable.', 'museum-railway-timetable' ),
		)
	);
}

/**
 * All services running on one calendar day (month view day panel).
 *
 * @return array<string, mixed>|WP_Error
 */
function MRT_get_timetable_day_data( string $dateYmd, string $train_type_slug = '' ) {
	if ( ! MRT_validate_date( $dateYmd ) ) {
		return new WP_Error( 'invalid_date', __( 'Invalid date.', 'museum-railway-timetable' ) );
	}

	$service_ids = MRT_services_running_on_date( $dateYmd, $train_type_slug );
	$services    = MRT_get_services_by_post_ids( $service_ids );

	$title = sprintf(
		/* translators: %s: formatted date */
		__( 'Timetable for %s', 'museum-railway-timetable' ),
		date_i18n( get_option( 'date_format' ), strtotime( $dateYmd ) )
	);

	$tt = MRT_dominant_timetable_type_for_date( $dateYmd );

	return MRT_build_timetable_overview_payload(
		$services,
		$dateYmd,
		array(
			'scope'         => 'day',
			'timetableId'   => 0,
			'title'         => $title,
			'timetableType' => $tt,
			'emptyMessage'  => __( 'No services running on this date.', 'museum-railway-timetable' ),
		)
	);
}

/**
 * @return array{label: string}
 */
function MRT_timetable_type_banner_text( string $type ): array {
	$labels = array(
		'green'  => 'GRÖN TIDTABELL',
		'red'    => 'RÖD TIDTABELL',
		'yellow' => 'GUL TIDTABELL',
		'orange' => 'ORANGE TIDTABELL',
		'blue'   => 'BLÅ TIDTABELL',
	);
	$key    = strtolower( $type );
	$label  = $labels[ $key ] ?? strtoupper( $type );
	return array( 'label' => $label );
}

/**
 * @param array<string, mixed> $group
 * @return array<string, mixed>
 */
function MRT_timetable_overview_group_to_json( array $group, string $dateYmd ): array {
	if ( MRT_timetable_group_is_branch_shuttle( $group ) ) {
		return MRT_timetable_branch_group_to_json( $group, $dateYmd );
	}
	return MRT_timetable_rail_group_to_json( $group, $dateYmd );
}
