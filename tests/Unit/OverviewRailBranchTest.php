<?php
/**
 * Full overview rail and branch JSON payloads.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OverviewRailBranchTest extends TestCase {

	/** @var mixed */
	private $original_wpdb = null;

	protected function tearDown(): void {
		if ( $this->original_wpdb !== null ) {
			$GLOBALS['wpdb'] = $this->original_wpdb;
		}
		unset(
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_posts'],
			$GLOBALS['mrt_test_terms'],
			$GLOBALS['mrt_test_post_terms'],
			$GLOBALS['mrt_test_get_posts']
		);
		parent::tearDown();
	}

	public function test_rail_group_to_json_builds_columns_and_rows(): void {
		if ( ! defined( 'MRT_URL' ) ) {
			define( 'MRT_URL', 'https://example.test/wp-content/plugins/museum-railway-timetable/' );
		}
		$this->boot_rail_fixture();

		$group = array(
			'route'     => $GLOBALS['mrt_test_posts'][50],
			'direction' => 'outbound',
			'stations'  => array( 101, 102 ),
			'services'  => array(
				array(
					'service'    => $GLOBALS['mrt_test_posts'][501],
					'train_type' => $GLOBALS['mrt_test_terms'][20],
					'stop_times' => array(
						101 => array_merge(
							array(
								'departure_time' => '09:00',
								'arrival_time'   => '',
							),
							MRT_test_stop_modes_both_scheduled()
						),
						102 => array_merge(
							array(
								'arrival_time'   => '09:30',
								'departure_time' => '',
							),
							MRT_test_stop_modes_both_scheduled()
						),
					),
				),
			),
		);

		$json = MRT_timetable_rail_group_to_json( $group, '2026-06-06' );

		self::assertSame( 'rail', $json['kind'] );
		self::assertSame( 'Från Alpha Till Beta', $json['routeLabel'] );
		self::assertStringContainsString( 'Alpha', $json['fromLabel'] );
		self::assertStringContainsString( 'Beta', $json['toLabel'] );
		self::assertCount( 1, $json['columns'] );
		self::assertSame( 501, $json['columns'][0]['serviceId'] );
		self::assertNotEmpty( $json['rows'] );
		self::assertSame( 'from', $json['rows'][0]['kind'] );
	}

	public function test_branch_group_to_json_lists_trip_times(): void {
		$this->boot_branch_fixture();

		$group = array(
			'route'     => $GLOBALS['mrt_test_posts'][60],
			'direction' => 'outbound',
			'stations'  => array( 9, 15 ),
			'services'  => array(
				array(
					'service'    => $GLOBALS['mrt_test_posts'][502],
					'train_type' => $GLOBALS['mrt_test_terms'][21],
					'stop_times' => array(
						9  => array_merge(
							array(
								'departure_time' => '10:53',
								'arrival_time'   => '',
							),
							MRT_test_stop_modes_both_scheduled()
						),
						15 => array_merge(
							array(
								'arrival_time'   => '11:00',
								'departure_time' => '',
							),
							MRT_test_stop_modes_both_scheduled()
						),
					),
				),
			),
		);

		$json = MRT_timetable_branch_group_to_json( $group, '2026-06-06' );

		self::assertSame( 'branch', $json['kind'] );
		self::assertCount( 1, $json['trips'] );
		self::assertSame( 'B1', $json['trips'][0]['trip'] );
		self::assertStringContainsString( '10.53', $json['trips'][0]['fromTime'] );
	}

	private function boot_rail_fixture(): void {
		$GLOBALS['mrt_test_posts'] = array(
			50  => new WP_Post( (object) array( 'ID' => 50, 'post_title' => 'Line', 'post_type' => MRT_POST_TYPE_ROUTE ) ),
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Alpha', 'post_type' => MRT_POST_TYPE_STATION ) ),
			102 => new WP_Post( (object) array( 'ID' => 102, 'post_title' => 'Beta', 'post_type' => MRT_POST_TYPE_STATION ) ),
			501 => new WP_Post( (object) array( 'ID' => 501, 'post_title' => 'Tur 1', 'post_type' => MRT_POST_TYPE_SERVICE ) ),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'50|mrt_route_stations'          => array( 101, 102 ),
			'50|mrt_route_start_station'     => 101,
			'50|mrt_route_end_station'       => 102,
			'501|mrt_service_number'         => '1',
			'501|mrt_service_end_station_id' => 102,
			'101|mrt_display_order'          => 1,
		);
		$term       = new WP_Term();
		$term->term_id = 20;
		$term->name    = 'Ångtåg';
		$term->slug    = 'angtag';
		$GLOBALS['mrt_test_terms']      = array( 20 => $term );
		$GLOBALS['mrt_test_post_terms'] = array( 501 => array( 20 ) );
		$GLOBALS['mrt_test_get_posts']  = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_STATION && isset( $args['post__in'] ) ) {
				$out = array();
				foreach ( (array) $args['post__in'] as $id ) {
					$out[] = $GLOBALS['mrt_test_posts'][ (int) $id ];
				}
				return $out;
			}
			return array();
		};
		$this->original_wpdb = $GLOBALS['wpdb'] ?? null;
		$GLOBALS['wpdb']     = new OverviewRailBranchTestDb( array() );
	}

	private function boot_branch_fixture(): void {
		$GLOBALS['mrt_test_posts'] = array(
			60  => new WP_Post( (object) array( 'ID' => 60, 'post_title' => 'Selknä buss', 'post_type' => MRT_POST_TYPE_ROUTE ) ),
			9   => new WP_Post( (object) array( 'ID' => 9, 'post_title' => 'Selknä', 'post_type' => MRT_POST_TYPE_STATION ) ),
			15  => new WP_Post( (object) array( 'ID' => 15, 'post_title' => 'Fjällnora', 'post_type' => MRT_POST_TYPE_STATION ) ),
			502 => new WP_Post( (object) array( 'ID' => 502, 'post_title' => 'Buss 1', 'post_type' => MRT_POST_TYPE_SERVICE ) ),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'60|mrt_route_stations'          => array( 9, 15 ),
			'60|mrt_route_start_station'     => 9,
			'60|mrt_route_end_station'       => 15,
			'502|mrt_service_number'         => 'B1',
			'502|mrt_service_end_station_id' => 15,
			'9|mrt_display_order'            => 1,
			'15|mrt_display_order'           => 2,
		);
		$term       = new WP_Term();
		$term->term_id = 21;
		$term->name    = 'Buss';
		$term->slug    = 'buss';
		$GLOBALS['mrt_test_terms']      = array( 21 => $term );
		$GLOBALS['mrt_test_post_terms'] = array( 502 => array( 21 ) );
		$GLOBALS['mrt_test_get_posts']  = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_STATION && isset( $args['post__in'] ) ) {
				$out = array();
				foreach ( (array) $args['post__in'] as $id ) {
					$out[] = $GLOBALS['mrt_test_posts'][ (int) $id ];
				}
				return $out;
			}
			return array();
		};
		$this->original_wpdb = $GLOBALS['wpdb'] ?? null;
		$GLOBALS['wpdb']     = new OverviewRailBranchTestDb( array() );
	}
}

/** @internal */
final class OverviewRailBranchTestDb {
	/** @var string */
	public $prefix = 'wp_';

	/** @var string */
	public $last_error = '';

	/** @param array<int, array<string, mixed>> $rows */
	public function __construct( array $rows ) {
		unset( $rows );
	}

	public function prepare( string $query, ...$args ): string {
		unset( $args );
		return $query;
	}

	/** @return array<int, array<string, mixed>> */
	public function get_results( $query = null, $output = ARRAY_A ): array {
		unset( $query, $output );
		return array();
	}
}
