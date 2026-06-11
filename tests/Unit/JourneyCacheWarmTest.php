<?php
/**
 * Journey cache warm helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyCacheWarmTest extends TestCase {

	public function test_shift_month_wraps_year(): void {
		$dec = MRT_journey_cache_shift_month( 2026, 1, -1 );
		self::assertSame( 2025, $dec['year'] );
		self::assertSame( 12, $dec['month'] );

		$jan = MRT_journey_cache_shift_month( 2026, 12, 1 );
		self::assertSame( 2027, $jan['year'] );
		self::assertSame( 1, $jan['month'] );
	}

	public function test_warm_popular_routes_returns_shape(): void {
		$result = MRT_journey_cache_warm_popular_routes( 2099, 1 );
		self::assertArrayHasKey( 'warmed', $result );
		self::assertArrayHasKey( 'pairs', $result );
		self::assertSame( 2099, $result['year'] );
		self::assertSame( 1, $result['month'] );
	}
}
