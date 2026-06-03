<?php
/**
 * CSV package import orchestration (inc/import/csv/importer.php).
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

if ( ! function_exists( 'term_exists' ) ) {
	/**
	 * @return array{term_id:int}|int|false
	 */
	function term_exists( $term, $taxonomy = '', $parent = null ) {
		unset( $term, $taxonomy, $parent );
		return false;
	}
}

if ( ! function_exists( 'wp_insert_term' ) ) {
	/**
	 * @param array<string, mixed> $args
	 * @return array{term_id:int}|WP_Error
	 */
	function wp_insert_term( $term, $taxonomy, $args = array() ) {
		unset( $taxonomy );
		if ( ! isset( $GLOBALS['mrt_test_next_term_id'] ) ) {
			$GLOBALS['mrt_test_next_term_id'] = 6000;
		}
		$id = (int) ++$GLOBALS['mrt_test_next_term_id'];
		$t  = new WP_Term();
		$t->term_id = $id;
		$t->name    = (string) $term;
		$t->slug    = (string) ( $args['slug'] ?? sanitize_title( (string) $term ) );
		$GLOBALS['mrt_test_terms'][ $id ] = $t;
		return array( 'term_id' => $id );
	}
}

final class CsvImportPackageTest extends TestCase {

	/** @var mixed */
	private $original_wpdb = null;

	protected function tearDown(): void {
		if ( $this->original_wpdb !== null ) {
			$GLOBALS['wpdb'] = $this->original_wpdb;
		}
		unset(
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_posts'],
			$GLOBALS['mrt_test_get_posts'],
			$GLOBALS['mrt_test_next_post_id'],
			$GLOBALS['mrt_test_next_term_id'],
			$GLOBALS['mrt_test_terms']
		);
		parent::tearDown();
	}

	public function test_import_package_rejects_missing_path(): void {
		$result = MRT_csv_import_package( ABSPATH . 'testdata/does-not-exist' );

		self::assertInstanceOf( WP_Error::class, $result );
	}

	public function test_import_package_merge_mode_imports_lennakatten_fixture(): void {
		$this->boot_get_posts_by_code();
		$result = MRT_csv_import_package( ABSPATH . 'testdata/fixtures/lennakatten', 'merge' );

		self::assertIsArray( $result );
		self::assertSame( 'merge', $result['mode'] );
		self::assertGreaterThan( 0, $result['stations'] );
		self::assertGreaterThan( 0, $result['routes'] );
		self::assertGreaterThan( 0, $result['services'] );
	}

	public function test_import_package_override_mode_sets_mode_in_result(): void {
		$this->boot_get_posts_by_code();
		$result = MRT_csv_import_package( ABSPATH . 'testdata/fixtures/lennakatten', 'override' );

		self::assertIsArray( $result );
		self::assertSame( 'override', $result['mode'] );
	}

	private function boot_get_posts_by_code(): void {
		$this->original_wpdb        = $GLOBALS['wpdb'] ?? null;
		$GLOBALS['wpdb']            = new CsvImportPackageTestDb();
		$GLOBALS['mrt_test_post_meta'] = array();
		$GLOBALS['mrt_test_posts']     = array();
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( isset( $args['post_type'], $args['fields'] ) && $args['fields'] === 'ids' ) {
				$ids = array();
				foreach ( $GLOBALS['mrt_test_posts'] as $id => $post ) {
					if ( ( $post->post_type ?? '' ) === $args['post_type'] ) {
						$ids[] = (int) $id;
					}
				}
				return $ids;
			}
			if ( isset( $args['meta_query'][0]['key'], $args['meta_query'][0]['value'] ) ) {
				$key   = (string) $args['meta_query'][0]['key'];
				$value = (string) $args['meta_query'][0]['value'];
				foreach ( $GLOBALS['mrt_test_post_meta'] as $meta_key => $meta_value ) {
					if ( ! str_ends_with( $meta_key, '|' . $key ) ) {
						continue;
					}
					if ( (string) $meta_value !== $value ) {
						continue;
					}
					$id = (int) explode( '|', $meta_key )[0];
					return ( $args['fields'] ?? '' ) === 'ids' ? array( $id ) : array( $GLOBALS['mrt_test_posts'][ $id ] );
				}
				return array();
			}
			return array();
		};
	}
}

/** @internal */
final class CsvImportPackageTestDb {
	/** @var string */
	public $prefix = 'wp_';

	/** @var string */
	public $last_error = '';

	public int $insert_id = 0;

	/**
	 * @param array<string, mixed> $data
	 * @param array<int, string>   $format
	 */
	public function insert( $table, $data, $format ) {
		unset( $table, $data, $format );
		++$this->insert_id;
		return 1;
	}

	/**
	 * @param array<string, mixed> $data
	 * @param array<int, string>   $where_format
	 */
	public function delete( $table, $data, $where_format = null ) {
		unset( $table, $data, $where_format );
		return 1;
	}
}
