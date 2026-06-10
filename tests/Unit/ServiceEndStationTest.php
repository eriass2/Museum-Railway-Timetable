<?php
/**
 * Service end station derivation (inc/domain/service/service-end-station.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/service/service-end-station.php';

final class ServiceEndStationTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'] );
		parent::tearDown();
	}

	public function test_derive_end_station_returns_last_station_with_time(): void {
		$stops = array(
			array( 'station_id' => 101, 'departure' => '09:00' ),
			array( 'station_id' => 102, 'arrival' => '09:30' ),
			array( 'station_id' => 103, 'arrival' => '10:00', 'departure' => '10:05' ),
		);

		self::assertSame( 103, MRT_derive_end_station_from_stop_rows( $stops ) );
	}

	public function test_derive_end_station_skips_rows_without_time(): void {
		$stops = array(
			array( 'station_id' => 101, 'departure' => '09:00' ),
			array( 'station_id' => 102, 'stops_here' => '1' ),
			array( 'station_id' => 103, 'arrival' => '10:00' ),
		);

		self::assertSame( 103, MRT_derive_end_station_from_stop_rows( $stops ) );
	}

	public function test_derive_end_station_returns_zero_when_no_times(): void {
		$stops = array(
			array( 'station_id' => 101, 'stops_here' => '1' ),
			array( 'station_id' => 102 ),
		);

		self::assertSame( 0, MRT_derive_end_station_from_stop_rows( $stops ) );
	}

	public function test_apply_service_end_station_updates_meta_and_title(): void {
		$this->boot_service_fixture();

		MRT_apply_service_end_station( 501, 102 );

		self::assertSame( 102, (int) get_post_meta( 501, 'mrt_service_end_station_id', true ) );
		self::assertSame( 'dit', get_post_meta( 501, 'mrt_direction', true ) );
		$post = get_post( 501 );
		self::assertInstanceOf( WP_Post::class, $post );
		self::assertSame( 'Uppsala – Faringe → Beta', $post->post_title );
	}

	public function test_apply_service_end_station_clears_meta_when_zero(): void {
		$this->boot_service_fixture();
		update_post_meta( 501, 'mrt_service_end_station_id', 102 );
		update_post_meta( 501, 'mrt_direction', 'dit' );

		MRT_apply_service_end_station( 501, 0 );

		self::assertSame( '', get_post_meta( 501, 'mrt_service_end_station_id', true ) );
		self::assertSame( '', get_post_meta( 501, 'mrt_direction', true ) );
	}

	public function test_sync_service_end_station_from_stops(): void {
		$this->boot_service_fixture();

		MRT_sync_service_end_station_from_stops(
			501,
			array(
				array( 'station_id' => 101, 'departure' => '09:00' ),
				array( 'station_id' => 102, 'arrival' => '09:30' ),
			)
		);

		self::assertSame( 102, (int) get_post_meta( 501, 'mrt_service_end_station_id', true ) );
	}

	private function boot_service_fixture(): void {
		$GLOBALS['mrt_test_posts'] = array(
			50  => new WP_Post( (object) array( 'ID' => 50, 'post_title' => 'Uppsala – Faringe', 'post_type' => MRT_POST_TYPE_ROUTE ) ),
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Alpha', 'post_type' => MRT_POST_TYPE_STATION ) ),
			102 => new WP_Post( (object) array( 'ID' => 102, 'post_title' => 'Beta', 'post_type' => MRT_POST_TYPE_STATION ) ),
			501 => new WP_Post( (object) array( 'ID' => 501, 'post_title' => 'Tur', 'post_type' => MRT_POST_TYPE_SERVICE ) ),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_route_id'     => 50,
			'50|mrt_route_stations'        => array( 101, 102 ),
			'50|mrt_route_start_station'   => 101,
			'50|mrt_route_end_station'     => 102,
		);
	}
}
