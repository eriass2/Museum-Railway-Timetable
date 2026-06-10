<?php
/**
 * Wizard stop time display meta (inc/domain/journey/stop-time-wizard-display.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/journey/stop-time-wizard-display.php';

final class StopTimeWizardDisplayTest extends TestCase {

	public function test_both_flags_without_time_shows_x(): void {
		$meta = MRT_journey_stop_wizard_time_meta( MRT_test_stop_modes_both_on_request() );

		self::assertSame( 'X', $meta['time_label'] );
		self::assertTrue( $meta['on_request_both'] );
	}

	public function test_approximate_flag_shows_ca_prefix(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array(
					'departure_time'   => '10:13',
					'approximate_time' => 1,
				),
				MRT_test_stop_modes_both_scheduled()
			)
		);

		self::assertSame( 'Ca 10.13', $meta['time_label'] );
		self::assertTrue( $meta['approximate_time'] );
	}

	public function test_fixed_time_without_approximate_flag(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array(
					'departure_time'   => '10:35',
					'approximate_time' => 0,
				),
				MRT_test_stop_modes_both_scheduled()
			)
		);

		self::assertSame( '10.35', $meta['time_label'] );
		self::assertFalse( $meta['approximate_time'] );
	}

	public function test_arrival_preference_at_terminus(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array(
					'arrival_time'     => '10:35',
					'departure_time'   => '10:45',
					'approximate_time' => 0,
				),
				MRT_test_stop_modes_both_scheduled()
			),
			'arrival'
		);

		self::assertSame( '10.35', $meta['time_label'] );
	}

	public function test_pickup_only_sets_on_request_pickup(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array( 'departure_time' => '09:00' ),
				MRT_test_stop_modes_pickup_only()
			)
		);

		self::assertSame( '09.00', $meta['time_label'] );
		self::assertTrue( $meta['on_request_pickup'] );
		self::assertFalse( $meta['on_request_dropoff'] );
	}

	public function test_pickup_only_at_trip_start_hides_pickup_footnote(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array( 'departure_time' => '09:00' ),
				MRT_test_stop_modes_pickup_only()
			),
			'departure',
			true,
			false
		);

		self::assertFalse( $meta['on_request_pickup'] );
	}

	public function test_dropoff_only_at_trip_end_keeps_dropoff_footnote(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array( 'arrival_time' => '09:30' ),
				MRT_test_stop_modes_dropoff_only()
			),
			'arrival',
			false,
			true
		);

		self::assertTrue( $meta['on_request_dropoff'] );
	}

	public function test_on_request_x_at_trip_start_keeps_dropoff_footnote_only(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			MRT_test_stop_modes_both_on_request(),
			'departure',
			true,
			false
		);

		self::assertSame( 'X', $meta['time_label'] );
		self::assertTrue( $meta['on_request_both'] );
		self::assertFalse( $meta['on_request_pickup'] );
		self::assertTrue( $meta['on_request_dropoff'] );
	}

	public function test_on_request_with_time_shows_ca_and_x_suffix(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array(
					'arrival_time'     => '10:09',
					'departure_time'   => '10:09',
					'approximate_time' => 1,
				),
				MRT_test_stop_modes_both_on_request()
			)
		);

		self::assertSame( 'Ca 10.09 X', $meta['time_label'] );
	}
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array( 'arrival_time' => '09:30' ),
				MRT_test_stop_modes_dropoff_only()
			),
			'arrival'
		);

		self::assertSame( '09.30', $meta['time_label'] );
		self::assertTrue( $meta['on_request_dropoff'] );
	}
}
