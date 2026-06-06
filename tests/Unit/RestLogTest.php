<?php
/**
 * REST error logging filter.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}

require_once ABSPATH . 'inc/infrastructure/rest/rest-log.php';

final class RestLogTest extends TestCase {

	public function test_should_log_plugin_route_with_server_error(): void {
		$request  = new WP_REST_Request( 'POST', '/museum-railway-timetable/v1/stations' );
		$response = new WP_REST_Response( array( 'code' => 'db_error' ), 500 );

		self::assertTrue( MRT_rest_should_log_response( $request, $response ) );
	}

	public function test_should_not_log_client_errors(): void {
		$request  = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/stations/99' );
		$response = new WP_REST_Response( array( 'code' => 'not_found' ), 404 );

		self::assertFalse( MRT_rest_should_log_response( $request, $response ) );
	}

	public function test_should_not_log_other_namespaces(): void {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = new WP_REST_Response( array( 'code' => 'internal' ), 500 );

		self::assertFalse( MRT_rest_should_log_response( $request, $response ) );
	}

	public function test_should_not_log_client_log_endpoint(): void {
		$request  = new WP_REST_Request( 'POST', '/museum-railway-timetable/v1/dev/client-log' );
		$response = new WP_REST_Response( array( 'code' => 'internal' ), 500 );

		self::assertFalse( MRT_rest_should_log_response( $request, $response ) );
	}

	public function test_log_filter_writes_when_logging_enabled(): void {
		$GLOBALS['mrt_test_filters']['mrt_should_log'] = static fn (): bool => true;
		$request  = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/dashboard' );
		$response = new WP_REST_Response( array( 'message' => 'fail' ), 503 );

		$result = MRT_rest_log_error_response( $response, null, $request );

		self::assertSame( $response, $result );
		unset( $GLOBALS['mrt_test_filters'] );
	}
}
