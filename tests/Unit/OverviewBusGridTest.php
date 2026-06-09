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
