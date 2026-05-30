<?php
/**
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/inc/infrastructure/rest/permissions.php';

final class RestPublicPermissionsTest extends TestCase
{
	public function test_verify_public_nonce_returns_bool_when_nonce_valid(): void
	{
		$nonce   = wp_create_nonce( 'wp_rest' );
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/timetables/1/overview' );
		$request->set_header( 'X-WP-Nonce', $nonce );

		$result = MRT_rest_verify_public_nonce( $request );

		$this->assertIsBool( $result );
		$this->assertTrue( $result );
	}

	public function test_verify_public_nonce_returns_false_for_empty_nonce(): void
	{
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/timetables/1/overview' );

		$this->assertFalse( MRT_rest_verify_public_nonce( $request ) );
	}
}
