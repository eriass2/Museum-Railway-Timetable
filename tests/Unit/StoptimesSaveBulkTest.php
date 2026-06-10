<?php
/**
 * Bulk stop-time save and editor payload (inc/domain/service/stoptimes-save.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/service/stoptimes-save.php';

final class StoptimesSaveBulkTest extends TestCase {

	/** @var mixed */
	private $original_wpdb = null;

	protected function tearDown(): void {
		if ( $this->original_wpdb !== null ) {
			$GLOBALS['wpdb'] = $this->original_wpdb;
		}
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_posts'], $GLOBALS['mrt_test_get_posts'] );
		parent::tearDown();
	}

	public function test_save_service_stoptimes_bulk_rejects_invalid_service(): void {
		$result = MRT_save_service_stoptimes_bulk( 0, array() );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid_service', $result->get_error_code() );
	}

	public function test_save_service_stoptimes_bulk_rejects_invalid_time(): void {
		$result = MRT_save_service_stoptimes_bulk(
			10,
			array(
				array(
					'station_id' => 1,
					'stops_here' => '1',
					'arrival'    => '99:00',
				),
			)
		);

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid_time', $result->get_error_code() );
	}

	public function test_save_service_stoptimes_bulk_replaces_rows_on_success(): void {
		$this->boot_success_wpdb();

		$result = MRT_save_service_stoptimes_bulk(
			501,
			array(
				array(
					'station_id' => 101,
					'stops_here' => '1',
					'departure'  => '09:00',
					'pickup'     => '1',
					'dropoff'    => '1',
				),
				array(
					'station_id' => 102,
					'stops_here' => '1',
					'arrival'    => '09:30',
					'pickup'     => '1',
					'dropoff'    => '1',
				),
			)
		);

		if ( is_wp_error( $result ) ) {
			self::fail( 'WP_Error: ' . $result->get_error_code() );
		}
		self::assertSame( true, $result );
		self::assertSame( 2, $GLOBALS['wpdb']->insert_count );
		self::assertTrue( $GLOBALS['wpdb']->query_called );
	}

	public function test_save_service_stoptimes_bulk_rolls_back_on_insert_failure(): void {
		$wpdb = new StoptimesSaveBulkFailInsertDb();
		$this->original_wpdb = $GLOBALS['wpdb'] ?? null;
		$GLOBALS['wpdb']     = $wpdb;

		$result = MRT_save_service_stoptimes_bulk(
			501,
			array(
				array(
					'station_id' => 101,
					'stops_here' => '1',
					'departure'  => '09:00',
				),
			)
		);

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'db_insert', $result->get_error_code() );
	}

	public function test_get_service_stoptimes_editor_payload_requires_route(): void {
		$result = MRT_get_service_stoptimes_editor_payload( 501 );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'no_route', $result->get_error_code() );
	}

	public function test_get_service_stoptimes_editor_payload_merges_route_and_existing(): void {
		$this->boot_editor_fixture();

		$result = MRT_get_service_stoptimes_editor_payload( 501 );

		self::assertIsArray( $result );
		self::assertSame( 50, $result['route_id'] );
		self::assertCount( 2, $result['stations'] );
		self::assertSame( 101, $result['stations'][0]['id'] );
		self::assertSame( 'Alpha', $result['stations'][0]['name'] );
		self::assertTrue( $result['stations'][0]['stops_here'] );
		self::assertSame( '09:00', $result['stations'][0]['departure_time'] );
		self::assertFalse( $result['stations'][1]['stops_here'] );
	}

	private function boot_success_wpdb(): void {
		$this->original_wpdb = $GLOBALS['wpdb'] ?? null;
		$GLOBALS['wpdb']     = new StoptimesSaveBulkSuccessDb();
	}

	private function boot_editor_fixture(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_route_id' => 50,
			'50|mrt_route_stations'    => array( 101, 102 ),
		);
		$GLOBALS['mrt_test_posts'] = array(
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Alpha', 'post_type' => MRT_POST_TYPE_STATION ) ),
			102 => new WP_Post( (object) array( 'ID' => 102, 'post_title' => 'Beta', 'post_type' => MRT_POST_TYPE_STATION ) ),
		);
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) !== MRT_POST_TYPE_STATION ) {
				return array();
			}
			$out = array();
			foreach ( (array) ( $args['post__in'] ?? array() ) as $id ) {
				if ( isset( $GLOBALS['mrt_test_posts'][ (int) $id ] ) ) {
					$out[] = $GLOBALS['mrt_test_posts'][ (int) $id ];
				}
			}
			return $out;
		};
		$this->original_wpdb = $GLOBALS['wpdb'] ?? null;
		$GLOBALS['wpdb']     = new StoptimesSaveBulkEditorDb();
	}
}

/** @internal */
final class StoptimesSaveBulkSuccessDb {
	/** @var string */
	public $prefix = 'wp_';

	/** @var string */
	public $last_error = '';

	public int $insert_id = 100;

	public int $insert_count = 0;

	public bool $delete_called = false;

	public bool $query_called = false;

	public int $cleanup_count = 0;

	public function prepare( string $query, ...$args ): string {
		foreach ( $args as $arg ) {
			$query = (string) preg_replace( '/%[ds]/', (string) (int) $arg, $query, 1 );
		}
		return $query;
	}

	/**
	 * @param array<string, mixed> $data
	 * @param array<int, string>   $format
	 */
	public function insert( $table, $data, $format ) {
		unset( $table, $data, $format );
		++$this->insert_count;
		++$this->insert_id;
		return 1;
	}

	/**
	 * @param array<string, mixed> $data
	 * @param array<int, string> $where_format
	 */
	public function delete( $table, $data, $where_format = null ) {
		unset( $table, $data, $where_format );
		$this->delete_called = true;
		return 1;
	}

	public function query( string $query ) {
		unset( $query );
		$this->query_called = true;
		return 1;
	}
}

/** @internal */
final class StoptimesSaveBulkFailInsertDb {
	/** @var string */
	public $prefix = 'wp_';

	/** @var string */
	public $last_error = 'insert failed';

	public int $cleanup_count = 0;

	public function prepare( string $query, ...$args ): string {
		unset( $args );
		return $query;
	}

	/**
	 * @param array<string, mixed> $data
	 * @param array<int, string>   $format
	 */
	public function insert( $table, $data, $format ) {
		unset( $table, $data, $format );
		return false;
	}

	/**
	 * @param array<string, mixed> $data
	 * @param array<int, string> $where_format
	 */
	public function delete( $table, $data, $where_format = null ) {
		unset( $table, $data, $where_format );
		++$this->cleanup_count;
		return 1;
	}
}

/** @internal */
final class StoptimesSaveBulkEditorDb {
	/** @var string */
	public $prefix = 'wp_';

	/** @var string */
	public $last_error = '';

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
		if ( preg_match( '/service_post_id = (\d+)/', $query, $m ) && (int) $m[1] === 501 ) {
			return array(
				array_merge(
					array(
						'station_post_id' => 101,
						'stop_sequence'   => 1,
						'arrival_time'    => null,
						'departure_time'  => '09:00',
					),
					MRT_test_stop_modes_both_scheduled()
				),
			);
		}
		return array();
	}
}
