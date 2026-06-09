<?php
/**
 * Journey calendar transient cache.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyCalendarCacheTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['mrt_test_transients']  = array();
		$GLOBALS['mrt_test_options']     = array();
		$GLOBALS['mrt_test_post_types']  = array();
	}

	public function test_returns_cached_month_without_rebuilding(): void {
		$key = MRT_journey_calendar_month_cache_key( 1, 2, 2026, 6, 'single' );
		$cached = array(
			'2026-06-01' => array( 'status' => 'ok', 'type' => 'green' ),
		);
		set_transient( $key, $cached, HOUR_IN_SECONDS );

		self::assertSame( $cached, MRT_get_journey_calendar_month( 1, 2, 2026, 6, 'single' ) );
	}

	public function test_bump_version_changes_cache_key(): void {
		$before = MRT_journey_calendar_month_cache_key( 1, 2, 2026, 6, 'single' );
		MRT_bump_journey_calendar_cache_version();
		$after = MRT_journey_calendar_month_cache_key( 1, 2, 2026, 6, 'single' );

		self::assertNotSame( $before, $after );
	}

	public function test_save_post_on_plugin_cpt_bumps_cache_version(): void {
		$before = MRT_journey_calendar_cache_version();
		MRT_journey_calendar_maybe_invalidate_on_save( 99 );
		self::assertSame( $before, MRT_journey_calendar_cache_version() );

		$GLOBALS['mrt_test_post_types'][100] = MRT_POST_TYPE_SERVICE;
		MRT_journey_calendar_maybe_invalidate_on_save( 100 );
		self::assertSame( $before + 1, MRT_journey_calendar_cache_version() );
	}
}
