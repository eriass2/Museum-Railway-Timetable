<?php
/**
 * Dashboard data-quality warning collectors.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_POST_TYPE_ROUTE' ) ) {
	define( 'MRT_POST_TYPE_ROUTE', 'mrt_route' );
}

if ( ! defined( 'MRT_TAXONOMY_TRAIN_TYPE' ) ) {
	define( 'MRT_TAXONOMY_TRAIN_TYPE', 'mrt_train_type' );
}

require_once ABSPATH . 'inc/domain/admin/dashboard-warnings.php';
require_once ABSPATH . 'inc/domain/admin/dashboard-warnings-quality.php';

final class DashboardWarningsTest extends TestCase
{
	/** @var mixed */
	private $previous_wpdb;

	protected function tearDown(): void
	{
		if ( isset( $this->previous_wpdb ) ) {
			$GLOBALS['wpdb'] = $this->previous_wpdb;
		}
		unset(
			$GLOBALS['mrt_test_get_posts'],
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_options'],
			$GLOBALS['mrt_test_get_terms'],
			$GLOBALS['mrt_test_terms_list'],
			$GLOBALS['mrt_test_post_terms'],
			$GLOBALS['mrt_test_terms'],
			$GLOBALS['mrt_test_current_timestamp'],
			$GLOBALS['mrt_test_wpdb_get_results']
		);
		parent::tearDown();
	}

	/**
	 * @param array<int, array<string, mixed>> $rows
	 */
	private function mock_stoptime_rows( array $rows ): void {
		$this->previous_wpdb = $GLOBALS['wpdb'];
		$GLOBALS['wpdb']     = new class( $rows ) {
			public string $prefix = 'wp_';

			public string $last_error = '';

			/** @var array<int, array<string, mixed>> */
			private array $rows;

			/**
			 * @param array<int, array<string, mixed>> $rows
			 */
			public function __construct( array $rows ) {
				$this->rows = $rows;
			}

			public function prepare( string $query, ...$args ): string {
				unset( $args );
				return $query;
			}

			public function get_results( $query = null, $output = ARRAY_A ): array {
				unset( $query, $output );
				return $this->rows;
			}

			public function get_var( $query = null ) {
				unset( $query );
				return '0';
			}
		};
	}

	public function test_empty_timetable_dates_warning(): void
	{
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_TIMETABLE && ( $args['fields'] ?? '' ) === 'ids' ) {
				return array( 5 );
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|mrt_timetable_dates' => array(),
		);

		$warnings = MRT_dashboard_warnings_empty_timetable_dates();

		self::assertCount( 1, $warnings );
		self::assertSame( 'timetable_no_dates', $warnings[0]['code'] );
		self::assertSame( '#/timetables/5', $warnings[0]['route'] );
	}

	public function test_timetable_without_trips_warning(): void
	{
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_TIMETABLE ) {
				return array(
					new WP_Post(
						(object) array(
							'ID'         => 8,
							'post_title' => 'Sommar',
						)
					),
				);
			}
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_SERVICE ) {
				return array();
			}
			return array();
		};

		$warnings = MRT_dashboard_warnings_timetables_without_trips();

		self::assertCount( 1, $warnings );
		self::assertSame( 'timetable_no_trips', $warnings[0]['code'] );
		self::assertStringContainsString( 'Sommar', $warnings[0]['message'] );
	}

	public function test_trip_without_stoptimes_warning(): void
	{
		$this->previous_wpdb = $GLOBALS['wpdb'];
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_SERVICE ) {
				return array(
					new WP_Post(
						(object) array(
							'ID'         => 30,
							'post_title' => 'Morgontåg',
						)
					),
				);
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'30|mrt_service_route_id'     => 2,
			'30|mrt_service_timetable_id' => 9,
		);
		$GLOBALS['wpdb'] = new class {
			public string $prefix = 'wp_';

			public function prepare( string $query, ...$args ): string {
				unset( $args );
				return $query;
			}

			public function get_var( $query = null ) {
				unset( $query );
				return '0';
			}
		};

		$warnings = MRT_dashboard_warnings_trips_without_stoptimes();

		self::assertCount( 1, $warnings );
		self::assertSame( 'trip_no_stoptimes', $warnings[0]['code'] );
		self::assertSame( '#/timetables/9', $warnings[0]['route'] );
	}

	public function test_route_without_stations_warning(): void
	{
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_ROUTE ) {
				return array(
					new WP_Post(
						(object) array(
							'ID'         => 4,
							'post_title' => 'Norra linjen',
						)
					),
				);
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'4|mrt_route_stations' => array(),
		);

		$warnings = MRT_dashboard_warnings_routes_without_stations();

		self::assertCount( 1, $warnings );
		self::assertSame( 'route_no_stations', $warnings[0]['code'] );
		self::assertSame( '#/stations-routes', $warnings[0]['route'] );
	}

	public function test_trip_stoptimes_count_mismatch_warning(): void
	{
		$this->mock_stoptime_rows(
			array(
				array(
					'station_post_id' => 10,
					'stop_sequence'   => 1,
				),
			)
		);
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_SERVICE ) {
				return array(
					new WP_Post(
						(object) array(
							'ID'         => 40,
							'post_title' => 'Tur 12',
						)
					),
				);
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'40|mrt_service_route_id'     => 7,
			'40|mrt_service_timetable_id' => 3,
			'7|mrt_route_stations'        => array( 10, 11, 12 ),
		);

		$warnings = MRT_dashboard_warnings_trip_stoptimes_route_mismatch();

		self::assertCount( 1, $warnings );
		self::assertSame( 'trip_stoptimes_count_mismatch', $warnings[0]['code'] );
		self::assertStringContainsString( 'Tur 12', $warnings[0]['message'] );
	}

	public function test_train_change_map_unknown_service_warning(): void
	{
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_STATION ) {
				return array(
					new WP_Post(
						(object) array(
							'ID'         => 6,
							'post_title' => 'Marielund',
						)
					),
				);
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'6|mrt_station_train_change_map' => array(
				'99' => array(
					'typeName'      => 'Dieseltåg',
					'serviceNumber' => '61',
				),
			),
		);
		$diesel                        = new WP_Term();
		$diesel->name                  = 'Dieseltåg';
		$diesel->slug                  = 'dieseltag';
		$GLOBALS['mrt_test_terms_list'] = array( $diesel );

		$warnings = MRT_dashboard_warnings_train_change_map_invalid();

		self::assertCount( 2, $warnings );
		self::assertSame( 'train_change_unknown_incoming', $warnings[0]['code'] );
		self::assertSame( 'train_change_unknown_outgoing', $warnings[1]['code'] );
	}

	public function test_transfer_hub_unconfigured_warning(): void
	{
		$GLOBALS['mrt_test_options'] = array(
			'mrt_line_registry' => array(
				'fjallnora' => array(
					'title'                 => 'Fjällnora',
					'kind'                  => 'branch',
					'junction_station_code' => 'selkna',
					'requires_transfer'     => true,
				),
			),
		);
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_STATION ) {
				return array(
					new WP_Post(
						(object) array(
							'ID'         => 6,
							'post_title' => 'Selkné',
						)
					),
				);
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'6|mrt_station_code' => 'selkna',
		);

		$warnings = MRT_dashboard_warnings_transfer_hub_unconfigured();

		self::assertCount( 1, $warnings );
		self::assertSame( 'transfer_hub_unconfigured', $warnings[0]['code'] );
		self::assertStringContainsString( 'Fjällnora', $warnings[0]['message'] );
	}

	public function test_timetable_no_upcoming_dates_warning(): void
	{
		$GLOBALS['mrt_test_current_timestamp'] = strtotime( '2026-06-11 12:00:00' );
		$GLOBALS['mrt_test_get_posts']         = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_TIMETABLE ) {
				return array(
					new WP_Post(
						(object) array(
							'ID'         => 12,
							'post_title' => 'Vår',
						)
					),
				);
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'12|mrt_timetable_dates' => array( '2026-01-01', '2026-03-15' ),
		);

		$warnings = MRT_dashboard_warnings_timetables_no_upcoming_dates();

		self::assertCount( 1, $warnings );
		self::assertSame( 'timetable_no_upcoming_dates', $warnings[0]['code'] );
		self::assertStringContainsString( 'Vår', $warnings[0]['message'] );
	}

	public function test_bus_without_rail_junction_warning(): void
	{
		$this->mock_stoptime_rows( array() );
		$GLOBALS['mrt_test_options'] = array(
			'mrt_line_registry' => array(
				'fjallnora' => array(
					'title'                 => 'Fjällnora',
					'kind'                  => 'branch',
					'junction_station_code' => 'selkna',
					'requires_transfer'     => true,
				),
			),
		);
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_STATION ) {
				return array(
					new WP_Post(
						(object) array(
							'ID'         => 6,
							'post_title' => 'Selkné',
						)
					),
				);
			}
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_SERVICE && ( $args['fields'] ?? '' ) === 'ids' ) {
				return array( 50 );
			}
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_SERVICE ) {
				return array( 50 );
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'6|mrt_station_code'          => 'selkna',
			'50|mrt_service_line_code'    => 'fjallnora',
			'50|mrt_service_route_id'     => 9,
			'9|mrt_route_stations'        => array( 6, 15 ),
			'6|mrt_station_bus_suffix'    => '1',
			'15|mrt_station_bus_suffix'   => '1',
		);

		$warnings = MRT_dashboard_warnings_bus_without_rail_junction();

		self::assertCount( 1, $warnings );
		self::assertSame( 'bus_line_no_rail_junction', $warnings[0]['code'] );
		self::assertStringContainsString( 'Fjällnora', $warnings[0]['message'] );
	}
}
