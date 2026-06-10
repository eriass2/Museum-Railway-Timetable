<?php
/**
 * Pack a CSV package directory into a zip (for import via admin).
 *
 * Usage: php scripts/csv-package-zip.php [source-dir] [output.zip]
 * Default: testdata/fixtures/lennakatten -> testdata/fixtures/lennakatten.zip
 *
 * Requires ext-zip, or use scripts/csv-package-zip.ps1 / csv-package-zip.sh (Docker).
 */

declare(strict_types=1);

$root = dirname( __DIR__ );
define( 'ABSPATH', $root . DIRECTORY_SEPARATOR );
define( 'MRT_PATH', ABSPATH );
define( 'MRT_VERSION', '0.3.0' );

require_once $root . '/tests/wp-stubs.php';
require_once $root . '/scripts/csv-cli-stubs.php';
require_once MRT_PATH . 'inc/import/csv/writer.php';
require_once MRT_PATH . 'inc/import/csv/package/package.php';
require_once MRT_PATH . 'inc/import/csv/schema.php';
require_once MRT_PATH . 'inc/import/csv/slugify.php';
require_once MRT_PATH . 'inc/import/csv/reader.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-manifest.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-codes.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-codes-entities.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-references.php';

function csv_package_zip_resolve_path( string $root, string $path ): string {
	if ( $path === '' ) {
		return '';
	}
	if ( $path[0] === '/' || preg_match( '/^[A-Za-z]:/', $path ) ) {
		return $path;
	}
	return $root . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $path );
}

$source = csv_package_zip_resolve_path( $root, $argv[1] ?? 'testdata/fixtures/lennakatten' );
$output = csv_package_zip_resolve_path(
	$root,
	$argv[2] ?? 'testdata/fixtures/lennakatten.zip'
);

if ( ! is_dir( $source ) || ! is_file( $source . DIRECTORY_SEPARATOR . 'manifest.json' ) ) {
	fwrite( STDERR, "Source must be a CSV package directory with manifest.json.\n" );
	exit( 1 );
}

$package = MRT_csv_load_package( $source );
if ( is_wp_error( $package ) ) {
	fwrite( STDERR, $package->get_error_message() . "\n" );
	exit( 1 );
}

$result = MRT_csv_validate_package( $package );
MRT_csv_close_package( $package );
if ( ! $result['valid'] ) {
	fwrite( STDERR, "Package validation failed; fix CSV before zipping.\n" );
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
}

$out_dir = dirname( $output );
if ( ! is_dir( $out_dir ) && ! wp_mkdir_p( $out_dir ) ) {
	fwrite( STDERR, "Could not create output directory.\n" );
	exit( 1 );
}

if ( ! MRT_csv_zip_package_files( $source, $output ) ) {
	fwrite( STDERR, "Could not create zip archive (is ext-zip enabled?).\n" );
	fwrite( STDERR, "Use: scripts/csv-package-zip.ps1 or scripts/csv-package-zip.sh\n" );
	exit( 1 );
}

$size_kb = round( filesize( $output ) / 1024, 1 );
echo "OK: {$output} ({$size_kb} KiB)\n";

/**
 * Zip manifest.json and CSV tables only (flat paths, forward slashes).
 */
function MRT_csv_zip_package_files( string $source_dir, string $zip_path ): bool {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return false;
	}
	$source_dir = rtrim( $source_dir, '/\\' ) . DIRECTORY_SEPARATOR;
	$files      = array( 'manifest.json' );
	foreach ( MRT_csv_entity_files() as $file ) {
		if ( is_file( $source_dir . $file ) ) {
			$files[] = $file;
		}
	}

	$zip = new ZipArchive();
	if ( $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
		return false;
	}
	foreach ( $files as $file ) {
		$full = $source_dir . $file;
		if ( ! is_file( $full ) ) {
			continue;
		}
		$zip->addFile( $full, str_replace( '\\', '/', $file ) );
	}
	$zip->close();
	return true;
}
