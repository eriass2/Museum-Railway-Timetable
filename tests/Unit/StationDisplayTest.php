<?php
/**
 * Station display helpers (inc/domain/station/stations.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StationDisplayTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_wp_query_posts'] );
		parent::tearDown();
	}

	public function test_display_name_appends_bus_suffix(): void {
		$station = new WP_Post( (object) array( 'ID' => 9, 'post_title' => 'Selknä' ) );
		$GLOBALS['mrt_test_post_meta'] = array(
			'9|mrt_station_bus_suffix' => '1',
		);

		self::assertSame( 'Selknä*', MRT_get_station_display_name( $station ) );
	}

	public function test_display_name_returns_empty_for_missing_title(): void {
		$station = new WP_Post( (object) array( 'ID' => 1, 'post_title' => '' ) );

		self::assertSame( '', MRT_get_station_display_name( $station ) );
		self::assertSame( '', MRT_get_station_display_name( null ) );
	}

	public function test_get_all_stations_returns_query_ids(): void {
		$GLOBALS['mrt_test_wp_query_posts'] = array( 101, 102, 103 );

		self::assertSame( array( 101, 102, 103 ), MRT_get_all_stations() );
	}
}
