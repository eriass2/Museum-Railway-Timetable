<?php
/**
 * Dashboard pricing warning collectors.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/admin/dashboard-warnings.php';
require_once ABSPATH . 'inc/domain/pricing/prices.php';

final class DashboardPricingWarningsTest extends TestCase
{
	protected function tearDown(): void
	{
		unset( $GLOBALS['mrt_test_options'], $GLOBALS['mrt_test_get_posts'] );
		parent::tearDown();
	}

	public function test_empty_price_matrix_warning(): void
	{
		$GLOBALS['mrt_test_options'] = array(
			'mrt_price_matrix' => array(),
		);

		$warnings = MRT_dashboard_warnings_pricing();

		self::assertCount( 1, $warnings );
		self::assertSame( 'prices_not_configured', $warnings[0]['code'] );
		self::assertSame( '#/prices', $warnings[0]['route'] );
	}

	public function test_stations_missing_zones_warning(): void
	{
		require_once ABSPATH . 'inc/domain/station/stations.php';
		require_once ABSPATH . 'inc/domain/pricing/station-zones.php';

		$GLOBALS['mrt_test_options'] = array(
			'mrt_price_matrix' => array(
				'single' => array(
					'adult' => array( 1 => 80 ),
				),
			),
			'mrt_price_schema' => array(
				'ticket_types' => array(
					array( 'key' => 'single', 'label' => 'Enkel' ),
				),
				'categories'   => array(
					array( 'key' => 'adult', 'label' => 'Vuxen' ),
				),
				'zones'        => array( 1 ),
				'zone_cap'     => 3,
			),
		);
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === 'mrt_station' ) {
				return array( 10, 11 );
			}
			return array();
		};

		$warnings = MRT_dashboard_warnings_pricing();

		$codes = array_column( $warnings, 'code' );
		self::assertContains( 'stations_missing_price_zones', $codes );
	}

	public function test_afternoon_return_missing_warning(): void
	{
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings'     => array(
				'afternoon_return_threshold_minutes' => 900,
			),
			'mrt_price_matrix' => array(
				'single' => array(
					'adult' => array( 1 => 80 ),
				),
			),
			'mrt_price_schema' => array(
				'ticket_types'     => array(
					array( 'key' => 'single', 'label' => 'Enkel' ),
				),
				'categories'       => array(
					array( 'key' => 'adult', 'label' => 'Vuxen' ),
				),
				'zones'            => array( 1 ),
				'zone_cap'         => 3,
				'afternoon_return' => array( 'adult' => 0 ),
			),
		);

		$warnings = MRT_dashboard_warnings_pricing();

		$codes = array_column( $warnings, 'code' );
		self::assertContains( 'afternoon_return_not_configured', $codes );
	}

	public function test_price_matrix_is_configured(): void
	{
		$GLOBALS['mrt_test_options'] = array(
			'mrt_price_matrix' => array(
				'single' => array(
					'adult' => array( 1 => 80 ),
				),
			),
			'mrt_price_schema' => array(
				'ticket_types' => array(
					array( 'key' => 'single', 'label' => 'Enkel' ),
				),
				'categories'   => array(
					array( 'key' => 'adult', 'label' => 'Vuxen' ),
				),
				'zones'        => array( 1 ),
				'zone_cap'     => 3,
			),
		);

		self::assertTrue( MRT_price_matrix_is_configured() );
	}
}
