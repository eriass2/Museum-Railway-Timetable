<?php
/**
 * Journey transfer rules and per-service notices.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyTransferRulesTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_options'], $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_get_posts'] );
		parent::tearDown();
	}

	public function test_min_and_max_transfer_minutes_from_settings(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'min_transfer_minutes' => 4,
				'max_transfer_minutes' => 45,
			),
		);

		self::assertSame( 4, MRT_journey_min_transfer_minutes() );
		self::assertSame( 45, MRT_journey_max_transfer_minutes() );
	}

	public function test_transfer_wait_valid_within_bounds(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'min_transfer_minutes' => 3,
				'max_transfer_minutes' => 120,
			),
		);

		self::assertTrue( MRT_journey_transfer_wait_is_valid( '10:00', '10:15' ) );
		self::assertFalse( MRT_journey_transfer_wait_is_valid( '10:00', '10:01' ) );
		self::assertFalse( MRT_journey_transfer_wait_is_valid( '10:00', '14:00' ) );
	}

	public function test_train_to_bus_at_bus_hub_allows_three_minute_wait_when_min_is_four(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'min_transfer_minutes' => 4,
				'max_transfer_minutes' => 120,
			),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'9|mrt_station_bus_suffix' => '1',
			'10|mrt_service_route_id'  => 8001,
			'8001|mrt_route_stations'  => array( 9, 15 ),
			'15|mrt_station_bus_suffix' => '1',
			'11|mrt_service_route_id'  => 900,
			'12|mrt_service_route_id'  => 901,
		);

		self::assertSame( 0, MRT_journey_min_transfer_between_legs( 9, 11, 10, 4 ) );
		self::assertSame( 4, MRT_journey_min_transfer_between_legs( 9, 11, 12, 4 ) );
		self::assertTrue(
			MRT_journey_transfer_wait_is_valid_between_services( '11:47', '11:50', 9, 11, 10, 4 )
		);
		self::assertFalse(
			MRT_journey_transfer_wait_is_valid_between_services( '11:47', '11:50', 9, 11, 12, 4 )
		);
	}

	public function test_transfer_station_priority_prefers_bus_hub(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'9|mrt_station_bus_suffix' => '1',
			'10|mrt_transfer_priority' => '25',
		);

		self::assertSame( 0, MRT_journey_transfer_station_priority( 9 ) );
		self::assertSame( 25, MRT_journey_transfer_station_priority( 10 ) );
		self::assertSame( 50, MRT_journey_transfer_station_priority( 99 ) );
	}

	public function test_station_allows_transfer_at_bus_hub_and_marked_priority(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'9|mrt_station_bus_suffix' => '1',
			'20|mrt_transfer_priority' => '0',
		);

		self::assertTrue( MRT_journey_station_allows_transfer( 9 ) );
		self::assertTrue( MRT_journey_station_allows_transfer( 20 ) );
		self::assertFalse( MRT_journey_station_allows_transfer( 15 ) );
	}

	public function test_compare_transfer_candidates_sorts_by_priority_then_wait(): void {
		$a = array( 'priority' => 0, 'wait' => 10, 'departure' => '09:00' );
		$b = array( 'priority' => 5, 'wait' => 5, 'departure' => '09:00' );
		$c = array( 'priority' => 0, 'wait' => 8, 'departure' => '09:00' );

		self::assertLessThan( 0, MRT_journey_compare_transfer_candidates( $a, $b ) );
		self::assertLessThan( 0, MRT_journey_compare_transfer_candidates( $c, $a ) );
	}
}

final class JourneyNoticeTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
	}

	public function test_get_service_notices_by_date_filters_invalid_entries(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'3|mrt_service_notices_by_date' => array(
				'2026-07-04' => ' Ersatt lok ',
				'bad-date'   => 'Skip',
				'2026-07-05' => '',
			),
		);

		$notices = MRT_get_service_notices_by_date( 3 );

		self::assertSame( array( '2026-07-04' => 'Ersatt lok' ), $notices );
	}

	public function test_get_service_notice_prefers_date_specific_over_global(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'4|mrt_service_notice'            => 'Global info',
			'4|mrt_service_notices_by_date'   => array( '2026-07-04' => 'Date override' ),
		);

		self::assertSame( 'Date override', MRT_get_service_notice( 4, '2026-07-04' ) );
		self::assertSame( 'Global info', MRT_get_service_notice( 4, '2026-07-05' ) );
	}

	public function test_get_service_notice_for_date_does_not_fall_back_to_global(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|mrt_service_notice' => 'Global only',
		);

		self::assertSame( '', MRT_get_service_notice_for_date( 5, '2026-07-04' ) );
	}
}
