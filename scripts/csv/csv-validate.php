<?php
/**
 * Validate a CSV package from the command line (no WordPress).
 *
 * Usage: php scripts/csv-validate.php testdata/fixtures/lennakatten
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );
define( 'ABSPATH', $root . DIRECTORY_SEPARATOR );
define( 'MRT_PATH', ABSPATH );
define( 'MRT_VERSION', '0.3.1' );

require_once $root . '/tests/wp-stubs.php';
require_once __DIR__ . '/csv-cli-stubs.php';
require_once MRT_PATH . 'inc/import/csv/schema.php';
require_once MRT_PATH . 'inc/import/csv/slugify.php';
require_once MRT_PATH . 'inc/import/csv/reader.php';
require_once MRT_PATH . 'inc/import/csv/writer.php';
require_once MRT_PATH . 'inc/import/csv/package/manifest.php';
require_once MRT_PATH . 'inc/import/csv/package/package-load.php';
require_once MRT_PATH . 'inc/import/csv/package/package.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-manifest.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-codes.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-codes-entities.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-lines.php';
require_once MRT_PATH . 'inc/domain/line/line-route-resolve.php';
require_once MRT_PATH . 'inc/domain/line/line-route-definitions.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-references.php';

$path = $argv[1] ?? '';
if ( $path === '' ) {
	fwrite( STDERR, "Usage: php scripts/csv-validate.php <package-path>\n" );
	exit( 1 );
}
if ( $path[0] !== '/' && ! preg_match( '/^[A-Za-z]:/', $path ) ) {
	$path = $root . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $path );
}

$package = MRT_csv_load_package( $path );
if ( is_wp_error( $package ) ) {
	fwrite( STDERR, $package->get_error_message() . "\n" );
	exit( 1 );
}

$result = MRT_csv_validate_package( $package );
MRT_csv_close_package( $package );

if ( $result['valid'] ) {
	echo "OK: CSV package is valid.\n";
	exit( 0 );
}

foreach ( $result['errors'] as $error ) {
	fprintf(
		STDERR,
		"%s:%d: %s\n",
		$error['file'],
		$error['line'],
		$error['message']
	);
}
exit( 1 );
