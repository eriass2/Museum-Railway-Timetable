<?php
/**
 * Tests for price matrix helpers (production code: inc/domain/pricing/prices.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Global MRT_* functions from inc/domain/pricing/prices.php (no namespace).
 */
final class JourneyPricesTest extends TestCase {

    protected function tearDown(): void {
        unset($GLOBALS['mrt_test_options']);
        parent::tearDown();
    }

    public function test_default_matrix_shape(): void {
        $m = MRT_get_default_price_matrix();
        self::assertSame(['single', 'return', 'day'], array_keys($m));
        foreach (MRT_price_ticket_type_keys() as $row) {
            self::assertArrayHasKey($row, $m);
            self::assertSame(MRT_price_category_keys(), array_keys($m[$row]));
            foreach (MRT_price_category_keys() as $cat) {
                self::assertSame(MRT_price_zone_keys(), array_keys($m[$row][$cat]));
            }
        }
        self::assertSame(80, $m['single']['adult']['1']);
        self::assertSame(130, $m['single']['adult']['3']);
        self::assertSame(200, $m['return']['student_senior']['2']);
        self::assertSame(60, $m['return']['child_4_15']['2']);
    }

    public function test_sanitize_rejects_negative_to_null(): void {
        $in = MRT_get_default_price_matrix();
        $in['single']['adult']['1'] = -5;
        $out = MRT_sanitize_price_matrix($in);
        self::assertNull($out['single']['adult']['1']);
    }

    public function test_sanitize_accepts_zero(): void {
        $in = MRT_get_default_price_matrix();
        $in['return']['child_4_15']['2'] = 0;
        $out = MRT_sanitize_price_matrix($in);
        self::assertSame(0, $out['return']['child_4_15']['2']);
    }

    public function test_sanitize_empty_string_to_null(): void {
        $in = MRT_get_default_price_matrix();
        $in['day']['student_senior']['4'] = '';
        $out = MRT_sanitize_price_matrix($in);
        self::assertNull($out['day']['student_senior']['4']);
    }

    public function test_sanitize_non_array_input_returns_defaults(): void {
        $out = MRT_sanitize_price_matrix('bad');
        self::assertEquals(MRT_get_default_price_matrix(), $out);
    }

    public function test_get_price_matrix_invalid_option_returns_defaults(): void {
        $GLOBALS['mrt_test_options'] = ['mrt_price_matrix' => 'not-array'];
        $m = MRT_get_price_matrix();
        self::assertEquals(MRT_get_default_price_matrix(), $m);
    }

    public function test_get_price_matrix_merges_stored(): void {
        $GLOBALS['mrt_test_options'] = [
            'mrt_price_matrix' => [
                'single' => ['adult' => ['2' => 100]],
            ],
        ];
        $m = MRT_get_price_matrix();
        self::assertSame(100, $m['single']['adult']['2']);
        self::assertSame(160, $m['return']['adult']['1']);
    }

    public function test_sanitize_legacy_flat_values_apply_to_all_zones(): void {
        $out = MRT_sanitize_price_matrix([
            'single' => ['adult' => 99],
        ]);
        self::assertSame([1 => 99, 2 => 99, 3 => 99, 4 => 99], $out['single']['adult']);
    }

    public function test_get_prices_for_context_active_row(): void {
        $GLOBALS['mrt_test_options'] = [
            'mrt_price_matrix' => [
                'return' => ['adult' => ['3' => 200]],
            ],
        ];
        $ctx = MRT_get_prices_for_context(['trip' => 'return']);
        self::assertSame('return', $ctx['active_ticket_type']);
        self::assertSame(200, $ctx['active_row']['adult']);
        self::assertSame(3, $ctx['active_zone']);
    }

    public function test_get_prices_for_context_invalid_trip_falls_back_to_single(): void {
        $ctx = MRT_get_prices_for_context(['trip' => 'unknown']);
        self::assertSame('single', $ctx['active_ticket_type']);
        self::assertSame(MRT_price_matrix_for_zone($ctx['matrix'], 3)['single'], $ctx['active_row']);
    }

    public function test_price_matrix_for_zone_flattens_selected_zone(): void {
        $m = MRT_get_default_price_matrix();
        $flat = MRT_price_matrix_for_zone($m, 2);
        self::assertSame(110, $flat['single']['adult']);
        self::assertSame(30, $flat['single']['child_4_15']);
    }

    public function test_boundary_station_can_count_as_either_zone(): void {
        self::assertSame(1, MRT_price_zones_between_zone_sets([1, 2], [2]));
        self::assertSame(1, MRT_price_zones_between_zone_sets([2], [2, 3]));
        self::assertSame(2, MRT_price_zones_between_zone_sets([1], [2, 3]));
        self::assertSame(4, MRT_price_zones_between_zone_sets([1], [4]));
    }

    public function test_afternoon_return_qualifies_when_both_legs_after_fifteen(): void {
        self::assertTrue(MRT_qualifies_for_afternoon_return('return', '15:00', '16:30'));
        self::assertFalse(MRT_qualifies_for_afternoon_return('return', '14:59', '16:30'));
        self::assertFalse(MRT_qualifies_for_afternoon_return('single', '15:00', '16:30'));
    }

    public function test_afternoon_return_prices_match_taxa_2026(): void {
        $prices = MRT_get_afternoon_return_prices();
        self::assertSame(160, $prices['adult']);
        self::assertSame(140, $prices['student_senior']);
        self::assertSame(60, $prices['child_4_15']);
    }

    public function test_price_zone_cap_limits_fare_lookup(): void {
        self::assertSame(3, MRT_price_zone_cap());
        $m = MRT_get_default_price_matrix();
        self::assertSame(130, MRT_price_matrix_for_zone($m, 4)['single']['adult']);
    }
}
