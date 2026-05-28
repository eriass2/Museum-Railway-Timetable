<?php
/**
 * Timetable HTML preview (wp-admin only). Public site uses overview-data JSON + Vue.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML timetable render is only for admin preview, not public shortcodes or AJAX.
 */
function MRT_timetable_allows_html_preview(): bool {
	return is_admin();
}

/**
 * Enqueue legacy overview CSS for the timetable edit screen preview box.
 */
function MRT_admin_enqueue_timetable_preview_styles(): void {
	if ( ! function_exists( 'MRT_enqueue_frontend_public_styles' ) ) {
		return;
	}
	$public = MRT_enqueue_frontend_public_styles();
	MRT_enqueue_frontend_overview_styles( $public );
}

/**
 * @param int $timetable_id Timetable post ID.
 */
function MRT_admin_render_timetable_overview_preview( int $timetable_id ): string {
	if ( ! MRT_timetable_allows_html_preview() ) {
		return '';
	}
	MRT_admin_enqueue_timetable_preview_styles();
	return MRT_render_timetable_overview( $timetable_id );
}
