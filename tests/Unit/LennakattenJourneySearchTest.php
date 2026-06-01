<?php
/**
 * Journey search against Lennakatten CSV fixture (Anslagstidtabell reference trips).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/fixture-read.php';

final class LennakattenJourneySearchTest extends TestCase {
	use MRT_Journey_Test_Fixture;

	private const DATE_GREEN = '2026-06-06';
	private const DATE_RED   = '2026-07-05';

	/** @var array<string, int>|null */
	private static ?array $station_ids = null;

	protected function tearDown(): void {
		$this->mrt_reset_journey_fixture();
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

	private function boot_service_fixture( string $service_code, string $date ): void {
		$stations = $this->station_ids();
		$stops = $this->fixture_stops_for_service( $service_code );
		$rows  = array();
		foreach ( $stops as $stop ) {
			$station_code = (string) ( $stop['station_code'] ?? '' );
			$station_id   = $stations[ $station_code ] ?? 0;
			self::assertGreaterThan( 0, $station_id, "Unknown station {$station_code}" );
			$rows[] = array(
				'service_post_id' => 7100,
				'station_post_id' => $station_id,
				'stop_sequence'   => (int) ( $stop['sequence'] ?? 0 ),
				'arrival_time'    => (string) ( $stop['arrival_time'] ?? '' ),
				'departure_time'  => (string) ( $stop['departure_time'] ?? '' ),
				'pickup_allowed'  => (int) ( $stop['pickup_allowed'] ?? 1 ),
				'dropoff_allowed' => (int) ( $stop['dropoff_allowed'] ?? 1 ),
			);
		}
		$this->mrt_use_journey_fixture(
			array( 7100 => $rows ),
			array( 900 => array( $date ) ),
			array( 7100 => 900 )
		);
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
