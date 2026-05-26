<?php
/**
 * AJAX: timetable overview HTML for Vue frontend.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return overview HTML for a timetable post ID.
 */
function MRT_ajax_timetable_overview_html(): void {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mrt_frontend' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Security check failed. Please refresh the page.', 'museum-railway-timetable' ),
			)
		);
	}

	$timetable_id = isset( $_POST['timetable_id'] ) ? (int) $_POST['timetable_id'] : 0;
	if ( $timetable_id <= 0 ) {
		wp_send_json_error(
			array(
				'message' => __( 'Timetable not found.', 'museum-railway-timetable' ),
			)
		);
	}

	wp_send_json_success(
		array(
			'html' => MRT_render_timetable_overview( $timetable_id ),
		)
	);
}

add_action( 'wp_ajax_mrt_timetable_overview_html', 'MRT_ajax_timetable_overview_html' );
add_action( 'wp_ajax_nopriv_mrt_timetable_overview_html', 'MRT_ajax_timetable_overview_html' );
