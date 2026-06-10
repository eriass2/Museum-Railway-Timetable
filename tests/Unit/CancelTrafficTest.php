<?php
/**
 * Tests for cancel-traffic domain helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CancelTrafficTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
	}

	public function test_merge_service_notice_preserves_other_dates(): void {
		require_once ABSPATH . 'inc/domain/admin/cancel-traffic.php';
		$GLOBALS['mrt_test_post_meta'] = array(
			'5|mrt_service_notices_by_date' => array(
				'2026-06-01' => 'Tidig avgång',
			),
		);

		MRT_merge_service_notice_for_date( 5, '2026-06-02', 'Inställd' );

		$stored = $GLOBALS['mrt_test_post_meta']['5|mrt_service_notices_by_date'];
		self::assertSame( 'Tidig avgång', $stored['2026-06-01'] );
		self::assertSame( 'Inställd', $stored['2026-06-02'] );
	}

	public function test_service_is_cancelled_detects_notice(): void {
		require_once ABSPATH . 'inc/domain/journey/journey-notice.php';
		require_once ABSPATH . 'inc/domain/admin/cancel-traffic.php';
		$GLOBALS['mrt_test_post_meta'] = array(
			'3|mrt_service_notices_by_date' => array( '2026-08-01' => 'Inställd pga väder' ),
		);

		self::assertTrue( MRT_service_is_cancelled_on_date( 3, '2026-08-01' ) );
		self::assertFalse( MRT_service_is_cancelled_on_date( 3, '2026-08-02' ) );
	}

	public function test_notice_indicates_cancelled(): void {
		require_once ABSPATH . 'inc/domain/journey/journey-notice.php';

		self::assertTrue( MRT_notice_indicates_cancelled( 'Inställd' ) );
		self::assertTrue( MRT_notice_indicates_cancelled( 'Inställd pga väder' ) );
		self::assertFalse( MRT_notice_indicates_cancelled( 'Försenad avgång' ) );
	}
}
