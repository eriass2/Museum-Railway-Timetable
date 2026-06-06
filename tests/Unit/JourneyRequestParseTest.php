<?php
/**
 * Tests for journey request parameter parsing (REST body).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyRequestParseTest extends TestCase {

    public function test_parse_stations_pair_success(): void {
        $out = MRT_journey_parse_stations_pair([
            'from_station' => '12',
            'to_station'   => '34',
        ]);
        self::assertIsArray($out);
        self::assertSame(12, $out['from']);
        self::assertSame(34, $out['to']);
    }

    public function test_parse_stations_pair_missing_station(): void {
        $out = MRT_journey_parse_stations_pair([
            'from_station' => '1',
            'to_station'   => '0',
        ]);
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_stations', $out->get_error_code());
    }

    public function test_parse_stations_pair_same_station(): void {
        $out = MRT_journey_parse_stations_pair([
            'from_station' => '5',
            'to_station'   => '5',
        ]);
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_same', $out->get_error_code());
    }

    public function test_parse_from_to_date_success(): void {
        $out = MRT_journey_parse_from_to_date([
            'from_station' => '1',
            'to_station'   => '2',
            'date'         => '2026-06-15',
        ]);
        self::assertIsArray($out);
        self::assertSame('2026-06-15', $out['date']);
    }

    public function test_parse_from_to_date_invalid_date(): void {
        $out = MRT_journey_parse_from_to_date([
            'from_station' => '1',
            'to_station'   => '2',
            'date'         => '15-06-2026',
        ]);
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_date', $out->get_error_code());
    }

    public function test_parse_trip_search_single(): void {
        $out = MRT_journey_parse_trip_search_params([
            'from_station' => '1',
            'to_station'   => '2',
            'date'         => '2026-01-20',
            'trip_type'    => 'single',
        ]);
        self::assertIsArray($out);
        self::assertSame('single', $out['trip_type']);
        self::assertArrayNotHasKey('outbound_arrival', $out);
    }

    public function test_parse_trip_search_return_requires_arrival(): void {
        $out = MRT_journey_parse_trip_search_params([
            'from_station'      => '1',
            'to_station'        => '2',
            'date'              => '2026-01-20',
            'trip_type'         => 'return',
            'outbound_arrival'  => '',
        ]);
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_return_arrival', $out->get_error_code());
    }

    public function test_parse_trip_search_return_success(): void {
        $out = MRT_journey_parse_trip_search_params([
            'from_station'           => '1',
            'to_station'             => '2',
            'date'                   => '2026-01-20',
            'trip_type'              => 'return',
            'outbound_arrival'       => '14:30',
            'outbound_service_id'    => '7',
            'min_turnaround_minutes' => '15',
        ]);
        self::assertIsArray($out);
        self::assertSame('return', $out['trip_type']);
        self::assertSame('14:30', $out['outbound_arrival']);
        self::assertSame(7, $out['outbound_service_id']);
        self::assertSame(15, $out['min_turnaround_minutes']);
    }

    public function test_parse_trip_search_return_default_turnaround(): void {
        $out = MRT_journey_parse_trip_search_params([
            'from_station'     => '1',
            'to_station'       => '2',
            'date'             => '2026-01-20',
            'trip_type'        => 'return',
            'outbound_arrival' => '14:30',
        ]);
        self::assertIsArray($out);
        self::assertSame(MRT_journey_min_transfer_minutes(), $out['min_turnaround_minutes']);
    }

    public function test_parse_calendar_month_params(): void {
        $out = MRT_journey_parse_calendar_month_params([
            'from_station' => '3',
            'to_station'   => '4',
            'year'         => '2026',
            'month'        => '4',
        ]);
        self::assertIsArray($out);
        self::assertSame(2026, $out['year']);
        self::assertSame(4, $out['month']);
        self::assertSame('single', $out['trip_type']);
    }

    public function test_parse_calendar_month_params_return_trip_type(): void {
        $out = MRT_journey_parse_calendar_month_params([
            'from_station' => '3',
            'to_station'   => '4',
            'year'         => '2026',
            'month'        => '4',
            'trip_type'    => 'return',
        ]);
        self::assertIsArray($out);
        self::assertSame('return', $out['trip_type']);
    }

    public function test_parse_calendar_month_invalid_range(): void {
        $out = MRT_journey_parse_calendar_month_params([
            'from_station' => '1',
            'to_station'   => '2',
            'year'         => '2026',
            'month'        => '13',
        ]);
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_calendar_month_range', $out->get_error_code());
    }

    public function test_parse_connection_detail_params(): void {
        $out = MRT_journey_parse_connection_detail_params([
            'from_station' => '1',
            'to_station'   => '2',
            'service_id'   => '88',
        ]);
        self::assertIsArray($out);
        self::assertSame(88, $out['service_id']);
        self::assertSame('', $out['date']);
    }

    public function test_parse_connection_detail_accepts_optional_date(): void {
        $out = MRT_journey_parse_connection_detail_params([
            'from_station' => '1',
            'to_station'   => '2',
            'service_id'   => '88',
            'date'         => '2026-06-06',
        ]);
        self::assertIsArray($out);
        self::assertSame('2026-06-06', $out['date']);
    }

    public function test_parse_connection_detail_rejects_invalid_date(): void {
        $out = MRT_journey_parse_connection_detail_params([
            'from_station' => '1',
            'to_station'   => '2',
            'service_id'   => '88',
            'date'         => 'not-a-date',
        ]);
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_date', $out->get_error_code());
    }

    public function test_parse_connection_detail_invalid_service(): void {
        $out = MRT_journey_parse_connection_detail_params([
            'from_station' => '1',
            'to_station'   => '2',
            'service_id'   => '0',
        ]);
        self::assertInstanceOf(WP_Error::class, $out);
        self::assertSame('mrt_journey_service', $out->get_error_code());
    }
}
