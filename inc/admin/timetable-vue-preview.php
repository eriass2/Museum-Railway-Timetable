<?php
/**
 * Timetable Vue preview in wp-admin (same JSON + component as public site).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/public/vue-shortcode-config.php';

/**
 * @param int $timetable_id Timetable post ID.
 */
function MRT_admin_render_timetable_overview_vue_preview( int $timetable_id ): string {
	if ( $timetable_id <= 0 ) {
		return MRT_render_alert( __( 'Invalid timetable.', 'museum-railway-timetable' ), 'error' );
	}

	$data = MRT_get_timetable_overview_data( $timetable_id );
	if ( is_wp_error( $data ) ) {
		return MRT_render_alert( $data->get_error_message(), 'info', 'mrt-empty' );
	}

	return MRT_render_vue_mount(
		'overview',
		array_merge(
			MRT_vue_overview_config( $timetable_id ),
			array(
				'overview' => $data,
				'embedded' => true,
			)
		)
	);
}

/**
 * Late enqueue when Vue mount renders after admin_enqueue_scripts.
 */
function MRT_admin_vue_footer_enqueue(): void {
	if ( MRT_vue_shortcode_was_used() ) {
		MRT_enqueue_vue_frontend_assets_if_needed();
	}
}
add_action( 'admin_footer', 'MRT_admin_vue_footer_enqueue', 5 );
