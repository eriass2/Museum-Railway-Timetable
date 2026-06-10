<?php
/**
 * Journey wizard shortcode (inc/public/journey-wizard/shell.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'MRT_render_vue_mount' ) ) {
	function MRT_render_vue_mount( string $app, array $config ): string {
		$GLOBALS['mrt_test_vue_mount'] = array(
			'app'    => $app,
			'config' => $config,
		);
		return '<div class="mrt-vue-mount"></div>';
	}
}

if ( ! function_exists( 'MRT_journey_wizard_debug_presets' ) ) {
	/**
	 * @return array<string, array<string, mixed>>
	 */
	function MRT_journey_wizard_debug_presets(): array {
		return array(
			'date'     => array(),
			'outbound' => array(),
		);
	}
}

require_once ABSPATH . 'inc/public/journey-wizard/timetable.php';
require_once ABSPATH . 'inc/public/journey-wizard/shell.php';
require_once ABSPATH . 'inc/public/vue-shortcode-config.php';

final class JourneyWizardShortcodeTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_vue_mount'], $GLOBALS['mrt_test_wp_query_posts'], $GLOBALS['mrt_test_filters'], $GLOBALS['mrt_test_options'] );
		parent::tearDown();
	}

	public function test_parse_shortcode_atts_defaults(): void {
		$parsed = MRT_journey_wizard_parse_shortcode_atts( array() );

		self::assertSame( '', $parsed['ticket_url'] );
		self::assertSame( '', $parsed['route_title'] );
		self::assertSame( '', $parsed['hero_subtitle'] );
		self::assertSame( '', $parsed['hero_background_url'] );
		self::assertSame( 0, $parsed['timetable_id'] );
		self::assertSame( '', $parsed['timetable_page_url'] );
		self::assertFalse( $parsed['embedded'] );
		self::assertSame( '', $parsed['debug'] );
	}

	public function test_shortcode_bool_parses_common_truthy_values(): void {
		self::assertTrue( MRT_journey_wizard_shortcode_bool( '1' ) );
		self::assertTrue( MRT_journey_wizard_shortcode_bool( 'yes' ) );
		self::assertFalse( MRT_journey_wizard_shortcode_bool( '0' ) );
		self::assertFalse( MRT_journey_wizard_shortcode_bool( 'off' ) );
	}

	public function test_parse_shortcode_atts_trims_route_title_and_escapes_urls(): void {
		$parsed = MRT_journey_wizard_parse_shortcode_atts(
			array(
				'ticket_url'         => 'https://example.test/tickets',
				'route_title'        => '  Min linje  ',
				'timetable_page_url' => 'https://example.test/timetable',
				'embedded'           => 'yes',
				'timetable_id'       => '7',
			)
		);

		self::assertSame( 'https://example.test/tickets', $parsed['ticket_url'] );
		self::assertSame( 'Min linje', $parsed['route_title'] );
		self::assertSame( 'https://example.test/timetable', $parsed['timetable_page_url'] );
		self::assertTrue( $parsed['embedded'] );
		self::assertSame( 7, $parsed['timetable_id'] );
	}

	public function test_sanitize_debug_attr_empty_outside_dev_mode(): void {
		self::assertSame( '', MRT_journey_wizard_sanitize_debug_attr( 'date' ) );
	}

	public function test_sanitize_debug_attr_accepts_allowed_preset_in_dev_mode(): void {
		$GLOBALS['mrt_test_filters']['mrt_is_development_mode'] = static fn (): bool => true;
		self::assertSame( 'date', MRT_journey_wizard_sanitize_debug_attr( 'date' ) );
		self::assertSame( '', MRT_journey_wizard_sanitize_debug_attr( 'invalid' ) );
	}

	public function test_resolve_timetable_id_by_title(): void {
		$post = new WP_Post(
			(object) array(
				'ID'         => 55,
				'post_title' => 'Green 2026',
				'post_type'  => MRT_POST_TYPE_TIMETABLE,
			)
		);
		$GLOBALS['mrt_test_wp_query_posts'] = array( $post );

		$parsed = MRT_journey_wizard_parse_shortcode_atts( array( 'timetable' => 'Green 2026' ) );
		self::assertSame( 55, $parsed['timetable_id'] );
	}

	public function test_vue_wizard_beta_banner_null_when_disabled(): void {
		self::assertNull( MRT_vue_wizard_beta_banner( array() ) );
	}

	public function test_vue_wizard_beta_banner_when_admin_setting_enabled(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'wizard_beta_enabled' => true,
			),
		);

		$banner = MRT_vue_wizard_beta_banner( array() );

		self::assertIsArray( $banner );
		self::assertSame( 'Beta', $banner['label'] );
		self::assertStringContainsString( 'testas', strtolower( (string) $banner['text'] ) );
		self::assertArrayNotHasKey( 'feedbackUrl', $banner );
	}

	public function test_render_shortcode_mounts_wizard_app(): void {
		$html = MRT_render_shortcode_journey_wizard(
			array(
				'route_title'  => 'Test wizard',
				'timetable_id' => '12',
				'embedded'     => '1',
			)
		);

		self::assertStringContainsString( 'mrt-vue-mount', $html );
		self::assertSame( 'wizard', $GLOBALS['mrt_test_vue_mount']['app'] ?? '' );
		self::assertSame( 12, $GLOBALS['mrt_test_vue_mount']['config']['timetableId'] ?? 0 );
		self::assertTrue( $GLOBALS['mrt_test_vue_mount']['config']['embedded'] ?? false );
		self::assertSame( 'Test wizard', $GLOBALS['mrt_test_vue_mount']['config']['labels']['routeTitle'] ?? '' );
		self::assertNull( $GLOBALS['mrt_test_vue_mount']['config']['betaBanner'] ?? null );
	}

	public function test_parse_shortcode_atts_escapes_hero_background_url(): void {
		$parsed = MRT_journey_wizard_parse_shortcode_atts(
			array(
				'hero_background_url' => 'https://example.test/hero.jpg',
			)
		);

		self::assertSame( 'https://example.test/hero.jpg', $parsed['hero_background_url'] );
	}

	public function test_parse_shortcode_uses_hero_background_from_settings(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'hero_background_url' => 'https://example.test/from-settings.jpg',
			),
		);

		$parsed = MRT_journey_wizard_parse_shortcode_atts( array() );

		self::assertSame( 'https://example.test/from-settings.jpg', $parsed['hero_background_url'] );
	}

	public function test_parse_shortcode_attr_overrides_settings_hero_background(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'hero_background_url' => 'https://example.test/from-settings.jpg',
			),
		);

		$parsed = MRT_journey_wizard_parse_shortcode_atts(
			array(
				'hero_background_url' => 'https://example.test/from-shortcode.jpg',
			)
		);

		self::assertSame( 'https://example.test/from-shortcode.jpg', $parsed['hero_background_url'] );
	}

	public function test_render_shortcode_passes_hero_background_url(): void {
		MRT_render_shortcode_journey_wizard(
			array(
				'hero_background_url' => 'https://example.test/hero.jpg',
			)
		);

		self::assertSame(
			'https://example.test/hero.jpg',
			$GLOBALS['mrt_test_vue_mount']['config']['heroBackgroundUrl'] ?? ''
		);
	}

	public function test_render_shortcode_passes_beta_banner_from_settings(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'wizard_beta_enabled' => true,
			),
		);

		MRT_render_shortcode_journey_wizard( array() );

		$banner = $GLOBALS['mrt_test_vue_mount']['config']['betaBanner'] ?? null;
		self::assertIsArray( $banner );
		self::assertSame( 'Beta', $banner['label'] ?? '' );
	}
}
