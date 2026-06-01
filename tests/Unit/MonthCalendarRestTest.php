<?php
/**
 * Month calendar REST payload.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MonthCalendarRestTest extends TestCase {
	protected function setUp(): void {
		require_once ABSPATH . 'inc/public/month-calendar/shortcode.php';
		parent::setUp();
	}

	public function test_month_calendar_data_for_month_rejects_invalid_month(): void {
		$result = MRT_month_calendar_data_for_month( 2026, 13, array(
			'train_type'   => '',
			'service'      => '',
			'start_monday' => 1,
		) );

		self::assertInstanceOf( WP_Error::class, $result );
	}

	public function test_month_calendar_data_for_month_returns_grid_shape(): void {
		$result = MRT_month_calendar_data_for_month( 2026, 6, array(
			'train_type'   => '',
			'service'      => '',
			'start_monday' => 1,
		) );

		self::assertIsArray( $result );
		self::assertSame( 2026, $result['year'] );
		self::assertSame( 6, $result['month'] );
		self::assertSame( 30, $result['daysInMonth'] );
		self::assertArrayHasKey( 'dates', $result );
		self::assertCount( 30, $result['dates'] );
		self::assertArrayHasKey( 'legendTimetableTypes', $result );
	}
}
