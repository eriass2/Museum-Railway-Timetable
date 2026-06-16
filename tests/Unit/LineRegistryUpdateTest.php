<?php
/**
 * Line registry admin updates (station order + derived route sync).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/line/line-registry-update.php';

final class LineRegistryUpdateTest extends TestCase {

	protected function tearDown(): void {
		unset(
			$GLOBALS['mrt_test_options'],
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_posts'],
			$GLOBALS['mrt_test_get_posts']
		);
		delete_option( MRT_line_registry_option_key() );
		parent::tearDown();
	}

	private function seed_branch_line(): void {
		MRT_set_line_registry(
			array(
				'fjallnora' => array(
					'title'                 => 'Selkné – Fjällnora',
					'kind'                  => 'branch',
					'station_codes'         => array( 'selkna', 'fjallnora' ),
					'junction_station_code' => 'selkna',
					'requires_transfer'     => true,
				),
			)
		);
	}

	private function seed_stations(): void {
		$GLOBALS['mrt_test_posts'] = array(
			10 => (object) array(
				'ID'         => 10,
				'post_type'  => MRT_POST_TYPE_STATION,
				'post_title' => 'Selkné',
			),
			20 => (object) array(
				'ID'         => 20,
				'post_type'  => MRT_POST_TYPE_STATION,
				'post_title' => 'Fjällnora',
			),
			30 => (object) array(
				'ID'         => 30,
				'post_type'  => MRT_POST_TYPE_STATION,
				'post_title' => 'Gunsta',
			),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'10|mrt_station_code' => 'selkna',
			'20|mrt_station_code' => 'fjallnora',
			'30|mrt_station_code' => 'gunsta',
		);
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

	public function test_station_codes_from_ids_rejects_unknown_station(): void {
		$this->seed_stations();
		$result = MRT_station_codes_from_ids( array( 999 ) );
		self::assertInstanceOf( WP_Error::class, $result );
	}

	public function test_station_codes_from_ids_requires_two_stations(): void {
		$this->seed_stations();
		$result = MRT_station_codes_from_ids( array( 10 ) );
		self::assertInstanceOf( WP_Error::class, $result );
	}

	public function test_update_line_registry_stations_syncs_derived_routes(): void {
		$this->seed_stations();
		$this->seed_branch_line();
		$outbound_id = 501;
		$inbound_id  = 502;
		$GLOBALS['mrt_test_posts'][ $outbound_id ] = (object) array(
			'ID'         => $outbound_id,
			'post_type'  => MRT_POST_TYPE_ROUTE,
			'post_title' => 'Selkné – Fjällnora',
		);
		$GLOBALS['mrt_test_posts'][ $inbound_id ] = (object) array(
			'ID'         => $inbound_id,
			'post_type'  => MRT_POST_TYPE_ROUTE,
			'post_title' => 'Fjällnora – Selkné',
		);
		$GLOBALS['mrt_test_post_meta'][ $outbound_id . '|mrt_route_code' ]     = 'selkna-fjallnora';
		$GLOBALS['mrt_test_post_meta'][ $outbound_id . '|mrt_route_stations' ] = array( 10, 20 );
		$GLOBALS['mrt_test_post_meta'][ $inbound_id . '|mrt_route_code' ]      = 'fjallnora-selkna';
		$GLOBALS['mrt_test_post_meta'][ $inbound_id . '|mrt_route_stations' ]  = array( 20, 10 );

		$result = MRT_update_line_registry_stations( 'fjallnora', array( 20, 10 ) );
		self::assertTrue( $result );

		$entry = MRT_line_registry_entry( 'fjallnora' );
		self::assertSame( array( 'fjallnora', 'selkna' ), $entry['station_codes'] ?? array() );

		self::assertSame( $outbound_id, MRT_route_post_id_from_code( 'selkna-fjallnora' ) );
		self::assertSame( array( 10, 20 ), get_post_meta( $outbound_id, 'mrt_route_stations', true ) );
		self::assertSame( $inbound_id, MRT_route_post_id_from_code( 'fjallnora-selkna' ) );
		self::assertSame( array( 20, 10 ), get_post_meta( $inbound_id, 'mrt_route_stations', true ) );
	}
}
