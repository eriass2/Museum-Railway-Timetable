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

	public function test_junction_bus_rows_use_continuation_train_for_merged_column(): void {
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
						$remote_id   => array( 'arrival_time' => '11:00' ),
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
					'train' => array( 'service_number' => '61', 'time_display' => '10:50' ),
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
			'501|mrt_service_number'     => 'B1',
			'9|mrt_station_bus_suffix'   => '1',
			'15|mrt_station_bus_suffix'  => '1',
		);
		$GLOBALS['mrt_test_posts'] = array(
			15 => (object) array( 'ID' => 15, 'post_title' => 'Fjällnora' ),
		);
		$info = array(
			array( 'service_number' => '71' ),
			array( 'service_number' => '61' ),
		);
		$display_columns = array(
			array(
				'primary_idx'      => 0,
				'continuation_idx' => 1,
				'split_station_id' => 8,
			),
		);

		$rows = MRT_timetable_junction_bus_rows_json(
			array(),
			$info,
			$connection,
			$branch,
			$display_columns
		);

		self::assertCount( 2, $rows );
		self::assertSame( '10.53', $rows[0]['cells'][0]['text'] );
		self::assertSame( 'B1', $rows[0]['cells'][0]['busServiceNumber'] );
	}

	public function test_junction_bus_rows_merge_matched_trains_into_two_rows(): void {
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

		self::assertCount( 2, $rows );
		self::assertSame( 'busDeparture', $rows[0]['kind'] );
		self::assertSame( 'Från Selknä*', $rows[0]['label'] );
		self::assertSame( '13.40', $rows[0]['cells'][0]['text'] );
		self::assertSame( '15.18', $rows[0]['cells'][1]['text'] );
		self::assertSame( 'busArrival', $rows[1]['kind'] );
		self::assertSame( 'Till Fjällnora*', $rows[1]['label'] );
		self::assertSame( '13.47', $rows[1]['cells'][0]['text'] );
		self::assertSame( '15.25', $rows[1]['cells'][1]['text'] );
	}

	public function test_branch_matches_junction_flow_for_outbound_and_inbound(): void {
		$junction_id = 9;
		$outbound    = array( 'stations' => array( $junction_id, 15 ) );
		$inbound     = array( 'stations' => array( 15, $junction_id ) );

		self::assertTrue( MRT_timetable_branch_matches_junction_flow( $outbound, $junction_id, 'outbound' ) );
		self::assertFalse( MRT_timetable_branch_matches_junction_flow( $inbound, $junction_id, 'outbound' ) );
		self::assertTrue( MRT_timetable_branch_matches_junction_flow( $inbound, $junction_id, 'inbound' ) );
		self::assertFalse( MRT_timetable_branch_matches_junction_flow( $outbound, $junction_id, 'inbound' ) );
	}

	public function test_junction_bus_rows_for_station_shows_inbound_branch_on_inbound_rail(): void {
		$junction_id = 9;
		$fjallnora   = 15;
		$faringe     = 1;
		$route_in    = 60;
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_number'     => 'B6',
			'62|mrt_service_number'      => '62',
			$route_in . '|mrt_route_start_station' => $faringe,
			$route_in . '|mrt_route_end_station'  => 14,
			$faringe . '|mrt_display_order'       => 14,
			'14|mrt_display_order'                => 1,
			'9|mrt_station_bus_suffix'            => '1',
			'15|mrt_station_bus_suffix'           => '1',
		);
		$GLOBALS['mrt_test_posts'] = array(
			15 => (object) array( 'ID' => 15, 'post_title' => 'Fjällnora' ),
		);
		$rail_group = array(
			'route_id' => $route_in,
			'stations' => array( $faringe, 5, $junction_id, 14 ),
			'services' => array(
				array(
					'service'    => (object) array( 'ID' => 62 ),
					'train_type' => (object) array( 'slug' => 'dieseltag' ),
					'stop_times' => array(
						$junction_id => array(
							'arrival_time'   => '13:01',
							'departure_time' => '13:04',
						),
					),
				),
			),
		);
		$paired_branches = array(
			array(
				'stations' => array( $fjallnora, $junction_id ),
				'services' => array(
					array(
						'service'    => (object) array( 'ID' => 501 ),
						'train_type' => (object) array( 'slug' => 'buss' ),
						'stop_times' => array(
							$fjallnora   => array( 'departure_time' => '12:51' ),
							$junction_id => array( 'arrival_time' => '12:58' ),
						),
					),
				),
			),
		);
		$info            = array( array( 'service_number' => '62' ) );
		$display_columns = array( array( 'primary_idx' => 0, 'continuation_idx' => null, 'split_station_id' => 0 ) );
		$rows            = MRT_timetable_junction_bus_rows_for_station(
			$info,
			$rail_group,
			$paired_branches,
			$junction_id,
			$display_columns
		);

		self::assertCount( 2, $rows );
		self::assertSame( 'busDeparture', $rows[0]['kind'] );
		self::assertStringContainsString( '12.51', (string) ( $rows[0]['cells'][0]['text'] ?? '' ) );
		self::assertSame( 'busArrival', $rows[1]['kind'] );
		self::assertStringContainsString( '12.58', (string) ( $rows[1]['cells'][0]['text'] ?? '' ) );
	}

	public function test_junction_bus_rows_for_station_skips_arrival_only_branch_on_outbound_rail(): void {
		$junction_id = 9;
		$fjallnora   = 15;
		$linnes      = 16;
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_number'     => 'B3',
			'502|mrt_service_number'     => 'B7',
			'9|mrt_station_bus_suffix'   => '1',
			'15|mrt_station_bus_suffix'  => '1',
			'16|mrt_station_bus_suffix'  => '1',
		);
		$GLOBALS['mrt_test_posts'] = array(
			15 => (object) array( 'ID' => 15, 'post_title' => 'Fjällnora' ),
			16 => (object) array( 'ID' => 16, 'post_title' => 'Linnés Hammarby' ),
		);
		$rail_group = array(
			'stations' => array( 1, $junction_id, 20 ),
			'services' => array(
				array(
					'service'    => (object) array( 'ID' => 62 ),
					'train_type' => (object) array( 'slug' => 'dieseltag' ),
					'stop_times' => array(
						$junction_id => array(
							'arrival_time'   => '13:01',
							'departure_time' => '13:01',
						),
					),
				),
			),
		);
		$paired_branches = array(
			array(
				'stations' => array( $junction_id, $fjallnora ),
				'services' => array(
					array(
						'service'    => (object) array( 'ID' => 501 ),
						'train_type' => (object) array( 'slug' => 'buss' ),
						'stop_times' => array(
							$junction_id => array( 'departure_time' => '13:40' ),
							$fjallnora   => array( 'arrival_time' => '13:47' ),
						),
					),
				),
			),
			array(
				'stations' => array( $fjallnora, $junction_id ),
				'services' => array(
					array(
						'service'    => (object) array( 'ID' => 502 ),
						'train_type' => (object) array( 'slug' => 'buss' ),
						'stop_times' => array(
							$fjallnora   => array( 'departure_time' => '14:42' ),
							$junction_id => array( 'arrival_time' => '14:49' ),
						),
					),
				),
			),
		);
		$info             = array( array( 'service_number' => '62' ) );
		$display_columns  = array( array( 'primary_idx' => 0, 'continuation_idx' => null, 'split_station_id' => 0 ) );
		$rows             = MRT_timetable_junction_bus_rows_for_station(
			$info,
			$rail_group,
			$paired_branches,
			$junction_id,
			$display_columns
		);

		self::assertCount( 2, $rows );
		self::assertSame( '13.40', $rows[0]['cells'][0]['text'] );
		self::assertSame( 'B3', $rows[0]['cells'][0]['busServiceNumber'] );
	}

	public function test_junction_bus_rows_for_station_keeps_one_branch_when_wait_ties(): void {
		$junction_id = 9;
		$fjallnora   = 15;
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_number'    => 'B1',
			'502|mrt_service_number'    => 'B2',
			'9|mrt_station_bus_suffix'  => '1',
			'15|mrt_station_bus_suffix' => '1',
		);
		$GLOBALS['mrt_test_posts'] = array(
			15 => (object) array( 'ID' => 15, 'post_title' => 'Fjällnora' ),
		);
		$rail_group = array(
			'stations' => array( 1, $junction_id, 20 ),
			'services' => array(
				array(
					'service'    => (object) array( 'ID' => 60 ),
					'train_type' => (object) array( 'slug' => 'dieseltag' ),
					'stop_times' => array(
						$junction_id => array(
							'arrival_time'   => '10:14',
							'departure_time' => '10:14',
						),
					),
				),
			),
		);
		$paired_branches = array(
			array(
				'stations' => array( $junction_id, $fjallnora ),
				'services' => array(
					array(
						'service'    => (object) array( 'ID' => 501 ),
						'train_type' => (object) array( 'slug' => 'buss' ),
						'stop_times' => array(
							$junction_id => array( 'departure_time' => '10:53' ),
							$fjallnora   => array( 'arrival_time' => '11:00' ),
						),
					),
				),
			),
			array(
				'stations' => array( $junction_id, $fjallnora ),
				'services' => array(
					array(
						'service'    => (object) array( 'ID' => 502 ),
						'train_type' => (object) array( 'slug' => 'buss' ),
						'stop_times' => array(
							$junction_id => array( 'departure_time' => '10:53' ),
							$fjallnora   => array( 'arrival_time' => '11:00' ),
						),
					),
				),
			),
		);
		$info            = array( array( 'service_number' => '60' ) );
		$display_columns = array( array( 'primary_idx' => 0, 'continuation_idx' => null, 'split_station_id' => 0 ) );
		$rows            = MRT_timetable_junction_bus_rows_for_station(
			$info,
			$rail_group,
			$paired_branches,
			$junction_id,
			$display_columns
		);

		self::assertCount( 2, $rows );
		self::assertContains( $rows[0]['cells'][0]['busServiceNumber'], array( 'B1', 'B2' ) );
		self::assertSame( 'Till Fjällnora*', $rows[1]['label'] );
	}

	public function test_junction_bus_departure_hides_pickup_suffix_on_from_row(): void {
		$stop = array_merge(
			array(
				'departure_time'   => '10:53',
				'approximate_time' => true,
			),
			MRT_test_stop_modes_pickup_only()
		);

		$display = MRT_timetable_bus_stop_display_time( $stop, true );

		self::assertSame( 'Ca 10.53', $display );
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
