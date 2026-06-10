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
		self::assertSame( '', MRT_format_time_display( null ) );
	}

	public function test_pickup_only_mid_trip_shows_p_prefix(): void {
		self::assertSame(
			'P 10.00',
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

	public function test_approximate_time_puts_ca_next_to_digits(): void {
		self::assertSame(
			'P Ca 11.13',
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
			MRT_format_stop_time_display(
				array_merge(
					array( 'departure_time' => '10:00' ),
					MRT_test_stop_modes_none()
				)
			)
		);
	}
}
