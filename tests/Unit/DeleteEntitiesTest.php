<?php
/**
 * Tests for admin entity deletion helpers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DeleteEntitiesTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['mrt_test_get_posts'], $GLOBALS['mrt_test_post_meta'], $GLOBALS['mrt_test_wpdb_get_var'] );
		parent::tearDown();
	}

	public function test_station_in_use_when_listed_on_route(): void {
		require_once ABSPATH . 'inc/domain/admin/delete-entities.php';
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) !== MRT_POST_TYPE_ROUTE ) {
				return array();
			}
			return array(
				new WP_Post(
					(object) array(
						'ID'        => 10,
						'post_type' => MRT_POST_TYPE_ROUTE,
					)
				),
			);
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'10|mrt_route_stations' => array( 5, 6 ),
		);
		$GLOBALS['mrt_test_wpdb_get_var'] = static fn (): int => 0;

		self::assertTrue( MRT_station_is_in_use( 5 ) );
		self::assertFalse( MRT_station_is_in_use( 99 ) );
	}

	public function test_route_in_use_when_service_references_it(): void {
		require_once ABSPATH . 'inc/domain/admin/delete-entities.php';
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) !== MRT_POST_TYPE_SERVICE ) {
				return array();
			}
			if ( isset( $args['meta_query'][0]['key'] ) && $args['meta_query'][0]['key'] === 'mrt_service_route_id' ) {
				return array( 42 );
			}
			return array();
		};

		self::assertSame( array( 42 ), MRT_route_referencing_service_ids( 7 ) );
	}
}
