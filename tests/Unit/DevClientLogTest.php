<?php
/**
 * Dev client-log REST handler.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'MRT_is_development_mode' ) ) {
	function MRT_is_development_mode(): bool {
		return true;
	}
}

require_once ABSPATH . 'inc/infrastructure/rest/dev-tools.php';

final class DevClientLogTest extends TestCase {

	public function test_sanitize_client_log_context_keeps_scalars(): void {
		$context = MRT_rest_sanitize_client_log_context(
			array(
				'status' => 500,
				'path'   => 'stations',
				'nested' => array( 'a' => 1 ),
			)
		);

		self::assertSame( 500, $context['status'] );
		self::assertSame( 'stations', $context['path'] );
		self::assertSame( '{"a":1}', $context['nested'] );
	}

	public function test_client_log_handler_rejects_empty_message(): void {
		$request = new WP_REST_Request( 'POST', '/museum-railway-timetable/v1/dev/client-log' );
		$request->set_json_params( array( 'message' => '' ) );

		$result = MRT_rest_dev_client_log_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid', $result->get_error_code() );
	}

	public function test_client_log_handler_accepts_message(): void {
		$request = new WP_REST_Request( 'POST', '/museum-railway-timetable/v1/dev/client-log' );
		$request->set_json_params(
			array(
				'message' => 'REST POST stations failed',
				'source'  => 'admin',
				'level'   => 'error',
				'context' => array( 'status' => 500 ),
			)
		);

		$response = MRT_rest_dev_client_log_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;

		self::assertIsArray( $data );
		self::assertTrue( $data['logged'] );
	}
}
