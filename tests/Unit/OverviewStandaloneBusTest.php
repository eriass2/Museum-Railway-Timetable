<?php
/**
 * Standalone bus columns in timetable overview rail grid.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OverviewStandaloneBusTest extends TestCase {

	private const LINNES_ID   = 16;
	private const MARIELUND_ID = 8;
	private const UPPSALA_ID  = 14;

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['mrt_test_posts'] = array(
			self::LINNES_ID => $this->station_post( self::LINNES_ID, 'Linnés Hammarby' ),
			self::MARIELUND_ID => $this->station_post( self::MARIELUND_ID, 'Marielund' ),
		);
	}

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_posts'], $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
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
			'|',
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

	public function test_standalone_bus_only_matches_inbound_rail_group(): void {
		$service_data = $this->b14_service_data();
		$inbound      = array(
			'stations' => array( 1, self::MARIELUND_ID, self::UPPSALA_ID ),
			'services' => array(
				array( 'service' => (object) array( 'ID' => 71 ) ),
			),
		);
		$outbound     = array( 'stations' => array( self::UPPSALA_ID, self::MARIELUND_ID, 1 ) );

		$GLOBALS['mrt_test_post_meta'] = array(
			'50|mrt_route_end_station' => '1',
			'71|mrt_service_route_id'  => '50',
		);
		$GLOBALS['mrt_test_posts'][1] = $this->station_post( 1, 'Faringe' );

		self::assertTrue( MRT_timetable_standalone_bus_matches_rail_group( $service_data, $inbound ) );
		self::assertFalse( MRT_timetable_standalone_bus_matches_rail_group( $service_data, $outbound ) );
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
}
