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
require_once ABSPATH . 'inc/infrastructure/rest/stations-routes.php';
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
		);

		$rows = MRT_rest_list_stations_payload();

		self::assertCount( 1, $rows );
		self::assertSame( 5, $rows[0]['id'] );
		self::assertSame( 'Selknä', $rows[0]['title'] );
		self::assertTrue( $rows[0]['bus_suffix'] );
		self::assertSame( 3, $rows[0]['display_order'] );
	}

	public function test_create_station_handler_rejects_empty_title(): void {
		$request = new WP_REST_Request( 'POST', '/stations' );
		$request->set_json_params( array() );

		$result = MRT_rest_create_station_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid', $result->get_error_code() );
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
