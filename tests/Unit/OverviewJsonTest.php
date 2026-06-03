<?php
/**
 * Timetable overview JSON helpers (overview-rail-rows.php, overview-print-key.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OverviewJsonTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_terms'], $GLOBALS['mrt_test_post_terms'] );
		parent::tearDown();
	}

	public function test_time_cell_json_for_missing_stop(): void {
		$cell = MRT_timetable_time_cell_json( null );

		self::assertSame( '—', $cell['text'] );
		self::assertFalse( $cell['edit']['stopsHere'] );
	}

	public function test_time_cell_json_includes_edit_payload(): void {
		$cell = MRT_timetable_time_cell_json(
			array(
				'arrival_time'    => '09:30',
				'departure_time'  => '',
				'pickup_allowed'  => 1,
				'dropoff_allowed' => 0,
			)
		);

		self::assertSame( '09:30', $cell['edit']['arrival'] );
		self::assertTrue( $cell['edit']['stopsHere'] );
		self::assertFalse( $cell['edit']['dropoffAllowed'] );
	}

	public function test_time_cell_text_uses_from_and_to_display(): void {
		$stop = array(
			'arrival_time'   => '09:30',
			'departure_time' => '09:32',
		);

		self::assertStringContainsString( '09.32', MRT_timetable_time_cell_text( $stop, true, false ) );
		self::assertStringContainsString( '09.30', MRT_timetable_time_cell_text( $stop, false, true ) );
	}

	public function test_row_times_json_builds_cells_per_service(): void {
		$row = MRT_timetable_row_times_json(
			'from',
			'Från Alpha',
			101,
			array(
				array(
					'stop_times' => array(
						101 => array(
							'departure_time'  => '09:00',
							'arrival_time'    => '',
							'pickup_allowed'  => 1,
							'dropoff_allowed' => 1,
						),
					),
				),
			),
			array(),
			true,
			false
		);

		self::assertSame( 'from', $row['kind'] );
		self::assertSame( 101, $row['stationId'] );
		self::assertCount( 1, $row['cells'] );
		self::assertSame( '09:00', $row['cells'][0]['edit']['departure'] );
	}

	public function test_train_change_row_for_marielund_service_71(): void {
		$station = new WP_Post( (object) array( 'ID' => 50, 'post_title' => 'Marielund' ) );
		$row     = MRT_timetable_train_change_row_json(
			$station,
			array( array() ),
			array( array( 'service_number' => '71' ) )
		);

		self::assertIsArray( $row );
		self::assertSame( 'trainChange', $row['kind'] );
		self::assertSame( '61', $row['cells'][0]['vehicles'][0]['serviceNumber'] );
	}

	public function test_vehicle_json_uses_train_type_icon_key(): void {
		$term       = new WP_Term();
		$term->term_id = 20;
		$term->name    = 'Dieseltåg';
		$term->slug    = 'diesel';
		$GLOBALS['mrt_test_terms']      = array( 20 => $term );
		$GLOBALS['mrt_test_terms_list'] = array( $term );

		$vehicle = MRT_timetable_vehicle_json( 'Dieseltåg', '61' );

		self::assertSame( '61', $vehicle['serviceNumber'] );
		self::assertSame( 'Dieseltåg', $vehicle['typeName'] );
		self::assertNotSame( '', $vehicle['iconKey'] );
	}

	public function test_print_key_base_rows_includes_standard_symbols(): void {
		$rows = MRT_timetable_print_key_base_rows();

		self::assertGreaterThanOrEqual( 3, count( $rows ) );
		self::assertSame( 'X', $rows[0]['symbol'] );
	}

	public function test_print_key_deviation_rows_adds_notice_for_date(): void {
		$service = new WP_Post( (object) array( 'ID' => 501, 'post_title' => 'Tur 71', 'post_type' => MRT_POST_TYPE_SERVICE ) );
		$GLOBALS['mrt_test_post_meta'] = array(
			'501|mrt_service_number'        => '71',
			'501|mrt_service_notices_by_date' => array(
				'2026-06-06' => 'Ersatt lok',
			),
		);

		$rows = MRT_timetable_print_key_deviation_rows( array( $service ), '2026-06-06' );

		self::assertCount( 1, $rows );
		self::assertSame( '71', $rows[0]['symbol'] );
		self::assertSame( 'Ersatt lok', $rows[0]['text'] );
	}
}
