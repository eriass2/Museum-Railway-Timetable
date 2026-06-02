<?php
/**
 * Tests for CSV package export (inc/import/csv/exporter.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_POST_TYPE_STATION' ) ) {
	define( 'MRT_POST_TYPE_STATION', 'mrt_station' );
}
if ( ! defined( 'MRT_POST_TYPE_ROUTE' ) ) {
	define( 'MRT_POST_TYPE_ROUTE', 'mrt_route' );
}
if ( ! defined( 'MRT_TAXONOMY_TRAIN_TYPE' ) ) {
	define( 'MRT_TAXONOMY_TRAIN_TYPE', 'mrt_train_type' );
}
require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/schema.php';
require_once ABSPATH . 'inc/import/csv/slugify.php';
require_once ABSPATH . 'inc/import/csv/reader.php';
require_once ABSPATH . 'inc/import/csv/writer.php';
require_once ABSPATH . 'inc/import/csv/package.php';
require_once ABSPATH . 'inc/import/csv/validate-manifest.php';
require_once ABSPATH . 'inc/import/csv/validate-codes.php';
require_once ABSPATH . 'inc/import/csv/validate-codes-entities.php';
require_once ABSPATH . 'inc/import/csv/validate-references.php';
require_once ABSPATH . 'inc/import/csv/codes-store.php';
require_once ABSPATH . 'inc/import/csv/exporter-entities.php';
require_once ABSPATH . 'inc/import/csv/exporter.php';

if ( ! function_exists( 'determine_locale' ) ) {
	function determine_locale(): string {
		return 'sv_SE';
	}
}

if ( ! function_exists( 'get_terms' ) ) {
	/**
	 * @param array<string, mixed> $args
	 * @return array<int, object>
	 */
	function get_terms( array $args ) {
		unset( $args );
		return array();
	}
}

if ( ! function_exists( 'update_post_meta' ) ) {
	function update_post_meta( int $post_id, string $key, $value ): bool {
		if ( ! isset( $GLOBALS['mrt_test_post_meta'] ) || ! is_array( $GLOBALS['mrt_test_post_meta'] ) ) {
			$GLOBALS['mrt_test_post_meta'] = array();
		}
		$GLOBALS['mrt_test_post_meta'][ $post_id . '|' . $key ] = $value;
		return true;
	}
}

final class CsvExportTest extends TestCase {

	private ?string $export_dir = null;

	protected function tearDown(): void {
		if ( $this->export_dir !== null && is_dir( $this->export_dir ) ) {
			MRT_csv_remove_dir( $this->export_dir );
			$this->export_dir = null;
		}
		unset( $GLOBALS['mrt_test_get_posts'], $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
	}

	public function test_export_writes_valid_manifest_and_stations_csv(): void {
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			if ( ( $args['post_type'] ?? '' ) !== MRT_POST_TYPE_STATION ) {
				return array();
			}
			return array(
				(object) array(
					'ID'         => 11,
					'post_title' => 'Uppsala Östra',
					'post_type'  => MRT_POST_TYPE_STATION,
				),
			);
		};
		$GLOBALS['mrt_test_post_meta'] = array(
			'11|mrt_station_code'    => 'uppsala',
			'11|mrt_station_type'    => 'station',
			'11|mrt_display_order'   => '1',
			'11|mrt_station_bus_suffix' => '0',
			'11|mrt_lat'             => '',
			'11|mrt_lng'             => '',
			'11|' . MRT_station_price_zones_meta_key() => array( 1 ),
		);

		$this->export_dir = sys_get_temp_dir() . '/mrt-export-test-' . bin2hex( random_bytes( 4 ) );
		$result           = MRT_csv_export_package( $this->export_dir );

		self::assertIsString( $result );
		self::assertFileExists( $this->export_dir . '/manifest.json' );
		self::assertFileExists( $this->export_dir . '/stations.csv' );

		$manifest = json_decode( (string) file_get_contents( $this->export_dir . '/manifest.json' ), true );
		self::assertIsArray( $manifest );
		self::assertSame( '1', $manifest['format_version'] ?? '' );
		self::assertContains( 'stations', $manifest['includes'] ?? array() );

		$package = MRT_csv_load_package( $this->export_dir );
		self::assertIsArray( $package );

		$errors = array();
		MRT_csv_validate_manifest( $package, $errors );
		self::assertSame( array(), $errors );

		$stations = $package['files']['stations.csv'] ?? array();
		self::assertCount( 1, $stations );
		self::assertSame( 'uppsala', $stations[0]['station_code'] ?? '' );
		self::assertSame( 'Uppsala Östra', $stations[0]['name'] ?? '' );
		self::assertSame( '1', $stations[0]['price_zones'] ?? '' );

		$raw_csv = (string) file_get_contents( $this->export_dir . '/stations.csv' );
		self::assertStringContainsString( 'name', $raw_csv );
		self::assertStringContainsString( 'Uppsala Östra', $raw_csv );
	}
}
