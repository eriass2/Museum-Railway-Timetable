<?php
/**
 * Timetable deviation persistence (REST + admin).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/admin/deviations-data.php';

/**
 * Apply deviation rows for all services on a timetable.
 *
 * @param int   $timetable_id Timetable ID.
 * @param array<int, array<string, array{train_type?: int, notice?: string}>> $by_service Service ID => date => fields.
 */
function MRT_apply_timetable_deviations( int $timetable_id, array $by_service ): void {
	$services = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_SERVICE,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'mrt_service_timetable_id',
					'value'   => $timetable_id,
					'compare' => '=',
				),
			),
		)
	);

	foreach ( $services as $service_id ) {
		$service_id = (int) $service_id;
		$rows       = isset( $by_service[ $service_id ] ) && is_array( $by_service[ $service_id ] )
			? $by_service[ $service_id ]
			: array();
		MRT_apply_service_deviation_rows( $service_id, $rows );
	}
}

/**
 * Persist deviation rows for one service.
 *
 * @param int   $service_id Service ID.
 * @param array<string, array{train_type?: int, notice?: string}> $rows Date => fields.
 */
function MRT_apply_service_deviation_rows( int $service_id, array $rows ): void {
	$by_type   = array();
	$by_notice = array();
	foreach ( $rows as $date => $row ) {
		$date = sanitize_text_field( (string) $date );
		if ( ! MRT_validate_date( $date ) || ! is_array( $row ) ) {
			continue;
		}
		$tid  = isset( $row['train_type'] ) ? (int) $row['train_type'] : 0;
		$text = isset( $row['notice'] ) ? sanitize_textarea_field( (string) $row['notice'] ) : '';
		if ( $tid > 0 ) {
			$term = get_term( $tid, MRT_TAXONOMY_TRAIN_TYPE );
			if ( $term && ! is_wp_error( $term ) ) {
				$by_type[ $date ] = $tid;
			}
		}
		if ( $text !== '' ) {
			$by_notice[ $date ] = $text;
		}
	}
	$by_type !== array()
		? update_post_meta( $service_id, 'mrt_service_train_types_by_date', $by_type )
		: delete_post_meta( $service_id, 'mrt_service_train_types_by_date' );
	$by_notice !== array()
		? update_post_meta( $service_id, 'mrt_service_notices_by_date', $by_notice )
		: delete_post_meta( $service_id, 'mrt_service_notices_by_date' );
}

/**
 * Serialize deviation rows for REST.
 *
 * @param int $timetable_id Timetable ID.
 * @return array<int, array{service_id: int, date: string, trip_label: string, train_type_id: int, notice: string}>
 */
function MRT_get_timetable_deviations_payload( int $timetable_id ): array {
	$services = MRT_get_services_for_timetable( $timetable_id );
	$posts    = array();
	foreach ( $services as $service ) {
		if ( $service instanceof WP_Post ) {
			$posts[] = $service;
		}
	}
	return MRT_collect_timetable_deviation_rows( $posts );
}
