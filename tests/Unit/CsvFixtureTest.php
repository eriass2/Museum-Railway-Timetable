<?php
/**
 * Tests for Lennakatten CSV fixture package.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/schema.php';
require_once ABSPATH . 'inc/import/csv/slugify.php';
require_once ABSPATH . 'inc/import/csv/symbol-map.php';
require_once ABSPATH . 'inc/import/csv/reader.php';
require_once ABSPATH . 'inc/import/csv/writer.php';
require_once ABSPATH . 'inc/import/csv/package.php';
require_once ABSPATH . 'inc/import/csv/validate-manifest.php';
require_once ABSPATH . 'inc/import/csv/validate-codes.php';
require_once ABSPATH . 'inc/import/csv/validate-codes-entities.php';
require_once ABSPATH . 'inc/import/csv/validate-references.php';

final class CsvFixtureTest extends TestCase {

	public function test_lennakatten_fixture_is_valid(): void {
		$path    = ABSPATH . 'testdata/fixtures/lennakatten';
		$package = MRT_csv_load_package( $path );
		self::assertIsArray( $package );
		$result = MRT_csv_validate_package( $package );
		self::assertTrue( $result['valid'], json_encode( $result['errors'] ) );
	}

	public function test_lennakatten_fixture_has_green_and_yellow_timetables(): void {
		$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
		self::assertIsArray( $package );
		$rows = $package['files']['timetables.csv'] ?? array();
		$codes = array_column( $rows, 'timetable_code' );
		self::assertContains( 'green', $codes );
		self::assertContains( 'yellow', $codes );
	}
}
