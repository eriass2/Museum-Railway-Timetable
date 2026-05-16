<?php
/**
 * Tests for multi-leg journey helpers (inc/functions/journey-multi-leg.php, journey-detail.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyMultiLegTest extends TestCase {
    use MRT_Journey_Test_Fixture;

    private const DATE = '2026-06-01';
    private const A = 101;
    private const X = 202;
    private const B = 303;
    private const OTHER_X = 204;

    protected function tearDown(): void {
        $this->mrt_reset_journey_fixture();
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

    public function test_find_multi_leg_returns_direct_connection(): void {
        $this->mrt_use_journey_fixture([
            11 => [
                $this->mrt_stop(11, self::A, 1, null, '09:00'),
                $this->mrt_stop(11, self::B, 2, '10:00', null),
            ],
        ], [900 => [self::DATE]]);

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE);

        self::assertSame('direct', $results[0]['connection_type']);
        self::assertNull($results[0]['transfer_station_id']);
        self::assertSame(11, $results[0]['legs'][0]['service_id']);
        self::assertSame('09:00', $results[0]['legs'][0]['from_departure']);
        self::assertSame('10:00', $results[0]['legs'][0]['to_arrival']);
    }

    public function test_find_multi_leg_returns_transfer_at_shared_station(): void {
        $this->mrt_use_journey_fixture($this->transferRows('10:10'), [900 => [self::DATE]]);

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE, 5, false);

        self::assertCount(1, $results);
        self::assertSame('transfer', $results[0]['connection_type']);
        self::assertSame(self::X, $results[0]['transfer_station_id']);
        self::assertSame([11, 22], array_column($results[0]['legs'], 'service_id'));
    }

    public function test_find_multi_leg_rejects_transfer_before_minimum_time(): void {
        $this->mrt_use_journey_fixture($this->transferRows('10:04'), [900 => [self::DATE]]);

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE, 5, false);

        self::assertSame([], $results);
    }

    public function test_find_multi_leg_accepts_transfer_at_minimum_time(): void {
        $this->mrt_use_journey_fixture($this->transferRows('10:05'), [900 => [self::DATE]]);

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE, 5, false);

        self::assertCount(1, $results);
        self::assertSame('10:05', $results[0]['legs'][1]['from_departure']);
    }

    public function test_find_multi_leg_returns_empty_without_common_station(): void {
        $this->mrt_use_journey_fixture([
            11 => [
                $this->mrt_stop(11, self::A, 1, null, '09:00'),
                $this->mrt_stop(11, self::X, 2, '10:00', null),
            ],
            22 => [
                $this->mrt_stop(22, self::OTHER_X, 1, null, '10:10'),
                $this->mrt_stop(22, self::B, 2, '11:00', null),
            ],
        ], [900 => [self::DATE]]);

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE, 5, false);

        self::assertSame([], $results);
    }

    public function test_find_multi_leg_requires_same_station_id_for_transfer(): void {
        $this->mrt_use_journey_fixture($this->sameNamedDifferentStationRows(), [900 => [self::DATE]]);

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE, 5, false);

        self::assertSame([], $results);
    }

    public function test_find_return_connections_filters_after_outbound_arrival(): void {
        $this->mrt_use_journey_fixture([
            31 => [
                $this->mrt_stop(31, self::B, 1, null, '09:50'),
                $this->mrt_stop(31, self::A, 2, '10:45', null),
            ],
            32 => [
                $this->mrt_stop(32, self::B, 1, null, '10:10'),
                $this->mrt_stop(32, self::A, 2, '11:00', null),
            ],
        ], [900 => [self::DATE]]);

        $results = MRT_find_return_connections(self::A, self::B, self::DATE, '10:00', 5);

        self::assertCount(1, $results);
        self::assertSame(32, $results[0]['service_id']);
        self::assertSame('10:10', $results[0]['from_departure']);
    }

    public function test_journey_calendar_month_marks_ok_traffic_without_match_and_none(): void {
        $this->mrt_use_journey_fixture([
            11 => [
                $this->mrt_stop(11, self::A, 1, null, '09:00'),
                $this->mrt_stop(11, self::B, 2, '10:00', null),
            ],
            22 => [
                $this->mrt_stop(22, self::A, 1, null, '09:00'),
                $this->mrt_stop(22, self::X, 2, '10:00', null),
            ],
        ], [
            900 => ['2026-06-01'],
            901 => ['2026-06-02'],
        ], [
            11 => 900,
            22 => 901,
        ]);

        $month = MRT_get_journey_calendar_month(self::A, self::B, 2026, 6);

        self::assertSame('ok', $month['2026-06-01']);
        self::assertSame('traffic_no_match', $month['2026-06-02']);
        self::assertSame('none', $month['2026-06-03']);
    }

    /**
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function transferRows(string $secondDeparture): array {
        return [
            11 => [
                $this->mrt_stop(11, self::A, 1, null, '09:00'),
                $this->mrt_stop(11, self::X, 2, '10:00', null),
            ],
            22 => [
                $this->mrt_stop(22, self::X, 1, null, $secondDeparture),
                $this->mrt_stop(22, self::B, 2, '11:00', null),
            ],
        ];
    }

    /**
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function sameNamedDifferentStationRows(): array {
        return [
            11 => [
                $this->mrt_stop(11, self::A, 1, null, '09:00'),
                $this->mrt_stop(11, self::X, 2, '10:00', null),
            ],
            22 => [
                $this->mrt_stop(22, self::OTHER_X, 1, null, '10:10'),
                $this->mrt_stop(22, self::B, 2, '11:00', null),
            ],
        ];
    }
}
