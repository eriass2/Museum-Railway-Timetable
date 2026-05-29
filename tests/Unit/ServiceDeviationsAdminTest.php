<?php
/**
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/admin/meta-boxes/service-deviations.php';
require_once ABSPATH . 'inc/admin/meta-boxes/timetable-deviations-panel.php';

final class ServiceDeviationsAdminTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
	}

	public function test_deviation_dates_merges_type_and_notice_keys(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'3|mrt_service_train_types_by_date' => array( '2026-07-04' => 20 ),
			'3|mrt_service_notices_by_date'     => array(
				'2026-07-04' => 'Ersatt lok',
				'2026-08-01' => 'Underhåll',
			),
		);
		self::assertSame(
			array( '2026-07-04', '2026-08-01' ),
			MRT_service_deviation_dates( 3 )
		);
		self::assertSame( 2, MRT_count_service_deviations( 3 ) );
	}

	public function test_available_dates_excludes_used(): void {
		$out = MRT_service_deviation_available_dates(
			array( '2026-07-04', '2026-07-11', '2026-07-18' ),
			array( '2026-07-11' )
		);
		self::assertSame( array( '2026-07-04', '2026-07-18' ), $out );
	}

	public function test_collect_timetable_deviation_rows_sorted(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'10|mrt_service_train_types_by_date' => array( '2026-07-11' => 2 ),
			'11|mrt_service_notices_by_date'     => array( '2026-07-04' => 'Ersatt' ),
		);
		$services = array(
			new WP_Post( (object) array( 'ID' => 10, 'post_title' => 'B-tur', 'post_type' => 'mrt_service' ) ),
			new WP_Post( (object) array( 'ID' => 11, 'post_title' => 'A-tur', 'post_type' => 'mrt_service' ) ),
		);
		$rows = MRT_collect_timetable_deviation_rows( $services );
		self::assertCount( 2, $rows );
		self::assertSame( '2026-07-04', $rows[0]['date'] );
		self::assertSame( '2026-07-11', $rows[1]['date'] );
	}
}
