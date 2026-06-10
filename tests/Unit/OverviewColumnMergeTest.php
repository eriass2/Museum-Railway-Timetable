<?php
/**
 * Timetable overview column merge at train-change stations.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OverviewColumnMergeTest extends TestCase {

	private const MARIELUND_ID = 50;

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
	}

	public function test_empty_map_keeps_one_column_per_service(): void {
		$view = $this->inbound_view_without_map();
		$cols = MRT_timetable_build_display_columns( $view );

		self::assertCount( 7, $cols );
		self::assertEqualsCanonicalizing(
			array( 0, 1, 2, 3, 4, 5, 6 ),
			array_column( $cols, 'primary_idx' )
		);
	}

	public function test_inbound_hides_continuation_columns_and_sorts_by_primary_time(): void {
		$this->set_marielund_train_change_map();
		$view = $this->inbound_view_without_map();
		$cols = MRT_timetable_build_display_columns( $view );

		self::assertCount( 5, $cols );
		$numbers = $this->column_service_numbers( $view, $cols );
		self::assertSame( array( '70', '60', '62', '96', '78' ), $numbers );
		self::assertNotContains( '64', $numbers );
		self::assertNotContains( '74', $numbers );
	}

	public function test_marielund_arrival_uses_primary_and_departure_uses_continuation(): void {
		$this->set_marielund_train_change_map();
		$view            = $this->inbound_view_without_map();
		$display_columns = MRT_timetable_build_display_columns( $view );
		$station_posts   = $view['station_posts'];
		$services        = $view['services_list'];
		$info            = $view['service_info'];
		$arrival         = MRT_timetable_row_times_json(
			'arrival',
			'Till Marielund',
			self::MARIELUND_ID,
			$services,
			$info,
			false,
			true,
			$display_columns,
			$station_posts
		);
		$departure = MRT_timetable_row_times_json(
			'departure',
			'Från Marielund',
			self::MARIELUND_ID,
			$services,
			$info,
			true,
			false,
			$display_columns,
			$station_posts
		);

		$col_idx = $this->column_index_for_service( $view, $display_columns, '60' );
		self::assertSame( '10:20', $arrival['cells'][ $col_idx ]['edit']['arrival'] );
		self::assertSame( '11:45', $departure['cells'][ $col_idx ]['edit']['departure'] );
	}

	public function test_train_change_row_shows_continuation_under_primary_column(): void {
		$this->set_marielund_train_change_map();
		$view            = $this->inbound_view_without_map();
		$display_columns = MRT_timetable_build_display_columns( $view );
		$station         = new WP_Post( (object) array( 'ID' => self::MARIELUND_ID, 'post_title' => 'Marielund' ) );
		$row             = MRT_timetable_train_change_row_json(
			$station,
			$view['services_list'],
			$view['service_info'],
			$display_columns
		);

		self::assertIsArray( $row );
		self::assertCount( 5, $row['cells'] );
		$idx_60 = $this->column_index_for_service( $view, $display_columns, '60' );
		$idx_96 = $this->column_index_for_service( $view, $display_columns, '96' );
		self::assertSame( '74', $row['cells'][ $idx_60 ]['vehicles'][0]['serviceNumber'] );
		self::assertSame( '64', $row['cells'][ $idx_96 ]['vehicles'][0]['serviceNumber'] );
	}

	public function test_outbound_merge_for_service_71_to_61(): void {
		$this->set_marielund_train_change_map();
		$view            = $this->outbound_view();
		$display_columns = MRT_timetable_build_display_columns( $view );

		self::assertCount( 1, $display_columns );
		self::assertSame( '71', $view['service_info'][0]['service_number'] );
		self::assertSame( 1, $display_columns[0]['continuation_idx'] );

		$departure = MRT_timetable_row_times_json(
			'departure',
			'Från Marielund',
			self::MARIELUND_ID,
			$view['services_list'],
			$view['service_info'],
			true,
			false,
			$display_columns,
			$view['station_posts']
		);
		self::assertSame( '10:40', $departure['cells'][0]['edit']['departure'] );
	}

	public function test_sort_puts_empty_first_station_time_last(): void {
		$list = array(
			array(
				'stop_times' => array(
					10 => array( 'departure_time' => '' ),
				),
			),
			array(
				'stop_times' => array(
					10 => array( 'departure_time' => '09:00' ),
				),
			),
		);
		$sorted = MRT_sort_timetable_services_by_first_station_time( $list, 10 );

		self::assertSame( '09:00', $sorted[0]['stop_times'][10]['departure_time'] );
		self::assertSame( '', $sorted[1]['stop_times'][10]['departure_time'] );
	}

	private function set_marielund_train_change_map(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			self::MARIELUND_ID . '|' . MRT_station_train_change_map_meta_key() => array(
				'71' => array( 'typeName' => 'Dieseltåg', 'serviceNumber' => '61' ),
				'63' => array( 'typeName' => 'Rälsbuss', 'serviceNumber' => '97' ),
				'60' => array( 'typeName' => 'Ångtåg', 'serviceNumber' => '74' ),
				'96' => array( 'typeName' => 'Dieseltåg', 'serviceNumber' => '64' ),
			),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function inbound_view_without_map(): array {
		$marielund = new WP_Post( (object) array( 'ID' => self::MARIELUND_ID, 'post_title' => 'Marielund' ) );
		$faringe   = new WP_Post( (object) array( 'ID' => 60, 'post_title' => 'Faringe' ) );
		$uppsala   = new WP_Post( (object) array( 'ID' => 10, 'post_title' => 'Uppsala Östra' ) );

		$specs = array(
			array( 'num' => '64', 'uppsala' => array(), 'marielund' => array( 'departure_time' => '11:50' ) ),
			array( 'num' => '74', 'uppsala' => array(), 'marielund' => array( 'departure_time' => '11:45' ) ),
			array( 'num' => '70', 'uppsala' => array( 'departure_time' => '08:00' ), 'marielund' => array( 'arrival_time' => '10:05', 'departure_time' => '10:05' ) ),
			array( 'num' => '60', 'uppsala' => array( 'departure_time' => '08:30' ), 'marielund' => array( 'arrival_time' => '10:20', 'departure_time' => '10:25' ) ),
			array( 'num' => '62', 'uppsala' => array( 'departure_time' => '09:00' ), 'marielund' => array( 'arrival_time' => '10:30', 'departure_time' => '10:30' ) ),
			array( 'num' => '96', 'uppsala' => array( 'departure_time' => '09:30' ), 'marielund' => array( 'arrival_time' => '10:35', 'departure_time' => '10:40' ) ),
			array( 'num' => '78', 'uppsala' => array( 'departure_time' => '10:00' ), 'marielund' => array( 'arrival_time' => '11:00', 'departure_time' => '11:00' ) ),
		);

		return $this->build_view( $uppsala, $marielund, $faringe, $specs );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function outbound_view(): array {
		$uppsala   = new WP_Post( (object) array( 'ID' => 10, 'post_title' => 'Uppsala Östra' ) );
		$marielund = new WP_Post( (object) array( 'ID' => self::MARIELUND_ID, 'post_title' => 'Marielund' ) );
		$faringe   = new WP_Post( (object) array( 'ID' => 60, 'post_title' => 'Faringe' ) );

		$specs = array(
			array(
				'num'       => '71',
				'uppsala'   => array( 'departure_time' => '10:00' ),
				'marielund' => array( 'arrival_time' => '10:35', 'departure_time' => '10:35' ),
			),
			array(
				'num'       => '61',
				'marielund' => array( 'arrival_time' => '10:35', 'departure_time' => '10:40' ),
				'faringe'   => array( 'arrival_time' => '11:00' ),
			),
		);

		return $this->build_view( $uppsala, $marielund, $faringe, $specs );
	}

	/**
	 * @param array<int, array<string, mixed>> $specs
	 * @return array<string, mixed>
	 */
	private function build_view( WP_Post $first, WP_Post $middle, WP_Post $last, array $specs ): array {
		$services_list = array();
		$service_info  = array();
		foreach ( $specs as $spec ) {
			$stop_times = array();
			if ( isset( $spec['uppsala'] ) ) {
				$stop_times[10] = array_merge( $spec['uppsala'], MRT_test_stop_modes_both_scheduled() );
			}
			if ( isset( $spec['marielund'] ) ) {
				$stop_times[ self::MARIELUND_ID ] = array_merge( $spec['marielund'], MRT_test_stop_modes_both_scheduled() );
			}
			if ( isset( $spec['faringe'] ) ) {
				$stop_times[60] = array_merge( $spec['faringe'], MRT_test_stop_modes_both_scheduled() );
			}
			$services_list[] = array(
				'service'    => (object) array( 'ID' => 1000 + count( $services_list ) ),
				'stop_times' => $stop_times,
			);
			$service_info[] = array( 'service_number' => (string) $spec['num'] );
		}

		return array(
			'station_posts' => array( $first, $middle, $last ),
			'services_list' => $services_list,
			'service_info'  => $service_info,
		);
	}

	/**
	 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}> $columns
	 * @return array<int, string>
	 */
	private function column_service_numbers( array $view, array $columns ): array {
		$numbers = array();
		foreach ( $columns as $col ) {
			$numbers[] = (string) $view['service_info'][ (int) $col['primary_idx'] ]['service_number'];
		}
		return $numbers;
	}

	/**
	 * @param array<string, mixed> $view
	 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}> $columns
	 */
	private function column_index_for_service( array $view, array $columns, string $service_number ): int {
		foreach ( $columns as $idx => $col ) {
			$num = (string) $view['service_info'][ (int) $col['primary_idx'] ]['service_number'];
			if ( $num === $service_number ) {
				return (int) $idx;
			}
		}
		self::fail( 'Column not found for service ' . $service_number );
	}
}
