<?php
/**
 * Tests for multi-leg journey helpers (inc/domain/journey/journey-multi-leg.php, journey-detail.php).
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
        unset( $GLOBALS['mrt_test_options'] );
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

    public function test_journey_engine_max_transfers_default_is_two(): void {
        self::assertSame(2, MRT_journey_engine_max_transfers());
    }

    public function test_journey_engine_max_transfers_reads_settings(): void {
        $GLOBALS['mrt_test_options'] = array(
            'mrt_settings' => array( 'max_transfers' => 1 ),
        );
        self::assertSame(1, MRT_journey_engine_max_transfers());
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

    public function test_journey_leg_destination_uses_service_end_not_passenger_alighting(): void {
        $GLOBALS['mrt_test_post_meta'] = array(
            '42|mrt_service_end_station_id' => 100,
        );
        $GLOBALS['mrt_test_posts'] = array(
            2   => (object) array( 'ID' => 2, 'post_title' => 'Lövstahagen' ),
            100 => (object) array( 'ID' => 100, 'post_title' => 'Marielund' ),
        );
        $conn = array(
            'service_id'     => 42,
            'from_departure' => '10:00',
            'to_arrival'     => '10:46',
            'train_type'     => 'Ångtåg',
        );
        $leg = MRT_journey_leg_from_connection_row( $conn, '2026-06-01', 1, 2 );
        self::assertSame( 'Marielund', $leg['destination'] );
        unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'] );
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
        self::assertSame([], MRT_find_multi_leg_connections(1, 2, '2026-06-01', 5, false));
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

    private const ROUTE_MAIN = 500;
    private const ST_UPPSALA = 10;
    private const ST_FYRISLUND = 11;
    private const ST_MARIELUND = 12;
    private const ST_FARINGE = 50;

    public function test_find_multi_leg_rejects_backtrack_past_pickup_only_destination(): void {
        $this->mrt_use_journey_fixture(
            [
                11 => [
                    $this->mrt_stop( 11, self::ST_UPPSALA, 1, null, '10:00' ),
                    array_merge(
                        [
                            'service_post_id' => 11,
                            'station_post_id' => self::ST_FYRISLUND,
                            'stop_sequence'   => 2,
                            'arrival_time'    => '10:05',
                            'departure_time'  => '10:06',
                        ],
                        MRT_test_stop_modes_pickup_only()
                    ),
                    $this->mrt_stop( 11, self::ST_MARIELUND, 3, '10:35', '10:36' ),
                    $this->mrt_stop( 11, self::ST_FARINGE, 4, '11:30', null ),
                ],
                22 => [
                    $this->mrt_stop( 22, self::ST_FARINGE, 1, null, '12:00' ),
                    $this->mrt_stop( 22, self::ST_MARIELUND, 2, '12:30', '12:31' ),
                    $this->mrt_stop( 22, self::ST_FYRISLUND, 3, '13:00', '13:01' ),
                    $this->mrt_stop( 22, self::ST_UPPSALA, 4, '13:36', null ),
                ],
            ],
            [900 => [self::DATE]],
            [],
            [],
            [],
            [
                self::ROUTE_MAIN => [
                    self::ST_UPPSALA,
                    self::ST_FYRISLUND,
                    self::ST_MARIELUND,
                    self::ST_FARINGE,
                ],
            ],
            [11 => self::ROUTE_MAIN, 22 => self::ROUTE_MAIN]
        );

        $results = MRT_find_multi_leg_connections(
            self::ST_UPPSALA,
            self::ST_FYRISLUND,
            self::DATE,
            5,
            true
        );

        self::assertSame( [], $results );
    }

    public function test_find_multi_leg_rejects_transfer_in_opposite_direction(): void {
        $mid  = 101;
        $goal = 303;

        $this->mrt_use_journey_fixture(
            [
                11 => [
                    $this->mrt_stop(11, $mid, 1, null, '09:00'),
                    $this->mrt_stop(11, self::ST_UPPSALA, 2, '10:00', null),
                ],
                22 => [
                    $this->mrt_stop(22, self::ST_UPPSALA, 1, null, '10:10'),
                    $this->mrt_stop(22, $mid, 2, '10:30', null),
                    $this->mrt_stop(22, $goal, 3, '11:00', null),
                ],
            ],
            [900 => [self::DATE]],
            [],
            $this->mrt_hub_station_meta(self::ST_UPPSALA),
            [],
            [self::ROUTE_MAIN => [self::ST_UPPSALA, $mid, $goal]],
            [11 => self::ROUTE_MAIN, 22 => self::ROUTE_MAIN]
        );

        $results = MRT_find_multi_leg_connections($mid, $goal, self::DATE, 5, false);

        self::assertSame([], $results);
    }

    public function test_find_multi_leg_returns_transfer_at_shared_station(): void {
        $this->mrt_use_journey_fixture($this->transferRows('10:10'), [900 => [self::DATE]], [], $this->mrt_hub_station_meta(self::X));

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE, 5, false);

        self::assertCount(1, $results);
        self::assertSame('transfer', $results[0]['connection_type']);
        self::assertSame(self::X, $results[0]['transfer_station_id']);
        self::assertSame([11, 22], array_column($results[0]['legs'], 'service_id'));
    }

    public function test_find_multi_leg_rejects_transfer_before_minimum_time(): void {
        $this->mrt_use_journey_fixture($this->transferRows('10:04'), [900 => [self::DATE]], [], $this->mrt_hub_station_meta(self::X));

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE, 5, false);

        self::assertSame([], $results);
    }

    public function test_find_multi_leg_accepts_three_minute_transfer_at_default_minimum(): void {
        $this->mrt_use_journey_fixture($this->transferRows('10:03'), [900 => [self::DATE]], [], $this->mrt_hub_station_meta(self::X));

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE, 3, false);

        self::assertCount(1, $results);
        self::assertSame('10:03', $results[0]['legs'][1]['from_departure']);
    }

    public function test_find_multi_leg_accepts_transfer_at_minimum_time(): void {
        $this->mrt_use_journey_fixture($this->transferRows('10:05'), [900 => [self::DATE]], [], $this->mrt_hub_station_meta(self::X));

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

    public function test_journey_transfer_wait_rejects_long_hub_wait(): void {
        self::assertFalse(MRT_journey_transfer_wait_is_valid('10:00', '14:00'));
        self::assertTrue(MRT_journey_transfer_wait_is_valid('10:00', '10:30'));
    }

    public function test_find_multi_leg_rejects_transfer_above_max_wait(): void {
        $this->mrt_use_journey_fixture($this->transferRows('12:30'), [900 => [self::DATE]], [], $this->mrt_hub_station_meta(self::X));

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE, 5, false);

        self::assertSame([], $results);
    }

    public function test_find_multi_leg_prefers_bus_hub_transfer_station(): void {
        $hub = 250;
        $other = 251;
        $this->mrt_use_journey_fixture([
            11 => [
                $this->mrt_stop(11, self::A, 1, null, '09:00'),
                $this->mrt_stop(11, $other, 2, '10:00', null),
            ],
            12 => [
                $this->mrt_stop(12, self::A, 1, null, '09:00'),
                $this->mrt_stop(12, $hub, 2, '10:00', null),
            ],
            21 => [
                $this->mrt_stop(21, $other, 1, null, '10:20'),
                $this->mrt_stop(21, self::B, 2, '11:00', null),
            ],
            22 => [
                $this->mrt_stop(22, $hub, 1, null, '10:10'),
                $this->mrt_stop(22, self::B, 2, '11:00', null),
            ],
        ], [900 => [self::DATE]], [], [
            $hub => ['mrt_station_bus_suffix' => '1'],
        ]);

        $results = MRT_find_multi_leg_connections(self::A, self::B, self::DATE, 5, false);

        self::assertCount(1, $results);
        self::assertSame($hub, $results[0]['transfer_station_id']);
    }

    public function test_find_multi_leg_skips_transfer_at_non_hub_intermediate_stop(): void {
        $mid = 252;
        $this->mrt_use_journey_fixture(
            array(
                11 => array(
                    $this->mrt_stop( 11, self::A, 1, null, '09:00' ),
                    $this->mrt_stop( 11, $mid, 2, '09:30', null ),
                    $this->mrt_stop( 11, self::B, 3, '10:30', null ),
                ),
                22 => array(
                    $this->mrt_stop( 22, $mid, 1, null, '09:45' ),
                    $this->mrt_stop( 22, self::B, 2, '10:00', null ),
                ),
            ),
            array( 900 => array( self::DATE ) )
        );

        $results = MRT_find_multi_leg_connections( self::A, self::B, self::DATE, 5, false );

        self::assertSame( array(), $results );
    }

    public function test_find_multi_leg_allows_transfer_at_marked_hub_stop(): void {
        $mid = 252;
        $this->mrt_use_journey_fixture(
            array(
                11 => array(
                    $this->mrt_stop( 11, self::A, 1, null, '09:00' ),
                    $this->mrt_stop( 11, $mid, 2, '09:30', null ),
                    $this->mrt_stop( 11, self::B, 3, '10:30', null ),
                ),
                22 => array(
                    $this->mrt_stop( 22, $mid, 1, null, '09:45' ),
                    $this->mrt_stop( 22, self::B, 2, '10:00', null ),
                ),
            ),
            array( 900 => array( self::DATE ) ),
            array(),
            $this->mrt_hub_station_meta( $mid )
        );

        $results = MRT_find_multi_leg_connections( self::A, self::B, self::DATE, 5, false );

        self::assertCount( 1, $results );
        self::assertSame( 'transfer', $results[0]['connection_type'] );
        self::assertSame( $mid, $results[0]['transfer_station_id'] );
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
        ], [], [
            900 => 'green',
            901 => 'yellow',
        ]);

        $month = MRT_get_journey_calendar_month(self::A, self::B, 2026, 6);

        self::assertSame('ok', $month['2026-06-01']['status'] ?? '');
        self::assertSame('green', $month['2026-06-01']['type'] ?? '');
        self::assertSame('traffic_no_match', $month['2026-06-02']['status'] ?? '');
        self::assertSame('yellow', $month['2026-06-02']['type'] ?? '');
        self::assertSame('none', $month['2026-06-03']['status'] ?? '');
        self::assertSame('', $month['2026-06-03']['type'] ?? '');
    }

    public function test_journey_calendar_month_return_marks_one_way_only_days_as_traffic_no_match(): void {
        $this->mrt_use_journey_fixture(
            [
                11 => [
                    $this->mrt_stop( 11, self::A, 1, null, '09:00' ),
                    $this->mrt_stop( 11, self::B, 2, '10:00', null ),
                ],
                22 => [
                    $this->mrt_stop( 22, self::B, 1, null, '11:00' ),
                    $this->mrt_stop( 22, self::A, 2, '12:00', null ),
                ],
                33 => [
                    $this->mrt_stop( 33, self::A, 1, null, '17:00' ),
                    $this->mrt_stop( 33, self::B, 2, '18:07', null ),
                ],
            ],
            [
                900 => [ '2026-06-01' ],
                901 => [ '2026-06-02' ],
            ],
            [
                11 => 900,
                22 => 900,
                33 => 901,
            ]
        );

        $single = MRT_get_journey_calendar_month( self::A, self::B, 2026, 6, 'single' );
        $return = MRT_get_journey_calendar_month( self::A, self::B, 2026, 6, 'return' );

        self::assertSame( 'ok', $single['2026-06-01']['status'] ?? '' );
        self::assertSame( 'ok', $single['2026-06-02']['status'] ?? '' );
        self::assertSame( 'ok', $return['2026-06-01']['status'] ?? '' );
        self::assertSame( 'traffic_no_match', $return['2026-06-02']['status'] ?? '' );
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
