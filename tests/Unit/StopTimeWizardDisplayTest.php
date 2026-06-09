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

	public function test_both_flags_with_time_shows_ca_prefix(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'pickup_allowed'  => 1,
				'dropoff_allowed' => 1,
				'departure_time'  => '10:13',
			)
		);

		self::assertSame( 'Ca 10.13', $meta['time_label'] );
		self::assertTrue( $meta['approximate_time'] );
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

	public function test_dropoff_only_sets_on_request_dropoff(): void {
		$meta = MRT_journey_stop_wizard_time_meta(
			array(
				'pickup_allowed'  => 0,
				'dropoff_allowed' => 1,
				'arrival_time'    => '09:30',
			)
		);

		self::assertSame( '09.30', $meta['time_label'] );
		self::assertTrue( $meta['on_request_dropoff'] );
	}
}
