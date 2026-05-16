<?php
/**
 * Tests for static Lennakatten import reference data.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/import-lennakatten/import-data.php';

final class ImportDataTest extends TestCase {

	public function test_yellow_timetable_dates_cover_friday_codes_c_and_d(): void {
		$dates = MRT_import_get_yellow_timetable_dates();

		self::assertCount( 17, $dates );
		self::assertSame( '2026-05-29', $dates[0] );
		self::assertContains( '2026-08-07', $dates );
		self::assertSame( '2026-09-25', $dates[ count( $dates ) - 1 ] );
	}

	public function test_yellow_timetable_services_are_railbus(): void {
		$out = MRT_import_get_yellow_services_out();
		$in  = MRT_import_get_yellow_services_in();

		self::assertSame( array( '101', '103' ), array_column( $out, 0 ) );
		self::assertSame( array( '100', '102' ), array_column( $in, 0 ) );
		self::assertSame( array( 'Rälsbuss' ), array_values( array_unique( array_column( array_merge( $out, $in ), 1 ) ) ) );
	}

	public function test_yellow_service_rows_match_main_route_station_count(): void {
		foreach ( array_merge( MRT_import_get_yellow_services_out(), MRT_import_get_yellow_services_in() ) as $service ) {
			self::assertCount( 14, $service[2], 'Each yellow service needs one time row per main-line station.' );
			self::assertCount( 14, $service[3], 'Each yellow service needs one stop symbol per main-line station.' );
		}
	}

	public function test_timetable_definitions_include_green_and_yellow(): void {
		$definitions = MRT_import_get_timetable_definitions();

		self::assertArrayHasKey( 'green', $definitions );
		self::assertArrayHasKey( 'yellow', $definitions );
		self::assertSame( 'GRÖN TIDTABELL 2026', $definitions['green']['title'] );
		self::assertSame( 'GUL TIDTABELL 2026', $definitions['yellow']['title'] );
		self::assertCount( 2, $definitions['yellow']['services_out'] );
		self::assertCount( 2, $definitions['yellow']['services_in'] );
	}
}
