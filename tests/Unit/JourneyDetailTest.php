<?php
/**
 * Journey segment detail (inc/domain/journey/journey-detail.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class JourneyDetailTest extends TestCase {

	/** @var mixed */
	private $original_wpdb = null;

	protected function tearDown(): void {
		if ( $this->original_wpdb !== null ) {
			$GLOBALS['wpdb'] = $this->original_wpdb;
		}
		unset( $GLOBALS['mrt_test_posts'] );
		parent::tearDown();
	}

	public function test_journey_map_stop_row_shapes_public_fields(): void {
		$GLOBALS['mrt_test_posts'] = array(
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Alpha' ) ),
		);
		$mapped = MRT_journey_map_stop_row(
			array_merge(
				array(
					'station_post_id' => 101,
					'stop_sequence'   => 1,
					'arrival_time'    => null,
					'departure_time'  => '09:00',
				),
				MRT_test_stop_modes_pickup_only()
			)
		);

		self::assertSame( 101, $mapped['station_id'] );
		self::assertSame( 'Alpha', $mapped['station_title'] );
		self::assertSame( '09:00', $mapped['departure_time'] );
		self::assertSame( 'on_request', $mapped['pickup_mode'] );
		self::assertSame( 'none', $mapped['dropoff_mode'] );
		self::assertSame( '09.00', $mapped['time_label'] );
		self::assertTrue( $mapped['on_request_pickup'] );
	}

	public function test_connection_journey_detail_returns_empty_for_invalid_ids(): void {
		$detail = MRT_get_connection_journey_detail( 0, 101, 102 );

		self::assertSame( array(), $detail['stops'] );
		self::assertNull( $detail['duration_minutes'] );
	}

	public function test_connection_journey_detail_rejects_same_station(): void {
		$detail = MRT_get_connection_journey_detail( 501, 101, 101 );

		self::assertSame( array(), $detail['stops'] );
	}

	public function test_connection_journey_detail_slices_stops_and_duration(): void {
		$GLOBALS['mrt_test_posts'] = array(
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Alpha' ) ),
			102 => new WP_Post( (object) array( 'ID' => 102, 'post_title' => 'Beta' ) ),
			103 => new WP_Post( (object) array( 'ID' => 103, 'post_title' => 'Gamma' ) ),
		);
		$this->boot_stop_times_db(
			array(
				array_merge(
					array(
						'station_post_id' => 101,
						'stop_sequence'   => 1,
						'arrival_time'    => null,
						'departure_time'  => '09:00',
					),
					MRT_test_stop_modes_both_scheduled()
				),
				array_merge(
					array(
						'station_post_id' => 102,
						'stop_sequence'   => 2,
						'arrival_time'    => '09:20',
						'departure_time'  => '09:22',
					),
					MRT_test_stop_modes_both_scheduled()
				),
				array_merge(
					array(
						'station_post_id' => 103,
						'stop_sequence'   => 3,
						'arrival_time'    => '09:45',
						'departure_time'  => null,
					),
					MRT_test_stop_modes_both_scheduled()
				),
			)
		);

		$detail = MRT_get_connection_journey_detail( 501, 101, 103 );

		self::assertCount( 3, $detail['stops'] );
		self::assertSame( 'Alpha', $detail['stops'][0]['station_title'] );
		self::assertSame( 'Gamma', $detail['stops'][2]['station_title'] );
		self::assertSame( '09.45', $detail['stops'][2]['time_label'] );
		self::assertSame( 45, $detail['duration_minutes'] );
	}

	public function test_connection_journey_detail_uses_arrival_at_last_stop(): void {
		$GLOBALS['mrt_test_posts'] = array(
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Start' ) ),
			102 => new WP_Post( (object) array( 'ID' => 102, 'post_title' => 'End' ) ),
		);
		$this->boot_stop_times_db(
			array(
				array_merge(
					array(
						'station_post_id' => 101,
						'stop_sequence'   => 1,
						'departure_time'  => '10:00',
					),
					MRT_test_stop_modes_pickup_only()
				),
				array_merge(
					array(
						'station_post_id'  => 102,
						'stop_sequence'    => 2,
						'arrival_time'     => '10:35',
						'departure_time'   => '10:45',
						'approximate_time' => 0,
					),
					MRT_test_stop_modes_both_scheduled()
				),
			)
		);

		$detail = MRT_get_connection_journey_detail( 501, 101, 102 );

		self::assertSame( '10.35', $detail['stops'][1]['time_label'] );
		self::assertFalse( $detail['stops'][1]['approximate_time'] );
	}

	public function test_connection_journey_detail_hides_pickup_footnote_at_origin(): void {
		$GLOBALS['mrt_test_posts'] = array(
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Start' ) ),
			102 => new WP_Post( (object) array( 'ID' => 102, 'post_title' => 'End' ) ),
		);
		$this->boot_stop_times_db(
			array(
				array_merge(
					array(
						'station_post_id' => 101,
						'stop_sequence'   => 1,
						'departure_time'  => '10:00',
					),
					MRT_test_stop_modes_pickup_only()
				),
				array_merge(
					array(
						'station_post_id' => 102,
						'stop_sequence'   => 2,
						'arrival_time'    => '10:35',
					),
					MRT_test_stop_modes_dropoff_only()
				),
			)
		);

		$detail = MRT_get_connection_journey_detail( 501, 101, 102 );

		self::assertFalse( $detail['stops'][0]['on_request_pickup'] );
		self::assertTrue( $detail['stops'][1]['on_request_dropoff'] );
	}

	public function test_connection_journey_detail_rejects_backwards_slice(): void {
		$this->boot_stop_times_db(
			array(
				array(
					'station_post_id' => 101,
					'stop_sequence'   => 1,
					'departure_time'  => '09:00',
				),
				array(
					'station_post_id' => 102,
					'stop_sequence'   => 2,
					'arrival_time'    => '09:30',
				),
			)
		);

		$detail = MRT_get_connection_journey_detail( 501, 102, 101 );

		self::assertSame( array(), $detail['stops'] );
	}

	/**
	 * @param array<int, array<string, mixed>> $rows
	 */
	private function boot_stop_times_db( array $rows ): void {
		$this->original_wpdb = $GLOBALS['wpdb'] ?? null;
		$GLOBALS['wpdb']     = new JourneyDetailTestDb( $rows );
	}
}

/** @internal */
final class JourneyDetailTestDb {
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
		if ( preg_match( '/service_post_id = (\d+)/', $query, $m ) ) {
			return $this->rows;
		}
		return array();
	}
}
