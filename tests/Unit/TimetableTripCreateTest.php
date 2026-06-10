<?php
/**
 * Timetable trip creation helpers (inc/domain/service/timetable-trip-create.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/service/timetable-trip-create.php';

final class TimetableTripCreateTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_posts'], $GLOBALS['mrt_test_terms'] );
		parent::tearDown();
	}

	public function test_build_service_auto_title_with_end_station(): void {
		$GLOBALS['mrt_test_posts'] = array(
			50  => new WP_Post( (object) array( 'ID' => 50, 'post_title' => 'Uppsala–Faringe' ) ),
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Faringe' ) ),
		);

		$title = MRT_build_service_auto_title( 50, 101, '' );

		self::assertSame( 'Uppsala–Faringe → Faringe', $title );
	}

	public function test_build_service_auto_title_uses_direction_when_no_end_station(): void {
		$GLOBALS['mrt_test_posts'] = array(
			50 => new WP_Post( (object) array( 'ID' => 50, 'post_title' => 'Uppsala–Faringe' ) ),
		);

		self::assertStringContainsString( 'Dit', MRT_build_service_auto_title( 50, 0, 'dit' ) );
		self::assertStringContainsString( 'Från', MRT_build_service_auto_title( 50, 0, 'från' ) );
	}

	public function test_build_add_service_response_shapes_admin_payload(): void {
		$term       = new WP_Term();
		$term->term_id = 20;
		$term->name    = 'Dieseltåg';
		$GLOBALS['mrt_test_terms'] = array( 20 => $term );
		$GLOBALS['mrt_test_posts'] = array(
			501 => new WP_Post( (object) array( 'ID' => 501, 'post_title' => 'Tur 71' ) ),
			50  => new WP_Post( (object) array( 'ID' => 50, 'post_title' => 'Uppsala–Faringe' ) ),
			101 => new WP_Post( (object) array( 'ID' => 101, 'post_title' => 'Faringe' ) ),
		);

		$response = MRT_build_add_service_response( 501, 50, 20, 101, '' );

		self::assertSame( 501, $response['service_id'] );
		self::assertSame( 'Tur 71', $response['service_title'] );
		self::assertSame( 'Uppsala–Faringe', $response['route_name'] );
		self::assertSame( 'Dieseltåg', $response['train_type_name'] );
		self::assertSame( 'Faringe', $response['destination'] );
		self::assertStringContainsString( 'post.php?post=501', $response['edit_url'] );
	}
}
