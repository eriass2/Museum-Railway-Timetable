<?php
/**
 * Disruption feed admin edit hints.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/traffic-notices/disruption-feed-admin.php';

final class DisruptionFeedAdminTest extends TestCase {
	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
	}

	public function test_general_item_edit_links_to_traffic_notices(): void {
		$item = MRT_disruption_feed_item_with_admin_edit(
			array(
				'source' => 'general',
			)
		);
		self::assertSame( '/traffic-notices', $item['edit']['path'] );
	}

	public function test_deviation_item_edit_links_to_timetable_deviations_tab(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'71|mrt_service_timetable_id' => 10,
		);
		$item = MRT_disruption_feed_item_with_admin_edit(
			array(
				'source'      => 'deviation',
				'service_ids' => array( 71 ),
			)
		);
		self::assertSame( '/timetables/10', $item['edit']['path'] );
		self::assertSame( 'deviations', $item['edit']['query']['tab'] );
	}
}
