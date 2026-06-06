<?php
/**
 * REST client config helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}

if ( ! function_exists( 'rest_url' ) ) {
	/**
	 * @param string $path REST route path.
	 */
	function rest_url( $path = '' ): string {
		return 'https://example.test/wp-json/' . ltrim( (string) $path, '/' );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	/**
	 * @param string $url URL.
	 */
	function esc_url_raw( $url ): string {
		return (string) $url;
	}
}

if ( ! function_exists( 'MRT_is_development_mode' ) ) {
	function MRT_is_development_mode(): bool {
		return false;
	}
}

require_once dirname( __DIR__, 2 ) . '/inc/infrastructure/rest/client-config.php';

final class RestClientConfigTest extends TestCase {

	public function test_rest_client_config_keys(): void {
		$config = MRT_rest_client_config();

		self::assertArrayHasKey( 'restUrl', $config );
		self::assertArrayHasKey( 'restNonce', $config );
		self::assertArrayHasKey( 'isDevMode', $config );
		self::assertFalse( $config['isDevMode'] );
		self::assertStringContainsString( MRT_REST_NAMESPACE, $config['restUrl'] );
		self::assertNotSame( '', $config['restNonce'] );
	}

	public function test_rest_base_url_uses_wordpress_rest_url(): void {
		$base = MRT_rest_base_url();

		self::assertStringStartsWith( 'https://example.test/wp-json/', $base );
		self::assertStringContainsString( MRT_REST_NAMESPACE, $base );
	}
}
