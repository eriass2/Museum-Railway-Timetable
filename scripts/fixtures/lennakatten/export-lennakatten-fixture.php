<?php
/**
 * Export current plugin DB data to testdata/fixtures/lennakatten (CLI only).
 *
 * Usage (Docker):
 *   docker compose run --rm wordpress-init wp --allow-root eval-file \
 *     wp-content/plugins/museum-railway-timetable/scripts/fixtures/lennakatten/export-lennakatten-fixture.php
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Run via wp eval-file inside WordPress.\n" );
	exit( 1 );
}

if ( ! function_exists( 'MRT_csv_export_package' ) ) {
	require_once MRT_PATH . 'inc/import/csv/loader.php';
}

if ( function_exists( 'MRT_dev_cli_set_admin_user' ) ) {
	MRT_dev_cli_set_admin_user();
}

$target = MRT_PATH . 'testdata/fixtures/lennakatten';
$result = MRT_csv_export_package(
	$target,
	array(
		'include_prices'   => true,
		'include_settings' => true,
	)
);

if ( is_wp_error( $result ) ) {
	fwrite( STDERR, $result->get_error_message() . PHP_EOL );
	exit( 1 );
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI JSON
echo wp_json_encode(
	array(
		'exported_to' => $target,
		'files'       => array_values(
			array_filter(
				scandir( $target ) ?: array(),
				static fn( string $f ): bool => str_ends_with( $f, '.csv' ) || $f === 'manifest.json'
			)
		),
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) . PHP_EOL;
