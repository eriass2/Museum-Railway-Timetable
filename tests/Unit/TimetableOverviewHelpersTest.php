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
		unset( $GLOBALS['mrt_test_post_meta'] );
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

	/**
	 * @return array<string, mixed>
	 */
	private function bus_service(): array {
		return array(
			'train_type' => (object) array( 'slug' => 'buss' ),
		);
	}
}
