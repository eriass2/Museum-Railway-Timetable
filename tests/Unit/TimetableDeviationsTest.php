<?php
/**
 * Timetable deviation persistence (inc/domain/admin/timetable-deviations.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_TAXONOMY_TRAIN_TYPE' ) ) {
	define( 'MRT_TAXONOMY_TRAIN_TYPE', 'mrt_train_type' );
}

require_once ABSPATH . 'inc/domain/admin/timetable-deviations.php';

final class TimetableDeviationsTest extends TestCase {

	protected function tearDown(): void {
		unset(
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_terms'],
			$GLOBALS['mrt_test_get_posts'],
			$GLOBALS['mrt_test_posts']
		);
		parent::tearDown();
	}

	public function test_apply_service_deviation_rows_persists_type_and_notice(): void {
		$term       = new WP_Term();
		$term->term_id = 20;
		$term->name    = 'Diesel';
		$GLOBALS['mrt_test_terms'] = array( 20 => $term );

		MRT_apply_service_deviation_rows(
			5,
			array(
				'2026-07-04' => array(
					'train_type' => 20,
					'notice'     => 'Ersatt lok',
				),
			)
		);

		self::assertSame(
			array( '2026-07-04' => 20 ),
			get_post_meta( 5, 'mrt_service_train_types_by_date', true )
		);
		self::assertSame(
			array( '2026-07-04' => 'Ersatt lok' ),
			get_post_meta( 5, 'mrt_service_notices_by_date', true )
		);
	}

	public function test_apply_service_deviation_rows_skips_invalid_date_and_unknown_term(): void {
		$GLOBALS['mrt_test_terms'] = array();

		MRT_apply_service_deviation_rows(
			6,
			array(
				'not-a-date' => array( 'notice' => 'Ignored' ),
				'2026-07-05' => array(
					'train_type' => 99,
					'notice'     => 'Valid notice',
				),
			)
		);

		self::assertSame( '', get_post_meta( 6, 'mrt_service_train_types_by_date', true ) );
		self::assertSame(
			array( '2026-07-05' => 'Valid notice' ),
			get_post_meta( 6, 'mrt_service_notices_by_date', true )
		);
	}

	public function test_apply_service_deviation_rows_clears_meta_when_empty(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'7|mrt_service_train_types_by_date' => array( '2026-07-04' => 2 ),
			'7|mrt_service_notices_by_date'     => array( '2026-07-04' => 'Old' ),
		);

		MRT_apply_service_deviation_rows( 7, array() );

		self::assertSame( '', get_post_meta( 7, 'mrt_service_train_types_by_date', true ) );
		self::assertSame( '', get_post_meta( 7, 'mrt_service_notices_by_date', true ) );
	}

	public function test_apply_timetable_deviations_updates_all_services_on_timetable(): void {
		$term       = new WP_Term();
		$term->term_id = 20;
		$GLOBALS['mrt_test_terms'] = array( 20 => $term );
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_SERVICE ) {
				return array( 10, 11 );
			}
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_SERVICE && ( $args['fields'] ?? '' ) === 'all' ) {
				return array();
			}
			return array();
		};

		MRT_apply_timetable_deviations(
			100,
			array(
				10 => array(
					'2026-07-04' => array( 'notice' => 'Tur A' ),
				),
			)
		);

		self::assertSame(
			array( '2026-07-04' => 'Tur A' ),
			get_post_meta( 10, 'mrt_service_notices_by_date', true )
		);
		self::assertSame( '', get_post_meta( 11, 'mrt_service_notices_by_date', true ) );
	}

	public function test_get_timetable_deviations_payload_collects_rows(): void {
		$GLOBALS['mrt_test_post_meta'] = array(
			'10|mrt_service_train_types_by_date' => array( '2026-07-11' => 2 ),
			'11|mrt_service_notices_by_date'     => array( '2026-07-04' => 'Ersatt' ),
		);
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) !== MRT_POST_TYPE_SERVICE ) {
				return array();
			}
			return array(
				new WP_Post( (object) array( 'ID' => 10, 'post_title' => 'B-tur', 'post_type' => MRT_POST_TYPE_SERVICE ) ),
				new WP_Post( (object) array( 'ID' => 11, 'post_title' => 'A-tur', 'post_type' => MRT_POST_TYPE_SERVICE ) ),
			);
		};

		$rows = MRT_get_timetable_deviations_payload( 50 );

		self::assertCount( 2, $rows );
		self::assertSame( '2026-07-04', $rows[0]['date'] );
		self::assertSame( 11, $rows[0]['service_id'] );
		self::assertSame( '2026-07-11', $rows[1]['date'] );
	}
}
