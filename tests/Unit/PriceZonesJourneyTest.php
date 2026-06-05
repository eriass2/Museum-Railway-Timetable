<?php
/**
 * Route-based fare zone counting via journey legs and stop lists.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PriceZonesJourneyTest extends TestCase {

	use MRT_Journey_Test_Fixture;

	/** @var mixed */
	private $mrt_original_wpdb = null;

	protected function tearDown(): void {
		$this->mrt_reset_journey_fixture();
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'], $GLOBALS['mrt_test_get_posts'] );
		parent::tearDown();
	}

	/**
	 * @return array<int, int[]>
	 */
	private function sample_zone_map(): array {
		return array(
			1 => array( 1 ),
			2 => array( 1, 2 ),
			3 => array( 2 ),
			4 => array( 3 ),
			6 => array( 3 ),
		);
	}

	private function boot_station_zone_meta(): void {
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_STATION && ( $args['fields'] ?? '' ) === 'ids' ) {
				return array( 1, 2, 3, 4, 6 );
			}
			return array();
		};
		foreach ( $this->sample_zone_map() as $station_id => $zones ) {
			$GLOBALS['mrt_test_post_meta'][ $station_id . '|' . MRT_station_price_zones_meta_key() ] = $zones;
		}
	}

	public function test_collect_journey_leg_station_ids_follows_service_stops(): void {
		$this->mrt_use_journey_fixture(
			array(
				10 => array(
					$this->mrt_stop( 10, 1, 1, null, '09:00' ),
					$this->mrt_stop( 10, 2, 2, '09:10', '09:12' ),
					$this->mrt_stop( 10, 3, 3, '09:30', null ),
				),
			),
			array( 900 => array( '2026-06-01' ) )
		);

		$legs = array(
			array(
				'service_id'      => 10,
				'from_station_id' => 1,
				'to_station_id'   => 3,
			),
		);

		self::assertSame( array( 1, 2, 3 ), MRT_collect_journey_leg_station_ids( $legs ) );
	}

	public function test_zones_for_journey_legs_uses_lowest_valid_path_zones(): void {
		$this->mrt_use_journey_fixture(
			array(
				10 => array(
					$this->mrt_stop( 10, 1, 1, null, '09:00' ),
					$this->mrt_stop( 10, 2, 2, '09:10', '09:12' ),
					$this->mrt_stop( 10, 3, 3, '09:30', null ),
				),
			),
			array( 900 => array( '2026-06-01' ) )
		);

		$legs = array(
			array(
				'service_id'      => 10,
				'from_station_id' => 1,
				'to_station_id'   => 3,
			),
		);

		self::assertSame( 2, MRT_zones_for_journey_legs( $legs, $this->sample_zone_map() ) );
		self::assertSame( 2, MRT_zones_for_station_pair( 1, 4, $this->sample_zone_map() ) );
	}

	public function test_zones_for_journey_legs_skipped_stops_reduce_zone_count(): void {
		$this->mrt_use_journey_fixture(
			array(
				10 => array(
					$this->mrt_stop( 10, 1, 1, null, '09:00' ),
					$this->mrt_stop( 10, 4, 2, '10:00', null ),
				),
			),
			array( 900 => array( '2026-06-01' ) )
		);

		$legs = array(
			array(
				'service_id'      => 10,
				'from_station_id' => 1,
				'to_station_id'   => 4,
			),
		);

		self::assertSame( 2, MRT_zones_for_journey_legs( $legs, $this->sample_zone_map() ) );
		self::assertSame( 2, MRT_zones_for_station_pair( 1, 4, $this->sample_zone_map() ) );
	}

	public function test_zones_for_trip_price_uses_outbound_zones_on_return(): void {
		$this->mrt_use_journey_fixture(
			array(
				10 => array(
					$this->mrt_stop( 10, 1, 1, null, '09:00' ),
					$this->mrt_stop( 10, 2, 2, '09:10', '09:12' ),
					$this->mrt_stop( 10, 3, 3, '09:30', null ),
				),
				11 => array(
					$this->mrt_stop( 11, 1, 1, null, '15:00' ),
					$this->mrt_stop( 11, 2, 2, '15:10', '15:12' ),
					$this->mrt_stop( 11, 6, 3, '15:40', null ),
				),
			),
			array( 900 => array( '2026-06-01' ) )
		);
		$this->boot_station_zone_meta();

		$outbound = array(
			array(
				'service_id'      => 10,
				'from_station_id' => 1,
				'to_station_id'   => 3,
			),
		);
		$inbound = array(
			array(
				'service_id'      => 11,
				'from_station_id' => 1,
				'to_station_id'   => 6,
			),
		);

		self::assertSame(
			2,
			MRT_zones_for_trip_price( 1, 3, $outbound, null )
		);
		self::assertSame(
			2,
			MRT_zones_for_trip_price( 1, 6, $outbound, $inbound )
		);
	}

	public function test_zones_for_trip_price_uses_inbound_when_outbound_legs_missing(): void {
		$this->mrt_use_journey_fixture(
			array(
				11 => array(
					$this->mrt_stop( 11, 1, 1, null, '15:00' ),
					$this->mrt_stop( 11, 2, 2, '15:10', '15:12' ),
					$this->mrt_stop( 11, 6, 3, '15:40', null ),
				),
			),
			array( 900 => array( '2026-06-01' ) )
		);
		$this->boot_station_zone_meta();

		$inbound = array(
			array(
				'service_id'      => 11,
				'from_station_id' => 1,
				'to_station_id'   => 6,
			),
		);

		self::assertSame( 2, MRT_zones_for_trip_price( 1, 6, null, $inbound ) );
	}

	public function test_zones_for_journey_legs_uppsala_to_fjallnora_is_two_zones(): void {
		$this->mrt_use_journey_fixture(
			array(
				93 => array(
					$this->mrt_stop( 93, 1, 1, null, '11:10' ),
					$this->mrt_stop( 93, 2, 2, '11:47', null ),
				),
				3 => array(
					$this->mrt_stop( 3, 2, 1, null, '11:50' ),
					$this->mrt_stop( 3, 3, 2, '11:57', null ),
				),
			),
			array( 900 => array( '2026-07-01' ) )
		);

		$zone_map = array(
			1 => array( 1 ),
			2 => array( 2 ),
			3 => array( 2 ),
		);

		$legs = array(
			array(
				'service_id'      => 93,
				'from_station_id' => 1,
				'to_station_id'   => 2,
			),
			array(
				'service_id'      => 3,
				'from_station_id' => 2,
				'to_station_id'   => 3,
			),
		);

		self::assertSame( 2, MRT_zones_for_journey_legs( $legs, $zone_map ) );
	}

	public function test_zones_for_journey_legs_uppsala_to_skolsta_is_one_zone(): void {
		$this->mrt_use_journey_fixture(
			array(
				71 => array(
					$this->mrt_stop( 71, 1, 1, null, '10:00' ),
					$this->mrt_stop( 71, 5, 2, '10:03', '10:03' ),
					$this->mrt_stop( 71, 2, 3, '10:05', '10:05' ),
					$this->mrt_stop( 71, 3, 4, '10:09', null ),
				),
			),
			array( 900 => array( '2026-06-06' ) )
		);

		$zone_map = array(
			1 => array( 1 ),
			5 => array( 1 ),
			2 => array( 1 ),
			3 => array( 1 ),
		);

		$legs = array(
			array(
				'service_id'      => 71,
				'from_station_id' => 1,
				'to_station_id'   => 3,
			),
		);

		self::assertSame( 1, MRT_zones_for_journey_legs( $legs, $zone_map ) );
	}

	public function test_trip_prices_response_uses_path_zones_when_legs_given(): void {
		$this->mrt_use_journey_fixture(
			array(
				10 => array(
					$this->mrt_stop( 10, 1, 1, null, '09:00' ),
					$this->mrt_stop( 10, 4, 2, '10:00', null ),
				),
			),
			array( 900 => array( '2026-06-01' ) )
		);

		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_STATION && ( $args['fields'] ?? '' ) === 'ids' ) {
				return array( 1, 4 );
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'1|' . MRT_station_price_zones_meta_key() => array( 1 ),
			'4|' . MRT_station_price_zones_meta_key() => array( 4 ),
		);

		$legs = array(
			array(
				'service_id'      => 10,
				'from_station_id' => 1,
				'to_station_id'   => 4,
			),
		);

		$without_legs = MRT_trip_prices_response( 1, 4, 'single' );
		$with_legs    = MRT_trip_prices_response( 1, 4, 'single', '', '', false, $legs );

		self::assertSame( 3, $without_legs['zones'] );
		self::assertSame( 2, $with_legs['zones'] );
	}
}
