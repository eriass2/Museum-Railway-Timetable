<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/wp-stubs.php';
require_once dirname(__DIR__, 2) . '/inc/infrastructure/wordpress/dev-localhost-url.php';

final class EnvironmentDevUrlTest extends TestCase {

	protected function tearDown(): void {
		unset( $_SERVER['HTTP_HOST'], $GLOBALS['mrt_test_options'], $GLOBALS['mrt_test_filters'] );
		parent::tearDown();
	}

	public function test_rewrite_localhost_dev_url_uses_request_port(): void {
		$GLOBALS['mrt_test_filters']['mrt_is_development_mode'] = static fn (): bool => true;
		$_SERVER['HTTP_HOST']                                   = 'localhost:8089';

		self::assertSame(
			'http://localhost:8089/museum-railway-timetable-component-demo/',
			MRT_rewrite_localhost_dev_url( 'http://localhost:8080/museum-railway-timetable-component-demo/' )
		);
	}

	public function test_rewrite_localhost_dev_url_leaves_url_when_not_dev(): void {
		$GLOBALS['mrt_test_filters']['mrt_is_development_mode'] = static fn (): bool => false;
		$_SERVER['HTTP_HOST']                                   = 'localhost:8089';

		$url = 'http://localhost:8080/demo/';
		self::assertSame( $url, MRT_rewrite_localhost_dev_url( $url ) );
	}

	public function test_sync_dev_site_url_from_request_updates_options(): void {
		$GLOBALS['mrt_test_filters']['mrt_is_development_mode'] = static fn (): bool => true;
		$_SERVER['HTTP_HOST']                                   = 'localhost:8089';
		$GLOBALS['mrt_test_options']                            = array(
			'home'    => 'http://localhost:8080',
			'siteurl' => 'http://localhost:8080',
		);

		MRT_sync_dev_site_url_from_request();

		self::assertSame( 'http://localhost:8089', get_option( 'home' ) );
		self::assertSame( 'http://localhost:8089', get_option( 'siteurl' ) );
	}

	public function test_nav_menu_filter_rewrites_item_urls(): void {
		$GLOBALS['mrt_test_filters']['mrt_is_development_mode'] = static fn (): bool => true;
		$_SERVER['HTTP_HOST']                                   = 'localhost:8089';

		$item     = (object) array(
			'url' => 'http://localhost:8080/debug-wizard-utresa/',
		);
		$filtered = MRT_filter_nav_menu_localhost_urls( array( $item ) );

		self::assertSame( 'http://localhost:8089/debug-wizard-utresa/', $filtered[0]->url );
	}
}
