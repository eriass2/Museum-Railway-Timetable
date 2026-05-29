<?php
/**
 * Dashboard: public timetable pages (index + per timetable).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render timetable pages section.
 */
function MRT_render_dashboard_timetable_pages(): void {
	?>
	<div class="mrt-section">
		<h2><?php esc_html_e( 'Public timetable pages', 'museum-railway-timetable' ); ?></h2>
		<p><?php esc_html_e( 'Create a Tidtabeller overview page and one linkable page per published timetable (with the full timetable overview).', 'museum-railway-timetable' ); ?></p>
		<?php MRT_render_dashboard_sync_timetable_pages_button(); ?>
		<?php if ( function_exists( 'MRT_render_timetable_public_page_admin_links' ) ) : ?>
			<h3 class="title"><?php esc_html_e( 'Timetable pages', 'museum-railway-timetable' ); ?></h3>
			<?php MRT_render_timetable_public_page_admin_links(); ?>
		<?php endif; ?>
	</div>
	<?php
}
