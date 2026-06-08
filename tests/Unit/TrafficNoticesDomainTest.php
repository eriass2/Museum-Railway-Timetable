<?php
/**
 * Traffic notices domain tests.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/traffic-notices/public-notices.php';
require_once ABSPATH . 'inc/domain/traffic-notices/aggregate.php';
require_once ABSPATH . 'inc/domain/journey/journey-notice.php';

final class TrafficNoticesDomainTest extends TestCase {
	protected function tearDown(): void {
		delete_option( MRT_OPTION_PUBLIC_NOTICES );
		parent::tearDown();
	}

	public function test_public_notice_rejects_text_over_500_chars(): void {
		$result = MRT_public_notice_sanitize_row(
			array(
				'text'    => str_repeat( 'x', 501 ),
				'enabled' => true,
			),
			true
		);
		self::assertInstanceOf( WP_Error::class, $result );
	}

	public function test_public_notice_active_on_date_respects_bounds(): void {
		$notice = array(
			'id'          => 'a',
			'text'        => 'Glassrea',
			'enabled'     => true,
			'active_from' => '2026-06-06',
			'active_to'   => '2026-06-06',
			'sort_order'  => 10,
		);
		self::assertTrue( MRT_public_notice_active_on_date( $notice, '2026-06-06' ) );
		self::assertFalse( MRT_public_notice_active_on_date( $notice, '2026-06-07' ) );
	}

	public function test_aggregate_empty_when_no_content(): void {
		$result = MRT_traffic_notices_aggregate( '2026-06-06', 1, true, true );
		self::assertIsArray( $result );
		self::assertTrue( $result['is_empty'] );
	}

	public function test_aggregate_includes_general_notice(): void {
		update_option(
			MRT_OPTION_PUBLIC_NOTICES,
			array(
				array(
					'id'          => 'n1',
					'text'        => 'Glassrean kl 14',
					'enabled'     => true,
					'active_from' => '',
					'active_to'   => '',
					'sort_order'  => 10,
				),
			),
			false
		);
		$result = MRT_traffic_notices_aggregate( '2026-06-06', 1, true, false );
		self::assertFalse( $result['is_empty'] );
		self::assertCount( 1, $result['general'] );
	}

	public function test_notice_indicates_cancelled_for_deviation_line(): void {
		self::assertTrue( MRT_notice_indicates_cancelled( 'Inställd' ) );
		$line = MRT_traffic_notice_deviation_line_text(
			array(
				'notice'         => 'Inställd',
				'service_number' => '71',
				'route_label'    => 'Uppsala Ö → Marielund',
			)
		);
		self::assertStringContainsString( '71', $line );
		self::assertStringContainsString( 'Inställd', $line );
	}
}
