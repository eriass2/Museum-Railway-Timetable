<?php
/**
 * Admin: Import Lennakatten reference data (PDF-derived test data).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/import/lennakatten/importer.php';

/**
 * Register Import Lennakatten submenu under Railway Timetable.
 */
function MRT_register_import_lennakatten_admin_menu(): void {
	if ( ! MRT_is_development_mode() ) {
		return;
	}
	add_submenu_page(
		'mrt_settings',
		__( 'Import Lennakatten', 'museum-railway-timetable' ),
		__( 'Import Lennakatten', 'museum-railway-timetable' ),
		'manage_options',
		'mrt_import_lennakatten',
		'MRT_render_import_lennakatten_admin_page'
	);
}

add_action( 'admin_menu', 'MRT_register_import_lennakatten_admin_menu' );

/**
 * Render import page.
 */
function MRT_render_import_lennakatten_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['mrt_import_lennakatten'] ) && check_admin_referer( 'mrt_import_lennakatten', 'mrt_import_nonce' ) ) {
		$message = MRT_run_lennakatten_import();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Lennakatten 2026 Test Data', 'museum-railway-timetable' ); ?></h1>
		<p><?php esc_html_e( 'Re-imports Lennakatten test data from the CSV fixture (testdata/fixtures/lennakatten), derived from reference PDFs. Existing fixture entities are updated; other plugin stations, routes, timetables, and services are removed.', 'museum-railway-timetable' ); ?></p>
		<?php if ( $message ) : ?>
			<div class="notice notice-success"><p><?php echo wp_kses_post( $message ); ?></p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'mrt_import_lennakatten', 'mrt_import_nonce' ); ?>
			<p>
				<input type="submit" name="mrt_import_lennakatten" class="button button-primary" value="<?php esc_attr_e( 'Re-import Lennakatten', 'museum-railway-timetable' ); ?>" />
			</p>
		</form>
	</div>
	<?php
}
