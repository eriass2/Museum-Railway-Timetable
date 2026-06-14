<?php
/**
 * Timetable overview shortcode (inc/public/timetable-overview/shortcode.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../helpers/VueShortcodeTestBootstrap.php';
MRT_test_boot_timetable_overview_shortcode();

final class TimetableOverviewShortcodeTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_vue_mount'], $GLOBALS['mrt_test_wp_query_posts'] );
		parent::tearDown();
	}

	public function test_rejects_missing_timetable(): void {
		$html = MRT_render_shortcode_overview( array() );

		self::assertStringContainsString( 'mrt-alert-error', $html );
		self::assertStringContainsString( 'Timetable not found', $html );
	}

	public function test_renders_vue_mount_for_timetable_id(): void {
		$html = MRT_render_shortcode_overview( array( 'timetable_id' => '42' ) );

		self::assertStringContainsString( 'mrt-vue-mount', $html );
		self::assertSame( 'overview', $GLOBALS['mrt_test_vue_mount']['app'] ?? '' );
		self::assertSame( 42, $GLOBALS['mrt_test_vue_mount']['config']['timetableId'] ?? 0 );
	}

	public function test_resolves_timetable_by_title(): void {
		$post = new WP_Post(
			(object) array(
				'ID'         => 99,
				'post_title' => 'Green 2026',
				'post_type'  => MRT_POST_TYPE_TIMETABLE,
			)
		);
		$GLOBALS['mrt_test_wp_query_posts'] = array( $post );

		$html = MRT_render_shortcode_overview( array( 'timetable' => 'Green 2026' ) );

		self::assertStringContainsString( 'mrt-vue-mount', $html );
		self::assertSame( 99, $GLOBALS['mrt_test_vue_mount']['config']['timetableId'] ?? 0 );
	}
}
