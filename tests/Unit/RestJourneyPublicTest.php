<?php
/**
 * Public journey REST handlers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}

require_once ABSPATH . 'inc/infrastructure/rest/journey-public.php';

final class RestJourneyPublicTest extends TestCase
{
	protected function tearDown(): void
	{
		unset( $GLOBALS['mrt_test_get_posts'] );
		parent::tearDown();
	}

	public function test_timetable_day_handler_rejects_invalid_date(): void
	{
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/timetables/day' );
		$request->set_param( 'date', 'not-a-date' );

		$result = MRT_rest_timetable_day_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'mrt_journey_date', $result->get_error_code() );
	}

	public function test_journey_search_response_returns_empty_connections_without_services(): void
	{
		$GLOBALS['mrt_test_get_posts'] = static function (): array {
			return array();
		};

		$result = MRT_journey_search_response(
			array(
				'from_station' => '1',
				'to_station'   => '2',
				'date'         => '2026-07-04',
				'trip_type'    => 'single',
			)
		);

		self::assertIsArray( $result );
		self::assertSame( 'single', $result['trip_type'] );
		self::assertSame( array(), $result['connections'] );
	}

	public function test_journey_search_response_propagates_validation_error(): void
	{
		$result = MRT_journey_search_response(
			array(
				'from_station' => '1',
				'to_station'   => '1',
				'date'         => '2026-07-04',
			)
		);

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'mrt_journey_same', $result->get_error_code() );
	}
}
