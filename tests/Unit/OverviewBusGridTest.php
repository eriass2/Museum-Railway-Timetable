<?php
/**
 * Overview bus rows and grid merge (overview-bus-rows.php, grid-merge.php, overview-branch-group.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OverviewBusGridTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'] );
		parent::tearDown();
	}

	public function test_connection_has_any_buses(): void {
		self::assertFalse(
			MRT_connection_has_any_buses( array( 'train_to_bus' => array() ) )
		);
		self::assertTrue(
			MRT_connection_has_any_buses(
				array(
					'train_to_bus' => array(
						array(
							'train' => array( 'service_number' => '71' ),
							'buses' => array(
								array( 'service_number' => 'B1' ),
							),
						),
					),
				)
			)
		);
	}

	public function test_station_is_bus_junction(): void {
		$connection = array(
			'junction_id'  => 9,
			'train_to_bus' => array(
				array(
					'train' => array( 'service_number' => '71' ),
					'buses' => array( array( 'service_number' => 'B1' ) ),
				),
			),
		);
		$branch = array( 'stations' => array( 9, 15 ) );

		self::assertTrue( MRT_timetable_station_is_bus_junction( $connection, $branch, 9 ) );
		self::assertFalse( MRT_timetable_station_is_bus_junction( $connection, $branch, 15 ) );
		self::assertFalse( MRT_timetable_station_is_bus_junction( null, $branch, 9 ) );
	}

	public function test_groups_link_branch_pairs(): void {
		$grouped = array(
			'main'   => array(
				'stations'  => array( 1, 9, 15, 20 ),
				'direction' => 'outbound',
				'services'  => array(
					array(
						'train_type' => (object) array( 'slug' => 'angtag' ),
					),
				),
			),
			'branch' => array(
				'stations'  => array( 9, 15 ),
				'direction' => 'outbound',
				'services'  => array(
					array(
						'train_type' => (object) array( 'slug' => 'buss' ),
					),
				),
			),
		);

		$linked = MRT_timetable_groups_link_branch_pairs( $grouped );
		$main   = null;
		$branch = null;
		foreach ( $linked as $group ) {
			if ( ! empty( $group['paired_branch'] ) ) {
				$main = $group;
			}
			if ( ! empty( $group['paired_rail'] ) ) {
				$branch = $group;
			}
		}

		self::assertNotNull( $main );
		self::assertNotNull( $branch );
		self::assertCount( 2, $main['paired_branch']['stations'] ?? array() );
		self::assertCount( 1, MRT_timetable_rail_paired_branches( $main ) );
	}

	public function test_groups_link_branch_pairs_keeps_all_branches_on_main(): void {
		$grouped = array(
			'main'    => array(
				'stations'  => array( 1, 9, 15, 20 ),
				'direction' => 'outbound',
				'services'  => array(
					array(
						'train_type' => (object) array( 'slug' => 'angtag' ),
					),
				),
			),
			'branch1' => array(
				'stations'  => array( 9, 100 ),
				'direction' => 'outbound',
				'services'  => array(
					array(
						'train_type' => (object) array( 'slug' => 'buss' ),
					),
				),
			),
			'branch2' => array(
				'stations'  => array( 9, 200 ),
				'direction' => 'outbound',
				'services'  => array(
					array(
						'train_type' => (object) array( 'slug' => 'buss' ),
					),
				),
			),
		);

		$linked = MRT_timetable_groups_link_branch_pairs( $grouped );
		$main   = null;
		foreach ( $linked as $group ) {
			if ( ! empty( $group['paired_branches'] ) ) {
				$main = $group;
				break;
			}
		}

		self::assertNotNull( $main );
		self::assertCount( 2, MRT_timetable_rail_paired_branches( $main ) );
		self::assertSame( 100, MRT_timetable_rail_paired_branches( $main )[0]['stations'][1] ?? 0 );
		self::assertSame( 200, MRT_timetable_rail_paired_branches( $main )[1]['stations'][1] ?? 0 );
	}

	public function test_groups_link_branch_pairs_skips_mismatched_direction(): void {
		$grouped = array(
			'main'   => array(
				'stations'  => array( 1, 9, 15, 20 ),
				'direction' => 'outbound',
				'services'  => array(
					array(
						'train_type' => (object) array( 'slug' => 'angtag' ),
					),
				),
			),
			'branch' => array(
				'stations'  => array( 9, 15 ),
				'direction' => 'inbound',
				'services'  => array(
					array(
						'train_type' => (object) array( 'slug' => 'buss' ),
					),
				),
			),
		);

		$linked = MRT_timetable_groups_link_branch_pairs( $grouped );
		foreach ( $linked as $group ) {
			self::assertArrayNotHasKey( 'paired_branch', $group );
			self::assertArrayNotHasKey( 'paired_rail', $group );
		}
	}

	public function test_branch_group_to_json_returns_empty_trips_without_stations(): void {
		$result = MRT_timetable_branch_group_to_json(
			array(
				'route'     => (object) array( 'ID' => 1, 'post_title' => 'Bus' ),
				'direction' => 'outbound',
				'stations'  => array(),
				'services'  => array(),
			),
			'2026-06-06'
		);

		self::assertSame( 'branch', $result['kind'] );
		self::assertSame( array(), $result['trips'] );
	}

	public function test_group_is_main_corridor(): void {
		self::assertTrue(
			MRT_timetable_group_is_main_corridor(
				array(
					'branch_code' => 'main',
					'services'    => array(
						array( 'train_type' => (object) array( 'slug' => 'angtag' ) ),
					),
				)
			)
		);
		self::assertTrue(
			MRT_timetable_group_is_main_corridor(
				array(
					'services' => array(
						array( 'train_type' => (object) array( 'slug' => 'angtag' ) ),
					),
				)
			)
		);
		self::assertFalse(
			MRT_timetable_group_is_main_corridor(
				array(
					'branch_code' => 'fjallnora',
					'stations'    => array( 9, 15 ),
					'services'    => array(
						array( 'train_type' => (object) array( 'slug' => 'buss' ) ),
					),
				)
			)
		);
	}

	public function test_find_main_group_only_pairs_to_main_corridor(): void {
		$grouped = array(
			'wrong'  => array(
				'stations'    => array( 1, 9, 8, 14 ),
				'branch_code' => 'fjallnora',
				'services'    => array(
					array( 'train_type' => (object) array( 'slug' => 'angtag' ) ),
				),
			),
			'main'   => array(
				'stations'    => array( 1, 9, 8, 14 ),
				'branch_code' => 'main',
				'services'    => array(
					array( 'train_type' => (object) array( 'slug' => 'angtag' ) ),
				),
			),
			'branch' => array(
				'stations'    => array( 8, 16 ),
				'branch_code' => 'linnes-hammarby',
				'services'    => array(
					array( 'train_type' => (object) array( 'slug' => 'buss' ) ),
				),
			),
		);

		self::assertSame(
			'main',
			MRT_timetable_find_main_group_for_branch( $grouped, $grouped['branch'], 'branch' )
		);
	}

	public function test_find_main_group_uses_line_junction_from_registry(): void {
		MRT_set_line_registry(
			array(
				'fjallnora' => array(
					'title'                   => 'Selkné – Fjällnora',
					'kind'                    => 'branch',
					'station_codes'           => array( 'selkna', 'fjallnora' ),
					'junction_station_code'   => 'selkna',
					'requires_transfer'       => true,
				),
			)
		);
		$selkna    = 9;
		$fjallnora = 15;
		$uppsala   = 14;
		$faringe   = 1;
		$route_out = 50;
		$route_in  = 60;
		$GLOBALS['mrt_test_post_meta'] = array(
			$route_out . '|mrt_route_start_station' => $uppsala,
			$route_out . '|mrt_route_end_station'   => $faringe,
			$route_in . '|mrt_route_start_station'  => $faringe,
			$route_in . '|mrt_route_end_station'   => $uppsala,
			$uppsala . '|mrt_display_order'        => 1,
			$faringe . '|mrt_display_order'        => 14,
			$selkna . '|mrt_station_code'          => 'selkna',
			'501|mrt_service_line_code'            => 'fjallnora',
		);
		$bus_service       = new WP_Post();
		$bus_service->ID   = 501;
		$train             = array( 'train_type' => (object) array( 'slug' => 'angtag' ) );
		$bus               = array(
			'train_type' => (object) array( 'slug' => 'buss' ),
			'service'    => $bus_service,
		);
		$grouped = array(
			'main_out' => array(
				'route_id'    => $route_out,
				'branch_code' => 'main',
				'stations'    => array( $uppsala, 12, 11, $selkna, 5, $faringe ),
				'services'    => array( $train ),
			),
			'main_in'  => array(
				'route_id'    => $route_in,
				'branch_code' => 'main',
				'stations'    => array( $faringe, 5, $selkna, 11, $uppsala ),
				'services'    => array( $train ),
			),
			'fj_out'   => array(
				'stations' => array( $selkna, $fjallnora ),
				'services' => array( $bus ),
			),
		);

		self::assertSame(
			'main_out',
			MRT_timetable_find_main_group_for_branch( $grouped, $grouped['fj_out'], 'fj_out' )
		);
		delete_option( MRT_line_registry_option_key() );
	}

	public function test_find_main_group_pairs_fjallnora_branches_to_matching_main_direction(): void {
		$uppsala   = 14;
		$faringe   = 1;
		$selkna    = 9;
		$fjallnora = 15;
		$route_out = 50;
		$route_in  = 60;
		$GLOBALS['mrt_test_post_meta'] = array(
			$route_out . '|mrt_route_start_station' => $uppsala,
			$route_out . '|mrt_route_end_station'   => $faringe,
			$route_in . '|mrt_route_start_station'  => $faringe,
			$route_in . '|mrt_route_end_station'   => $uppsala,
			$uppsala . '|mrt_display_order'        => 1,
			$faringe . '|mrt_display_order'        => 14,
		);
		$train = array(
			'train_type' => (object) array( 'slug' => 'angtag' ),
		);
		$bus = array(
			'train_type' => (object) array( 'slug' => 'buss' ),
		);
		$grouped = array(
			'main_out' => array(
				'route_id'    => $route_out,
				'branch_code' => 'main',
				'stations'    => array( $uppsala, 12, 11, $selkna, 5, $faringe ),
				'services'    => array( $train ),
			),
			'main_in'  => array(
				'route_id'    => $route_in,
				'branch_code' => 'main',
				'stations'    => array( $faringe, 5, $selkna, 11, $uppsala ),
				'services'    => array( $train ),
			),
			'fj_out'   => array(
				'stations' => array( $selkna, $fjallnora ),
				'services' => array( $bus ),
			),
			'fj_in'    => array(
				'stations' => array( $fjallnora, $selkna ),
				'services' => array( $bus ),
			),
		);

		self::assertSame(
			'main_out',
			MRT_timetable_find_main_group_for_branch( $grouped, $grouped['fj_out'], 'fj_out' )
		);
		self::assertSame(
			'main_in',
			MRT_timetable_find_main_group_for_branch( $grouped, $grouped['fj_in'], 'fj_in' )
		);
	}

	public function test_find_main_group_prefers_matching_travel_direction_on_tie(): void {
		$uppsala  = 14;
		$marielund = 8;
		$faringe  = 1;
		$linnes   = 16;
		$main_out = array(
			'stations' => array( $uppsala, 13, 12, 11, 10, 9, $marielund, 7, 6, 5, 4, 3, 2, $faringe ),
		);
		$main_in  = array(
			'stations' => array( $faringe, 2, 3, 4, 5, 6, 7, $marielund, 9, 10, 11, 12, 13, $uppsala ),
		);
		$out_branch = array( 'stations' => array( $marielund, $linnes ) );
		$in_branch  = array( 'stations' => array( $linnes, $marielund ) );

		self::assertGreaterThan(
			MRT_timetable_branch_main_pair_score( $main_in['stations'], $out_branch['stations'] ),
			MRT_timetable_branch_main_pair_score( $main_out['stations'], $out_branch['stations'] )
		);
		self::assertGreaterThan(
			MRT_timetable_branch_main_pair_score( $main_out['stations'], $in_branch['stations'] ),
			MRT_timetable_branch_main_pair_score( $main_in['stations'], $in_branch['stations'] )
		);
	}

	public function test_find_bus_service_in_branch(): void {
		$branch = array(
			'services' => array(
				array(
					'service' => (object) array( 'ID' => 501 ),
				),
			),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_number' => 'B1',
		);

		$hit = MRT_find_bus_service_in_branch( $branch, 'B1' );
		self::assertIsArray( $hit );
		self::assertNull( MRT_find_bus_service_in_branch( $branch, 'missing' ) );
	}
}
