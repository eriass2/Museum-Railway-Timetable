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

require_once ABSPATH . 'inc/domain/admin/dashboard-warnings.php';

final class DashboardWarningsTest extends TestCase
{
	/** @var mixed */
	private $previous_wpdb;

	protected function tearDown(): void
	{
		if ( isset( $this->previous_wpdb ) ) {
			$GLOBALS['wpdb'] = $this->previous_wpdb;
		}
		unset( $GLOBALS['mrt_test_get_posts'], $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
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
}
