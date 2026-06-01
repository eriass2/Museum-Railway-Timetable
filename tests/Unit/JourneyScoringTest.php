<?php
/**
 * Tests for journey connection scoring (inc/domain/journey/journey-scoring.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyScoringTest extends TestCase {
    use MRT_Journey_Test_Fixture;

    private const DATE = '2026-06-01';
    private const A = 101;
    private const B = 303;

    protected function tearDown(): void {
        $this->mrt_reset_journey_fixture();
        parent::tearDown();
    }

    public function test_return_score_prefers_departure_near_outbound_arrival(): void {
        $early = $this->connection('10:35', '11:35', 'direct');
        $late = $this->connection('14:00', '15:00', 'direct');

        self::assertGreaterThan(
            MRT_journey_score_return_connection($late, true, '10:30'),
            MRT_journey_score_return_connection($early, true, '10:30')
        );
    }

    public function test_sort_outbound_orders_by_departure(): void {
        $connections = array(
            $this->connection('08:00', '10:30', 'direct'),
            $this->connection('09:00', '10:05', 'transfer', 5),
            $this->connection('07:00', '08:00', 'direct'),
        );

        $sorted = MRT_journey_sort_outbound_connections($connections, self::A, self::B, self::DATE);

        self::assertSame('07:00', MRT_journey_normalized_departure_hhmm($sorted[0]));
        self::assertSame('08:00', MRT_journey_normalized_departure_hhmm($sorted[1]));
        self::assertSame('09:00', MRT_journey_normalized_departure_hhmm($sorted[2]));
    }

    public function test_sort_return_orders_early_departure_first(): void {
        $connections = array(
            $this->connection('14:00', '15:00', 'direct'),
            $this->connection('10:35', '11:35', 'direct'),
        );

        $sorted = MRT_journey_sort_return_connections(
            $connections,
            self::B,
            self::A,
            self::DATE,
            '10:30'
        );

        self::assertSame('10:35', MRT_journey_normalized_departure_hhmm($sorted[0]));
        self::assertSame('14:00', MRT_journey_normalized_departure_hhmm($sorted[1]));
    }

    public function test_find_normalized_connections_applies_outbound_sort(): void {
        $this->mrt_use_journey_fixture([
            11 => [
                $this->mrt_stop(11, self::A, 1, null, '08:00'),
                $this->mrt_stop(11, self::B, 2, '10:30', null),
            ],
            12 => [
                $this->mrt_stop(12, self::A, 1, null, '07:00'),
                $this->mrt_stop(12, self::B, 2, '08:00', null),
            ],
        ], [900 => [self::DATE]]);

        $results = MRT_journey_find_normalized_connections(self::A, self::B, self::DATE);

        self::assertCount(2, $results);
        self::assertSame(12, $results[0]['service_id']);
        self::assertSame(11, $results[1]['service_id']);
    }

    public function test_find_return_connections_applies_return_sort(): void {
        $this->mrt_use_journey_fixture([
            31 => [
                $this->mrt_stop(31, self::B, 1, null, '10:20'),
                $this->mrt_stop(31, self::A, 2, '11:10', null),
            ],
            32 => [
                $this->mrt_stop(32, self::B, 1, null, '10:35'),
                $this->mrt_stop(32, self::A, 2, '11:25', null),
            ],
        ], [900 => [self::DATE]]);

        $results = MRT_find_return_connections(self::A, self::B, self::DATE, '10:00', 5);

        self::assertCount(2, $results);
        self::assertSame(31, $results[0]['service_id']);
        self::assertSame('10:20', $results[0]['from_departure']);
        self::assertSame(32, $results[1]['service_id']);
    }

    public function test_invalid_times_sort_last(): void {
        $bad = ['connection_type' => 'direct', 'from_departure' => '', 'to_arrival' => ''];
        $good = $this->connection('09:00', '10:00', 'direct');
        $sorted = MRT_journey_sort_outbound_connections([$bad, $good], self::A, self::B, self::DATE);

        self::assertSame('09:00', MRT_journey_normalized_departure_hhmm($sorted[0]));
    }

    /**
     * @return array<string, mixed>
     */
    private function connection(string $dep, string $arr, string $type, int $wait = 0): array {
        $row = [
            'connection_type' => $type,
            'from_departure' => $dep,
            'to_arrival' => $arr,
            'departure' => $dep,
            'arrival' => $arr,
            'legs' => $type === 'transfer' ? [['from_departure' => $dep], ['to_arrival' => $arr]] : [],
        ];
        if ($wait > 0) {
            $row['transfer_wait_minutes'] = $wait;
        }
        return $row;
    }
}
