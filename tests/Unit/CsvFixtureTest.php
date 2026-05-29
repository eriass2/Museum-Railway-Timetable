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
require_once ABSPATH . 'inc/import/csv/fixture-read.php';

final class CsvFixtureTest extends TestCase {

	/** @var array<string, array<int, array<string, string>>>|null */
	private static ?array $files = null;

	/**
	 * @return array<string, array<int, array<string, string>>>
	 */
	private function fixture_files(): array {
		if ( self::$files === null ) {
			$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
			self::assertIsArray( $package );
			self::$files = (array) ( $package['files'] ?? array() );
		}
		return self::$files;
	}

	public function test_lennakatten_fixture_is_valid(): void {
		$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
		self::assertIsArray( $package );
		$result = MRT_csv_validate_package( $package );
		self::assertTrue( $result['valid'], json_encode( $result['errors'] ) );
	}

	public function test_yellow_timetable_dates_cover_friday_codes_c_and_d(): void {
		$dates = MRT_csv_fixture_timetable_dates( 'yellow' );

		self::assertCount( 17, $dates );
		self::assertSame( '2026-05-29', $dates[0] );
		self::assertContains( '2026-08-07', $dates );
		self::assertSame( '2026-09-25', $dates[ count( $dates ) - 1 ] );
	}

	public function test_green_timetable_dates_match_anslagstidtabell_2026(): void {
		$dates = MRT_csv_fixture_timetable_dates( 'green' );

		self::assertCount( 12, $dates );
		self::assertSame( '2026-05-30', $dates[0] );
		self::assertNotContains( '2026-05-31', $dates );
		self::assertNotContains( '2026-07-01', $dates );
		self::assertNotContains( '2026-06-20', $dates );
		self::assertNotContains( '2026-09-12', $dates );
		self::assertSame( '2026-09-26', $dates[ count( $dates ) - 1 ] );
	}

	public function test_green_vard_timetable_dates_match_buss_vard_pdf(): void {
		$dates = MRT_csv_fixture_timetable_dates( 'green-vard' );

		self::assertCount( 12, $dates );
		self::assertSame( '2026-07-01', $dates[0] );
		self::assertSame( '2026-08-06', $dates[ count( $dates ) - 1 ] );
	}

	public function test_green_vard_rail_services_mirror_green(): void {
		$green = $this->fixture_services( 'green', 'uppsala-faringe' );
		$vard  = $this->fixture_services( 'green-vard', 'uppsala-faringe' );
		self::assertCount( count( $green ), $vard );
		self::assertSame(
			array_column( $green, 'service_number' ),
			array_column( $vard, 'service_number' )
		);
	}

	public function test_timetable_titles_in_fixture(): void {
		$files = $this->fixture_files();
		$titles = array_column( $files['timetables.csv'] ?? array(), 'title', 'timetable_code' );
		self::assertStringContainsString( 'GRÖN TIDTABELL 2026', $titles['green'] ?? '' );
		self::assertStringContainsString( 'ons/tors', $titles['green-vard'] ?? '' );
		self::assertSame( 'GUL TIDTABELL 2026', $titles['yellow'] ?? '' );
		self::assertStringContainsString( 'GRÖN TIDTABELL 2026', MRT_csv_fixture_timetable_title( 'green' ) );
	}

	public function test_yellow_rail_services_are_railbus(): void {
		$out = $this->fixture_services( 'yellow', 'uppsala-faringe' );
		$in  = $this->fixture_services( 'yellow', 'faringe-uppsala-ostra' );
		self::assertSame( array( '101', '103' ), array_column( $out, 'service_number' ) );
		self::assertSame( array( '100', '102' ), array_column( $in, 'service_number' ) );
		foreach ( array_merge( $out, $in ) as $row ) {
			self::assertSame( 'ralsbuss', $this->fixture_train_slug( $row['service_code'] ) );
		}
	}

