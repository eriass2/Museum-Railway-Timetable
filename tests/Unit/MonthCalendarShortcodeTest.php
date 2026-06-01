<?php
/**
 * Month shortcode defaults (G5: show_counts off by default).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MonthCalendarShortcodeTest extends TestCase {
	protected function setUp(): void {
		require_once ABSPATH . 'inc/public/month-calendar/shortcode.php';
		parent::setUp();
	}

	public function test_shortcode_defaults_show_counts_off(): void {
		$context = MRT_month_shortcode_build_context( array() );
		self::assertIsArray( $context );
		self::assertSame( 0, (int) $context['atts']['show_counts'] );
	}

	public function test_shortcode_accepts_show_counts_on(): void {
		$context = MRT_month_shortcode_build_context( array( 'show_counts' => 1 ) );
		self::assertIsArray( $context );
		self::assertSame( 1, (int) $context['atts']['show_counts'] );
	}

	public function test_vue_month_config_preserves_show_counts_att(): void {
		$context = MRT_month_shortcode_build_context( array( 'show_counts' => 0 ) );
		self::assertIsArray( $context );
		require_once ABSPATH . 'inc/public/vue-shortcode-config.php';
		$config = MRT_vue_month_config( $context );
		self::assertSame( 0, (int) ( $config['atts']['show_counts'] ?? -1 ) );
	}
}
