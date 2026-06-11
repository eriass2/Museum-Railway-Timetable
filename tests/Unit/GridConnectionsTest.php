<?php
/**
 * Rail–bus grid connection helpers (inc/domain/timetable/view/grid/grid-connections.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GridConnectionsTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'] );
		parent::tearDown();
	}

	public function test_rail_and_bus_service_filters(): void {
		$rail = (object) array( 'slug' => 'angtag' );
		$bus  = (object) array( 'slug' => 'buss' );
		$group = array(
			'services' => array(
				array( 'train_type' => $rail ),
				array( 'train_type' => $bus ),
			),
		);

		self::assertCount( 1, MRT_rail_services_from_group( $group ) );
		self::assertCount( 1, MRT_bus_services_from_group( $group ) );
	}

	public function test_rail_grid_direction_inbound_from_route_end_station(): void {
		$GLOBALS['mrt_test_posts'] = array(
			1 => new WP_Post( (object) array( 'ID' => 1, 'post_title' => 'Faringe' ) ),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'50|mrt_route_end_station' => '1',
			'71|mrt_service_route_id'  => '50',
		);
		$inbound_group = array(
			'stations' => array( 1, 2 ),
			'services' => array(
				array( 'service' => (object) array( 'ID' => 71 ) ),
			),
		);

		self::assertSame( 'inbound', MRT_timetable_rail_grid_direction( $inbound_group ) );
		self::assertSame(
			'outbound',
			MRT_timetable_rail_grid_direction( array( 'stations' => array( 2, 3 ) ) )
		);
	}

	public function test_train_connection_anchor_time_prefers_arrival_when_both_set(): void {
		$stop = array(
			'arrival_time'   => '10:15',
			'departure_time' => '10:20',
		);

		self::assertSame( '10:15', MRT_train_connection_anchor_time( $stop ) );
	}

	public function test_buses_for_train_at_junction_matches_outbound_transfer(): void {
		$junction_id = 9;
		$train_stop  = array(
			'arrival_time'   => '10:00',
			'departure_time' => '10:05',
		);
		$bus_services = array(
			array(
				'service'    => (object) array( 'ID' => 501 ),
				'stop_times' => array(
					$junction_id => array_merge(
						array(
							'departure_time' => '10:15',
							'arrival_time'   => '',
						),
						MRT_test_stop_modes_both_scheduled()
					),
				),
			),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_number' => 'B1',
		);

		$buses = MRT_buses_for_train_at_junction( $train_stop, 'outbound', $junction_id, $bus_services );

		self::assertCount( 1, $buses );
		self::assertSame( 'B1', $buses[0]['service_number'] );
	}

	public function test_build_rail_bus_connection_data_links_train_and_bus(): void {
		$GLOBALS['mrt_test_posts'] = array(
			9  => new WP_Post( (object) array( 'ID' => 9, 'post_title' => 'Selknä' ) ),
			15 => new WP_Post( (object) array( 'ID' => 15, 'post_title' => 'Fjällnora' ) ),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'9|mrt_station_bus_suffix'  => '1',
			'71|mrt_service_number'     => '71',
			'501|mrt_service_number'    => 'B1',
			'501|mrt_service_route_id'  => 50,
			'501|mrt_service_end_station_id' => 15,
		);

		$rail_group = array(
			'stations' => array( 1, 9, 15, 20 ),
			'services' => array(
				array(
					'service'    => (object) array( 'ID' => 71 ),
					'train_type' => (object) array( 'slug' => 'angtag' ),
					'stop_times' => array(
						9 => array(
							'arrival_time'   => '10:00',
							'departure_time' => '10:05',
						),
					),
				),
			),
		);
		$branch_group = array(
			'stations' => array( 9, 15 ),
			'services' => array(
				array(
					'service'    => (object) array( 'ID' => 501 ),
					'train_type' => (object) array( 'slug' => 'buss' ),
					'stop_times' => array(
						9  => array( 'departure_time' => '10:15' ),
						15 => array( 'arrival_time' => '10:45' ),
					),
				),
			),
		);

		$data = MRT_build_rail_bus_connection_data( $rail_group, $branch_group );

		self::assertSame( 9, $data['junction_id'] );
		self::assertSame( 'Selknä*', $data['junction_label'] );
		self::assertNotEmpty( $data['train_to_bus'] );
		self::assertSame( '71', $data['train_to_bus'][0]['train']['service_number'] );
	}

	public function test_connection_buses_for_column_uses_continuation_train_number(): void {
		$connection = array(
			'train_to_bus' => array(
				array(
					'train' => array( 'service_number' => '61' ),
					'buses' => array(
						array(
							'service_number' => 'B1',
							'time_display'   => '10:53',
							'destination'    => 'Fjällnora',
						),
					),
				),
			),
		);
		$info   = array(
			array( 'service_number' => '71' ),
			array( 'service_number' => '61' ),
		);
		$column = array(
			'primary_idx'      => 0,
			'continuation_idx' => 1,
			'split_station_id' => 8,
		);

		$match = MRT_connection_buses_for_column( $connection, $info, $column );

		self::assertSame( '61', $match['train_number'] );
		self::assertSame( 'B1', $match['buses'][0]['service_number'] ?? '' );
	}

	public function test_buses_for_train_at_junction_inbound_allows_bus_before_departure(): void {
		$junction_id = 8;
		$train_stop  = array_merge(
			array(
				'arrival_time'   => '13:07',
				'departure_time' => '14:05',
			),
			MRT_test_stop_modes_both_scheduled()
		);
		$bus_services = array(
			array(
				'service'    => (object) array( 'ID' => 501 ),
				'stop_times' => array(
					$junction_id => array_merge(
						array(
							'arrival_time'   => '13:40',
							'departure_time' => '',
						),
						MRT_test_stop_modes_both_scheduled()
					),
				),
			),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_number' => 'B12',
		);

		$buses = MRT_buses_for_train_at_junction( $train_stop, 'inbound', $junction_id, $bus_services );

		self::assertCount( 1, $buses );
		self::assertSame( 'B12', $buses[0]['service_number'] );
	}
}
