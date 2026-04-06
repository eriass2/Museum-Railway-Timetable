<?php
/**
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyValidateStationPairTest extends TestCase {

    public function test_null_when_valid_pair(): void {
        self::assertNull(MRT_journey_validate_station_pair_ids(10, 20));
    }

    public function test_error_when_missing_station(): void {
        $e = MRT_journey_validate_station_pair_ids(0, 5);
        self::assertInstanceOf(WP_Error::class, $e);
        self::assertSame('mrt_journey_stations', $e->get_error_code());
    }

    public function test_error_when_same_station(): void {
        $e = MRT_journey_validate_station_pair_ids(7, 7);
        self::assertInstanceOf(WP_Error::class, $e);
        self::assertSame('mrt_journey_same', $e->get_error_code());
    }
}
