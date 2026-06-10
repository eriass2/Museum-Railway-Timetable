<?php
/**
 * Tests for admin dashboard domain payload.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DashboardDataTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_get_posts'], $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_current_timestamp'] );
		parent::tearDown();
	}

	public function test_warning_row_shape(): void {
		require_once ABSPATH . 'inc/domain/admin/dashboard-warnings.php';
		$row = MRT_dashboard_warning_row( 'code', 'Meddelande', '#/x' );
		self::assertSame( 'code', $row['code'] );
		self::assertSame( 'Meddelande', $row['message'] );
		self::assertSame( '#/x', $row['route'] );
	}

	public function test_next_traffic_sorts_upcoming_dates(): void {
		require_once ABSPATH . 'inc/domain/admin/dashboard-data.php';
		$GLOBALS['mrt_test_current_timestamp'] = strtotime( '2099-01-01 12:00:00 UTC' );
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_TIMETABLE ) {
				return array(
					new WP_Post(
						(object) array(
							'ID'         => 1,
							'post_title' => 'Tidtabell A',
						)
					),
					new WP_Post(
						(object) array(
							'ID'         => 2,
							'post_title' => 'Tidtabell B',
						)
					),
				);
			}
			return array();
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'1|mrt_timetable_dates' => array( '2099-06-01', '2099-05-01' ),
			'2|mrt_timetable_dates' => array( '2099-04-01' ),
		);

		$rows = MRT_dashboard_next_traffic( 2 );

		self::assertCount( 2, $rows );
		self::assertSame( '2099-04-01', $rows[0]['date'] );
		self::assertSame( '2099-05-01', $rows[1]['date'] );
	}
}
