<?php
/**
 * Line CSV pilot (LINES_REFACTOR Fas 1).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_TAXONOMY_TRAIN_TYPE' ) ) {
	define( 'MRT_TAXONOMY_TRAIN_TYPE', 'mrt_train_type' );
}

require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/loader.php';

final class CsvLinesTest extends TestCase {

	protected function tearDown(): void {
		delete_option( MRT_line_registry_option_key() );
		parent::tearDown();
	}

	public function test_lennakatten_fixture_declares_main_line_with_fourteen_stations(): void {
		$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
		self::assertIsArray( $package );
		$files = (array) ( $package['files'] ?? array() );
		self::assertArrayHasKey( 'lines.csv', $files );
		self::assertArrayHasKey( 'line_stations.csv', $files );

		$main_rows = array_values(
			array_filter(
				$files['line_stations.csv'],
				static fn ( array $row ): bool => ( $row['line_code'] ?? '' ) === 'main'
			)
		);
		self::assertCount( 14, $main_rows );
		self::assertSame( 'faringe', $main_rows[0]['station_code'] ?? '' );
		self::assertSame( 'uppsala-ostra', $main_rows[13]['station_code'] ?? '' );
	}

	public function test_rail_services_on_main_routes_declare_line_code_main(): void {
		$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
		$files   = (array) ( $package['files'] ?? array() );
		$branch  = MRT_csv_routes_branch_from_file( $files );
		$matched = 0;
		foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
			$route = MRT_csv_resolve_service_route_code( $row, $files, $branch );
			if ( ! in_array( $route, MRT_csv_main_corridor_route_codes(), true ) ) {
				continue;
			}
			++$matched;
			self::assertSame(
				'main',
				(string) ( $row['line_code'] ?? '' ),
				(string) ( $row['service_code'] ?? '' )
			);
		}
		self::assertGreaterThan( 0, $matched );
	}

	public function test_lennakatten_bus_services_declare_transfer_branch_line_codes(): void {
		$expected = array(
			'green-b1-bus-out' => 'fjallnora',
			'green-b6-bus-in'  => 'fjallnora',
			'red-b9-bus-out'   => 'linnes-marielund',
			'red-b12-bus-in'   => 'linnes-marielund',
		);
		$files = (array) ( MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' )['files'] ?? array() );
		$by_code = array();
		foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
			$by_code[ (string) ( $row['service_code'] ?? '' ) ] = (string) ( $row['line_code'] ?? '' );
		}
		foreach ( $expected as $service_code => $line_code ) {
			self::assertSame( $line_code, $by_code[ $service_code ] ?? '', $service_code );
		}
	}

	public function test_import_lines_persists_branch_junctions(): void {
		$files = array(
			'lines.csv' => array(
				array( 'line_code' => 'fjallnora', 'title' => 'Selkné – Fjällnora', 'kind' => 'branch' ),
			),
			'line_stations.csv' => array(
				array( 'line_code' => 'fjallnora', 'sequence' => '1', 'station_code' => 'selkna' ),
				array( 'line_code' => 'fjallnora', 'sequence' => '2', 'station_code' => 'fjallnora' ),
			),
			'branch_junctions.csv' => array(
				array(
					'line_code'               => 'fjallnora',
					'junction_station_code'   => 'selkna',
					'requires_transfer'       => '1',
				),
			),
		);

		MRT_csv_import_lines( $files );
		$registry = MRT_get_line_registry();
		self::assertSame( 'selkna', $registry['fjallnora']['junction_station_code'] ?? '' );
		self::assertTrue( (bool) ( $registry['fjallnora']['requires_transfer'] ?? false ) );
	}

	public function test_import_lines_persists_registry(): void {
		$files = array(
			'lines.csv' => array(
				array(
					'line_code' => 'main',
					'title'     => 'Faringe – Uppsala Östra',
					'kind'      => 'main',
				),
			),
			'line_stations.csv' => array(
				array( 'line_code' => 'main', 'sequence' => '1', 'station_code' => 'faringe' ),
				array( 'line_code' => 'main', 'sequence' => '2', 'station_code' => 'uppsala-ostra' ),
			),
		);

		self::assertSame( 1, MRT_csv_import_lines( $files ) );
		$registry = MRT_get_line_registry();
		self::assertArrayHasKey( 'main', $registry );
		self::assertSame( 'main', $registry['main']['kind'] ?? '' );
		self::assertSame( array( 'faringe', 'uppsala-ostra' ), $registry['main']['station_codes'] ?? array() );
	}

	public function test_b14_declares_linnes_uppsala_pattern_without_overview_csv_meta(): void {
		$files = (array) ( MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' )['files'] ?? array() );
		$b14   = null;
		foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
			if ( ( $row['service_code'] ?? '' ) === 'red-b14-bus-in' ) {
				$b14 = $row;
				break;
			}
		}
		self::assertIsArray( $b14 );
		self::assertSame( 'linnes-uppsala', (string) ( $b14['line_code'] ?? '' ) );
		self::assertSame( '', (string) ( $b14['route_code'] ?? '' ) );
		self::assertSame( '', (string) ( $b14['overview_column'] ?? '' ) );
		self::assertSame( '', (string) ( $b14['overview_pass_from_station'] ?? '' ) );
	}

	public function test_resolve_route_from_line_for_main_outbound_and_inbound(): void {
		$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
		$files   = (array) ( $package['files'] ?? array() );
		$branch  = MRT_csv_routes_branch_from_file( $files );
		$out_row = array(
			'service_code'     => 'green-71-out',
			'line_code'        => 'main',
			'end_station_code' => 'marielund',
		);
		$in_row = array(
			'service_code'     => 'green-62-in',
			'line_code'        => 'main',
			'end_station_code' => 'marielund',
		);
		self::assertSame( 'uppsala-faringe', MRT_csv_resolve_service_route_code( $out_row, $files, $branch ) );
		self::assertSame( 'faringe-uppsala-ostra', MRT_csv_resolve_service_route_code( $in_row, $files, $branch ) );
	}

	public function test_import_lines_persists_pattern_corridor_after_station(): void {
		$files = array(
			'lines.csv' => array(
				array(
					'line_code'                      => 'linnes-uppsala',
					'title'                          => 'Linnés Hammarby – Uppsala Östra',
					'kind'                           => 'pattern',
					'overview_corridor_after_station' => 'marielund',
				),
			),
			'line_stations.csv' => array(
				array( 'line_code' => 'linnes-uppsala', 'sequence' => '1', 'station_code' => 'linnes-hammarby' ),
				array( 'line_code' => 'linnes-uppsala', 'sequence' => '2', 'station_code' => 'uppsala-ostra' ),
			),
		);

		MRT_csv_import_lines( $files );
		$registry = MRT_get_line_registry();
		self::assertSame( 'pattern', $registry['linnes-uppsala']['kind'] ?? '' );
		self::assertSame( 'marielund', $registry['linnes-uppsala']['overview_corridor_after_station_code'] ?? '' );
		self::assertFalse( (bool) ( $registry['linnes-uppsala']['requires_transfer'] ?? true ) );
	}

	public function test_import_service_sets_overview_column_from_pattern_line(): void {
		$this->boot_posts();
		MRT_set_line_registry(
			array(
				'linnes-uppsala' => array(
					'title'         => 'Linnés Hammarby – Uppsala Östra',
					'kind'          => 'pattern',
					'station_codes' => array( 'linnes-hammarby', 'uppsala-ostra' ),
				),
			)
		);
		$maps  = array(
			'station'   => array( 'linnes-hammarby' => 8, 'uppsala-ostra' => 14 ),
			'route'     => array( 'linnes-uppsala' => 70 ),
			'timetable' => array( 'red' => 20 ),
			'service'   => array(),
		);
		$files = array(
			'routes.csv' => array(
				array(
					'route_code'         => 'linnes-uppsala',
					'title'              => 'Linnés Hammarby – Uppsala Östra',
					'branch_code'        => '',
				),
			),
			'services.csv' => array(
				array(
					'service_code'     => 'red-b14-bus-in',
					'timetable_code'   => 'red',
					'route_code'       => 'linnes-uppsala',
					'line_code'        => 'linnes-uppsala',
					'service_number'   => 'B14',
					'end_station_code' => 'uppsala-ostra',
				),
			),
			'service_train_types.csv' => array(
				array( 'service_code' => 'red-b14-bus-in', 'train_type_slug' => 'red-buss' ),
			),
		);
		$term                      = new WP_Term();
		$term->term_id             = 6002;
		$term->slug                = 'red-buss';
		$GLOBALS['mrt_test_terms'] = array( 6002 => $term );

		MRT_csv_import_services( $files, $maps );
		$service_id = $maps['service']['red-b14-bus-in'] ?? 0;
		self::assertGreaterThan( 0, $service_id );
		self::assertTrue( MRT_service_has_overview_column( (int) $service_id ) );
		self::assertSame( 0, MRT_service_overview_pass_from_station_id( (int) $service_id ) );
	}

	public function test_import_service_sets_line_code_meta_for_main_train(): void {
		$this->boot_posts();
		$maps  = array(
			'station'   => array( 'faringe' => 1, 'uppsala-ostra' => 14 ),
			'route'     => array( 'uppsala-faringe' => 50 ),
			'timetable' => array( 'green' => 10 ),
			'service'   => array(),
		);
		$files = array(
			'routes.csv' => array(
				array(
					'route_code'         => 'uppsala-faringe',
					'title'              => 'Uppsala – Faringe',
					'branch_code'        => 'main',
				),
			),
			'services.csv' => array(
				array(
					'service_code'     => 'green-71-out',
					'timetable_code'   => 'green',
					'route_code'       => 'uppsala-faringe',
					'line_code'        => 'main',
					'service_number'   => '71',
					'end_station_code' => 'faringe',
				),
			),
			'service_train_types.csv' => array(
				array( 'service_code' => 'green-71-out', 'train_type_slug' => 'dieseltag' ),
			),
		);
		$term            = new WP_Term();
		$term->term_id   = 6001;
		$term->slug      = 'dieseltag';
		$GLOBALS['mrt_test_terms'] = array( 6001 => $term );

		MRT_csv_import_services( $files, $maps );
		$service_id = $maps['service']['green-71-out'] ?? 0;
		self::assertGreaterThan( 0, $service_id );
		self::assertSame( 'main', MRT_get_service_line_code( (int) $service_id ) );
	}

	private function boot_posts(): void {
		if ( ! isset( $GLOBALS['mrt_test_next_post_id'] ) ) {
			$GLOBALS['mrt_test_next_post_id'] = 1000;
		}
		$GLOBALS['mrt_test_posts']     = array();
		$GLOBALS['mrt_test_post_meta']   = array();
		$GLOBALS['mrt_test_get_posts']   = array();
	}
}
