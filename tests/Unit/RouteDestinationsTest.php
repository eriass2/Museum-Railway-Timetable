<?php
/**
 * Route destination lists for trip creation.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/route/destinations.php';

final class RouteDestinationsTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'] );
		parent::tearDown();
	}

	public function test_build_route_destinations_list_marks_termini_and_intermediates(): void {
		$route_id = 50;
		$GLOBALS['mrt_test_post_meta'] = array(
			$route_id . '|mrt_route_stations'      => array( 10, 20, 30 ),
			$route_id . '|mrt_route_start_station' => 10,
			$route_id . '|mrt_route_end_station'   => 30,
		);
		$GLOBALS['mrt_test_posts'] = array(
			10 => (object) array( 'ID' => 10, 'post_title' => 'Uppsala' ),
			20 => (object) array( 'ID' => 20, 'post_title' => 'Marielund' ),
			30 => (object) array( 'ID' => 30, 'post_title' => 'Faringe' ),
		);

		$list = MRT_build_route_destinations_list( $route_id );

		self::assertCount( 3, $list );
		self::assertSame( 10, $list[0]['id'] );
		self::assertStringContainsString( 'Start', $list[0]['name'] );
		self::assertSame( 30, $list[1]['id'] );
		self::assertStringContainsString( 'End', $list[1]['name'] );
		self::assertSame( 20, $list[2]['id'] );
		self::assertSame( 'Marielund', $list[2]['name'] );
	}

	public function test_build_route_destinations_list_skips_missing_intermediate(): void {
		$route_id = 51;
		$GLOBALS['mrt_test_post_meta'] = array(
			$route_id . '|mrt_route_stations'      => array( 10, 99, 30 ),
			$route_id . '|mrt_route_start_station' => 10,
			$route_id . '|mrt_route_end_station'   => 30,
		);
		$GLOBALS['mrt_test_posts'] = array(
			10 => (object) array( 'ID' => 10, 'post_title' => 'Start' ),
			30 => (object) array( 'ID' => 30, 'post_title' => 'End' ),
		);

		$list = MRT_build_route_destinations_list( $route_id );

		self::assertCount( 2, $list );
		self::assertSame( 10, $list[0]['id'] );
		self::assertSame( 30, $list[1]['id'] );
	}
}
