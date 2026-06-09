<?php
/**
 * CSV override import helpers (inc/import/csv/import/import-override.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_TAXONOMY_TRAIN_TYPE' ) ) {
	define( 'MRT_TAXONOMY_TRAIN_TYPE', 'mrt_train_type' );
}

require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/loader.php';

final class CsvImportOverrideTest extends TestCase {

	protected function tearDown(): void {
		unset(
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_posts'],
			$GLOBALS['mrt_test_get_posts'],
			$GLOBALS['mrt_test_deleted_posts'],
			$GLOBALS['mrt_test_terms'],
			$GLOBALS['mrt_test_terms_list'],
			$GLOBALS['mrt_test_deleted_terms']
		);
		parent::tearDown();
	}

	public function test_collect_package_codes_indexes_entities(): void {
		$codes = MRT_csv_collect_package_codes(
			array(
				'stations.csv'   => array( array( 'station_code' => 'alpha', 'name' => 'Alpha' ) ),
				'routes.csv'     => array( array( 'route_code' => 'main', 'title' => 'Main' ) ),
				'timetables.csv' => array( array( 'timetable_code' => 'green', 'title' => 'Green' ) ),
				'services.csv'   => array( array( 'service_code' => 's1', 'service_number' => '71' ) ),
				'train_types.csv' => array( array( 'slug' => 'diesel', 'name' => 'Diesel' ) ),
			)
		);

		self::assertTrue( $codes['stations']['alpha'] ?? false );
		self::assertTrue( $codes['routes']['main'] ?? false );
		self::assertTrue( $codes['timetables']['green'] ?? false );
		self::assertTrue( $codes['services']['s1'] ?? false );
		self::assertTrue( $codes['train_types']['diesel'] ?? false );
	}

	public function test_delete_orphan_posts_removes_unknown_codes(): void {
		$GLOBALS['mrt_test_deleted_posts'] = array();
		$GLOBALS['mrt_test_posts']         = array(
			10 => new WP_Post( (object) array( 'ID' => 10, 'post_type' => MRT_POST_TYPE_STATION ) ),
			11 => new WP_Post( (object) array( 'ID' => 11, 'post_type' => MRT_POST_TYPE_STATION ) ),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'10|mrt_station_code' => 'keep',
			'11|mrt_station_code' => 'drop',
		);
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) === MRT_POST_TYPE_STATION && ( $args['fields'] ?? '' ) === 'ids' ) {
				return array( 10, 11 );
			}
			return array();
		};

		MRT_csv_delete_orphan_posts( MRT_POST_TYPE_STATION, 'mrt_station_code', array( 'keep' => true ) );

		self::assertSame( array( 11 ), $GLOBALS['mrt_test_deleted_posts'] );
		self::assertArrayHasKey( 10, $GLOBALS['mrt_test_posts'] );
		self::assertArrayNotHasKey( 11, $GLOBALS['mrt_test_posts'] );
	}

	public function test_delete_orphan_train_types_removes_unlisted_slugs(): void {
		$keep       = new WP_Term();
		$keep->term_id = 20;
		$keep->slug    = 'diesel';
		$drop       = new WP_Term();
		$drop->term_id = 21;
		$drop->slug    = 'old-type';
		$GLOBALS['mrt_test_terms_list']  = array( $keep, $drop );
		$GLOBALS['mrt_test_deleted_terms'] = array();

		MRT_csv_delete_orphan_train_types( array( 'diesel' => true ) );

		self::assertSame( array( 21 ), $GLOBALS['mrt_test_deleted_terms'] );
	}
}
