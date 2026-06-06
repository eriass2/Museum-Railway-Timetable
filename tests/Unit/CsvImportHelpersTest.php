<?php
/**
 * CSV import helpers extracted during refactor (package-load, import-errors, REST download).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/loader.php';
require_once ABSPATH . 'inc/infrastructure/rest/csv-download.php';

final class CsvImportHelpersTest extends TestCase {

	private string $tmpdir = '';

	protected function tearDown(): void {
		if ( $this->tmpdir !== '' && is_dir( $this->tmpdir ) ) {
			MRT_csv_remove_dir( $this->tmpdir );
		}
		$this->tmpdir = '';
		parent::tearDown();
	}

	public function test_parse_manifest_file_rejects_invalid_json(): void {
		$this->tmpdir = sys_get_temp_dir() . '/mrt-manifest-parse-' . wp_generate_password( 6, false );
		wp_mkdir_p( $this->tmpdir );
		file_put_contents( $this->tmpdir . '/manifest.json', 'not-json' );

		$result = MRT_csv_parse_manifest_file( $this->tmpdir . '/manifest.json' );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'mrt_csv_manifest', $result->get_error_code() );
	}

	public function test_load_manifest_from_dir_reads_existing_manifest(): void {
		$this->tmpdir = sys_get_temp_dir() . '/mrt-manifest-load-' . wp_generate_password( 6, false );
		wp_mkdir_p( $this->tmpdir );
		file_put_contents(
			$this->tmpdir . '/manifest.json',
			wp_json_encode(
				array(
					'format_version' => '1',
					'includes'       => array( 'stations' ),
				)
			)
		);

		$manifest = MRT_csv_load_manifest_from_dir( $this->tmpdir );

		self::assertIsArray( $manifest );
		self::assertSame( '1', $manifest['format_version'] ?? '' );
		self::assertSame( array( 'stations' ), $manifest['includes'] ?? array() );
	}

	public function test_stage_single_csv_upload_rejects_unknown_filename(): void {
		$this->tmpdir = sys_get_temp_dir() . '/mrt-single-bad-' . wp_generate_password( 6, false );
		file_put_contents( $this->tmpdir, "name\nTest\n" );

		$result = MRT_csv_stage_single_csv_upload( $this->tmpdir, 'wrong-name.csv' );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'mrt_csv_single', $result->get_error_code() );
		unlink( $this->tmpdir );
		$this->tmpdir = '';
	}

	public function test_import_error_message_formats_validation_errors(): void {
		$error = new WP_Error(
			'mrt_csv_invalid',
			'CSV validation failed.',
			array(
				array(
					'file'    => 'stoptimes.csv',
					'line'    => 5,
					'message' => 'Unknown service_code',
				),
			)
		);

		$message = MRT_csv_import_error_message( $error );

		self::assertStringContainsString( 'stoptimes.csv', $message );
		self::assertStringContainsString( 'Unknown service_code', $message );
	}

	public function test_import_error_message_passes_through_non_validation_errors(): void {
		$error = new WP_Error( 'mrt_csv_single', 'Unknown CSV file.' );

		self::assertSame( 'Unknown CSV file.', MRT_csv_import_error_message( $error ) );
	}

	public function test_format_validation_error_line_without_row_number(): void {
		$line = MRT_csv_format_validation_error_line(
			array(
				'file'    => 'manifest.json',
				'line'    => 0,
				'message' => 'Unsupported format_version.',
			)
		);

		self::assertStringContainsString( 'manifest.json', $line );
		self::assertStringContainsString( 'Unsupported format_version.', $line );
		self::assertStringNotContainsString( 'rad', $line );
	}

	public function test_zip_download_payload_returns_base64_and_deletes_file(): void {
		$zip_path = sys_get_temp_dir() . '/mrt-dl-test-' . wp_generate_password( 6, false ) . '.zip';
		file_put_contents( $zip_path, 'zip-bytes' );

		$payload = MRT_rest_zip_download_payload( $zip_path, 'demo.zip' );

		self::assertIsArray( $payload );
		self::assertSame( 'demo.zip', $payload['filename'] );
		self::assertSame( base64_encode( 'zip-bytes' ), $payload['content_base64'] );
		self::assertFileDoesNotExist( $zip_path );
	}
}
