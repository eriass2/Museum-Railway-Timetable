<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/wp-stubs.php';
require_once dirname(__DIR__, 2) . '/inc/infrastructure/wordpress/plugin-settings.php';

final class PluginSettingsTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_options'] );
		parent::tearDown();
	}

	public function test_default_transfer_limits(): void {
		$defaults = MRT_default_plugin_settings();
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
