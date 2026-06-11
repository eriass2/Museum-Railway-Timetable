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
		foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
			$route = (string) ( $row['route_code'] ?? '' );
			if ( ! in_array( $route, MRT_csv_main_corridor_route_codes(), true ) ) {
				continue;
			}
			self::assertSame(
				'main',
				(string) ( $row['line_code'] ?? '' ),
				(string) ( $row['service_code'] ?? '' )
			);
		}
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
