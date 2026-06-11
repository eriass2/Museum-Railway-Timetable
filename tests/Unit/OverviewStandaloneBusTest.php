<?php
/**
 * Standalone bus columns in timetable overview rail grid.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OverviewStandaloneBusTest extends TestCase {

	public function test_standalone_bus_cell_shows_pipe_between_pass_from_and_alight(): void {
		$service_data = array(
			'stop_times' => array(
				16 => array(
					'departure_time'   => '16:20',
					'arrival_time'     => '',
					'avg_pickup_mode'  => 'on_request',
					'avg_dropoff_mode' => 'none',
				),
				14 => array(
					'arrival_time'     => '16:45',
					'departure_time'   => '',
					'ank_dropoff_mode' => 'scheduled',
					'avg_dropoff_mode' => 'none',
				),
			),
		);
		$info = array(
			'standalone_overview_column'    => true,
			'overview_pass_from_station_id' => 8,
		);
		$station_posts = array(
			$this->station_post( 1, 'Faringe' ),
			$this->station_post( 8, 'Marielund' ),
			$this->station_post( 10, 'Gunsta' ),
			$this->station_post( 14, 'Uppsala Östra' ),
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
			'|',
			MRT_timetable_standalone_bus_cell_text(
				$service_data,
				$info,
				8,
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

	public function test_standalone_bus_bus_departure_row_shows_boarding_time(): void {
		$service_data = array(
			'stop_times' => array(
				16 => array(
					'departure_time'   => '16:20',
					'arrival_time'     => '',
					'avg_pickup_mode'  => 'on_request',
					'avg_dropoff_mode' => 'none',
					'approximate_time' => 1,
				),
			),
		);
		$info = array( 'standalone_overview_column' => true );

		self::assertStringContainsString(
			'16.20',
			MRT_timetable_standalone_bus_cell_text(
				$service_data,
				$info,
				0,
				'busDeparture',
				array(),
				true,
				false
			)
		);
	}

	private function station_post( int $id, string $title ): WP_Post {
		$post = new WP_Post();
		$post->ID = $id;
		$post->post_title = $title;
		return $post;
	}
}
