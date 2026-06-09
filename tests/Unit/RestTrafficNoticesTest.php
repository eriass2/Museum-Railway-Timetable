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
		delete_option( 'mrt_public_notices' );
		parent::tearDown();
	}

	public function test_public_handler_rejects_invalid_date(): void {
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/traffic-notices' );
		$request->set_param( 'date', 'not-a-date' );

		$result = MRT_rest_traffic_notices_public_handler( $request );
		self::assertInstanceOf( WP_Error::class, $result );
	}

	public function test_admin_put_saves_messages(): void {
		$request = new WP_REST_Request( 'PUT', '/museum-railway-timetable/v1/traffic-notices/messages' );
		$request->set_param(
			'messages',
			array(
				array(
					'text'        => 'Café öppet',
					'enabled'     => true,
					'active_from' => '',
					'active_to'   => '',
				),
			)
		);

		$response = MRT_rest_traffic_notices_messages_put_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;
		self::assertTrue( $data['saved'] );
		self::assertCount( 1, $data['messages'] );
	}
}
