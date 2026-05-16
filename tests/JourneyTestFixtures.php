<?php
/**
 * Shared journey-search fixtures for PHPUnit.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

/**
 * Minimal wpdb fake for journey search tests.
 */
final class MRT_Journey_Test_Db {
	/** @var string */
	public $prefix = 'wp_';

	/** @var string */
	public $last_error = '';

	/** @var array<int, array<int, array<string, mixed>>> */
	private array $rows_by_service;

	/**
	 * @param array<int, array<int, array<string, mixed>>> $rows_by_service
	 */
	public function __construct( array $rows_by_service ) {
		$this->rows_by_service = $rows_by_service;
	}

	public function prepare( string $query, ...$args ): string {
		foreach ( $args as $arg ) {
			$query = (string) preg_replace( '/%[ds]/', $this->format_arg( $arg ), $query, 1 );
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
			return $this->ordered_stops_for_service( (int) $m[1] );
		}
		if ( str_contains( $query, 'FROM wp_mrt_stoptimes from_st' ) ) {
			return $this->connection_rows( $query );
		}
		return array();
	}

	private function format_arg( $arg ): string {
		if ( is_int( $arg ) || ctype_digit( (string) $arg ) ) {
			return (string) (int) $arg;
		}
		return "'" . addslashes( (string) $arg ) . "'";
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function ordered_stops_for_service( int $service_id ): array {
		$rows = $this->rows_by_service[ $service_id ] ?? array();
		usort( $rows, static fn ( array $a, array $b ): int => (int) $a['stop_sequence'] <=> (int) $b['stop_sequence'] );
		return $rows;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function connection_rows( string $query ): array {
		if ( ! preg_match( '/from_st\.station_post_id = (\d+).*to_st\.station_post_id = (\d+)/s', $query, $stations ) ) {
			return array();
		}
		$service_ids = $this->service_ids_from_query( $query );
		$rows        = $this->matching_connection_rows( (int) $stations[1], (int) $stations[2], $service_ids );
		usort( $rows, static fn ( array $a, array $b ): int => strcmp( (string) $a['from_departure'], (string) $b['from_departure'] ) );
		return $rows;
	}

	/**
	 * @return int[]
	 */
	private function service_ids_from_query( string $query ): array {
		if ( ! preg_match( '/service_post_id IN \(([^)]+)\)/', $query, $m ) ) {
			return array_keys( $this->rows_by_service );
		}
		return array_map( 'intval', array_filter( array_map( 'trim', explode( ',', $m[1] ) ) ) );
	}

	/**
	 * @param int[] $service_ids
	 * @return array<int, array<string, mixed>>
	 */
	private function matching_connection_rows( int $from_id, int $to_id, array $service_ids ): array {
		$out = array();
		foreach ( $service_ids as $service_id ) {
			$from = $this->stop_for_station( $service_id, $from_id );
			$to   = $this->stop_for_station( $service_id, $to_id );
			if ( $this->can_connect_on_service( $from, $to ) ) {
				$out[] = $this->connection_row( $service_id, $from, $to );
			}
		}
		return $out;
	}

	private function stop_for_station( int $service_id, int $station_id ): ?array {
		foreach ( $this->rows_by_service[ $service_id ] ?? array() as $row ) {
			if ( (int) $row['station_post_id'] === $station_id ) {
				return $row;
			}
		}
		return null;
	}

	private function can_connect_on_service( ?array $from, ?array $to ): bool {
		if ( $from === null || $to === null ) {
			return false;
		}
		return (int) $from['stop_sequence'] < (int) $to['stop_sequence']
			&& ! empty( $from['pickup_allowed'] )
			&& ! empty( $to['dropoff_allowed'] );
	}

	private function connection_row( int $service_id, array $from, array $to ): array {
		return array(
			'service_post_id' => $service_id,
			'from_departure' => $from['departure_time'],
			'from_arrival'   => $from['arrival_time'],
			'from_sequence'  => $from['stop_sequence'],
			'to_arrival'     => $to['arrival_time'],
			'to_departure'   => $to['departure_time'],
			'to_sequence'    => $to['stop_sequence'],
		);
	}
}

/**
 * Fixture helpers for journey tests.
 */
trait MRT_Journey_Test_Fixture {
	/** @var mixed */
	private $mrt_original_wpdb = null;

	/**
	 * @param array<int, array<int, array<string, mixed>>> $rows_by_service
	 * @param array<int, string[]>                         $timetable_dates
	 * @param array<int, int>                              $service_timetables
	 */
	protected function mrt_use_journey_fixture( array $rows_by_service, array $timetable_dates, array $service_timetables = array() ): void {
		$this->mrt_original_wpdb = $GLOBALS['wpdb'] ?? null;
		$service_timetables      = $this->mrt_service_timetables( array_keys( $rows_by_service ), $service_timetables );
		$GLOBALS['wpdb']         = new MRT_Journey_Test_Db( $rows_by_service );
		$GLOBALS['mrt_test_post_meta'] = $this->mrt_post_meta( $timetable_dates, $service_timetables );
		$GLOBALS['mrt_test_get_posts'] = $this->mrt_get_posts_callback( $timetable_dates, $service_timetables );
	}

	protected function mrt_reset_journey_fixture(): void {
		if ( $this->mrt_original_wpdb !== null ) {
			$GLOBALS['wpdb'] = $this->mrt_original_wpdb;
		}
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_get_posts'] );
	}

	protected function mrt_stop( int $service_id, int $station_id, int $sequence, ?string $arrival, ?string $departure ): array {
		return array(
			'service_post_id' => $service_id,
			'station_post_id' => $station_id,
			'stop_sequence'   => $sequence,
			'arrival_time'    => $arrival,
			'departure_time'  => $departure,
			'pickup_allowed'  => 1,
			'dropoff_allowed' => 1,
		);
	}

	/**
	 * @param int[]           $service_ids
	 * @param array<int, int> $service_timetables
	 * @return array<int, int>
	 */
	private function mrt_service_timetables( array $service_ids, array $service_timetables ): array {
		foreach ( $service_ids as $service_id ) {
			$service_timetables[ (int) $service_id ] = $service_timetables[ (int) $service_id ] ?? 900;
		}
		return $service_timetables;
	}

	/**
	 * @param array<int, string[]> $timetable_dates
	 * @param array<int, int>      $service_timetables
	 * @return array<string, mixed>
	 */
	private function mrt_post_meta( array $timetable_dates, array $service_timetables ): array {
		$meta = array();
		foreach ( $timetable_dates as $timetable_id => $dates ) {
			$meta[ (int) $timetable_id . '|mrt_timetable_dates' ] = $dates;
		}
		foreach ( $service_timetables as $service_id => $timetable_id ) {
			$meta[ (int) $service_id . '|mrt_service_timetable_id' ] = (int) $timetable_id;
			$meta[ (int) $service_id . '|mrt_service_number' ]       = (string) $service_id;
		}
		return $meta;
	}

	/**
	 * @param array<int, string[]> $timetable_dates
	 * @param array<int, int>      $service_timetables
	 */
	private function mrt_get_posts_callback( array $timetable_dates, array $service_timetables ): Closure {
		return static function ( array $args ) use ( $timetable_dates, $service_timetables ): array {
			$post_type = (string) ( $args['post_type'] ?? '' );
			if ( $post_type === MRT_POST_TYPE_TIMETABLE ) {
				return MRT_Journey_Test_Fixture_Filter::timetables( $args, $timetable_dates );
			}
			if ( $post_type === MRT_POST_TYPE_SERVICE ) {
				return MRT_Journey_Test_Fixture_Filter::services( $args, $service_timetables );
			}
			return array();
		};
	}
}

/**
 * Small static filters used by journey fixtures.
 */
final class MRT_Journey_Test_Fixture_Filter {
	/**
	 * @param array<string, mixed> $args
	 * @param array<int, string[]> $timetable_dates
	 * @return int[]
	 */
	public static function timetables( array $args, array $timetable_dates ): array {
		$date = (string) ( $args['meta_query'][0]['value'] ?? '' );
		if ( $date === '' ) {
			return array_keys( $timetable_dates );
		}
		return array_values(
			array_filter(
				array_keys( $timetable_dates ),
				static fn ( int $id ): bool => in_array( $date, $timetable_dates[ $id ], true )
			)
		);
	}

	/**
	 * @param array<string, mixed> $args
	 * @param array<int, int>      $service_timetables
	 * @return int[]
	 */
	public static function services( array $args, array $service_timetables ): array {
		$timetables = (array) ( $args['meta_query'][0]['value'] ?? array_values( $service_timetables ) );
		$timetables = array_map( 'intval', $timetables );
		return array_values(
			array_filter(
				array_keys( $service_timetables ),
				static fn ( int $id ): bool => in_array( (int) $service_timetables[ $id ], $timetables, true )
			)
		);
	}
}
