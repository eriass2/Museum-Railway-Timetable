<?php
/**
 * Admin: CSV import and export.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/import/csv/loader.php';

/**
 * Register Import / Export submenu.
 */
function MRT_register_csv_import_export_menu(): void {
	add_submenu_page(
		'mrt_settings',
		__( 'Import / Export CSV', 'museum-railway-timetable' ),
		__( 'Import / Export CSV', 'museum-railway-timetable' ),
		'manage_options',
		'mrt_csv_import_export',
		'MRT_render_csv_import_export_page'
	);
}

add_action( 'admin_menu', 'MRT_register_csv_import_export_menu' );

/**
 * Handle CSV export download.
 */
function MRT_handle_csv_export_download(): void {
	if ( ! isset( $_GET['mrt_csv_export'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'mrt_csv_export' );
	$include_prices   = ! empty( $_GET['include_prices'] );
	$include_settings = ! empty( $_GET['include_settings'] );
	$zip_path         = wp_tempnam( 'mrt-export.zip' );
	if ( ! is_string( $zip_path ) ) {
		wp_die( esc_html__( 'Could not create export file.', 'museum-railway-timetable' ) );
	}
	$result = MRT_csv_export_zip(
		$zip_path,
		array(
			'include_prices'   => $include_prices,
			'include_settings' => $include_settings,
		)
	);
	if ( is_wp_error( $result ) ) {
		wp_delete_file( $zip_path );
		wp_die( esc_html( $result->get_error_message() ) );
	}
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="mrt-timetable-export.zip"' );
	header( 'Content-Length: ' . (string) filesize( $zip_path ) );
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- binary download
	readfile( $zip_path );
	wp_delete_file( $zip_path );
	exit;
}

add_action( 'admin_init', 'MRT_handle_csv_export_download' );

/**
 * Handle CSV import POST.
 */
function MRT_handle_csv_import_upload(): void {
	if ( ! isset( $_POST['mrt_csv_import'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'mrt_csv_import', 'mrt_csv_import_nonce' );
	if ( empty( $_FILES['mrt_csv_file']['tmp_name'] ) ) {
		MRT_csv_import_redirect_notice( __( 'No file uploaded.', 'museum-railway-timetable' ), 'error' );
	}
	$mode = isset( $_POST['mrt_csv_mode'] ) && $_POST['mrt_csv_mode'] === 'override' ? 'override' : 'merge';
	$tmp  = (string) $_FILES['mrt_csv_file']['tmp_name'];
	$result = MRT_csv_import_package( $tmp, $mode );
	if ( is_wp_error( $result ) ) {
		$errors = $result->get_error_data();
		$msg    = $result->get_error_message();
		if ( is_array( $errors ) ) {
			$lines = array();
			foreach ( $errors as $err ) {
				if ( is_array( $err ) ) {
					$lines[] = sprintf(
						'%s:%d %s',
						$err['file'] ?? '',
						$err['line'] ?? 0,
						$err['message'] ?? ''
					);
				}
			}
			if ( $lines !== array() ) {
				$msg .= ' ' . implode( '; ', array_slice( $lines, 0, 5 ) );
			}
		}
		MRT_csv_import_redirect_notice( $msg, 'error' );
	}
	$summary = sprintf(
		/* translators: 1: stations, 2: routes, 3: timetables, 4: services */
		__( 'Import complete. Stations: %1$d, Routes: %2$d, Timetables: %3$d, Services: %4$d.', 'museum-railway-timetable' ),
		(int) ( $result['stations'] ?? 0 ),
		(int) ( $result['routes'] ?? 0 ),
		(int) ( $result['timetables'] ?? 0 ),
		(int) ( $result['services'] ?? 0 )
	);
	MRT_csv_import_redirect_notice( $summary, 'success' );
}

add_action( 'admin_init', 'MRT_handle_csv_import_upload' );

/**
 * @param string $type success|error
 */
function MRT_csv_import_redirect_notice( string $message, string $type ): void {
	$arg = $type === 'error' ? 'mrt_csv_error' : 'mrt_csv_ok';
	wp_safe_redirect(
		add_query_arg(
			array( $arg => rawurlencode( $message ) ),
			admin_url( 'admin.php?page=mrt_csv_import_export' )
		)
	);
	exit;
}

/**
 * Render import/export admin page.
 */
function MRT_render_csv_import_export_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	MRT_csv_render_import_export_notices();
	$export_url = wp_nonce_url(
		add_query_arg(
			array(
				'mrt_csv_export' => '1',
			),
			admin_url( 'admin.php' )
		),
		'mrt_csv_export'
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import / Export CSV', 'museum-railway-timetable' ); ?></h1>
		<p><?php esc_html_e( 'Import or export timetable data as a zip package. See docs/CSV_FORMAT.md for column definitions.', 'museum-railway-timetable' ); ?></p>

		<div class="mrt-section">
			<h2><?php esc_html_e( 'Import', 'museum-railway-timetable' ); ?></h2>
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'mrt_csv_import', 'mrt_csv_import_nonce' ); ?>
				<p>
					<input type="file" name="mrt_csv_file" accept=".zip,application/zip" required />
				</p>
				<p>
					<label>
						<input type="radio" name="mrt_csv_mode" value="merge" checked />
						<?php esc_html_e( 'Add / update (keep data not in file)', 'museum-railway-timetable' ); ?>
					</label><br />
					<label>
						<input type="radio" name="mrt_csv_mode" value="override" />
						<?php esc_html_e( 'Replace scope (remove items missing from file)', 'museum-railway-timetable' ); ?>
					</label>
				</p>
				<p>
					<input type="submit" name="mrt_csv_import" class="button button-primary" value="<?php esc_attr_e( 'Import CSV zip', 'museum-railway-timetable' ); ?>" />
				</p>
			</form>
		</div>

		<div class="mrt-section">
			<h2><?php esc_html_e( 'Export', 'museum-railway-timetable' ); ?></h2>
			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
				<input type="hidden" name="mrt_csv_export" value="1" />
				<?php wp_nonce_field( 'mrt_csv_export' ); ?>
				<p>
					<label>
						<input type="checkbox" name="include_settings" value="1" />
						<?php esc_html_e( 'Include settings', 'museum-railway-timetable' ); ?>
					</label><br />
					<label>
						<input type="checkbox" name="include_prices" value="1" />
						<?php esc_html_e( 'Include prices', 'museum-railway-timetable' ); ?>
					</label>
				</p>
				<p>
					<button type="submit" class="button button-secondary"><?php esc_html_e( 'Download CSV zip', 'museum-railway-timetable' ); ?></button>
				</p>
			</form>
		</div>
	</div>
	<?php
}

/**
 * Show import result notices.
 */
function MRT_csv_render_import_export_notices(): void {
	if ( isset( $_GET['mrt_csv_ok'] ) ) {
		echo '<div class="notice notice-success"><p>' . esc_html( rawurldecode( (string) $_GET['mrt_csv_ok'] ) ) . '</p></div>';
	}
	if ( isset( $_GET['mrt_csv_error'] ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html( rawurldecode( (string) $_GET['mrt_csv_error'] ) ) . '</p></div>';
	}
}
