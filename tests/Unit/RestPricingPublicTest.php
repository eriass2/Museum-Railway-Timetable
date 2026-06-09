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

require_once ABSPATH . 'inc/infrastructure/rest/public/pricing-public.php';

final class RestPricingPublicTest extends TestCase {
	use MRT_Lennakatten_Test_Fixture;

	protected function tearDown(): void {
		$this->mrt_clear_test_options();
		parent::tearDown();
	}

	public function test_trip_prices_handler_rejects_invalid_station(): void {
		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/prices/trip' );
		$request->set_param( 'from_id', 0 );
		$request->set_param( 'to_id', 2 );

		$result = MRT_rest_trip_prices_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'mrt_prices_invalid', $result->get_error_code() );
	}

	public function test_trip_prices_handler_returns_zone_matrix(): void {
		$this->mrt_apply_lennakatten_options();
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

	public function test_trip_prices_handler_counts_zones_from_outbound_legs(): void {
		$this->mrt_apply_lennakatten_options();
		$GLOBALS['wpdb'] = new MRT_Journey_Test_Db(
			array(
				10 => array(
					array(
						'service_post_id' => 10,
						'station_post_id' => 1,
						'stop_sequence'   => 1,
						'arrival_time'    => null,
						'departure_time'  => '09:00',
						'pickup_allowed'  => 1,
						'dropoff_allowed' => 1,
					),
					array(
						'service_post_id' => 10,
						'station_post_id' => 4,
						'stop_sequence'   => 2,
						'arrival_time'    => '10:00',
						'departure_time'  => null,
						'pickup_allowed'  => 1,
						'dropoff_allowed' => 1,
					),
				),
			)
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'1|' . MRT_station_price_zones_meta_key() => array( 1 ),
			'4|' . MRT_station_price_zones_meta_key() => array( 4 ),
		);
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_STATION && ( $args['fields'] ?? '' ) === 'ids' ) {
				return array( 1, 4 );
			}
			return array();
		};

		$legs = wp_json_encode(
			array(
				array(
					'service_id'      => 10,
					'from_station_id' => 1,
					'to_station_id'   => 4,
				),
			)
		);

		$request = new WP_REST_Request( 'GET', '/museum-railway-timetable/v1/prices/trip' );
		$request->set_param( 'from_id', 1 );
		$request->set_param( 'to_id', 4 );
		$request->set_param( 'trip_type', 'single' );
		$request->set_param( 'outbound_legs', (string) $legs );

		$response = MRT_rest_trip_prices_handler( $request );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;

		self::assertIsArray( $data );
		self::assertSame( 2, $data['zones'] );

		unset( $GLOBALS['wpdb'], $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_get_posts'] );
	}
}
