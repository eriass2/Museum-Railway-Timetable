<?php
/**
 * Standalone bus columns in timetable overview rail grid.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/loader.php';

final class OverviewStandaloneBusTest extends TestCase {

	/** @var array<string, array<int, array<string, string>>>|null */
	private static ?array $fixture_files = null;

	private const LINNES_ID   = 16;
	private const MARIELUND_ID = 8;
	private const UPPSALA_ID  = 14;

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['mrt_test_posts'] = array(
			self::LINNES_ID => $this->station_post( self::LINNES_ID, 'Linnés Hammarby' ),
			self::MARIELUND_ID => $this->station_post( self::MARIELUND_ID, 'Marielund' ),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			self::LINNES_ID . '|mrt_station_bus_suffix' => '1',
		);
	}

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_posts'], $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
	}

	public function test_red_95_fixture_ends_at_marielund(): void {
		$row = null;
		foreach ( $this->fixture_stops_for_service( 'red-95-out' ) as $stop ) {
			if ( ( $stop['station_code'] ?? '' ) === 'marielund' ) {
				$row = $stop;
			}
		}
		self::assertIsArray( $row );
		self::assertSame( '13:57', $row['arrival_time'] ?? '' );
		self::assertSame( '', $row['departure_time'] ?? '' );
		self::assertCount( 7, $this->fixture_stops_for_service( 'red-95-out' ) );
	}

	public function test_standalone_bus_cell_shows_pipe_after_pass_from_station(): void {
		$service_data = $this->b14_service_data();
		$info         = array(
			'standalone_overview_column'    => true,
			'overview_pass_from_station_id' => self::MARIELUND_ID,
		);
		$station_posts = array(
			$this->station_post( 1, 'Faringe' ),
			$this->station_post( self::MARIELUND_ID, 'Marielund' ),
			$this->station_post( 10, 'Gunsta' ),
			$this->station_post( self::UPPSALA_ID, 'Uppsala Östra' ),
		);

		self::assertSame(
			'—',
			MRT_timetable_standalone_bus_cell_text(
				$service_data,
				$info,
				1,
				'station',
				$station_posts,
				false,
				false
			)
		);
		self::assertSame(
			'—',
			MRT_timetable_standalone_bus_cell_text(
				$service_data,
				$info,
				self::MARIELUND_ID,
				'station',
				$station_posts,
				false,
				false
			)
		);
		self::assertSame(
			'|',
			MRT_timetable_standalone_bus_cell_text(
				$service_data,
				$info,
				10,
				'station',
				$station_posts,
				false,
				false
			)
		);
	}

	public function test_standalone_bus_junction_rows_show_boarding_and_corridor_entry(): void {
		$service_data = $this->b14_service_data();
		$info         = array(
			'standalone_overview_column'    => true,
			'overview_pass_from_station_id' => self::MARIELUND_ID,
		);

		self::assertStringContainsString(
			'16.20',
			MRT_timetable_standalone_bus_cell_text(
				$service_data,
				$info,
				0,
				'busDeparture',
				array(),
				false,
				false,
				'Från Linnés Hammarby*'
			)
		);
		self::assertSame(
			'—',
			MRT_timetable_standalone_bus_cell_text(
				$service_data,
				$info,
				0,
				'busArrival',
				array(),
				false,
				false,
				'Till Marielund*'
			)
		);
	}

	public function test_standalone_bus_corridor_starts_on_fran_marielund_departure(): void {
		$service_data = $this->b14_service_data();
		$info         = array(
			'standalone_overview_column'    => true,
			'overview_pass_from_station_id' => self::MARIELUND_ID,
		);
		$station_posts = array(
			$this->station_post( 1, 'Faringe' ),
			$this->station_post( self::MARIELUND_ID, 'Marielund' ),
			$this->station_post( 10, 'Gunsta' ),
			$this->station_post( self::UPPSALA_ID, 'Uppsala Östra' ),
		);

		self::assertSame(
			'|',
			MRT_timetable_standalone_bus_cell_text(
				$service_data,
				$info,
				self::MARIELUND_ID,
				'departure',
				$station_posts,
				true,
				false,
				'Från Marielund'
			)
		);
		self::assertSame(
			'|',
			MRT_timetable_standalone_bus_cell_text(
				$service_data,
				$info,
				10,
				'station',
				$station_posts,
				false,
				false
			)
		);
	}

	public function test_standalone_bus_inserts_departure_row_when_no_junction_bus_departure_exists(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			self::LINNES_ID . '|mrt_station_bus_suffix' => '1',
		);
		$service_data = $this->b14_service_data();
		$info         = array(
			'standalone_overview_column'        => true,
			'overview_pass_from_station_id'   => self::MARIELUND_ID,
		);
		$rows = array(
			array(
				'kind'       => 'arrival',
				'label'      => 'Till Marielund',
				'stationId'  => self::MARIELUND_ID,
				'cells'      => array( array( 'text' => '10.20' ), array( 'text' => '—' ) ),
			),
		);
		$display_columns = array(
			array( 'primary_idx' => 0, 'continuation_idx' => null, 'split_station_id' => 0 ),
			array( 'primary_idx' => 1, 'continuation_idx' => null, 'split_station_id' => 0 ),
		);
		$view = array(
			'service_info'  => array( array(), $info ),
			'services_list' => array( array(), $service_data ),
			'station_posts' => array(
				$this->station_post( self::MARIELUND_ID, 'Marielund' ),
			),
		);

		$patched = MRT_timetable_patch_standalone_bus_junction_rows( $rows, $display_columns, $view );

		self::assertSame( 'busDeparture', $patched[0]['kind'] ?? '' );
		self::assertStringContainsString( '16.20', (string) ( $patched[0]['cells'][1]['text'] ?? '' ) );
		self::assertSame( 'arrival', $patched[1]['kind'] ?? '' );
	}

	public function test_standalone_bus_boarding_fallback_fills_linnes_bus_departure_row(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			self::LINNES_ID . '|mrt_station_bus_suffix' => '1',
		);
		$service_data = $this->b14_service_data();
		$info         = array( 'standalone_overview_column' => true );
		$rows         = array(
			array(
				'kind'  => 'busDeparture',
				'label' => 'Från Linnés Hammarby*',
				'cells' => array( array( 'text' => '—' ) ),
			),
		);
		$display_columns = array(
			array( 'primary_idx' => 0, 'continuation_idx' => null, 'split_station_id' => 0 ),
		);
		$view = array(
			'service_info'  => array( $info ),
			'services_list' => array( $service_data ),
			'station_posts' => array(),
		);

		$patched = MRT_timetable_patch_standalone_bus_junction_rows( $rows, $display_columns, $view );

		self::assertStringContainsString( '16.20', (string) ( $patched[0]['cells'][0]['text'] ?? '' ) );
	}

	public function test_standalone_bus_only_on_grid_ending_at_alight_station(): void {
		$service_data = $this->b14_service_data();
		$to_uppsala   = array( 'stations' => array( 1, self::MARIELUND_ID, self::UPPSALA_ID ) );
		$to_faringe   = array( 'stations' => array( self::UPPSALA_ID, self::MARIELUND_ID, 1 ) );

		self::assertTrue( MRT_timetable_standalone_bus_matches_rail_group( $service_data, $to_uppsala ) );
		self::assertFalse( MRT_timetable_standalone_bus_matches_rail_group( $service_data, $to_faringe ) );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function b14_service_data(): array {
		return array(
			'stop_times' => array(
				self::LINNES_ID => array(
					'departure_time'   => '16:20',
					'arrival_time'     => '',
					'avg_pickup_mode'  => 'on_request',
					'avg_dropoff_mode' => 'none',
				),
				self::UPPSALA_ID => array(
					'arrival_time'     => '16:45',
					'departure_time'   => '',
					'ank_dropoff_mode' => 'scheduled',
					'avg_dropoff_mode' => 'none',
				),
			),
		);
	}

	private function station_post( int $id, string $title ): WP_Post {
		$post             = new WP_Post();
		$post->ID         = $id;
		$post->post_title = $title;
		return $post;
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	private function fixture_stops_for_service( string $service_code ): array {
		$stops = array();
		foreach ( $this->fixture_files()['stoptimes.csv'] ?? array() as $row ) {
			if ( ( $row['service_code'] ?? '' ) === $service_code ) {
				$stops[] = $row;
			}
		}
		usort(
			$stops,
			static fn ( array $a, array $b ): int => (int) ( $a['sequence'] ?? 0 ) <=> (int) ( $b['sequence'] ?? 0 )
		);
		return $stops;
	}

	/**
	 * @return array<string, array<int, array<string, string>>>
	 */
	private function fixture_files(): array {
		if ( self::$fixture_files === null ) {
			$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
			self::assertIsArray( $package );
			self::$fixture_files = (array) ( $package['files'] ?? array() );
		}
		return self::$fixture_files;
	}
}
