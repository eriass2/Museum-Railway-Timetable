<?php
/**
 * Public trip prices REST handler.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}

require_once ABSPATH . 'inc/infrastructure/rest/pricing-public.php';

final class RestPricingPublicTest extends TestCase {

	public function test_trip_prices_handler_rejects_invalid_station(): void {
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/prices/trip' );
		$request->set_param( 'from_id', 0 );
		$request->set_param( 'to_id', 2 );

		$result = MRT_rest_trip_prices_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'mrt_prices_invalid', $result->get_error_code() );
	}

	public function test_trip_prices_handler_returns_zone_matrix(): void {
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/prices/trip' );
		$request->set_param( 'from_id', 1 );
		$request->set_param( 'to_id', 2 );
		$request->set_param( 'trip_type', 'single' );

		$response = MRT_rest_trip_prices_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;

		self::assertIsArray( $data );
		self::assertArrayHasKey( 'zones', $data );
		self::assertSame( 3, $data['zones'] );
		self::assertArrayHasKey( 'trip', $data );
		self::assertNotNull( $data['trip'] );
		self::assertSame( 'single', $data['trip']['activeType'] );
	}
}
