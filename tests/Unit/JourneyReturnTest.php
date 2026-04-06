<?php
/**
 * Tests for return-journey helpers (inc/functions/journey-return.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyReturnTest extends TestCase {

    public function test_raw_item_first_departure_prefers_from_departure(): void {
        $item = [
            'legs' => [
                [
                    'from_departure' => '14:20',
                    'from_arrival' => '14:18',
                ],
            ],
        ];
        self::assertSame('14:20', MRT_journey_raw_item_first_departure($item));
    }

    public function test_raw_item_first_departure_falls_back_to_from_arrival(): void {
        $item = [
            'legs' => [
                [
                    'from_departure' => '',
                    'from_arrival' => '09:05',
                ],
            ],
        ];
        self::assertSame('09:05', MRT_journey_raw_item_first_departure($item));
    }

    public function test_find_return_connections_empty_for_invalid_date(): void {
        self::assertSame([], MRT_find_return_connections(1, 2, 'bad-date', '12:00', 0));
    }

    public function test_find_return_connections_empty_no_services(): void {
        self::assertSame([], MRT_find_return_connections(1, 2, '2026-06-01', '12:00', 0));
    }
}
