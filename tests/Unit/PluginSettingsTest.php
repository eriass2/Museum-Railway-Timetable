<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/wp-stubs.php';
require_once dirname(__DIR__, 2) . '/inc/infrastructure/wordpress/plugin-settings.php';

final class PluginSettingsTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_options'], $GLOBALS['mrt_test_filters'] );
		parent::tearDown();
	}

	public function test_default_transfer_limits(): void {
		$defaults = MRT_default_plugin_settings();
		self::assertFalse( $defaults['wizard_beta_enabled'] );
		self::assertFalse( $defaults['wizard_feedback_enabled'] );
		self::assertSame( 0, $defaults['min_transfer_minutes'] );
		self::assertSame( 120, $defaults['max_transfer_minutes'] );
		self::assertSame( 2, $defaults['max_transfers'] );
		self::assertSame( 900, $defaults['afternoon_return_threshold_minutes'] );
	}

	public function test_sanitize_clamps_max_to_min(): void {
		$out = MRT_sanitize_plugin_settings(
			array(
				'enabled'              => '1',
				'min_transfer_minutes' => '30',
				'max_transfer_minutes' => '10',
			)
		);
		self::assertSame( 30, $out['min_transfer_minutes'] );
		self::assertSame( 30, $out['max_transfer_minutes'] );
	}

	public function test_sanitize_operator_and_ticket_url(): void {
		$out = MRT_sanitize_plugin_settings(
			array(
				'operator_name' => '  Test Line  ',
				'ticket_url'    => 'https://example.com/tickets',
			)
		);
		self::assertSame( 'Test Line', $out['operator_name'] );
		self::assertSame( 'https://example.com/tickets', $out['ticket_url'] );
	}

	public function test_plugin_hero_background_url_reads_settings(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'hero_background_url' => 'https://example.test/hero.jpg',
			),
		);

		self::assertSame( 'https://example.test/hero.jpg', MRT_plugin_hero_background_url() );
	}

	public function test_rewrite_localhost_plugin_asset_url_uses_current_mrt_url(): void {
		$stored = 'http://localhost:8080/wp-content/plugins/museum-railway-timetable/testdata/images/wizard-hero-bosshus.jpg';

		self::assertSame(
			'https://example.test/wp-content/plugins/museum-railway-timetable/testdata/images/wizard-hero-bosshus.jpg',
			MRT_rewrite_localhost_plugin_asset_url( $stored )
		);
	}

	public function test_plugin_hero_background_url_rewrites_localhost_in_dev_mode(): void {
		$GLOBALS['mrt_test_filters']['mrt_is_development_mode'] = static fn (): bool => true;

		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'hero_background_url' => 'http://localhost:8080/wp-content/plugins/museum-railway-timetable/testdata/images/wizard-hero-bosshus.jpg',
			),
		);

		self::assertSame(
			'https://example.test/wp-content/plugins/museum-railway-timetable/testdata/images/wizard-hero-bosshus.jpg',
			MRT_plugin_hero_background_url()
		);
	}

	public function test_plugin_wizard_beta_enabled_reads_settings(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'wizard_beta_enabled' => true,
			),
		);

		self::assertTrue( MRT_plugin_wizard_beta_enabled() );
	}

	public function test_plugin_wizard_feedback_enabled_reads_settings(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'wizard_feedback_enabled' => true,
			),
		);

		self::assertTrue( MRT_plugin_wizard_feedback_enabled() );
	}

	public function test_sanitize_clamps_max_transfers_and_afternoon_threshold(): void {
		$out = MRT_sanitize_plugin_settings(
			array(
				'max_transfers'                      => '99',
				'afternoon_return_threshold_minutes' => '5000',
			)
		);
		self::assertSame( 5, $out['max_transfers'] );
		self::assertSame( 1439, $out['afternoon_return_threshold_minutes'] );
	}
}
