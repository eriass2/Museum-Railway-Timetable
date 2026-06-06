<?php
/**
 * Station price zone meta helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/pricing/prices.php';
require_once ABSPATH . 'inc/import/lennakatten/reference-data.php';

final class StationZonesTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'], $GLOBALS['mrt_test_get_posts'] );
		parent::tearDown();
	}

	public function test_sanitize_station_price_zones_caps_at_two(): void {
		self::assertSame( array( 1, 2 ), MRT_sanitize_station_price_zones( array( 2, 1, 3, 4 ) ) );
		self::assertSame( array(), MRT_sanitize_station_price_zones( array( 0, 9 ) ) );
	}

	public function test_parse_station_price_zones_csv(): void {
		self::assertSame( array( 1, 2 ), MRT_parse_station_price_zones_csv( '2,1' ) );
		self::assertSame( array(), MRT_parse_station_price_zones_csv( '' ) );
	}

	public function test_get_station_price_zones_uses_meta_when_set(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|' . MRT_station_price_zones_meta_key() => array( 2, 3 ),
		);
		self::assertSame( array( 2, 3 ), MRT_get_station_price_zones( 5 ) );
		self::assertTrue( MRT_station_price_zones_is_custom( 5 ) );
	}

	public function test_get_station_price_zones_empty_without_meta(): void {
		$GLOBALS['mrt_test_post_meta'] = array();
		$GLOBALS['mrt_test_posts']     = array(
			7 => (object) array(
				'ID'         => 7,
				'post_title' => 'Årsta',
				'post_type'  => MRT_POST_TYPE_STATION,
			),
		);
		self::assertSame( array(), MRT_get_station_price_zones( 7 ) );
		self::assertFalse( MRT_station_price_zones_is_custom( 7 ) );
	}

	public function test_update_station_price_zones_meta_persists_and_clears(): void {
		$GLOBALS['mrt_test_post_meta'] = array();
		MRT_update_station_price_zones_meta( 8, array( 2, 1 ) );
		self::assertSame(
			array( 1, 2 ),
			get_post_meta( 8, MRT_station_price_zones_meta_key(), true )
		);
		MRT_update_station_price_zones_meta( 8, array() );
		self::assertSame( '', get_post_meta( 8, MRT_station_price_zones_meta_key(), true ) );
	}

	public function test_get_station_price_zones_map_only_includes_configured_stations(): void {
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_STATION && ( $args['fields'] ?? '' ) === 'ids' ) {
				return array( 5, 7 );
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|' . MRT_station_price_zones_meta_key() => array( 2, 3 ),
		);
		$GLOBALS['mrt_test_posts']     = array(
			7 => (object) array(
				'ID'         => 7,
				'post_title' => 'Årsta',
				'post_type'  => MRT_POST_TYPE_STATION,
			),
		);

		$map = MRT_get_station_price_zones_map();

		self::assertSame( array( 2, 3 ), $map[5] );
		self::assertArrayNotHasKey( 7, $map );
	}

	public function test_lennakatten_reference_zones_only_gunsta_and_almunge_span_two_bands(): void {
		$defaults = MRT_lennakatten_reference_station_price_zones_by_title();
		$dual     = array();
		foreach ( $defaults as $title => $zones ) {
			if ( count( $zones ) > 1 ) {
				$dual[ $title ] = $zones;
			}
		}
		self::assertSame(
			array(
				'Gunsta'  => array( 1, 2 ),
				'Almunge' => array( 2, 3 ),
			),
			$dual
		);
	}
}
