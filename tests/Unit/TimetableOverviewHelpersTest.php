<?php
/**
 * Timetable overview JSON helpers (branch shuttles, rail–bus junction).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TimetableOverviewHelpersTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'] );
		parent::tearDown();
	}

	public function test_branch_shuttle_accepts_two_and_three_stop_bus_groups(): void {
		$two_stop = array(
			'stations' => array( 1, 2 ),
			'services' => array( $this->bus_service() ),
		);
		$three_stop = array(
			'stations' => array( 1, 2, 3 ),
			'services' => array( $this->bus_service() ),
		);

		self::assertTrue( MRT_timetable_group_is_branch_shuttle( $two_stop ) );
		self::assertTrue( MRT_timetable_group_is_branch_shuttle( $three_stop ) );
	}

	public function test_branch_shuttle_rejects_rail_mixed_or_wrong_stop_count(): void {
		$rail = array(
			'stations' => array( 1, 2 ),
			'services' => array(
				array(
					'train_type' => (object) array( 'slug' => 'anglok' ),
				),
			),
		);
		$four_stop = array(
			'stations' => array( 1, 2, 3, 4 ),
			'services' => array( $this->bus_service() ),
		);

		self::assertFalse( MRT_timetable_group_is_branch_shuttle( $rail ) );
		self::assertFalse( MRT_timetable_group_is_branch_shuttle( $four_stop ) );
	}

	public function test_junction_prefers_bus_suffix_station_when_multiple_shared(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'9|mrt_station_bus_suffix'  => '1',
			'1|mrt_station_bus_suffix' => '0',
		);

		$rail_group = array( 'stations' => array( 1, 9, 15 ) );
		$bus_group  = array( 'stations' => array( 9, 15, 1 ) );

		self::assertSame( 9, MRT_timetable_branch_junction_station_id( $rail_group, $bus_group ) );
	}

	public function test_junction_falls_back_to_first_shared_station(): void {
		$rail_group = array( 'stations' => array( 10, 20 ) );
		$bus_group  = array( 'stations' => array( 20, 30 ) );

		self::assertSame( 20, MRT_timetable_branch_junction_station_id( $rail_group, $bus_group ) );
	}

	public function test_from_and_to_row_display_stop_times(): void {
		$stop = array_merge(
			array(
				'arrival_time'     => '10:15',
				'departure_time'   => '10:20',
				'approximate_time' => 1,
			),
			MRT_test_stop_modes_both_scheduled()
		);

		$from = MRT_get_from_row_display_stop_time( $stop );
		self::assertIsArray( $from );
		self::assertSame( '', $from['arrival_time'] );
		self::assertSame( '10.20', $from['departure_time'] );
		self::assertSame( 1, $from['approximate_time'] );

		$to = MRT_get_to_row_display_stop_time( $stop );
		self::assertIsArray( $to );
		self::assertSame( '10.15', $to['arrival_time'] );
		self::assertSame( '', $to['departure_time'] );
		self::assertSame( 1, $to['approximate_time'] );
	}

	public function test_print_key_base_rows_include_standard_symbols(): void {
		$rows    = MRT_timetable_print_key_base_rows();
		$symbols = array_column( $rows, 'symbol' );

		self::assertSame( array( 'X', 'P', '*' ), $symbols );
		self::assertCount( 3, $rows );
		foreach ( $rows as $row ) {
			self::assertNotSame( '', $row['text'] );
		}
	}

	public function test_junction_bus_rows_use_inline_departure_and_arrival_times(): void {
		$junction_id = 9;
		$remote_id   = 15;
		$branch      = array(
			'stations' => array( $junction_id, $remote_id ),
			'services' => array(
				array(
					'service'    => (object) array( 'ID' => 501 ),
					'stop_times' => array(
						$junction_id => array(
							'arrival_time'   => '10:53',
							'departure_time' => '10:53',
						),
						$remote_id   => array(
							'arrival_time'   => '11:00',
							'departure_time' => '11:00',
						),
					),
				),
			),
		);
		$connection = array(
			'junction_id'    => $junction_id,
			'junction_label' => 'Selknä*',
			'direction'      => 'outbound',
			'train_to_bus'   => array(
				array(
					'train' => array( 'service_number' => '71', 'time_display' => '10:50' ),
					'buses' => array(
						array(
							'service_number' => 'B1',
							'time_display'   => '10:53',
							'destination'    => 'Fjällnora*',
						),
					),
				),
			),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_number' => 'B1',
			'9|mrt_station_bus_suffix' => '1',
			'15|mrt_station_bus_suffix' => '1',
		);
		$GLOBALS['mrt_test_posts'] = array(
			15 => (object) array(
				'ID'         => 15,
				'post_title' => 'Fjällnora',
			),
		);

		$services = array( array( 'service' => (object) array( 'ID' => 71 ) ) );
		$info     = array( array( 'service_number' => '71' ) );

		self::assertSame( 15, MRT_timetable_bus_remote_station_id( $branch, $junction_id ) );
		self::assertSame( 'Fjällnora*', MRT_timetable_bus_remote_station_label( $branch, $junction_id ) );

		$rows = MRT_timetable_junction_bus_rows_json( $services, $info, $connection, $branch );

		self::assertCount( 2, $rows );
		self::assertSame( 'busDeparture', $rows[0]['kind'] );
		self::assertSame( 'Från Selknä*', $rows[0]['label'] );
		self::assertSame( '10.53', $rows[0]['cells'][0]['text'] );
		self::assertSame( 'B1', $rows[0]['cells'][0]['busServiceNumber'] );
		self::assertSame( 'busArrival', $rows[1]['kind'] );
		self::assertSame( 'Till Fjällnora*', $rows[1]['label'] );
		self::assertSame( '11.00', $rows[1]['cells'][0]['text'] );
	}

	public function test_junction_bus_rows_use_one_pair_per_matched_train(): void {
		$junction_id = 9;
		$remote_id   = 15;
		$branch      = array(
			'stations' => array( $junction_id, $remote_id ),
			'services' => array(
				array(
					'service'    => (object) array( 'ID' => 501 ),
					'stop_times' => array(
						$junction_id => array( 'departure_time' => '13:40' ),
						$remote_id   => array( 'arrival_time' => '13:47' ),
					),
				),
				array(
					'service'    => (object) array( 'ID' => 502 ),
					'stop_times' => array(
						$junction_id => array( 'departure_time' => '15:18' ),
						$remote_id   => array( 'arrival_time' => '15:25' ),
					),
				),
			),
		);
		$connection = array(
			'junction_id'    => $junction_id,
			'junction_label' => 'Selknä*',
			'direction'      => 'outbound',
			'train_to_bus'   => array(
				array(
					'train' => array( 'service_number' => '62' ),
					'buses' => array( array( 'service_number' => 'B3' ) ),
				),
				array(
					'train' => array( 'service_number' => '96' ),
					'buses' => array( array( 'service_number' => 'B4' ) ),
				),
			),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_number' => 'B3',
			'502|mrt_service_number' => 'B4',
			'9|mrt_station_bus_suffix'  => '1',
			'15|mrt_station_bus_suffix' => '1',
		);
		$GLOBALS['mrt_test_posts'] = array(
			15 => (object) array(
				'ID'         => 15,
				'post_title' => 'Fjällnora',
			),
		);
		$info = array(
			array( 'service_number' => '62' ),
			array( 'service_number' => '96' ),
		);

		$rows = MRT_timetable_junction_bus_rows_json( array(), $info, $connection, $branch );

		self::assertCount( 4, $rows );
		self::assertSame( '13.40', $rows[0]['cells'][0]['text'] );
		self::assertSame( '—', $rows[0]['cells'][1]['text'] );
		self::assertSame( '13.47', $rows[1]['cells'][0]['text'] );
		self::assertSame( '15.18', $rows[2]['cells'][1]['text'] );
		self::assertSame( '—', $rows[2]['cells'][0]['text'] );
		self::assertSame( '15.25', $rows[3]['cells'][1]['text'] );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function bus_service(): array {
		return array(
			'train_type' => (object) array( 'slug' => 'buss' ),
		);
	}
}
