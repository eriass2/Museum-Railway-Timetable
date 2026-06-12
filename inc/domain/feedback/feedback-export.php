<?php
/**
 * Export wizard feedback reports as CSV.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/feedback/wizard-feedback.php';

/**
 * CSV column headers for feedback export.
 *
 * @return array<int, string>
 */
function MRT_feedback_export_csv_headers(): array {
	return array(
		'id',
		'created_at',
		'type',
		'status',
		'message',
		'email',
		'page_url',
		'wizard_step',
		'from_station_id',
		'to_station_id',
		'date',
		'trip_type',
	);
}

/**
 * Build one CSV row for a feedback post.
 *
 * @param array<int, string> $headers Column order.
 * @return array<int, string>
 */
function MRT_feedback_export_csv_line( int $post_id, array $headers ): array {
	$item = MRT_feedback_format_item( $post_id );
	$post = get_post( $post_id );
	$ctx  = is_array( $item['context'] ?? null ) ? $item['context'] : array();
	$created_at = $post instanceof WP_Post ? (string) $post->post_date : '';
	$row  = array(
		'id'               => (string) $post_id,
		'created_at'       => $created_at,
		'type'             => (string) ( $item['type'] ?? '' ),
		'status'           => (string) ( $item['status'] ?? '' ),
		'message'          => (string) ( $item['message'] ?? '' ),
		'email'            => (string) ( $item['email'] ?? '' ),
		'page_url'         => (string) ( $item['page_url'] ?? '' ),
		'wizard_step'      => (string) ( $item['wizard_step'] ?? '' ),
		'from_station_id'  => (string) ( $ctx['fromStationId'] ?? '' ),
		'to_station_id'    => (string) ( $ctx['toStationId'] ?? '' ),
		'date'             => (string) ( $ctx['date'] ?? '' ),
		'trip_type'        => (string) ( $ctx['tripType'] ?? '' ),
	);
	$line = array();
	foreach ( $headers as $header ) {
		$line[] = (string) ( $row[ $header ] ?? '' );
	}
	return $line;
}

/**
 * Export all feedback posts as UTF-8 CSV (Excel BOM).
 */
function MRT_feedback_export_csv(): string {
	$headers = MRT_feedback_export_csv_headers();
	$ids     = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_FEEDBACK,
			'post_status'    => 'private',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	$handle  = fopen( 'php://temp', 'r+' );
	if ( $handle === false ) {
		return '';
	}
	fwrite( $handle, "\xEF\xBB\xBF" );
	fputcsv( $handle, $headers );
	foreach ( array_map( 'intval', (array) $ids ) as $post_id ) {
		if ( $post_id <= 0 ) {
			continue;
		}
		fputcsv( $handle, MRT_feedback_export_csv_line( $post_id, $headers ) );
	}
	rewind( $handle );
	$csv = stream_get_contents( $handle );
	fclose( $handle );
	return is_string( $csv ) ? $csv : '';
}

/**
 * REST download payload for feedback CSV export.
 *
 * @return array{filename: string, content_base64: string}
 */
function MRT_feedback_export_download_payload(): array {
	$filename = 'mrt-feedback-' . gmdate( 'Y-m-d' ) . '.csv';
	return array(
		'filename'       => $filename,
		'content_base64' => base64_encode( MRT_feedback_export_csv() ),
	);
}
