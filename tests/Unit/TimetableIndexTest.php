<?php
/**
 * Timetable index helpers and markup.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TimetableIndexTest extends TestCase {

	public static function setUpBeforeClass(): void {
		require_once ABSPATH . 'inc/public/timetable-index/shortcode.php';
	}

	public function test_traffic_days_summary_single_date(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|mrt_timetable_dates' => array( '2026-05-04' ),
		);
		$GLOBALS['mrt_test_options'] = array( 'date_format' => 'Y-m-d' );

		self::assertSame(
			'1 traffic day · 2026-05-04',
			MRT_timetable_traffic_days_summary( 5 )
		);

		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_options'] );
	}

	public function test_traffic_days_summary_span(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|mrt_timetable_dates' => array( '2026-09-28', '2026-05-04' ),
		);
		$GLOBALS['mrt_test_options'] = array( 'date_format' => 'Y-m-d' );

		self::assertSame(
			'2 traffic days · 2026-05-04 – 2026-09-28',
			MRT_timetable_traffic_days_summary( 5 )
		);

		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_options'] );
	}

	public function test_color_modifier_from_code(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'3|mrt_timetable_code' => 'yellow',
		);
		self::assertSame( 'yellow', MRT_timetable_index_color_modifier( 3 ) );
		unset( $GLOBALS['mrt_test_post_meta'] );
	}

	public function test_index_html_stacks_title_and_meta(): void {
		$html = MRT_render_timetable_index_html(
			array(
				array(
					'url'       => 'http://example.test/tidtabell-green/',
					'label'     => 'GRÖN TIDTABELL 2026',
					'meta'      => '15 traffic days · 2026-05-04 – 2026-09-28',
					'modifier'  => 'green',
					'aria_hint' => '15 traffic days · 2026-05-04 – 2026-09-28',
				),
			),
			false
		);

		self::assertStringContainsString( 'mrt-timetable-index__title', $html );
		self::assertStringContainsString( 'mrt-timetable-index__meta', $html );
		self::assertStringContainsString( 'mrt-timetable-index__item--green', $html );
		self::assertStringNotContainsString( '202615', $html );
	}
}
