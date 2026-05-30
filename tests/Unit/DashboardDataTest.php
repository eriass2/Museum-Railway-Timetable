<?php
/**
 * Tests for admin dashboard domain payload.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DashboardDataTest extends TestCase {

	public function test_warning_row_shape(): void {
		require_once ABSPATH . 'inc/domain/admin/dashboard-warnings.php';
		$row = MRT_dashboard_warning_row( 'code', 'Meddelande', '#/x' );
		self::assertSame( 'code', $row['code'] );
		self::assertSame( 'Meddelande', $row['message'] );
		self::assertSame( '#/x', $row['route'] );
	}
}
