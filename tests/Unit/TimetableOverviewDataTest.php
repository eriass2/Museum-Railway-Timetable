<?php
/**
 * Timetable overview JSON payload (overview-data.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TimetableOverviewDataTest extends TestCase {

	/** @var mixed */
	private $original_wpdb = null;

	protected function tearDown(): void {
		if ( $this->original_wpdb !== null ) {
			$GLOBALS['wpdb'] = $this->original_wpdb;
		}
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'], $GLOBALS['mrt_test_get_posts'], $GLOBALS['mrt_test_terms'], $GLOBALS['mrt_test_post_terms'] );
		parent::tearDown();
	}

	public function test_build_overview_payload_rejects_empty_services(): void {
		$result = MRT_build_timetable_overview_payload(
			array(),
			'2026-06-06',
			array( 'emptyMessage' => 'Nothing here' )
		);

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'empty', $result->get_error_code() );
		self::assertSame( 'Nothing here', $result->get_error_message() );
	}

	public function test_get_overview_data_rejects_invalid_timetable(): void {
		$result = MRT_get_timetable_overview_data( 0 );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid_timetable', $result->get_error_code() );
	}

	public function test_get_overview_data_builds_rail_group_for_single_service(): void {
		if ( ! defined( 'MRT_URL' ) ) {
			define( 'MRT_URL', 'https://example.test/wp-content/plugins/museum-railway-timetable/' );
		}
		$this->boot_minimal_overview_fixture();
		$data = MRT_get_timetable_overview_data( 10, '2026-06-06' );

		self::assertIsArray( $data );
		self::assertSame( 'timetable', $data['scope'] );
		self::assertSame( 10, $data['timetableId'] );
		self::assertSame( '2026-06-06', $data['dateYmd'] );
		self::assertNotEmpty( $data['groups'] );
		self::assertSame( 'rail', $data['groups'][0]['kind'] ?? '' );
	}

	private function boot_minimal_overview_fixture(): void {
		$GLOBALS['mrt_test_posts'] = array(
			10  => new WP_Post( (object) array( 'ID' => 10, 'post_title' => 'Green', 'post_type' => MRT_POST_TYPE_TIMETABLE ) ),
			50  => new WP_Post( (object) array( 'ID' => 50, 'post_title' => 'Line', 'post_type' => MRT_POST_TYPE_ROUTE ) ),
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Alpha', 'post_type' => MRT_POST_TYPE_STATION ) ),
			102 => new WP_Post( (object) array( 'ID' => 102, 'post_title' => 'Beta', 'post_type' => MRT_POST_TYPE_STATION ) ),
			501 => new WP_Post( (object) array( 'ID' => 501, 'post_title' => 'Tur 1', 'post_type' => MRT_POST_TYPE_SERVICE ) ),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'10|mrt_timetable_type'          => 'green',
			'50|mrt_route_stations'          => array( 101, 102 ),
			'50|mrt_route_start_station'     => 101,
			'50|mrt_route_end_station'       => 102,
			'501|mrt_service_timetable_id'   => 10,
			'501|mrt_service_route_id'       => 50,
			'501|mrt_service_end_station_id' => 102,
			'501|mrt_service_number'       => '1',
			'101|mrt_display_order'          => 1,
		);
		$term = new WP_Term();
		$term->term_id = 20;
		$term->name    = 'Ångtåg';
		$term->slug    = 'angtag';
		$GLOBALS['mrt_test_terms']      = array( 20 => $term );
		$GLOBALS['mrt_test_post_terms'] = array( 501 => array( 20 ) );
		$GLOBALS['mrt_test_get_posts']  = static function ( array $args ): array {
			$post_type = (string) ( $args['post_type'] ?? '' );
			if ( $post_type === MRT_POST_TYPE_SERVICE && ( $args['meta_query'][0]['key'] ?? '' ) === 'mrt_service_timetable_id' ) {
				return array( $GLOBALS['mrt_test_posts'][501] );
			}
			if ( $post_type === MRT_POST_TYPE_STATION && isset( $args['post__in'] ) ) {
				$out = array();
				foreach ( (array) $args['post__in'] as $id ) {
					$out[] = $GLOBALS['mrt_test_posts'][ (int) $id ];
				}
				return $out;
			}
			return array();
		};
		$this->original_wpdb = $GLOBALS['wpdb'] ?? null;
		$GLOBALS['wpdb']     = new TimetableOverviewDataTestDb(
			array(
				array(
					'service_post_id' => 501,
					'station_post_id' => 101,
					'stop_sequence'   => 1,
					'arrival_time'    => null,
					'departure_time'  => '09:00',
					'pickup_allowed'  => 1,
					'dropoff_allowed' => 1,
				),
				array(
					'service_post_id' => 501,
					'station_post_id' => 102,
					'stop_sequence'   => 2,
					'arrival_time'    => '09:30',
					'departure_time'  => null,
					'pickup_allowed'  => 1,
					'dropoff_allowed' => 1,
				),
			)
		);
	}
}

/** @internal */
final class TimetableOverviewDataTestDb {
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
		unset( $output );
		$query = (string) $query;
		if ( preg_match( '/WHERE service_post_id = (\d+)/', $query, $m ) ) {
			$service_id = (int) $m[1];
			return array_values(
				array_filter(
					$this->rows,
					static fn ( array $row ): bool => (int) $row['service_post_id'] === $service_id
				)
			);
		}
		return array();
	}
}
