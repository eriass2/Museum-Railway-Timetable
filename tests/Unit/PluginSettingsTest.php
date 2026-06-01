<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/inc/infrastructure/wordpress/plugin-settings.php';

final class PluginSettingsTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_options'] );
		parent::tearDown();
	}

	public function test_default_transfer_limits(): void {
		$defaults = MRT_default_plugin_settings();
		self::assertSame( 3, $defaults['min_transfer_minutes'] );
		self::assertSame( 120, $defaults['max_transfer_minutes'] );
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
}
