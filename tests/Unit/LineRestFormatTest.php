<?php
/**
 * Line registry REST formatting.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/line/line-rest-format.php';

final class LineRestFormatTest extends TestCase {

	protected function tearDown(): void {
		unset(
			$GLOBALS['mrt_test_options'],
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_posts'],
			$GLOBALS['mrt_test_get_posts']
		);
		parent::tearDown();
	}

	private function boot_station_lookup(): void {
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			$meta_key   = (string) ( $args['meta_key'] ?? '' );
			$meta_value = (string) ( $args['meta_value'] ?? '' );
			if ( $meta_key === '' || $meta_value === '' ) {
				return array();
			}
			foreach ( $GLOBALS['mrt_test_post_meta'] as $key => $value ) {
				$parts = explode( '|', (string) $key, 2 );
				if ( count( $parts ) !== 2 || $parts[1] !== $meta_key || (string) $value !== $meta_value ) {
					continue;
				}
				$id = (int) $parts[0];
				return ( ( $args['fields'] ?? '' ) === 'ids' ) ? array( $id ) : array( $GLOBALS['mrt_test_posts'][ $id ] );
			}
			return array();
		};
	}

	public function test_format_lines_list_resolves_station_ids(): void {
		$this->boot_station_lookup();
		$GLOBALS['mrt_test_options'] = array(
			'mrt_line_registry' => array(
				'main' => array(
					'title'         => 'Faringe – Uppsala Östra',
					'kind'          => 'main',
					'station_codes' => array( 'faringe', 'uppsala-ostra' ),
				),
				'fjallnora' => array(
					'title'                  => 'Selkné – Fjällnora',
					'kind'                   => 'branch',
					'station_codes'          => array( 'selkna', 'fjallnora' ),
					'junction_station_code'  => 'selkna',
					'requires_transfer'      => true,
				),
			),
		);
		$GLOBALS['mrt_test_posts'] = array(
			1  => (object) array( 'ID' => 1, 'post_title' => 'Faringe' ),
			14 => (object) array( 'ID' => 14, 'post_title' => 'Uppsala Östra' ),
			6  => (object) array( 'ID' => 6, 'post_title' => 'Selkné' ),
			15 => (object) array( 'ID' => 15, 'post_title' => 'Fjällnora' ),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'1|mrt_station_code'  => 'faringe',
			'14|mrt_station_code' => 'uppsala-ostra',
			'6|mrt_station_code'  => 'selkna',
			'15|mrt_station_code' => 'fjallnora',
		);

		$lines = MRT_rest_format_lines_list();

		self::assertCount( 2, $lines );
		self::assertSame( 'main', $lines[0]['code'] ?? '' );
		self::assertSame( array( 1, 14 ), $lines[0]['station_ids'] ?? array() );
		self::assertTrue( $lines[0]['bidirectional'] ?? false );
		self::assertSame( 'fjallnora', $lines[1]['code'] ?? '' );
		self::assertSame( 6, $lines[1]['junction_station_id'] ?? 0 );
		self::assertSame( 'Selkné', $lines[1]['junction_station_name'] ?? '' );
	}

	public function test_format_line_options_from_lines_list(): void {
		$this->boot_station_lookup();
		$GLOBALS['mrt_test_options'] = array(
			'mrt_line_registry' => array(
				'main' => array(
					'title'         => 'Main',
					'kind'          => 'main',
					'station_codes' => array( 'faringe', 'uppsala-ostra' ),
				),
			),
		);
		$GLOBALS['mrt_test_posts'] = array(
			1  => (object) array( 'ID' => 1, 'post_title' => 'Faringe' ),
			14 => (object) array( 'ID' => 14, 'post_title' => 'Uppsala Östra' ),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'1|mrt_station_code'  => 'faringe',
			'14|mrt_station_code' => 'uppsala-ostra',
		);

		$options = MRT_rest_format_line_options();

		self::assertCount( 1, $options );
		self::assertSame( 'main', $options[0]['code'] ?? '' );
		self::assertCount( 2, $options[0]['termini'] ?? array() );
	}
}
