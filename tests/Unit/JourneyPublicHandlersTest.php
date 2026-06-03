<?php
/**
 * Journey public handler helpers (inc/domain/journey/public-handlers.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/journey/public-handlers.php';

final class JourneyPublicHandlersTest extends TestCase {

	/** @var mixed */
	private $original_wpdb = null;

	protected function tearDown(): void {
		if ( $this->original_wpdb !== null ) {
			$GLOBALS['wpdb'] = $this->original_wpdb;
		}
		unset( $GLOBALS['mrt_test_get_posts'], $GLOBALS['mrt_test_posts'], $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
	}

	public function test_calendar_response_rejects_invalid_month(): void {
		$result = MRT_journey_calendar_response(
			array(
				'from_station' => '1',
				'to_station'   => '2',
				'year'         => '2026',
				'month'        => '13',
			)
		);

		self::assertInstanceOf( WP_Error::class, $result );
	}

	public function test_calendar_response_returns_month_shape(): void {
		$result = MRT_journey_calendar_response(
			array(
				'from_station' => '1',
				'to_station'   => '2',
				'year'         => '2026',
				'month'        => '6',
			)
		);

		self::assertIsArray( $result );
		self::assertSame( 2026, $result['year'] );
		self::assertSame( 6, $result['month'] );
		self::assertIsArray( $result['days'] );
	}

	public function test_connection_detail_response_returns_stops(): void {
		$GLOBALS['mrt_test_posts'] = array(
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Alpha' ) ),
			102 => new WP_Post( (object) array( 'ID' => 102, 'post_title' => 'Beta' ) ),
		);
		$this->original_wpdb = $GLOBALS['wpdb'] ?? null;
		$GLOBALS['wpdb']     = new JourneyPublicHandlersTestDb(
			array(
				array(
					'station_post_id' => 101,
					'stop_sequence'   => 1,
					'departure_time'  => '09:00',
					'arrival_time'    => null,
					'pickup_allowed'  => 1,
					'dropoff_allowed' => 1,
				),
				array(
					'station_post_id' => 102,
					'stop_sequence'   => 2,
					'arrival_time'    => '09:30',
					'departure_time'  => null,
					'pickup_allowed'  => 1,
					'dropoff_allowed' => 1,
				),
			)
		);

		$result = MRT_journey_connection_detail_response(
			array(
				'from_station' => '101',
				'to_station'   => '102',
				'service_id'   => '501',
			)
		);

		self::assertIsArray( $result );
		self::assertCount( 2, $result['detail']['stops'] );
		self::assertSame( 30, $result['detail']['duration_minutes'] );
	}

	public function test_find_connections_delegates_single_trip_to_normalized_search(): void {
		$GLOBALS['mrt_test_get_posts'] = static function (): array {
			return array();
		};

		$connections = MRT_journey_find_connections(
			array(
				'trip_type'              => 'single',
				'from'                   => 1,
				'to'                     => 2,
				'date'                   => '2026-07-04',
				'outbound_arrival'       => '',
				'min_turnaround_minutes' => 0,
			)
		);

		self::assertSame( array(), $connections );
	}
}

/** @internal */
final class JourneyPublicHandlersTestDb {
	/** @var string */
	public $prefix = 'wp_';

	/** @var string */
	public $last_error = '';

	/** @var array<int, array<string, mixed>> */
	private array $rows;

	/** @param array<int, array<string, mixed>> $rows */
	public function __construct( array $rows ) {
		$this->rows = $rows;
	}

	public function prepare( string $query, ...$args ): string {
		foreach ( $args as $arg ) {
			$query = (string) preg_replace( '/%[ds]/', (string) (int) $arg, $query, 1 );
		}
		return $query;
	}

	/** @return array<int, array<string, mixed>> */
	public function get_results( $query = null, $output = ARRAY_A ): array {
		unset( $output );
		return $this->rows;
	}
}
