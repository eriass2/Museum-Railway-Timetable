<?php
/**
 * Traffic notices shortcode tests.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/public/traffic-notices/shortcode.php';
require_once ABSPATH . 'inc/domain/timetable/timetable-pages.php';

final class TrafficNoticesShortcodeTest extends TestCase {
	protected function tearDown(): void {
		delete_option( MRT_OPTION_PUBLIC_NOTICES );
		parent::tearDown();
	}

	public function test_build_context_defaults_horizon_days_to_ninety(): void {
		$context = MRT_traffic_notices_build_context( array() );
		self::assertSame( '90', $context['atts']['horizon_days'] );
		self::assertTrue( $context['payload']['is_empty'] );
	}

	public function test_traffic_disruptions_page_content_includes_shortcode(): void {
		$content = MRT_traffic_disruptions_page_content();
		self::assertStringContainsString( '[museum_traffic_notices', $content );
	}

	public function test_render_html_shows_empty_message(): void {
		$html = MRT_render_traffic_notices_html(
			array(
				'is_empty' => true,
				'ongoing'  => array(),
				'upcoming' => array(),
			)
		);
		self::assertStringContainsString( 'mrt-traffic-notices__empty', $html );
	}

	public function test_render_html_includes_feed_item(): void {
		$html = MRT_render_traffic_notices_html(
			array(
				'is_empty' => false,
				'ongoing'  => array(
					array(
						'id'          => 'notice-1',
						'kind'        => 'info',
						'date_label'  => '6 Jun 2026',
						'headline'    => 'Baninfo',
						'body'        => 'Baninfo',
					),
				),
				'upcoming' => array(),
			)
		);
		self::assertStringContainsString( 'Baninfo', $html );
		self::assertStringContainsString( 'mrt-traffic-notices__section-title', $html );
	}
}
