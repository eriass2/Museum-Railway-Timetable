<?php
/**
 * Stop time display formatting (P, A, Ca, X, |).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StopTimeDisplayTest extends TestCase {

	public function test_format_time_display_replaces_colon_with_dot(): void {
		self::assertSame( '10.13', MRT_format_time_display( '10:13' ) );
		self::assertSame( '25.10', MRT_format_time_display( '25:10' ) );
		self::assertSame( '', MRT_format_time_display( null ) );
	}

	public function test_time_past_midnight_stays_in_the_cell(): void {
		self::assertSame(
			'Ca 25.10',
			MRT_format_stop_time_display(
				array_merge(
					array(
						'departure_time'   => '25:10',
						'approximate_time' => 1,
					),
					MRT_test_stop_modes_both_scheduled()
				)
			)
		);
	}

	public function test_pickup_only_mid_trip_shows_p_suffix(): void {
		self::assertSame(
			'10.00 P',
			MRT_format_stop_time_display(
				array_merge(
					array( 'departure_time' => '10:00' ),
					MRT_test_stop_modes_pickup_only()
				),
				'departure'
			)
		);
	}

	public function test_from_row_hides_pickup_only_prefix(): void {
		self::assertSame(
			'10.00',
			MRT_format_stop_time_display(
				array_merge(
					array( 'departure_time' => '10:00' ),
					MRT_test_stop_modes_pickup_only()
				),
				'from'
			)
		);
	}

	public function test_approximate_time_puts_ca_before_digits_with_suffix(): void {
		self::assertSame(
			'Ca 11.13 P',
			MRT_format_stop_time_display(
				array_merge(
					array(
						'departure_time'   => '11:13',
						'approximate_time' => 1,
					),
					MRT_test_stop_modes_pickup_only()
				),
				'departure'
			)
		);
	}

	public function test_approximate_time_with_x_suffix(): void {
		self::assertSame(
			'Ca 10.09 X',
			MRT_format_stop_time_display(
				array_merge(
					array(
						'arrival_time'     => '10:09',
						'departure_time'   => '10:09',
						'approximate_time' => 1,
					),
					MRT_test_stop_modes_both_on_request()
				)
			)
		);
	}

	public function test_to_row_hides_dropoff_only_prefix(): void {
		self::assertSame(
			'09.30',
			MRT_format_stop_time_display(
				array_merge(
					array( 'arrival_time' => '09:30' ),
					MRT_test_stop_modes_dropoff_only()
				),
				'to'
			)
		);
	}

	public function test_on_request_without_time_shows_x(): void {
		self::assertSame(
			'X',
			MRT_format_stop_time_display( MRT_test_stop_modes_both_on_request() )
		);
	}

	public function test_pass_through_stop_shows_pipe(): void {
		self::assertSame(
			'|',
			MRT_format_stop_time_display( MRT_test_stop_modes_none() )
		);
	}

	public function test_wizard_footnote_flags_hide_pickup_on_passed_through_middle(): void {
		$flags = MRT_stop_time_restriction_footnote_flags( true, false, true, false, false );
		self::assertFalse( $flags['pickup_restriction'] );
		self::assertFalse( $flags['dropoff_restriction'] );
	}

	public function test_wizard_footnote_flags_show_dropoff_only_at_alighting(): void {
		$flags = MRT_stop_time_restriction_footnote_flags( false, true, true, false, true );
		self::assertFalse( $flags['pickup_restriction'] );
		self::assertTrue( $flags['dropoff_restriction'] );
	}

	public function test_wizard_footnote_flags_show_pickup_only_at_boarding(): void {
		$flags = MRT_stop_time_restriction_footnote_flags( true, false, true, true, false );
		self::assertTrue( $flags['pickup_restriction'] );
		self::assertFalse( $flags['dropoff_restriction'] );
	}

	public function test_wizard_footnote_flags_show_dropoff_when_both_on_request_with_time_at_alighting(): void {
		$flags = MRT_stop_time_restriction_footnote_flags( true, true, true, false, true );
		self::assertFalse( $flags['pickup_restriction'] );
		self::assertTrue( $flags['dropoff_restriction'] );
		self::assertFalse( $flags['on_request_both'] );
	}
}
