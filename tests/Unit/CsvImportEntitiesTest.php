<?php
/**
 * CSV entity import (write path).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_TAXONOMY_TRAIN_TYPE' ) ) {
	define( 'MRT_TAXONOMY_TRAIN_TYPE', 'mrt_train_type' );
}

require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/loader.php';

if ( ! function_exists( 'term_exists' ) ) {
	/**
	 * @return array{term_id:int}|int|false
	 */
	function term_exists( $term, $taxonomy = '', $parent = null ) {
		unset( $term, $taxonomy, $parent );
		return false;
	}
}

if ( ! function_exists( 'wp_insert_term' ) ) {
	/**
	 * @param array<string, mixed> $args
	 * @return array{term_id:int}|WP_Error
	 */
	function wp_insert_term( $term, $taxonomy, $args = array() ) {
		unset( $taxonomy );
		if ( ! isset( $GLOBALS['mrt_test_next_term_id'] ) ) {
			$GLOBALS['mrt_test_next_term_id'] = 6000;
		}
		$id = (int) ++$GLOBALS['mrt_test_next_term_id'];
		$t  = new WP_Term();
		$t->term_id = $id;
		$t->name    = (string) $term;
		$t->slug    = (string) ( $args['slug'] ?? sanitize_title( (string) $term ) );
		$GLOBALS['mrt_test_terms'][ $id ] = $t;
		return array( 'term_id' => $id );
	}
}

final class CsvImportEntitiesTest extends TestCase {

	protected function tearDown(): void {
		unset(
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_posts'],
			$GLOBALS['mrt_test_get_posts'],
			$GLOBALS['mrt_test_next_post_id'],
			$GLOBALS['mrt_test_next_term_id'],
			$GLOBALS['mrt_test_terms']
		);
		parent::tearDown();
	}

	public function test_import_stations_creates_posts_and_maps_codes(): void {
		$this->boot_get_posts_by_code();
		$maps  = array( 'station' => array(), 'route' => array(), 'timetable' => array(), 'service' => array() );
		$files = array(
			'stations.csv' => array(
				array(
					'station_code'     => 'alpha',
					'name'             => 'Alpha',
					'station_type'     => 'station',
					'display_order'    => '2',
					'bus_stop_marker'  => '0',
					'lat'              => '',
					'lng'              => '',
				),
			),
		);

		$count = MRT_csv_import_stations( $files, $maps );

		self::assertSame( 1, $count );
		self::assertArrayHasKey( 'alpha', $maps['station'] );
		$id = $maps['station']['alpha'];
		self::assertSame( 'Alpha', $GLOBALS['mrt_test_posts'][ $id ]->post_title );
		self::assertSame( 'alpha', get_post_meta( $id, 'mrt_station_code', true ) );
		self::assertSame( 2, (int) get_post_meta( $id, 'mrt_display_order', true ) );
	}

	public function test_import_stations_saves_price_zones(): void {
		$this->boot_get_posts_by_code();
		$maps  = array( 'station' => array(), 'route' => array(), 'timetable' => array(), 'service' => array() );
		$files = array(
			'stations.csv' => array(
				array(
					'station_code'    => 'arsta',
					'name'            => 'Årsta',
					'price_zones'     => '2,1',
				),
			),
		);

		MRT_csv_import_stations( $files, $maps );
		$id = $maps['station']['arsta'];
		self::assertSame(
			array( 1, 2 ),
			get_post_meta( $id, MRT_station_price_zones_meta_key(), true )
		);
	}

	public function test_import_routes_resolves_station_order(): void {
		$this->boot_get_posts_by_code();
		$maps = array(
			'station'   => array( 'a' => 101, 'b' => 102 ),
			'route'     => array(),
			'timetable' => array(),
			'service'   => array(),
		);
		$files = array(
			'routes.csv' => array(
				array(
					'route_code'          => 'main',
					'title'               => 'Main line',
					'start_station_code'  => 'a',
					'end_station_code'    => 'b',
				),
			),
			'route_stations.csv' => array(
				array( 'route_code' => 'main', 'sequence' => '1', 'station_code' => 'a' ),
				array( 'route_code' => 'main', 'sequence' => '2', 'station_code' => 'b' ),
			),
		);

		$count = MRT_csv_import_routes( $files, $maps );

		self::assertSame( 1, $count );
		$route_id = $maps['route']['main'];
		self::assertSame( array( 101, 102 ), get_post_meta( $route_id, 'mrt_route_stations', true ) );
		self::assertSame( 101, (int) get_post_meta( $route_id, 'mrt_route_start_station', true ) );
		self::assertSame( 102, (int) get_post_meta( $route_id, 'mrt_route_end_station', true ) );
	}

	public function test_run_import_counts_entities_from_minimal_package(): void {
		$this->boot_get_posts_by_code();
		$package = array(
			'manifest' => array(
				'includes' => array( 'stations', 'routes' ),
			),
			'files'    => array(
				'stations.csv' => array(
					array(
						'station_code'    => 'x',
						'name'              => 'X',
						'station_type'      => 'station',
						'display_order'     => '1',
						'bus_stop_marker'   => '0',
					),
				),
				'routes.csv' => array(
					array(
						'route_code'         => 'r1',
						'title'              => 'R1',
						'start_station_code' => 'x',
						'end_station_code'   => 'x',
					),
				),
				'route_stations.csv' => array(
					array( 'route_code' => 'r1', 'sequence' => '1', 'station_code' => 'x' ),
				),
			),
		);

		$stats = MRT_csv_run_import( $package );

		self::assertSame( 1, $stats['stations'] );
		self::assertSame( 1, $stats['routes'] );
		self::assertSame( 0, $stats['services'] );
	}

	private function boot_get_posts_by_code(): void {
		$GLOBALS['mrt_test_post_meta'] = array();
		$GLOBALS['mrt_test_posts']     = array();
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( isset( $args['meta_query'][0]['key'], $args['meta_query'][0]['value'] ) ) {
				$key   = (string) $args['meta_query'][0]['key'];
				$value = (string) $args['meta_query'][0]['value'];
				foreach ( $GLOBALS['mrt_test_post_meta'] as $meta_key => $meta_value ) {
					if ( ! str_ends_with( $meta_key, '|' . $key ) ) {
						continue;
					}
					if ( (string) $meta_value !== $value ) {
						continue;
					}
					$id = (int) explode( '|', $meta_key )[0];
					return ( $args['fields'] ?? '' ) === 'ids' ? array( $id ) : array( $GLOBALS['mrt_test_posts'][ $id ] );
				}
				return array();
			}
			return array();
		};
	}
}
