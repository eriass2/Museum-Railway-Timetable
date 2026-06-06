<?php
/**
 * Admin REST handlers not covered by stop-times / timetables-data tests.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}
if ( ! defined( 'MRT_TAXONOMY_TRAIN_TYPE' ) ) {
	define( 'MRT_TAXONOMY_TRAIN_TYPE', 'mrt_train_type' );
}

require_once ABSPATH . 'inc/infrastructure/rest/permissions.php';
require_once ABSPATH . 'inc/infrastructure/rest/stations.php';
require_once ABSPATH . 'inc/infrastructure/rest/routes.php';
require_once ABSPATH . 'inc/infrastructure/rest/operations.php';
require_once ABSPATH . 'inc/infrastructure/rest/import-export.php';
require_once ABSPATH . 'inc/infrastructure/rest/dashboard.php';
require_once ABSPATH . 'inc/infrastructure/rest/settings-admin.php';
require_once ABSPATH . 'inc/infrastructure/rest/train-types.php';

if ( ! function_exists( 'MRT_admin_app_url' ) ) {
	function MRT_admin_app_url( string $path = '' ): string {
		return 'https://example.test/wp-admin/admin.php?page=mrt-admin#' . ltrim( $path, '#' );
	}
}

if ( ! function_exists( 'home_url' ) ) {
	function home_url( string $path = '' ): string {
		return 'https://example.test' . $path;
	}
}

if ( ! function_exists( 'MRT_is_development_mode' ) ) {
	function MRT_is_development_mode(): bool {
		return false;
	}
}

final class RestAdminHandlersTest extends TestCase {

	protected function tearDown(): void {
		unset(
			$GLOBALS['mrt_test_get_posts'],
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_posts'],
			$GLOBALS['mrt_test_options'],
			$GLOBALS['mrt_test_terms_list'],
			$GLOBALS['mrt_test_term_meta'],
			$GLOBALS['mrt_test_current_timestamp']
		);
		parent::tearDown();
	}

	public function test_list_stations_payload_formats_meta(): void {
		$post = new WP_Post(
			(object) array(
				'ID'         => 5,
				'post_title' => 'Selknä',
				'post_type'  => MRT_POST_TYPE_STATION,
			)
		);
		$GLOBALS['mrt_test_get_posts'] = static fn (): array => array( $post );
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|mrt_station_type'       => 'station',
			'5|mrt_station_bus_suffix' => '1',
			'5|mrt_display_order'      => 3,
			'5|' . MRT_station_price_zones_meta_key() => array( 2, 3 ),
		);

		$rows = MRT_rest_list_stations_payload();

		self::assertCount( 1, $rows );
		self::assertSame( 5, $rows[0]['id'] );
		self::assertSame( 'Selknä', $rows[0]['title'] );
		self::assertTrue( $rows[0]['bus_suffix'] );
		self::assertSame( 3, $rows[0]['display_order'] );
		self::assertSame( array( 2, 3 ), $rows[0]['price_zones'] );
	}

	public function test_apply_station_meta_saves_price_zones(): void {
		$GLOBALS['mrt_test_post_meta'] = array();
		MRT_rest_apply_station_meta( 9, array( 'price_zones' => array( 1, 2 ) ) );
		self::assertSame(
			array( 1, 2 ),
			get_post_meta( 9, MRT_station_price_zones_meta_key(), true )
		);
	}

	public function test_apply_station_meta_clears_price_zones_when_empty(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'9|' . MRT_station_price_zones_meta_key() => array( 3 ),
		);
		MRT_rest_apply_station_meta( 9, array( 'price_zones' => array() ) );
		self::assertSame( '', get_post_meta( 9, MRT_station_price_zones_meta_key(), true ) );
	}

	public function test_apply_station_meta_saves_train_change_map(): void {
		$GLOBALS['mrt_test_post_meta'] = array();
		MRT_rest_apply_station_meta(
			9,
			array(
				'train_change_map' => array(
					'71' => array(
						'typeName'      => 'Dieseltåg',
						'serviceNumber' => '61',
					),
				),
			)
		);
		self::assertSame(
			array(
				'71' => array(
					'typeName'      => 'Dieseltåg',
					'serviceNumber' => '61',
				),
			),
			get_post_meta( 9, MRT_station_train_change_map_meta_key(), true )
		);
	}

	public function test_list_stations_includes_train_change_map(): void {
		$post = new WP_Post(
			(object) array(
				'ID'         => 5,
				'post_title' => 'Marielund',
				'post_type'  => MRT_POST_TYPE_STATION,
			)
		);
		$GLOBALS['mrt_test_get_posts'] = static fn (): array => array( $post );
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|' . MRT_station_train_change_map_meta_key() => array(
				'71' => array(
					'typeName'      => 'Dieseltåg',
					'serviceNumber' => '61',
				),
			),
		);
		$rows = MRT_rest_list_stations_payload();
		self::assertSame( '61', $rows[0]['train_change_map']['71']['serviceNumber'] );
	}

	public function test_create_station_handler_applies_optional_meta(): void {
		$request = new WP_REST_Request( 'POST', '/stations' );
		$request->set_json_params(
			array(
				'title'         => 'Faringe',
				'station_type'  => 'halt',
				'lat'           => '57.48',
				'lng'           => '15.82',
				'bus_suffix'    => true,
				'display_order' => 5,
				'price_zones'   => array( 1, 2 ),
			)
		);

		$result = MRT_rest_create_station_handler( $request );

		self::assertIsArray( $result );
		$data = $result;
		self::assertSame( 'Faringe', $data['title'] );
		self::assertSame( 'halt', $data['station_type'] );
		self::assertSame( '57.48', $data['lat'] );
		self::assertTrue( $data['bus_suffix'] );
		self::assertSame( array( 1, 2 ), $data['price_zones'] );
	}

	public function test_create_station_handler_rejects_empty_title(): void {
		$request = new WP_REST_Request( 'POST', '/stations' );
		$request->set_json_params( array() );

		$result = MRT_rest_create_station_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid', $result->get_error_code() );
	}

	public function test_create_route_handler_applies_station_order_and_endpoints(): void {
		$request = new WP_REST_Request( 'POST', '/routes' );
		$request->set_json_params(
			array(
				'title'         => 'Uppsala – Faringe',
				'station_ids'   => array( 1, 2, 3 ),
				'start_station' => 1,
				'end_station'   => 3,
			)
		);

		$result = MRT_rest_create_route_handler( $request );

		self::assertIsArray( $result );
		self::assertSame( array( 1, 2, 3 ), $result['station_ids'] );
		self::assertSame( 1, $result['start_station'] );
		self::assertSame( 3, $result['end_station'] );
	}

	public function test_update_route_handler_applies_title_and_station_order(): void {
		$post = new WP_Post(
			(object) array(
				'ID'         => 7,
				'post_title' => 'Old title',
				'post_type'  => MRT_POST_TYPE_ROUTE,
			)
		);
		$GLOBALS['mrt_test_posts']     = array( 7 => $post );
		$GLOBALS['mrt_test_post_meta'] = array(
			'7|mrt_route_stations'      => array( 1, 2 ),
			'7|mrt_route_start_station' => 1,
			'7|mrt_route_end_station'   => 2,
		);

		$request = new WP_REST_Request( 'PATCH', '/routes/7' );
		$request['id'] = 7;
		$request->set_json_params(
			array(
				'title'         => 'New title',
				'station_ids'   => array( 2, 1, 3 ),
				'start_station' => 2,
				'end_station'   => 3,
			)
		);

		$result = MRT_rest_update_route_handler( $request );

		self::assertIsArray( $result );
		self::assertSame( 'New title', $result['title'] );
		self::assertSame( array( 2, 1, 3 ), $result['station_ids'] );
		self::assertSame( 2, $result['start_station'] );
		self::assertSame( 3, $result['end_station'] );
	}

	public function test_format_route_includes_station_ids(): void {
		$post = new WP_Post(
			(object) array(
				'ID'         => 7,
				'post_title' => 'Uppsala – Faringe',
				'post_type'  => MRT_POST_TYPE_ROUTE,
			)
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'7|mrt_route_stations'      => array( 1, 2, 3 ),
			'7|mrt_route_start_station' => 1,
			'7|mrt_route_end_station'   => 3,
		);

		$row = MRT_rest_format_route( $post );

		self::assertSame( array( 1, 2, 3 ), $row['station_ids'] );
		self::assertSame( 1, $row['start_station'] );
		self::assertSame( 3, $row['end_station'] );
	}

	public function test_import_csv_handler_requires_uploaded_file(): void {
		$request = new WP_REST_Request( 'POST', '/import/csv' );

		$result = MRT_rest_import_csv_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'no_file', $result->get_error_code() );
	}

	public function test_clear_plugin_data_handler_clears_plugin_data(): void {
		require_once ABSPATH . 'inc/admin/tools/clear-db.php';
		require_once ABSPATH . 'inc/admin/tools/dev-cli.php';

		$GLOBALS['mrt_test_get_posts'] = static fn (): array => array();
		$GLOBALS['mrt_test_options']   = array(
			'mrt_price_schema' => array(
				'ticket_types' => array(
					array(
						'key'   => 'family',
						'label' => 'Familj',
					),
				),
				'categories'   => array(
					array(
						'key'   => 'adult',
						'label' => 'Vuxen',
					),
				),
				'zones'        => array( 1, 2 ),
			),
		);

		$response = MRT_rest_clear_plugin_data_handler( new WP_REST_Request( 'POST', '/data/clear' ) );
		$data     = $response instanceof WP_REST_Response ? $response->get_data() : $response;

		self::assertIsArray( $data );
		self::assertTrue( $data['cleared'] ?? false );
		self::assertFalse( get_option( 'mrt_price_schema', false ) );
	}

	public function test_get_dashboard_returns_payload_keys(): void {
		$GLOBALS['mrt_test_get_posts'] = static fn (): array => array();
		$GLOBALS['mrt_test_current_timestamp'] = strtotime( '2026-06-06 10:00:00 UTC' );

		$data = MRT_rest_get_dashboard( new WP_REST_Request( 'GET', '/dashboard' ) );

		self::assertIsArray( $data );
		self::assertArrayHasKey( 'stats', $data );
		self::assertArrayHasKey( 'warnings', $data );
	}

	public function test_get_settings_handler_returns_transfer_bounds(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'enabled'              => '1',
				'note'                 => 'Test',
				'min_transfer_minutes' => 5,
				'max_transfer_minutes' => 90,
			),
		);

		$data = MRT_rest_get_settings_handler( new WP_REST_Request( 'GET', '/settings' ) );

		self::assertTrue( $data['enabled'] );
		self::assertSame( 'Test', $data['note'] );
		self::assertSame( 5, $data['min_transfer_minutes'] );
		self::assertSame( 90, $data['max_transfer_minutes'] );
	}

	public function test_get_prices_handler_includes_matrix_labels(): void {
		$data = MRT_rest_get_prices_handler( new WP_REST_Request( 'GET', '/settings/prices' ) );

		self::assertIsArray( $data );
		self::assertArrayHasKey( 'matrix', $data );
		self::assertArrayHasKey( 'ticket_types', $data );
		self::assertArrayHasKey( 'zones', $data );
		self::assertArrayHasKey( 'zone_cap', $data );
		self::assertArrayHasKey( 'afternoon_return', $data );
	}

	public function test_save_prices_handler_persists_schema(): void {
		$request = new WP_REST_Request( 'PATCH', '/settings/prices' );
		$request->set_json_params(
			array(
				'ticket_types' => array( 'family' => 'Familjebiljett' ),
				'categories'   => array( 'adult' => 'Vuxen' ),
				'zones'        => array( 1, 2 ),
				'matrix'       => array(
					'family' => array(
						'adult' => array(
							1 => 100,
							2 => 150,
						),
					),
				),
			)
		);

		MRT_rest_save_prices_handler( $request );

		self::assertSame( array( 'family' ), MRT_price_ticket_type_keys() );
		self::assertSame( array( 1, 2 ), MRT_price_zone_keys() );
	}

	public function test_list_train_types_handler_returns_icon_keys(): void {
		$term = new WP_Term();
		$term->term_id = 8;
		$term->name    = 'Rälsbuss';
		$term->slug    = 'railsbuss';
		$GLOBALS['mrt_test_terms_list'] = array( $term );

		$data = MRT_rest_list_train_types_handler( new WP_REST_Request( 'GET', '/train-types' ) );

		self::assertArrayHasKey( 'items', $data );
		self::assertArrayHasKey( 'icon_keys', $data );
		self::assertSame( 'Rälsbuss', $data['items'][0]['name'] );
	}

	public function test_cancel_traffic_handler_rejects_invalid_date(): void {
		$request = new WP_REST_Request( 'POST', '/operations/cancel-traffic' );
		$request->set_json_params( array( 'date' => 'not-a-date' ) );

		$result = MRT_rest_cancel_traffic_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid_date', $result->get_error_code() );
	}
}
