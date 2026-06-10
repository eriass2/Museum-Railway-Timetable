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
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'pickup_allowed'  => 1,
				'dropoff_allowed' => 1,
			)
		);

		self::assertSame( 'X', $meta['time_label'] );
		self::assertTrue( $meta['on_request_both'] );
	}

	public function test_approximate_flag_shows_ca_prefix(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'pickup_allowed'   => 1,
				'dropoff_allowed'  => 1,
				'departure_time'   => '10:13',
				'approximate_time' => 1,
			)
		);

		self::assertSame( 'Ca 10.13', $meta['time_label'] );
		self::assertTrue( $meta['approximate_time'] );
	}

	public function test_fixed_time_without_approximate_flag(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'pickup_allowed'   => 1,
				'dropoff_allowed'  => 1,
				'departure_time'   => '10:35',
				'approximate_time' => 0,
			)
		);

		self::assertSame( '10.35', $meta['time_label'] );
		self::assertFalse( $meta['approximate_time'] );
	}

	public function test_arrival_preference_at_terminus(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'arrival_time'     => '10:35',
				'departure_time'   => '10:45',
				'pickup_allowed'   => 1,
				'dropoff_allowed'  => 1,
				'approximate_time' => 0,
			),
			'arrival'
		);

		self::assertSame( '10.35', $meta['time_label'] );
	}

	public function test_pickup_only_sets_on_request_pickup(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'pickup_allowed'  => 1,
				'dropoff_allowed' => 0,
				'departure_time'  => '09:00',
			)
		);

		self::assertSame( '09.00', $meta['time_label'] );
		self::assertTrue( $meta['on_request_pickup'] );
		self::assertFalse( $meta['on_request_dropoff'] );
	}

	public function test_pickup_only_at_trip_start_hides_pickup_footnote(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'pickup_allowed'  => 1,
				'dropoff_allowed' => 0,
				'departure_time'  => '09:00',
			),
			'departure',
			true,
			false
		);

		self::assertFalse( $meta['on_request_pickup'] );
	}

	public function test_dropoff_only_at_trip_end_keeps_dropoff_footnote(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'pickup_allowed'  => 0,
				'dropoff_allowed' => 1,
				'arrival_time'    => '09:30',
			),
			'arrival',
			false,
			true
		);

		self::assertTrue( $meta['on_request_dropoff'] );
	}

	public function test_on_request_x_at_trip_start_keeps_dropoff_footnote_only(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'pickup_allowed'  => 1,
				'dropoff_allowed' => 1,
			),
			'departure',
			true,
			false
		);

		self::assertSame( 'X', $meta['time_label'] );
		self::assertTrue( $meta['on_request_both'] );
		self::assertFalse( $meta['on_request_pickup'] );
		self::assertTrue( $meta['on_request_dropoff'] );
	}

	public function test_dropoff_only_sets_on_request_dropoff(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'pickup_allowed'  => 0,
				'dropoff_allowed' => 1,
				'arrival_time'    => '09:30',
			),
			'arrival'
		);

		self::assertSame( '09.30', $meta['time_label'] );
		self::assertTrue( $meta['on_request_dropoff'] );
	}
}
