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
		self::assertSame( 'both', $meta['behov_hint'] );
	}

	public function test_approximate_on_scheduled_stop_does_not_show_ca(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array(
					'departure_time'   => '10:13',
					'approximate_time' => 1,
				),
				MRT_test_stop_modes_both_scheduled()
			)
		);

		self::assertSame( '10.13', $meta['time_label'] );
		self::assertTrue( $meta['approximate_time'] );
		self::assertSame( '', $meta['behov_hint'] );
	}

	public function test_approximate_on_behov_stop_shows_ca_prefix(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array(
					'departure_time'   => '10:13',
					'approximate_time' => 1,
				),
				MRT_test_stop_modes_pickup_only()
			)
		);

		self::assertSame( 'Ca 10.13', $meta['time_label'] );
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

	public function test_pickup_only_at_passed_through_middle_hides_pickup_footnote(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array( 'departure_time' => '09:30' ),
				MRT_test_stop_modes_pickup_only()
			),
			'departure',
			false,
			false
		);

		self::assertSame( '09.30', $meta['time_label'] );
		self::assertSame( '', $meta['behov_hint'] );
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

		self::assertSame( '', $meta['behov_hint'] );
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

		self::assertSame( 'dropoff', $meta['behov_hint'] );
	}

	public function test_on_request_x_at_trip_start_keeps_dropoff_footnote_only(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			MRT_test_stop_modes_both_on_request(),
			'departure',
			true,
			false
		);

		self::assertSame( 'X', $meta['time_label'] );
		self::assertSame( 'both', $meta['behov_hint'] );
	}

	public function test_on_request_with_time_shows_ca_without_x_suffix(): void {
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

		self::assertSame( 'Ca 10.09', $meta['time_label'] );
		self::assertSame( '', $meta['behov_hint'] );
	}

	public function test_dropoff_only_with_time_keeps_dropoff_footnote_at_alighting(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array_merge(
				array( 'arrival_time' => '09:30' ),
				MRT_test_stop_modes_dropoff_only()
			),
			'arrival',
			false,
			true
		);

		self::assertSame( '09.30', $meta['time_label'] );
		self::assertSame( 'dropoff', $meta['behov_hint'] );
	}

	public function test_behov_hint_maps_restriction_flags(): void {
		self::assertSame( '', MRT_journey_stop_behov_hint( false, false, false ) );
		self::assertSame( 'pickup', MRT_journey_stop_behov_hint( true, false, false ) );
		self::assertSame( 'dropoff', MRT_journey_stop_behov_hint( false, true, false ) );
		self::assertSame( 'both', MRT_journey_stop_behov_hint( false, true, true ) );
	}
}
