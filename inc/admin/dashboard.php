<?php
/**
 * Admin Dashboard – Statistics, Routes Overview, Settings, Guide
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once MRT_PATH . 'inc/admin/dashboard/dashboard-stats.php';
require_once MRT_PATH . 'inc/admin/dashboard/dashboard-routes.php';
require_once MRT_PATH . 'inc/admin/dashboard/dashboard-quick-actions.php';
require_once MRT_PATH . 'inc/admin/dashboard/dashboard-guide.php';
require_once MRT_PATH . 'inc/admin/dashboard/dashboard-shortcodes.php';
require_once MRT_PATH . 'inc/admin/dashboard/dashboard-dev-tools.php';
require_once MRT_PATH . 'inc/admin/dashboard/dashboard-timetable-pages.php';
require_once MRT_PATH . 'inc/admin/dashboard/dashboard-prices.php';

/**
 * Sanitize plugin settings input (Settings API callback).
 *
 * @param array<string, mixed> $input Raw input array
 * @return array<string, mixed>
 */
function MRT_sanitize_settings( $input ) {
	return MRT_sanitize_plugin_settings( $input );
}

/**
 * Render the enabled checkbox field
 */
function MRT_render_enabled_field() {
	$opts = MRT_get_plugin_settings();
	echo '<input type="checkbox" name="mrt_settings[enabled]" value="1" ' . checked( ! empty( $opts['enabled'] ), true, false ) . ' />';
}

/**
 * Render the note text field
 */
function MRT_render_note_field() {
	$opts = MRT_get_plugin_settings();
	echo '<input type="text" name="mrt_settings[note]" value="' . esc_attr( $opts['note'] ?? '' ) . '" class="regular-text" />';
}

/**
 * Get dashboard statistics
 *
 * @return array Stats array
 */
function MRT_get_dashboard_stats() {
	$train_types_count = wp_count_terms(
		array(
			'taxonomy'   => 'mrt_train_type',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $train_types_count ) ) {
		$train_types_count = 0;
	}
	return array(
		'stations_count'    => wp_count_posts( 'mrt_station' )->publish,
		'routes_count'      => wp_count_posts( 'mrt_route' )->publish,
		'timetables_count'  => wp_count_posts( 'mrt_timetable' )->publish,
		'services_count'    => wp_count_posts( 'mrt_service' )->publish,
		'train_types_count' => $train_types_count,
	);
}

/**
 * Render the main admin settings page (Dashboard)
 */
function MRT_render_admin_page() {
	$target = MRT_is_development_mode() ? 'mrt_app_dev_tools' : MRT_ADMIN_APP_SLUG;
	wp_safe_redirect( admin_url( 'admin.php?page=' . $target ) );
	exit;
}
