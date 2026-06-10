<?php
/**
 * Tests for helpers extracted during code-quality refactors.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/route/routes.php';

final class RefactoredHelpersTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'] );
		parent::tearDown();
	}

	public function test_route_direction_from_configured_endpoints(): void {
		$endpoints = array(
			'start' => 10,
			'end'   => 30,
		);

		self::assertSame( 'dit', MRT_route_direction_from_configured_endpoints( $endpoints, 30 ) );
		self::assertSame( 'från', MRT_route_direction_from_configured_endpoints( $endpoints, 10 ) );
		self::assertSame( '', MRT_route_direction_from_configured_endpoints( $endpoints, 20 ) );
	}

	public function test_route_direction_from_station_order(): void {
		$route_stations = array( 10, 20, 30, 40 );
		$endpoints      = array(
			'start' => 10,
			'end'   => 40,
		);

		self::assertSame( 'från', MRT_route_direction_from_station_order( $route_stations, $endpoints, 20 ) );
		self::assertSame( 'dit', MRT_route_direction_from_station_order( $route_stations, $endpoints, 30 ) );
		self::assertSame( '', MRT_route_direction_from_station_order( $route_stations, $endpoints, 99 ) );
	}

	public function test_route_leg_travels_towards_station(): void {
		$route_id = 77;
		$GLOBALS['mrt_test_post_meta'] = array(
			$route_id . '|mrt_route_stations' => array( 10, 101, 303 ),
		);

		self::assertTrue( MRT_route_leg_travels_towards_station( $route_id, 101, 303, 303 ) );
		self::assertFalse( MRT_route_leg_travels_towards_station( $route_id, 101, 10, 303 ) );
	}

	public function test_journey_transfer_overshoots_destination(): void {
		$route_id = 77;
		$uppsala  = 10;
		$fyris    = 11;
		$marie    = 12;
		$faringe  = 50;
		$GLOBALS['mrt_test_post_meta'] = array(
			$route_id . '|mrt_route_stations' => array( $uppsala, $fyris, $marie, $faringe ),
		);

		self::assertTrue(
			MRT_journey_transfer_overshoots_destination( $route_id, $uppsala, $faringe, $fyris )
		);
		self::assertTrue(
			MRT_journey_transfer_overshoots_destination( $route_id, $uppsala, $marie, $fyris )
		);
		self::assertFalse(
			MRT_journey_transfer_overshoots_destination( $route_id, $uppsala, $marie, $faringe )
		);
		self::assertFalse(
			MRT_journey_transfer_overshoots_destination( $route_id, $uppsala, $fyris, $marie )
		);
	}

	public function test_route_label_from_shared_service_end_station(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'77|mrt_route_start_station'    => 10,
			'501|mrt_service_end_station_id' => 40,
			'502|mrt_service_end_station_id' => 40,
		);
		$GLOBALS['mrt_test_posts']     = array(
			10 => (object) array(
				'ID'         => 10,
				'post_title' => 'Uppsala Östra',
			),
			40 => (object) array(
				'ID'         => 40,
				'post_title' => 'Faringe',
			),
		);
		$services = array(
			array(
				'service' => (object) array( 'ID' => 501 ),
			),
			(object) array( 'ID' => 502 ),
		);

		self::assertSame( 'Från Uppsala Östra Till Faringe', MRT_get_route_label_from_services_end_station( 77, $services ) );
	}

	public function test_route_label_ignores_mixed_service_end_stations(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_end_station_id' => 40,
			'502|mrt_service_end_station_id' => 41,
		);
		$services = array(
			(object) array( 'ID' => 501 ),
			(object) array( 'ID' => 502 ),
		);

		self::assertSame( '', MRT_get_route_label_from_services_end_station( 77, $services ) );
	}
}
