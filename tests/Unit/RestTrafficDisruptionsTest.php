<?php
/**
 * Disruption feed REST tests.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}

require_once ABSPATH . 'inc/infrastructure/rest/shared/permissions.php';
require_once ABSPATH . 'inc/infrastructure/rest/public/traffic-disruptions-public.php';

final class RestTrafficDisruptionsTest extends TestCase {
	protected function tearDown(): void {
		delete_option( MRT_OPTION_PUBLIC_NOTICES );
		parent::tearDown();
	}

	public function test_feed_handler_rejects_invalid_date(): void {
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/traffic-disruptions/feed' );
		$request->set_param( 'date', 'bad' );

		$result = MRT_rest_traffic_disruptions_feed_handler( $request );
		self::assertInstanceOf( WP_Error::class, $result );
	}

	public function test_feed_handler_returns_notices_shape(): void {
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

		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/traffic-disruptions/feed' );
		$request->set_param( 'date', '2026-06-06' );
		$request->set_param( 'horizon_days', 30 );

		$response = MRT_rest_traffic_disruptions_feed_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;

		self::assertSame( '2026-06-06', $data['reference_date'] );
		self::assertSame( 30, $data['horizon_days'] );
		self::assertFalse( $data['is_empty'] );
		self::assertNotEmpty( $data['ongoing'] );
	}
}
