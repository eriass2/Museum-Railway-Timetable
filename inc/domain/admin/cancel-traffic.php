<?php
/**
 * Cancel traffic for a date (set deviation notice on all running services).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/datetime/datetime.php';
require_once MRT_PATH . 'inc/domain/service/services.php';
require_once MRT_PATH . 'inc/domain/journey/journey-notice.php';

/** Default notice when cancelling traffic for a day. */
const MRT_CANCEL_TRAFFIC_NOTICE = 'Inställd';

/**
 * Whether a service is marked cancelled on a date.
 */
function MRT_service_is_cancelled_on_date( int $service_id, string $date_ymd ): bool {
	$notices = get_post_meta( $service_id, 'mrt_service_notices_by_date', true );
	if ( ! is_array( $notices ) || ! isset( $notices[ $date_ymd ] ) ) {
		return false;
	}
	return MRT_notice_indicates_cancelled( (string) $notices[ $date_ymd ] );
}

/**
 * Merge one date notice without clearing other deviation dates.
 */
function MRT_merge_service_notice_for_date( int $service_id, string $date_ymd, string $notice ): void {
	$notices = get_post_meta( $service_id, 'mrt_service_notices_by_date', true );
	if ( ! is_array( $notices ) ) {
		$notices = array();
	}
	$notice = sanitize_textarea_field( $notice );
	if ( $notice === '' ) {
		unset( $notices[ $date_ymd ] );
	} else {
		$notices[ $date_ymd ] = $notice;
	}
	$notices !== array()
		? update_post_meta( $service_id, 'mrt_service_notices_by_date', $notices )
		: delete_post_meta( $service_id, 'mrt_service_notices_by_date' );
}

/**
 * Mark all services running on a date as cancelled.
 *
 * @return array{date: string, notice: string, services_updated: int}|WP_Error
 */
function MRT_cancel_traffic_for_date( string $date_ymd, string $notice = MRT_CANCEL_TRAFFIC_NOTICE ) {
	if ( ! MRT_validate_date( $date_ymd ) ) {
		return new WP_Error( 'invalid_date', __( 'Invalid date.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$notice = sanitize_textarea_field( $notice );
	if ( $notice === '' ) {
		return new WP_Error( 'invalid_notice', __( 'Notice is required.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}

	$service_ids = MRT_services_running_on_date( $date_ymd );
	$updated     = 0;
	foreach ( $service_ids as $service_id ) {
		MRT_merge_service_notice_for_date( (int) $service_id, $date_ymd, $notice );
		++$updated;
	}

	return array(
		'date'              => $date_ymd,
		'notice'            => $notice,
		'services_updated'  => $updated,
	);
}

/**
 * Count how many services on a date are already marked cancelled.
 *
 * @param array<int> $service_ids Service IDs.
 */
function MRT_count_services_cancelled_on_date( array $service_ids, string $date_ymd ): int {
	$count = 0;
	foreach ( $service_ids as $service_id ) {
		if ( MRT_service_is_cancelled_on_date( (int) $service_id, $date_ymd ) ) {
			++$count;
		}
	}
	return $count;
}
