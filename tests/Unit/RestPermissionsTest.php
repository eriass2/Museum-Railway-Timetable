<?php
/**
 * REST permission callbacks and request input helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/inc/infrastructure/rest/permissions.php';

final class RestPermissionsTest extends TestCase
{
	protected function tearDown(): void
	{
		unset( $GLOBALS['mrt_test_current_user_can'], $GLOBALS['mrt_test_current_user_can_calls'] );
		parent::tearDown();
	}

	public function test_can_read_allows_edit_posts(): void
	{
		$GLOBALS['mrt_test_current_user_can'] = static function ( string $cap ): bool {
			return $cap === 'edit_posts';
		};

		self::assertTrue( MRT_rest_can_read() );
	}

	public function test_can_manage_requires_manage_options(): void
	{
		$GLOBALS['mrt_test_current_user_can'] = static function ( string $cap ): bool {
			return $cap === 'edit_posts';
		};

		self::assertFalse( MRT_rest_can_manage() );
	}

	public function test_can_edit_operations_allows_edit_posts(): void
	{
		$GLOBALS['mrt_test_current_user_can'] = static function ( string $cap ): bool {
			return $cap === 'edit_posts';
		};

		self::assertTrue( MRT_rest_can_edit_operations() );
	}

	public function test_can_read_public_allows_admin_without_nonce(): void
	{
		$GLOBALS['mrt_test_current_user_can'] = static function ( string $cap ): bool {
			return $cap === 'edit_posts';
		};
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/journey/search' );

		self::assertTrue( MRT_rest_can_read_public( $request ) );
	}

	public function test_verify_public_nonce_returns_true_for_valid_header(): void
	{
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/timetables/1/overview' );
		$request->set_header( 'X-WP-Nonce', 'unit-test-nonce' );

		self::assertTrue( MRT_rest_verify_public_nonce( $request ) );
	}

	public function test_verify_public_nonce_returns_false_for_empty_nonce(): void
	{
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/timetables/1/overview' );

		self::assertFalse( MRT_rest_verify_public_nonce( $request ) );
	}

	public function test_verify_public_nonce_from_wpnonce_param(): void
	{
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/timetables/1/overview' );
		$request->set_param( '_wpnonce', 'unit-test-nonce' );

		self::assertTrue( MRT_rest_verify_public_nonce( $request ) );
	}

	public function test_verify_public_nonce_rejects_invalid_nonce(): void
	{
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/timetables/1/overview' );
		$request->set_header( 'X-WP-Nonce', 'bad-nonce' );

		self::assertFalse( MRT_rest_verify_public_nonce( $request ) );
	}

	public function test_request_input_prefers_json_body(): void
	{
		$request = new WP_REST_Request( 'POST', '/museum-railway-timetable/v1/journey/search' );
		$request->set_param( 'from_station', '1' );
		$request->set_json_params(
			array(
				'from_station' => '12',
				'to_station'   => '34',
			)
		);

		self::assertSame(
			array(
				'from_station' => '12',
				'to_station'   => '34',
			),
			MRT_rest_request_input( $request )
		);
	}

	public function test_request_input_falls_back_to_query_params(): void
	{
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/timetables/day' );
		$request->set_param( 'date', '2026-07-04' );

		self::assertSame( array( 'date' => '2026-07-04' ), MRT_rest_request_input( $request ) );
	}
}
