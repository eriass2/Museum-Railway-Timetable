<?php
/**
 * Traffic notices REST tests.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}

require_once ABSPATH . 'inc/infrastructure/rest/shared/permissions.php';
require_once ABSPATH . 'inc/infrastructure/rest/public/traffic-notices-public.php';
require_once ABSPATH . 'inc/infrastructure/rest/admin/traffic-notices-admin.php';

final class RestTrafficNoticesTest extends TestCase {
	protected function tearDown(): void {
		delete_option( MRT_OPTION_PUBLIC_NOTICES );
		parent::tearDown();
	}

	public function test_public_handler_rejects_invalid_date(): void {
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/traffic-notices' );
		$request->set_param( 'date', 'not-a-date' );

		$result = MRT_rest_traffic_notices_public_handler( $request );
		self::assertInstanceOf( WP_Error::class, $result );
	}

	public function test_public_handler_returns_general_notices_for_date(): void {
		update_option(
			MRT_OPTION_PUBLIC_NOTICES,
			array(
				array(
					'id'          => 'n1',
					'text'        => 'Banarbete vid station X',
					'enabled'     => true,
					'active_from' => '',
					'active_to'   => '',
					'sort_order'  => 10,
				),
			),
			false
		);

		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/traffic-notices' );
		$request->set_param( 'date', '2026-06-06' );
		$request->set_param( 'show_general', true );
		$request->set_param( 'show_deviations', false );

		$response = MRT_rest_traffic_notices_public_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;

		self::assertSame( '2026-06-06', $data['reference_date'] );
		self::assertFalse( $data['is_empty'] );
		self::assertCount( 1, $data['general'] );
		self::assertSame( 'Banarbete vid station X', $data['general'][0]['text'] );
	}

	public function test_public_handler_clamps_days_between_one_and_two(): void {
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/traffic-notices' );
		$request->set_param( 'date', '2026-06-06' );
		$request->set_param( 'days', 9 );
		$request->set_param( 'show_general', false );
		$request->set_param( 'show_deviations', false );

		$response = MRT_rest_traffic_notices_public_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;

		self::assertSame( 2, $data['days'] );
		self::assertTrue( $data['is_empty'] );
	}

	public function test_public_handler_uses_current_date_when_param_empty(): void {
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/traffic-notices' );
		$request->set_param( 'show_general', false );
		$request->set_param( 'show_deviations', false );

		$response = MRT_rest_traffic_notices_public_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;
		$today    = MRT_get_current_datetime()['date'];

		self::assertSame( $today, $data['reference_date'] );
	}

	public function test_admin_get_returns_saved_messages(): void {
		update_option(
			MRT_OPTION_PUBLIC_NOTICES,
			array(
				array(
					'id'          => 'saved',
					'text'        => 'Serverhallen stängd',
					'enabled'     => true,
					'active_from' => '',
					'active_to'   => '',
					'sort_order'  => 10,
				),
			),
			false
		);

		$response = MRT_rest_traffic_notices_messages_get_handler( new WP_REST_Request( 'GET', '/messages' ) );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;

		self::assertCount( 1, $data['messages'] );
		self::assertSame( 'Serverhallen stängd', $data['messages'][0]['text'] );
	}

	public function test_admin_put_saves_messages(): void {
		$request = new WP_REST_Request( 'PUT', '/museum-railway-timetable/v1/traffic-notices/messages' );
		$request->set_json_params(
			array(
				'messages' => array(
					array(
						'text'        => 'Café öppet',
						'enabled'     => true,
						'active_from' => '',
						'active_to'   => '',
					),
				),
			)
		);

		$response = MRT_rest_traffic_notices_messages_put_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;
		self::assertTrue( $data['saved'] );
		self::assertCount( 1, $data['messages'] );
	}

	public function test_admin_put_rejects_missing_messages_payload(): void {
		$request = new WP_REST_Request( 'PUT', '/museum-railway-timetable/v1/traffic-notices/messages' );
		$request->set_json_params( array( 'other' => array() ) );

		$result = MRT_rest_traffic_notices_messages_put_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 400, $result->get_error_data()['status'] ?? 0 );
	}

	public function test_admin_put_rejects_empty_notice_text(): void {
		$request = new WP_REST_Request( 'PUT', '/museum-railway-timetable/v1/traffic-notices/messages' );
		$request->set_json_params(
			array(
				'messages' => array(
					array(
						'text'    => '   ',
						'enabled' => true,
					),
				),
			)
		);

		$result = MRT_rest_traffic_notices_messages_put_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'mrt_notice_empty', $result->get_error_code() );
	}

	public function test_admin_feed_includes_edit_hints(): void {
		update_option(
			MRT_OPTION_PUBLIC_NOTICES,
			array(
				array(
					'id'          => 'n1',
					'text'        => 'Baninfo',
					'enabled'     => true,
					'active_from' => '2026-06-06',
					'active_to'   => '2026-06-06',
					'sort_order'  => 10,
				),
			),
			false
		);

		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/traffic-notices/feed' );
		$request->set_param( 'date', '2026-06-06' );

		$response = MRT_rest_traffic_notices_feed_get_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;

		self::assertFalse( $data['is_empty'] );
		self::assertSame( '/traffic-notices', $data['ongoing'][0]['edit']['path'] );
	}
}
