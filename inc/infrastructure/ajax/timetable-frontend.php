<?php
/**
 * AJAX handler for timetable by date (frontend)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Day timetable as JSON for Vue (month calendar panel).
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_get_timetable_for_date(): void {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mrt_frontend' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page.', 'museum-railway-timetable' ) ) );
	}

	$date       = sanitize_text_field( $_POST['date'] ?? '' );
	$train_type = sanitize_text_field( $_POST['train_type'] ?? '' );

	if ( $date === '' || ! MRT_validate_date( $date ) ) {
		wp_send_json_error( array( 'message' => __( 'Please select a valid date.', 'museum-railway-timetable' ) ) );
	}

	$data = MRT_get_timetable_day_data( $date, $train_type );
	if ( is_wp_error( $data ) ) {
		wp_send_json_error( array( 'message' => $data->get_error_message() ) );
	}

	wp_send_json_success(
		array(
			'overview' => $data,
		)
	);
}
