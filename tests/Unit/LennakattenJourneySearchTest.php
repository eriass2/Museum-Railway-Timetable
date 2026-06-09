<?php
/**
 * Journey search against Lennakatten CSV fixture (Anslagstidtabell reference trips).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/loader.php';
require_once ABSPATH . 'inc/domain/journey/train-change.php';

final class LennakattenJourneySearchTest extends TestCase {
	use MRT_Journey_Test_Fixture;

	private const DATE_GREEN = '2026-06-06';
	private const DATE_RED   = '2026-07-05';

	/** @var array<string, int>|null */
	private static ?array $station_ids = null;

	protected function tearDown(): void {
		$this->mrt_reset_journey_fixture();
		unset( $GLOBALS['mrt_test_options'] );
		parent::tearDown();
	}

	/**
	 * @return array<string, int>
	 */
	private function station_ids(): array {
		if ( self::$station_ids !== null ) {
			return self::$station_ids;
		}
		$ids = array();
		$next = 1001;
		foreach ( $this->fixture_files()['stations.csv'] ?? array() as $row ) {
			$code = (string) ( $row['station_code'] ?? '' );
			if ( $code === '' ) {
				continue;
			}
			$ids[ $code ] = $next++;
		}
		self::$station_ids = $ids;
		return $ids;
	}

	public function test_fixture_marielund_allows_dropoff_on_reference_trains(): void {
		$cases = array(
			array( 'green-71-out', 'marielund' ),
			array( 'red-81-out', 'marielund' ),
			array( 'orange-73-out', 'marielund' ),
		);
		foreach ( $cases as $case ) {
			$service_code = $case[0];
			$station_code = $case[1];
			$row = $this->fixture_stop_row( $service_code, $station_code );
			self::assertSame(
				'1',
				$row['dropoff_allowed'] ?? '',
				"{$service_code} at {$station_code} must allow dropoff per Anslagstidtabell"
			);
		}
	}

	public function test_find_connections_uppsala_marielund_on_green_saturday(): void {
		$this->boot_service_fixture( 'green-71-out', self::DATE_GREEN );
		$stations = $this->station_ids();

		$connections = MRT_find_connections(
			$stations['uppsala-ostra'],
			$stations['marielund'],
			self::DATE_GREEN
		);

		self::assertNotEmpty( $connections, 'Expected Uppsala Östra → Marielund on green traffic day' );
		self::assertSame( '10:00', $connections[0]['from_departure'] ?? '' );
		self::assertSame( '10:35', $connections[0]['to_arrival'] ?? '' );
	}

	public function test_green_71_out_alone_does_not_reach_faringe(): void {
		$this->boot_service_fixture( 'green-71-out', self::DATE_GREEN );
		$stations = $this->station_ids();

		$connections = MRT_find_connections(
			$stations['uppsala-ostra'],
			$stations['faringe'],
			self::DATE_GREEN
		);

		self::assertSame( array(), $connections );
	}

	public function test_find_uppsala_faringe_on_green_train_71_splits_at_marielund(): void {
		$this->boot_fixture_services(
			array( 'green-71-out', 'green-61-out' ),
			self::DATE_GREEN
		);
		$stations = $this->station_ids();

		$results = MRT_journey_find_normalized_connections(
			$stations['uppsala-ostra'],
			$stations['faringe'],
			self::DATE_GREEN
		);

		self::assertNotEmpty( $results );
		$first = $results[0];
		self::assertSame( 'transfer', $first['connection_type'] ?? '' );
		self::assertSame( $stations['marielund'], $first['transfer_station_id'] ?? 0 );
		self::assertCount( 2, $first['legs'] ?? array() );
		self::assertSame( '71', $first['legs'][0]['service_number'] ?? '' );
		self::assertSame( '61', $first['legs'][1]['service_number'] ?? '' );
		self::assertSame( '11:25', $first['to_arrival'] ?? '' );
	}

	public function test_find_uppsala_marielund_stays_direct_with_alighting_destination(): void {
		$this->boot_service_fixture( 'green-71-out', self::DATE_GREEN );
		$stations = $this->station_ids();

		$results = MRT_journey_find_normalized_connections(
			$stations['uppsala-ostra'],
			$stations['marielund'],
			self::DATE_GREEN
		);

		self::assertNotEmpty( $results );
		self::assertSame( 'direct', $results[0]['connection_type'] ?? '' );
		self::assertSame( 'Marielund', $results[0]['legs'][0]['destination'] ?? '' );
	}

	public function test_find_connections_uppsala_faringe_on_green_thuns_express(): void {
		$this->boot_service_fixture( 'green-93-out', self::DATE_GREEN );
		$stations = $this->station_ids();

		$connections = MRT_find_connections(
			$stations['uppsala-ostra'],
			$stations['faringe'],
			self::DATE_GREEN
		);

		self::assertNotEmpty( $connections );
		self::assertSame( '11:10', $connections[0]['from_departure'] ?? '' );
		self::assertSame( '12:17', $connections[0]['to_arrival'] ?? '' );
	}

	public function test_find_connections_uppsala_marielund_on_yellow_friday(): void {
		$this->boot_service_fixture( 'yellow-101-out', '2026-06-05' );
		$stations = $this->station_ids();

		$connections = MRT_find_connections(
			$stations['uppsala-ostra'],
			$stations['marielund'],
			'2026-06-05'
		);

		self::assertNotEmpty( $connections );
		self::assertSame( '16:45', $connections[0]['from_departure'] ?? '' );
		self::assertSame( '17:10', $connections[0]['to_arrival'] ?? '' );
	}

	public function test_find_connections_selkna_fjallnora_on_green_buss_day(): void {
		$this->boot_service_fixture( 'green-b1-bus-out', '2026-07-04' );
		$stations = $this->station_ids();

		$connections = MRT_find_connections(
			$stations['selkna'],
			$stations['fjallnora'],
			'2026-07-04'
		);

		self::assertNotEmpty( $connections );
		self::assertSame( '10:53', $connections[0]['from_departure'] ?? '' );
		self::assertSame( '11:00', $connections[0]['to_arrival'] ?? '' );
	}

	public function test_fixture_green_buss_b1_matches_anslag_branch_times(): void {
		$row = $this->fixture_stop_row( 'green-b1-bus-out', 'selkna' );
		self::assertSame( '10:53', $row['departure_time'] ?? '' );
		$fjar = $this->fixture_stop_row( 'green-b1-bus-out', 'fjallnora' );
		self::assertSame( '11:00', $fjar['arrival_time'] ?? '' );
	}

	public function test_find_connections_selkna_linnes_hammarby_on_green_buss_day(): void {
		$this->boot_service_fixture( 'green-b5-bus-out', '2026-07-04' );
		$stations = $this->station_ids();

		$connections = MRT_find_connections(
			$stations['selkna'],
			$stations['linnes-hammarby'],
			'2026-07-04'
		);

		self::assertNotEmpty( $connections );
		self::assertSame( '10:53', $connections[0]['from_departure'] ?? '' );
		self::assertSame( '11:00', $connections[0]['to_arrival'] ?? '' );
	}

	public function test_find_multi_leg_uppsala_linnes_hammarby_on_green_buss_day(): void {
		$this->boot_fixture_services(
			array( 'green-71-out', 'green-61-out', 'green-b5-bus-out' ),
			'2026-07-04'
		);
		$stations = $this->station_ids();

		$results = MRT_journey_find_normalized_connections(
			$stations['uppsala-ostra'],
			$stations['linnes-hammarby'],
			'2026-07-04'
		);

		self::assertNotEmpty( $results, 'Expected Uppsala Östra → Linnés Hammarby via Marielund, Selknä' );
		$first = $results[0];
		self::assertSame( 'transfer', $first['connection_type'] ?? '' );
		self::assertGreaterThanOrEqual( 2, count( $first['legs'] ?? array() ) );
		self::assertSame( '10:53', $first['legs'][ count( $first['legs'] ) - 1 ]['from_departure'] ?? '' );
		self::assertSame( '11:00', MRT_journey_normalized_arrival_hhmm( $first ) );
	}

	public function test_find_multi_leg_train_to_bus_at_selkna_on_green_buss_day(): void {
		$this->boot_fixture_services(
			array( 'green-61-out', 'green-b1-bus-out' ),
			'2026-07-04'
		);
		$stations = $this->station_ids();

		$results = MRT_find_multi_leg_connections(
			$stations['marielund'],
			$stations['fjallnora'],
			'2026-07-04'
		);

		self::assertNotEmpty( $results, 'Expected train+bus Marielund → Fjällnora via Selknä' );
		$transfer = null;
		foreach ( $results as $row ) {
			if ( ( $row['connection_type'] ?? '' ) === 'transfer' ) {
				$transfer = $row;
				break;
			}
		}
		self::assertIsArray( $transfer );
		self::assertSame( $stations['selkna'], $transfer['transfer_station_id'] ?? 0 );
		self::assertCount( 2, $transfer['legs'] ?? array() );
		self::assertSame( '10:53', $transfer['legs'][1]['from_departure'] ?? '' );
		self::assertSame( '11:00', $transfer['legs'][1]['to_arrival'] ?? '' );
	}

	public function test_find_uppsala_fjallnora_prefers_b2_bus_after_green_vard_93(): void {
		$this->boot_fixture_services(
			array( 'green-vard-93-out', 'green-b2-bus-out', 'green-b3-bus-out' ),
			'2026-07-01'
		);
		$stations = $this->station_ids();

		$results = MRT_journey_find_normalized_connections(
			$stations['uppsala-ostra'],
			$stations['fjallnora'],
			'2026-07-01'
		);

		self::assertNotEmpty( $results, 'Expected Uppsala Östra → Fjällnora on green weekday' );
		$first = $results[0];
		self::assertSame( '11:10', MRT_journey_normalized_departure_hhmm( $first ) );
		self::assertSame( '11:57', MRT_journey_normalized_arrival_hhmm( $first ) );
		self::assertSame( 'transfer', $first['connection_type'] ?? '' );
		self::assertSame( '11:50', $first['legs'][1]['from_departure'] ?? '' );
	}

	public function test_find_uppsala_fjallnora_uses_b2_even_when_min_transfer_is_four(): void {
		$GLOBALS['mrt_test_options'] = array(
			'mrt_settings' => array(
				'min_transfer_minutes' => 4,
				'max_transfer_minutes' => 120,
			),
		);
		$this->boot_fixture_services(
			array( 'green-vard-93-out', 'green-b2-bus-out', 'green-b3-bus-out' ),
			'2026-07-01'
		);
		$stations = $this->station_ids();

		$results = MRT_journey_find_normalized_connections(
			$stations['uppsala-ostra'],
			$stations['fjallnora'],
			'2026-07-01'
		);

		self::assertNotEmpty( $results );
		self::assertSame( '11:57', MRT_journey_normalized_arrival_hhmm( $results[0] ) );
		self::assertSame( '11:50', $results[0]['legs'][1]['from_departure'] ?? '' );
	}

	public function test_find_uppsala_fjallnora_green_weekday_lists_all_train_bus_connections(): void {
		$this->boot_fixture_services(
			array(
				'green-vard-71-out',
				'green-vard-61-out',
				'green-vard-93-out',
				'green-vard-75-out',
				'green-vard-63-out',
				'green-vard-97-out',
				'green-b1-bus-out',
				'green-b2-bus-out',
				'green-b3-bus-out',
				'green-b4-bus-out',
			),
			'2026-07-01'
		);
		$stations = $this->station_ids();

		$results = MRT_journey_find_normalized_connections(
			$stations['uppsala-ostra'],
			$stations['fjallnora'],
			'2026-07-01'
		);

		$deps = array_map(
			static fn ( array $row ): string => MRT_journey_normalized_departure_hhmm( $row ),
			$results
		);
		self::assertSame(
			array( '10:00', '11:10', '12:38', '14:10' ),
			$deps,
			'Expected four Uppsala→Fjällnora connections (train+bus); not a search limit'
		);
	}

	public function test_find_connections_uppsala_marielund_on_red_sunday(): void {
		$this->boot_service_fixture( 'red-81-out', self::DATE_RED );
		$stations = $this->station_ids();

		$connections = MRT_find_connections(
			$stations['uppsala-ostra'],
			$stations['marielund'],
			self::DATE_RED
		);

		self::assertNotEmpty( $connections, 'Expected Uppsala Östra → Marielund on red traffic day' );
		self::assertSame( '10:00', $connections[0]['from_departure'] ?? '' );
		self::assertSame( '10:35', $connections[0]['to_arrival'] ?? '' );
	}

	public function test_find_multi_leg_fyrislund_barby_rejects_opposite_direction_transfer(): void {
		$this->boot_fixture_services(
			array( 'orange-72-in', 'orange-73-out' ),
			'2026-07-17'
		);
		$stations = $this->station_ids();

		$results = MRT_find_multi_leg_connections(
			$stations['fyrislund'],
			$stations['barby'],
			'2026-07-17'
		);

		foreach ( $results as $row ) {
			self::assertNotSame(
				'transfer',
				$row['connection_type'] ?? '',
				'Must not suggest transfer via Uppsala when traveling towards Bärby'
			);
		}
		self::assertNotEmpty( $results, 'Expected direct Fyrislund → Bärby on orange-73-out' );
		self::assertSame( 'direct', $results[0]['connection_type'] ?? '' );
		self::assertSame( '11:18', $results[0]['legs'][0]['from_departure'] ?? '' );
		self::assertSame( '11:37', $results[0]['legs'][0]['to_arrival'] ?? '' );
	}

	private function register_fixture_station_posts(): void {
		if ( ! isset( $GLOBALS['mrt_test_posts'] ) || ! is_array( $GLOBALS['mrt_test_posts'] ) ) {
			$GLOBALS['mrt_test_posts'] = array();
		}
		foreach ( $this->fixture_files()['stations.csv'] ?? array() as $row ) {
			$code = (string) ( $row['station_code'] ?? '' );
			$name = (string) ( $row['name'] ?? '' );
			$id   = $this->station_ids()[ $code ] ?? 0;
			if ( $id <= 0 || $name === '' ) {
				continue;
			}
			$GLOBALS['mrt_test_posts'][ $id ] = (object) array(
				'ID'         => $id,
				'post_title' => $name,
			);
		}
	}

	private function boot_service_fixture( string $service_code, string $date ): void {
		$this->register_fixture_station_posts();
		$this->boot_fixture_services( array( $service_code ), $date );
	}

	/**
	 * @param string[] $service_codes
	 */
	private function boot_fixture_services( array $service_codes, string $date ): void {
		$this->register_fixture_station_posts();
		$stations           = $this->station_ids();
		$rows_by_service    = array();
		$service_timetables = array();
		$service_code_to_id = array();
		$next_service_id    = 7100;
		foreach ( $service_codes as $service_code ) {
			$stops = $this->fixture_stops_for_service( $service_code );
			self::assertNotEmpty( $stops, "Missing fixture stops for {$service_code}" );
			$rows = array();
			foreach ( $stops as $stop ) {
				$station_code = (string) ( $stop['station_code'] ?? '' );
				$station_id   = $stations[ $station_code ] ?? 0;
				self::assertGreaterThan( 0, $station_id, "Unknown station {$station_code}" );
				$rows[] = array(
					'service_post_id' => $next_service_id,
					'station_post_id' => $station_id,
					'stop_sequence'   => (int) ( $stop['sequence'] ?? 0 ),
					'arrival_time'    => (string) ( $stop['arrival_time'] ?? '' ),
					'departure_time'  => (string) ( $stop['departure_time'] ?? '' ),
					'pickup_allowed'  => (int) ( $stop['pickup_allowed'] ?? 1 ),
					'dropoff_allowed' => (int) ( $stop['dropoff_allowed'] ?? 1 ),
				);
			}
			$rows_by_service[ $next_service_id ]       = $rows;
			$service_timetables[ $next_service_id ]    = 900;
			$service_code_to_id[ $service_code ]       = $next_service_id;
			++$next_service_id;
		}
		$station_meta = $this->fixture_station_hub_meta( $stations );
		$this->mrt_use_journey_fixture(
			$rows_by_service,
			array( 900 => array( $date ) ),
			$service_timetables,
			$station_meta
		);
		$route_meta = $this->fixture_route_post_meta( $stations, $service_code_to_id );
		$GLOBALS['mrt_test_post_meta'] = array_merge( $GLOBALS['mrt_test_post_meta'] ?? array(), $route_meta );
	}

	/**
	 * @param array<string, int> $stations station_code => post ID
	 * @param array<string, int> $service_code_to_id service_code => post ID
	 * @return array<string, mixed>
	 */
	private function fixture_route_post_meta( array $stations, array $service_code_to_id ): array {
		$meta                     = array();
		$route_code_to_id         = array();
		$route_stations_by_code   = array();
		$next_route_id            = 8000;

		foreach ( $this->fixture_files()['route_stations.csv'] ?? array() as $row ) {
			$route_code    = (string) ( $row['route_code'] ?? '' );
			$station_code  = (string) ( $row['station_code'] ?? '' );
			$station_id    = $stations[ $station_code ] ?? 0;
			if ( $route_code === '' || $station_id <= 0 ) {
				continue;
			}
			$route_stations_by_code[ $route_code ][] = $station_id;
		}

		foreach ( $this->fixture_files()['routes.csv'] ?? array() as $row ) {
			$route_code = (string) ( $row['route_code'] ?? '' );
			if ( $route_code === '' ) {
				continue;
			}
			$route_id                      = $next_route_id++;
			$route_code_to_id[ $route_code ] = $route_id;
			if ( isset( $route_stations_by_code[ $route_code ] ) ) {
				$meta[ $route_id . '|mrt_route_stations' ] = $route_stations_by_code[ $route_code ];
			}
			$start_id = $stations[ (string) ( $row['start_station_code'] ?? '' ) ] ?? 0;
			$end_id   = $stations[ (string) ( $row['end_station_code'] ?? '' ) ] ?? 0;
			if ( $start_id > 0 ) {
				$meta[ $route_id . '|mrt_route_start_station' ] = $start_id;
			}
			if ( $end_id > 0 ) {
				$meta[ $route_id . '|mrt_route_end_station' ] = $end_id;
			}
		}

		foreach ( $this->fixture_files()['services.csv'] ?? array() as $row ) {
			$service_code = (string) ( $row['service_code'] ?? '' );
			$route_code   = (string) ( $row['route_code'] ?? '' );
			$service_id   = $service_code_to_id[ $service_code ] ?? 0;
			$route_id     = $route_code_to_id[ $route_code ] ?? 0;
			if ( $service_id > 0 && $route_id > 0 ) {
				$meta[ $service_id . '|mrt_service_route_id' ] = $route_id;
			}
			$end_station_id = $stations[ (string) ( $row['end_station_code'] ?? '' ) ] ?? 0;
			if ( $service_id > 0 && $end_station_id > 0 ) {
				$meta[ $service_id . '|mrt_service_end_station_id' ] = $end_station_id;
			}
			$service_number = (string) ( $row['service_number'] ?? '' );
			if ( $service_id > 0 && $service_number !== '' ) {
				$meta[ $service_id . '|mrt_service_number' ] = $service_number;
			}
		}

		return $meta;
	}

	/**
	 * @param array<string, int> $stations station_code => post ID
	 * @return array<int, array<string, string>>
	 */
	private function fixture_bus_hub_station_meta( array $stations ): array {
		$meta = array();
		foreach ( $this->fixture_files()['stations.csv'] ?? array() as $row ) {
			if ( ( $row['bus_stop_marker'] ?? '' ) !== '1' ) {
				continue;
			}
			$code = (string) ( $row['station_code'] ?? '' );
			$id   = $stations[ $code ] ?? 0;
			if ( $id > 0 ) {
				$meta[ $id ] = array( 'mrt_station_bus_suffix' => '1' );
			}
		}
		return $meta;
	}

	/**
	 * @param array<string, int> $stations station_code => post ID
	 * @return array<int, array<string, mixed>>
	 */
	private function fixture_train_change_station_meta( array $stations ): array {
		$meta       = array();
		$maps_by_id = array();
		foreach ( $this->fixture_files()['station_train_changes.csv'] ?? array() as $row ) {
			$code = (string) ( $row['station_code'] ?? '' );
			$from = (string) ( $row['from_service'] ?? '' );
			$id   = $stations[ $code ] ?? 0;
			if ( $id <= 0 || $from === '' ) {
				continue;
			}
			$maps_by_id[ $id ][ $from ] = MRT_train_change_map_entry(
				(string) ( $row['type_name'] ?? '' ),
				(string) ( $row['to_service'] ?? '' )
			);
		}
		foreach ( $maps_by_id as $station_id => $map ) {
			$meta[ (int) $station_id ] = array(
				MRT_station_train_change_map_meta_key() => MRT_sanitize_station_train_change_map( $map ),
			);
		}
		return $meta;
	}

	/**
	 * @param array<string, int> $stations station_code => post ID
	 * @return array<int, array<string, mixed>>
	 */
	private function fixture_station_hub_meta( array $stations ): array {
		$meta = $this->fixture_bus_hub_station_meta( $stations );
		foreach ( $this->fixture_train_change_station_meta( $stations ) as $station_id => $fields ) {
			$meta[ $station_id ] = array_merge( $meta[ $station_id ] ?? array(), $fields );
		}
		return $meta;
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	private function fixture_stops_for_service( string $service_code ): array {
		$stops = array();
		foreach ( $this->fixture_files()['stoptimes.csv'] ?? array() as $row ) {
			if ( ( $row['service_code'] ?? '' ) === $service_code ) {
				$stops[] = $row;
			}
		}
		usort(
			$stops,
			static fn ( array $a, array $b ): int => (int) ( $a['sequence'] ?? 0 ) <=> (int) ( $b['sequence'] ?? 0 )
		);
		return $stops;
	}

	/**
	 * @return array<string, string>
	 */
	private function fixture_stop_row( string $service_code, string $station_code ): array {
		foreach ( $this->fixture_stops_for_service( $service_code ) as $row ) {
			if ( ( $row['station_code'] ?? '' ) === $station_code ) {
				return $row;
			}
		}
		return array();
	}

	/**
	 * @return array<string, array<int, array<string, string>>>
	 */
	private function fixture_files(): array {
		$package = MRT_csv_get_fixture_package();
		return (array) ( $package['files'] ?? array() );
	}
}
