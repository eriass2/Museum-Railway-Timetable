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
	<div class="mrt-card mrt-card--warning mrt-mt-xl">
		<h2><?php esc_html_e( 'Plugin data and demo tools', 'museum-railway-timetable' ); ?></h2>
		<p><?php esc_html_e( 'Use these admin tools to reset plugin data, import test data, and create a page showing every public shortcode.', 'museum-railway-timetable' ); ?></p>
		<div class="mrt-grid mrt-grid-auto-250 mrt-mt-1">
			<?php MRT_render_dashboard_clear_db_button(); ?>
			<?php MRT_render_dashboard_import_demo_button(); ?>
			<?php MRT_render_dashboard_create_demo_page_button(); ?>
		</div>
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
		<button type="submit" class="button mrt-btn mrt-btn--action mrt-btn--danger">
			<strong><?php esc_html_e( 'Clear plugin database', 'museum-railway-timetable' ); ?></strong>
			<span><?php esc_html_e( 'Delete stations, routes, timetables, trips, train types, stop times, settings, and demo page.', 'museum-railway-timetable' ); ?></span>
		</button>
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
		<button type="submit" class="button button-primary mrt-btn mrt-btn--action">
			<strong><?php esc_html_e( 'Import demo data', 'museum-railway-timetable' ); ?></strong>
			<span><?php esc_html_e( 'Create Lennakatten test stations, routes, train types, timetable, trips, and stop times.', 'museum-railway-timetable' ); ?></span>
		</button>
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
		<button type="submit" class="button button-primary mrt-btn mrt-btn--action">
			<strong><?php esc_html_e( 'Create demo page', 'museum-railway-timetable' ); ?></strong>
			<span><?php esc_html_e( 'Create or update a draft page showing all public plugin shortcodes.', 'museum-railway-timetable' ); ?></span>
		</button>
	</form>
	<?php
}
