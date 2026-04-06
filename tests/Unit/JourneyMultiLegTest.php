<?php
/**
 * Tests for multi-leg journey helpers (inc/functions/journey-multi-leg.php, journey-detail.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyMultiLegTest extends TestCase {

    protected function tearDown(): void {
        unset($GLOBALS['mrt_test_post_meta']);
        parent::tearDown();
    }

    public function test_find_multi_leg_rejects_invalid_from_station(): void {
        self::assertSame([], MRT_find_multi_leg_connections(0, 2, '2026-06-01'));
    }

    public function test_find_multi_leg_rejects_same_station(): void {
        self::assertSame([], MRT_find_multi_leg_connections(5, 5, '2026-06-01'));
    }

    public function test_find_multi_leg_rejects_invalid_date(): void {
        self::assertSame([], MRT_find_multi_leg_connections(1, 2, 'not-a-date'));
    }

    public function test_find_multi_leg_returns_empty_when_no_timetables(): void {
        self::assertSame([], MRT_find_multi_leg_connections(1, 2, '2026-06-01'));
    }

    public function test_find_multi_leg_exclude_direct_skips_wrap(): void {
        self::assertSame([], MRT_find_multi_leg_connections(1, 2, '2026-06-01', 5, false));
    }

    public function test_journey_find_stop_index_finds_station(): void {
        $ordered = [
            ['station_post_id' => 10],
            ['station_post_id' => 20],
        ];
        self::assertSame(1, MRT_journey_find_stop_index($ordered, 20));
    }

    public function test_journey_find_stop_index_returns_null_when_missing(): void {
        self::assertNull(MRT_journey_find_stop_index([['station_post_id' => 1]], 99));
    }

    public function test_journey_leg_from_connection_row_maps_core_fields(): void {
        $conn = [
            'service_id' => 42,
            'from_departure' => '09:10',
            'from_arrival' => '',
            'to_arrival' => '10:00',
            'to_departure' => '',
            'train_type' => 'Fallback',
        ];
        $leg = MRT_journey_leg_from_connection_row($conn, '2026-06-01', 1, 2);
        self::assertSame(42, $leg['service_id']);
        self::assertSame(1, $leg['from_station_id']);
        self::assertSame(2, $leg['to_station_id']);
        self::assertSame('09:10', $leg['from_departure']);
        self::assertSame('10:00', $leg['to_arrival']);
        self::assertSame('Fallback', $leg['train_type']);
        self::assertSame('42', $leg['service_number']);
    }

    public function test_journey_wrap_direct_multi_falls_back_when_no_stoptimes(): void {
        $conn = [
            'service_id' => 7,
            'from_departure' => '08:00',
            'from_arrival' => '',
            'to_arrival' => '09:30',
            'to_departure' => '',
            'train_type' => 'Steam',
        ];
        $wrapped = MRT_journey_wrap_direct_multi($conn, '2026-06-01', 3, 4);
        self::assertSame('direct', $wrapped['connection_type']);
        self::assertNull($wrapped['transfer_station_id']);
        self::assertCount(1, $wrapped['legs']);
        $leg = $wrapped['legs'][0];
        self::assertSame(7, $leg['service_id']);
        self::assertSame('08:00', $leg['from_departure']);
        self::assertSame('09:30', $leg['to_arrival']);
        self::assertSame('Steam', $leg['train_type']);
    }

    public function test_append_transfer_options_no_ops_when_no_services(): void {
        $results = [];
        $seen = [];
        MRT_journey_append_transfer_options($results, $seen, 1, 2, '2026-06-01', 5);
        self::assertSame([], $results);
        self::assertSame([], $seen);
    }
}
