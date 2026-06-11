<?php
/**
 * Line-derived route definitions (LINES Fas C).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/loader.php';

final class LineRouteDefinitionsTest extends TestCase {

	public function test_lennakatten_derives_seven_directed_routes_from_lines(): void {
		$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
		self::assertIsArray( $package );
		$files = (array) ( $package['files'] ?? array() );
		$rows  = MRT_csv_line_derived_route_rows( $files );

		self::assertArrayHasKey( 'faringe-uppsala-ostra', $rows );
		self::assertArrayHasKey( 'uppsala-faringe', $rows );
		self::assertArrayHasKey( 'selkna-fjallnora', $rows );
		self::assertArrayHasKey( 'fjallnora-selkna', $rows );
		self::assertArrayHasKey( 'marielund-linnes-hammarby', $rows );
		self::assertArrayHasKey( 'linnes-hammarby-marielund', $rows );
		self::assertArrayHasKey( 'linnes-uppsala', $rows );
		self::assertCount( 7, $rows );
		self::assertSame( 'main', $rows['faringe-uppsala-ostra']['branch_code'] ?? '' );
		self::assertSame( 'linnes-marielund', $rows['marielund-linnes-hammarby']['branch_code'] ?? '' );
	}

	public function test_update_line_registry_title(): void {
		MRT_set_line_registry(
			array(
				'main' => array(
					'title'         => 'Old',
					'kind'          => 'main',
					'station_codes' => array( 'faringe', 'uppsala-ostra' ),
				),
			)
		);
		self::assertTrue( MRT_update_line_registry_title( 'main', 'New title' ) );
		self::assertSame( 'New title', MRT_line_registry_entry( 'main' )['title'] ?? '' );
		self::assertFalse( MRT_update_line_registry_title( 'missing', 'X' ) );
		delete_option( MRT_line_registry_option_key() );
	}
}
