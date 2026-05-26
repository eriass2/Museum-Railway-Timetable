<?php
/**
 * Dashboard: Plugin data and demo tools.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render dashboard tool actions.
 */
function MRT_render_dashboard_dev_tools() {
	?>
	<div class="mrt-section">
		<h2><?php esc_html_e( 'Plugin data and demo tools', 'museum-railway-timetable' ); ?></h2>
		<p><?php esc_html_e( 'Use these tools to reset plugin data, import Lennakatten test data, or create a draft page with all public shortcodes. The same import is also available under Railway Timetable → Import Lennakatten.', 'museum-railway-timetable' ); ?></p>
		<?php MRT_render_dashboard_clear_db_button(); ?>
		<?php MRT_render_dashboard_import_demo_button(); ?>
		<?php MRT_render_dashboard_create_demo_page_button(); ?>
	</div>
	<?php
}

/**
 * Render clear database form.
 */
function MRT_render_dashboard_clear_db_button(): void {
	?>
	<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete ALL plugin data? This cannot be undone!', 'museum-railway-timetable' ) ); ?>');">
		<?php wp_nonce_field( 'mrt_clear_db', 'mrt_clear_db_nonce' ); ?>
		<input type="hidden" name="mrt_action" value="clear_db" />
		<p>
			<button type="submit" class="button button-link-delete"><?php esc_html_e( 'Clear plugin database', 'museum-railway-timetable' ); ?></button>
			<span class="description"><?php esc_html_e( 'Deletes stations, routes, timetables, trips, train types, stop times, settings, and demo page.', 'museum-railway-timetable' ); ?></span>
		</p>
	</form>
	<?php
}

/**
 * Render import demo data form.
 */
function MRT_render_dashboard_import_demo_button(): void {
	?>
	<form method="post">
		<?php wp_nonce_field( 'mrt_import_lennakatten', 'mrt_import_nonce' ); ?>
		<input type="hidden" name="mrt_action" value="import_demo_data" />
		<p>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Import demo data', 'museum-railway-timetable' ); ?></button>
			<span class="description"><?php esc_html_e( 'Creates Lennakatten test stations, two routes, train types, GRÖN and GUL timetables, trips, and stop times.', 'museum-railway-timetable' ); ?></span>
		</p>
	</form>
	<?php
}

/**
 * Render create demo page form.
 */
function MRT_render_dashboard_create_demo_page_button(): void {
	?>
	<form method="post">
		<?php wp_nonce_field( 'mrt_components_demo', 'mrt_components_demo_nonce' ); ?>
		<input type="hidden" name="mrt_action" value="create_demo_page" />
		<p>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'Create demo page', 'museum-railway-timetable' ); ?></button>
			<span class="description"><?php esc_html_e( 'Creates or updates a draft page with all public shortcodes. Open Railway Timetable → Component demo page for edit and preview links.', 'museum-railway-timetable' ); ?></span>
		</p>
	</form>
	<?php
}
