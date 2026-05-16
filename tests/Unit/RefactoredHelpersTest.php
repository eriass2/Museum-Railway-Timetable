<?php
/**
 * Tests for helpers extracted during code-quality refactors.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/functions/helpers-routes.php';
require_once ABSPATH . 'inc/functions/journey-connections-table.php';

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

	public function test_journey_connection_display_helpers(): void {
		$row = array(
			'from_departure' => '09:05',
			'from_arrival'   => '',
			'to_arrival'     => '',
			'to_departure'   => '10:15',
			'destination'    => '',
			'direction'      => 'Faringe',
		);

		self::assertSame( '09.05', MRT_journey_connection_departure_display( $row ) );
		self::assertSame( '10.15', MRT_journey_connection_arrival_display( $row ) );
		self::assertSame( 'Faringe', MRT_journey_connection_destination_display( $row ) );
	}
}
