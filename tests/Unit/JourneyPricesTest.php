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

    public function test_default_matrix_is_empty(): void {
        $m = MRT_get_default_price_matrix();
        self::assertSame(['single', 'return', 'day'], array_keys($m));
        foreach (MRT_price_ticket_type_keys() as $row) {
            self::assertArrayHasKey($row, $m);
            self::assertSame(MRT_price_category_keys(), array_keys($m[$row]));
            foreach (MRT_price_category_keys() as $cat) {
                self::assertSame(MRT_price_zone_keys(), array_keys($m[$row][$cat]));
                foreach (MRT_price_zone_keys() as $zone) {
                    self::assertNull($m[$row][$cat][$zone]);
                }
            }
        }
    }

    public function test_builtin_reference_matrix_has_lennakatten_taxa(): void {
        $m = MRT_get_builtin_price_matrix();
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

    public function test_sanitize_non_array_input_returns_empty_defaults(): void {
        $out = MRT_sanitize_price_matrix('bad');
        self::assertEquals(MRT_get_default_price_matrix(), $out);
    }

    public function test_get_price_matrix_invalid_option_returns_empty_defaults(): void {
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
        self::assertNull($m['return']['adult']['1']);
    }

    public function test_afternoon_return_prices_match_schema_defaults(): void {
        $prices = MRT_get_afternoon_return_prices();
        self::assertSame(160, $prices['adult']);
        self::assertSame(60, $prices['child_4_15']);
    }

    public function test_price_zone_cap_limits_fare_lookup(): void {
        self::assertSame(3, MRT_price_zone_cap());
        $m = MRT_get_default_price_matrix();
        self::assertNull($m['single']['adult']['1']);
    }
}
