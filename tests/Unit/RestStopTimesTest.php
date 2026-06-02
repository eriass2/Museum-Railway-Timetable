<?php
/**
 * Admin REST: stop times.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}

require_once ABSPATH . 'inc/infrastructure/rest/permissions.php';
require_once ABSPATH . 'inc/infrastructure/rest/stop-times.php';

final class RestStopTimesTest extends TestCase {

	/** @var mixed */
	private $original_wpdb = null;

	protected function tearDown(): void {
		if ( $this->original_wpdb !== null ) {
			$GLOBALS['wpdb'] = $this->original_wpdb;
		}
		unset(
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_posts'],
			$GLOBALS['mrt_test_get_posts'],
			$GLOBALS['mrt_test_current_user_can']
		);
		parent::tearDown();
	}

	public function test_normalize_stops_payload_maps_client_aliases(): void {
		$rows = MRT_rest_normalize_stops_payload(
			array(
				array(
					'id'               => 12,
					'stops_here'       => true,
					'arrival_time'     => '09:00',
					'departure_time'   => '09:02',
					'pickup_allowed'   => true,
					'dropoff_allowed'  => false,
				),
			)
		);

		self::assertSame(
			array(
				'station_id' => 12,
				'stops_here' => '1',
				'arrival'    => '09:00',
				'departure'  => '09:02',
				'pickup'     => '1',
				'dropoff'    => '',
			),
			$rows[0]
		);
	}

	public function test_stop_times_write_permission_requires_quick_edit_for_operators(): void {
		$GLOBALS['mrt_test_current_user_can'] = static function ( string $cap ): bool {
			return $cap === 'edit_posts';
		};
		$request = new WP_REST_Request( 'PUT', '/services/1/stop-times' );
		$request->set_json_params( array( 'stops' => array() ) );

		self::assertFalse( MRT_rest_stop_times_write_permission( $request ) );

		$request->set_json_params( array( 'quick_edit' => true, 'stops' => array() ) );
		self::assertTrue( MRT_rest_stop_times_write_permission( $request ) );
	}

	public function test_get_stop_times_handler_returns_error_without_route(): void {
		$request = new WP_REST_Request( 'GET', '/services/501/stop-times' );
		$request->set_param( 'id', 501 );

		$result = MRT_rest_get_stop_times_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'no_route', $result->get_error_code() );
	}

	public function test_get_stop_times_handler_returns_station_rows(): void {
		$this->boot_stop_times_fixture(
			array(
				array(
					'station_post_id' => 101,
					'stop_sequence'   => 1,
					'arrival_time'    => null,
					'departure_time'  => '09:00',
					'pickup_allowed'  => 1,
					'dropoff_allowed' => 1,
				),
			)
		);
		$request = new WP_REST_Request( 'GET', '/services/501/stop-times' );
		$request->set_param( 'id', 501 );

		$data = MRT_rest_get_stop_times_handler( $request );

		self::assertIsArray( $data );
		self::assertSame( 50, $data['route_id'] );
		self::assertCount( 2, $data['stations'] );
		self::assertSame( 101, $data['stations'][0]['id'] );
		self::assertSame( '09:00', $data['stations'][0]['departure_time'] );
	}

	public function test_save_stop_times_handler_rejects_invalid_time(): void {
		$this->boot_stop_times_fixture( array() );
		$request = new WP_REST_Request( 'PUT', '/services/501/stop-times' );
		$request->set_param( 'id', 501 );
		$request->set_json_params(
			array(
				'stops' => array(
					array(
						'station_id' => 101,
						'stops_here' => true,
						'departure'  => '25:99',
						'pickup'     => true,
						'dropoff'    => true,
					),
				),
			)
		);

		$result = MRT_rest_save_stop_times_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid_time', $result->get_error_code() );
	}

	public function test_quick_departure_handler_rejects_invalid_time(): void {
		$this->boot_stop_times_fixture( array() );
		$request = new WP_REST_Request( 'PATCH', '/services/501/departure' );
		$request->set_param( 'id', 501 );
		$request->set_json_params( array( 'departure' => '99:99' ) );

		$result = MRT_rest_quick_departure_handler( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid_time', $result->get_error_code() );
	}

	/**
	 * @param array<int, array<string, mixed>> $existing_rows
	 */
	private function boot_stop_times_fixture( array $existing_rows ): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_route_id' => 50,
			'50|mrt_route_stations'    => array( 101, 102 ),
		);
		$GLOBALS['mrt_test_posts']     = array(
			501 => new WP_Post(
				(object) array(
					'ID'        => 501,
					'post_type' => MRT_POST_TYPE_SERVICE,
				)
			),
		);
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) !== MRT_POST_TYPE_STATION ) {
				return array();
			}
			return array(
				new WP_Post(
					(object) array(
						'ID'         => 101,
						'post_title' => 'Alpha',
						'post_type'  => MRT_POST_TYPE_STATION,
					)
				),
				new WP_Post(
					(object) array(
						'ID'         => 102,
						'post_title' => 'Beta',
						'post_type'  => MRT_POST_TYPE_STATION,
					)
				),
			);
		};
		$this->original_wpdb       = $GLOBALS['wpdb'] ?? null;
		$GLOBALS['wpdb']           = new RestStopTimesTestDb( $existing_rows );
	}
}

/** Minimal wpdb fake for stop-time editor reads. */
final class RestStopTimesTestDb {
	/** @var string */
	public $prefix = 'wp_';

	/** @var string */
	public $last_error = '';

	/** @var array<int, array<string, mixed>> */
	private array $rows;

	/**
	 * @param array<int, array<string, mixed>> $rows
	 */
	public function __construct( array $rows ) {
		$this->rows = $rows;
	}

	public function prepare( string $query, ...$args ): string {
		foreach ( $args as $arg ) {
			$query = (string) preg_replace( '/%[ds]/', (string) (int) $arg, $query, 1 );
		}
		return $query;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_results( $query = null, $output = ARRAY_A ): array {
		unset( $output, $query );
		return $this->rows;
	}
}
