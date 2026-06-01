<?php
/**
 * Timetable type helpers for calendar colouring.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TimetableTypeCalendarTest extends TestCase {
	public function test_normalize_timetable_type_accepts_known_slugs(): void {
		self::assertSame( 'green', MRT_normalize_timetable_type( 'green' ) );
		self::assertSame( 'yellow', MRT_normalize_timetable_type( 'YELLOW' ) );
		self::assertSame( '', MRT_normalize_timetable_type( 'unknown' ) );
	}

	public function test_month_calendar_legend_types_sorted_and_unique(): void {
		$dates = array(
			1 => array( 'type' => 'yellow', 'running' => true ),
			2 => array( 'type' => 'green', 'running' => true ),
			3 => array( 'type' => 'green', 'running' => true ),
		);

		$legend = MRT_month_calendar_legend_types( $dates );

		self::assertSame(
			array(
				array( 'type' => 'green', 'label' => 'GRÖN tidtabell' ),
				array( 'type' => 'yellow', 'label' => 'GUL tidtabell' ),
			),
			$legend
		);
	}
}
