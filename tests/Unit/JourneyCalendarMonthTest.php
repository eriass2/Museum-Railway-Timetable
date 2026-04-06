<?php
/**
 * Edge cases for MRT_get_journey_calendar_month (no DB; early returns only).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyCalendarMonthTest extends TestCase {

    public function test_empty_when_same_station(): void {
        self::assertSame([], MRT_get_journey_calendar_month(3, 3, 2026, 6));
    }

    public function test_empty_when_non_positive_station(): void {
        self::assertSame([], MRT_get_journey_calendar_month(0, 2, 2026, 6));
        self::assertSame([], MRT_get_journey_calendar_month(1, 0, 2026, 6));
    }

    public function test_empty_when_year_out_of_range(): void {
        self::assertSame([], MRT_get_journey_calendar_month(1, 2, 1969, 6));
        self::assertSame([], MRT_get_journey_calendar_month(1, 2, 2101, 6));
    }

    public function test_empty_when_month_invalid(): void {
        self::assertSame([], MRT_get_journey_calendar_month(1, 2, 2026, 0));
        self::assertSame([], MRT_get_journey_calendar_month(1, 2, 2026, 13));
    }
}
