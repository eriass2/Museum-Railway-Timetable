<?php
/**
 * Traffic notices shortcode tests.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/public/traffic-notices/shortcode.php';

final class TrafficNoticesShortcodeTest extends TestCase {
	protected function tearDown(): void {
		delete_option( 'mrt_public_notices' );
		parent::tearDown();
	}

	public function test_build_context_defaults_days_to_one(): void {
		$context = MRT_traffic_notices_build_context( array() );
		self::assertSame( '1', $context['atts']['days'] );
		self::assertTrue( $context['payload']['is_empty'] );
	}

	public function test_render_html_shows_empty_message(): void {
		$html = MRT_render_traffic_notices_html(
			array(
				'is_empty' => true,
				'general'  => array(),
				'by_date'  => array(),
			)
		);
		self::assertStringContainsString( 'mrt-traffic-notices__empty', $html );
	}

	public function test_render_html_includes_general_notice(): void {
		$html = MRT_render_traffic_notices_html(
			array(
				'is_empty' => false,
				'general'  => array(
					array(
						'id'   => '1',
						'text' => 'Baninfo',
					),
				),
				'by_date'  => array(),
				'days'     => 1,
			)
		);
		self::assertStringContainsString( 'Baninfo', $html );
	}
}
