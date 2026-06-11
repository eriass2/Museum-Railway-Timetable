<?php
/**
 * Unified journey wizard resource cache.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyCacheTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['mrt_test_transients']  = array();
		$GLOBALS['mrt_test_options']     = array();
		$GLOBALS['mrt_test_post_types']  = array();
	}

	public function test_calendar_month_cache_hit(): void {
		$params = array(
			'from'      => '1',
			'to'        => '2',
			'year'      => '2026',
			'month'     => '6',
			'trip_type' => 'single',
		);
		$payload = array(
			'2026-06-01' => array( 'status' => 'ok', 'type' => 'green' ),
		);
		MRT_journey_cache_set( 'calendar.month', $params, $payload );

		self::assertSame( $payload, MRT_journey_cache_get( 'calendar.month', $params ) );
	}

	public function test_bump_generation_invalidates_keys(): void {
		$before = MRT_journey_cache_key( 'journey.search', array( 'date' => '2026-06-01' ) );
		MRT_journey_cache_bump_generation( 'test' );
		$after = MRT_journey_cache_key( 'journey.search', array( 'date' => '2026-06-01' ) );

		self::assertNotSame( $before, $after );
	}

	public function test_search_response_uses_cache(): void {
		$input = array(
			'from_station' => 1,
			'to_station'   => 2,
			'date'         => '2099-01-01',
			'trip_type'    => 'single',
		);
		$first = MRT_journey_search_response( $input );
		self::assertIsArray( $first );
		$second = MRT_journey_search_response( $input );
		self::assertSame( $first, $second );
	}

	public function test_save_post_on_plugin_cpt_bumps_generation(): void {
		$before = MRT_journey_cache_generation();
		$GLOBALS['mrt_test_post_types'][100] = MRT_POST_TYPE_SERVICE;
		MRT_journey_cache_maybe_invalidate_on_save( 100 );
		self::assertSame( $before + 1, MRT_journey_cache_generation() );
	}
}
