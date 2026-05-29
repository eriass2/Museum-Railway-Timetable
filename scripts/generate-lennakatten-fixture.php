<?php
/**
 * Generate Lennakatten CSV fixture from reference-data.php.
 *
 * Usage: php scripts/generate-lennakatten-fixture.php
 */

declare(strict_types=1);

$root = dirname( __DIR__ );
define( 'ABSPATH', $root . DIRECTORY_SEPARATOR );
define( 'MRT_PATH', ABSPATH );
define( 'MRT_VERSION', '0.3.0' );

require_once $root . '/tests/wp-stubs.php';
require_once $root . '/scripts/csv-cli-stubs.php';
require_once MRT_PATH . 'inc/import/csv/schema.php';
require_once MRT_PATH . 'inc/import/csv/slugify.php';
require_once MRT_PATH . 'inc/import/csv/symbol-map.php';
require_once MRT_PATH . 'inc/import/csv/writer.php';
require_once MRT_PATH . 'inc/import/lennakatten/reference-data.php';
require_once MRT_PATH . 'inc/import/csv/build-lennakatten.php';
require_once MRT_PATH . 'inc/import/csv/build-lennakatten-routes.php';

$target = $root . '/testdata/fixtures/lennakatten';
if ( ! MRT_csv_write_lennakatten_fixture( $target ) ) {
	fwrite( STDERR, "Failed to write fixture to {$target}\n" );
	exit( 1 );
}

echo "Wrote Lennakatten CSV fixture to {$target}\n";
