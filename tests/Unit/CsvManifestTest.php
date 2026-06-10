<?php
/**
 * CSV manifest inference and template export (inc/import/csv/package/manifest.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/loader.php';

final class CsvManifestTest extends TestCase {

	private string $tmpdir = '';

	protected function tearDown(): void {
		if ( $this->tmpdir !== '' && is_dir( $this->tmpdir ) ) {
			MRT_csv_remove_dir( $this->tmpdir );
		}
		$this->tmpdir = '';
		parent::tearDown();
	}

	public function test_infer_includes_from_csv_files(): void {
		$this->tmpdir = sys_get_temp_dir() . '/mrt-manifest-test-' . wp_generate_password( 6, false );
		wp_mkdir_p( $this->tmpdir );
		file_put_contents( $this->tmpdir . '/stations.csv', "name\nTest\n" );
		file_put_contents( $this->tmpdir . '/stoptimes.csv', "service_code\nx\n" );

		$includes = MRT_csv_infer_includes_from_dir( $this->tmpdir );

		self::assertSame( array( 'stations', 'stoptimes' ), $includes );
	}

	public function test_load_package_without_manifest_builds_includes(): void {
		$this->tmpdir = sys_get_temp_dir() . '/mrt-manifest-test-' . wp_generate_password( 6, false );
		wp_mkdir_p( $this->tmpdir );
		file_put_contents( $this->tmpdir . '/stations.csv', "name\nTest\n" );

		$package = MRT_csv_load_package( $this->tmpdir );

		self::assertIsArray( $package );
		self::assertTrue( $package['manifest']['generated'] ?? false );
		self::assertContains( 'stations', $package['manifest']['includes'] ?? array() );
		MRT_csv_close_package( $package );
	}

	public function test_export_template_package_writes_header_csv_files(): void {
		$this->tmpdir = sys_get_temp_dir() . '/mrt-template-test-' . wp_generate_password( 6, false );
		$result       = MRT_csv_export_template_package( $this->tmpdir );

		self::assertSame( $this->tmpdir, $result );
		self::assertFileExists( $this->tmpdir . '/manifest.json' );
		self::assertFileExists( $this->tmpdir . '/stations.csv' );
		$stations = (string) file_get_contents( $this->tmpdir . '/stations.csv' );
		self::assertStringContainsString( 'station_code', $stations );
		self::assertStringContainsString( 'name', $stations );
	}

	public function test_resolve_upload_csv_filename(): void {
		self::assertSame( 'stoptimes.csv', MRT_csv_resolve_upload_csv_filename( 'stoptimes.csv' ) );
		self::assertSame( 'stations.csv', MRT_csv_resolve_upload_csv_filename( 'STATIONS.CSV' ) );
		self::assertNull( MRT_csv_resolve_upload_csv_filename( 'my-data.csv' ) );
	}

	public function test_stage_single_csv_upload_creates_package_dir(): void {
		$this->tmpdir = sys_get_temp_dir() . '/mrt-single-src-' . wp_generate_password( 6, false );
		file_put_contents( $this->tmpdir, "service_code,sequence,station_code,ank_pickup_mode,ank_dropoff_mode,avg_pickup_mode,avg_dropoff_mode\n" );

		$opened = MRT_csv_stage_single_csv_upload( $this->tmpdir, 'stoptimes.csv' );
		self::assertIsArray( $opened );
		self::assertFileExists( $opened['dir'] . '/stoptimes.csv' );
		MRT_csv_remove_dir( $opened['dir'] );
		unlink( $this->tmpdir );
		$this->tmpdir = '';
	}

	public function test_load_package_from_single_csv_upload(): void {
		$src = sys_get_temp_dir() . '/mrt-single-src-' . wp_generate_password( 6, false );
		file_put_contents(
			$src,
			"service_code,sequence,station_code,ank_pickup_mode,ank_dropoff_mode,avg_pickup_mode,avg_dropoff_mode\nx,1,y,scheduled,scheduled,scheduled,scheduled\n"
		);

		$package = MRT_csv_load_package( $src, 'stoptimes.csv' );
		self::assertIsArray( $package );
		self::assertContains( 'stoptimes', $package['manifest']['includes'] ?? array() );
		self::assertArrayHasKey( 'stoptimes.csv', $package['files'] );
		MRT_csv_close_package( $package );
		unlink( $src );
	}

	public function test_format_validation_errors_lists_rows(): void {
		$text = MRT_csv_format_validation_errors(
			array(
				array(
					'file'    => 'stations.csv',
					'line'    => 3,
					'message' => 'Missing name',
				),
			)
		);
		self::assertStringContainsString( 'stations.csv', $text );
		self::assertStringContainsString( 'Missing name', $text );
	}
}
