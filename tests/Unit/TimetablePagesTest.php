<?php
/**
 * Public timetable page helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TimetablePagesTest extends TestCase {

	public function test_single_page_content_uses_overview_shortcode(): void {
		self::assertSame(
			'[museum_timetable_overview timetable_id="42"]',
			MRT_timetable_single_page_content( 42 )
		);
	}

	public function test_index_page_content_uses_index_shortcode(): void {
		self::assertSame( '[museum_timetable_index]', MRT_timetables_index_page_content() );
	}

	public function test_public_page_slug_uses_code_when_present(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'7|mrt_timetable_code' => 'green',
		);
		self::assertSame( 'tidtabell-green', MRT_timetable_public_page_slug( 7, 'GRÖN TIDTABELL 2026' ) );
		unset( $GLOBALS['mrt_test_post_meta'] );
	}

	public function test_set_timetables_index_as_front_page_updates_options(): void {
		$GLOBALS['mrt_test_posts']   = array( 99 => (object) array( 'ID' => 99 ) );
		$GLOBALS['mrt_test_options'] = array();
		MRT_set_timetables_index_as_front_page( 99 );
		self::assertSame( 'page', get_option( 'show_on_front' ) );
		self::assertSame( 99, (int) get_option( 'page_on_front' ) );
		self::assertSame( 0, (int) get_option( 'page_for_posts' ) );
		unset( $GLOBALS['mrt_test_posts'], $GLOBALS['mrt_test_options'] );
	}
}