	public function test_yellow_rail_services_have_fourteen_stops(): void {
		$services = array_merge(
			$this->fixture_services( 'yellow', 'uppsala-faringe' ),
			$this->fixture_services( 'yellow', 'faringe-uppsala-ostra' )
		);
		foreach ( $services as $row ) {
			self::assertSame( 14, $this->fixture_stoptime_count( $row['service_code'] ) );
		}
	}

	public function test_bus_services_have_three_stops_through_uppsala(): void {
		$bus_routes = array( 'selkna-uppsala-ostra', 'uppsala-ostra-selkna' );
		foreach ( array( 'green-buss', 'yellow-buss' ) as $tt ) {
			foreach ( $bus_routes as $route ) {
				foreach ( $this->fixture_services( $tt, $route ) as $row ) {
					self::assertSame( 3, $this->fixture_stoptime_count( $row['service_code'] ) );
				}
			}
		}
	}

	public function test_red_timetable_dates_match_anslagstidtabell_2026(): void {
		$dates = MRT_csv_fixture_timetable_dates( 'red' );
		self::assertCount( 7, $dates );
		self::assertSame( '2026-07-05', $dates[0] );
		self::assertSame( '2026-08-16', $dates[ count( $dates ) - 1 ] );
	}

	public function test_orange_timetable_dates_match_anslagstidtabell_2026(): void {
		$dates = MRT_csv_fixture_timetable_dates( 'orange' );
		self::assertCount( 6, $dates );
		self::assertSame( '2026-07-03', $dates[0] );
		self::assertSame( '2026-08-07', $dates[ count( $dates ) - 1 ] );
	}

	public function test_green_buss_dates_are_summer_connection_window(): void {
		$dates = MRT_csv_fixture_timetable_dates( 'green-buss' );
		self::assertCount( 18, $dates );
		self::assertContains( '2026-07-01', $dates );
		self::assertContains( '2026-08-15', $dates );
		self::assertNotContains( '2026-05-30', $dates );
	}

	public function test_thuns_express_highlight_is_csv_driven_per_service(): void {
		$green_out = $this->fixture_service_row( 'green-93-out' );
		$green_in  = $this->fixture_service_row( 'green-96-in' );
		$plain     = $this->fixture_service_row( 'green-71-out' );

		self::assertSame( 'Thun\'s-expressen', $green_out['highlight_label'] ?? '' );
		self::assertSame( '#fff9c4', $green_out['highlight_color'] ?? '' );
		self::assertNotSame( '', $green_out['highlight_note'] ?? '' );
		self::assertSame( 'Thun\'s-expressen', $green_in['highlight_label'] ?? '' );
		self::assertSame( '', $plain['highlight_label'] ?? '' );
	}

	/**
	 * @return array<string, string>
	 */
	private function fixture_service_row( string $service_code ): array {
		foreach ( $this->fixture_files()['services.csv'] ?? array() as $row ) {
			if ( ( $row['service_code'] ?? '' ) === $service_code ) {
				return $row;
			}
		}
		return array();
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	private function fixture_services( string $timetable_code, string $route_code ): array {
		$out = array();
		foreach ( $this->fixture_files()['services.csv'] ?? array() as $row ) {
			if ( ( $row['timetable_code'] ?? '' ) === $timetable_code && ( $row['route_code'] ?? '' ) === $route_code ) {
				$out[] = $row;
			}
		}
		return $out;
	}

	private function fixture_train_slug( string $service_code ): string {
		foreach ( $this->fixture_files()['service_train_types.csv'] ?? array() as $row ) {
			if ( ( $row['service_code'] ?? '' ) === $service_code ) {
				return (string) ( $row['train_type_slug'] ?? '' );
			}
		}
		return '';
	}

	private function fixture_stoptime_count( string $service_code ): int {
		$count = 0;
		foreach ( $this->fixture_files()['stoptimes.csv'] ?? array() as $row ) {
			if ( ( $row['service_code'] ?? '' ) === $service_code ) {
				++$count;
			}
		}
		return $count;
	}
}
