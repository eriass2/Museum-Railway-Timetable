<?php
/**
 * Admin REST: timetable serializers and mutations.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! defined( 'MRT_REST_NAMESPACE' ) ) {
	define( 'MRT_REST_NAMESPACE', 'museum-railway-timetable/v1' );
}

if ( ! defined( 'MRT_TAXONOMY_TRAIN_TYPE' ) ) {
	define( 'MRT_TAXONOMY_TRAIN_TYPE', 'mrt_train_type' );
}

require_once ABSPATH . 'inc/infrastructure/rest/timetables-data.php';
require_once ABSPATH . 'inc/infrastructure/rest/timetables.php';

final class RestTimetablesDataTest extends TestCase {

	protected function tearDown(): void {
		unset(
			$GLOBALS['mrt_test_get_posts'],
			$GLOBALS['mrt_test_post_meta'],
			$GLOBALS['mrt_test_posts'],
			$GLOBALS['mrt_test_terms'],
			$GLOBALS['mrt_test_post_terms'],
			$GLOBALS['mrt_test_terms_list'],
			$GLOBALS['mrt_test_get_terms'],
			$GLOBALS['mrt_test_next_post_id']
		);
		parent::tearDown();
	}

	public function test_list_timetables_counts_dates_and_trips(): void {
		$this->boot_timetable_posts();
		$rows = MRT_rest_list_timetables();

		self::assertCount( 1, $rows );
		self::assertSame( 10, $rows[0]['id'] );
		self::assertSame( 'Green 2026', $rows[0]['title'] );
		self::assertSame( 2, $rows[0]['dates_count'] );
		self::assertSame( 1, $rows[0]['trips_count'] );
	}

	public function test_get_timetable_detail_not_found(): void {
		$result = MRT_rest_get_timetable_detail( 404 );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'not_found', $result->get_error_code() );
	}

	public function test_get_timetable_detail_includes_services_and_options(): void {
		$this->boot_timetable_posts();
		$GLOBALS['mrt_test_terms_list'] = array();
		$GLOBALS['mrt_test_post_terms'] = array(
			701 => array( 20 ),
		);
		$term = new WP_Term();
		$term->term_id = 20;
		$term->name    = 'Ångtåg';
		$term->slug    = 'angtag';
		$GLOBALS['mrt_test_terms'] = array( 20 => $term );

		$data = MRT_rest_get_timetable_detail( 10 );

		self::assertIsArray( $data );
		self::assertSame( 10, $data['id'] );
		self::assertSame( 'green', $data['type'] );
		self::assertSame( array( '2026-06-06', '2026-06-07' ), $data['dates'] );
		self::assertCount( 1, $data['services'] );
		self::assertSame( 701, $data['services'][0]['id'] );
		self::assertSame( '71', $data['services'][0]['service_number'] );
		self::assertSame( 'Ångtåg', $data['services'][0]['train_type_name'] );
	}

	public function test_create_timetable_rejects_empty_title(): void {
		$result = MRT_rest_create_timetable( array() );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'invalid_title', $result->get_error_code() );
	}

	public function test_create_timetable_handler_returns_detail_payload(): void {
		$this->boot_timetable_posts();
		$request = new WP_REST_Request( 'POST', '/timetables' );
		$request->set_json_params( array( 'title' => 'Orange 2026' ) );

		$data = MRT_rest_create_timetable_handler( $request );

		self::assertIsArray( $data );
		self::assertSame( 'Orange 2026', $data['title'] );
		self::assertSame( array(), $data['dates'] );
	}

	public function test_update_timetable_rejects_unknown_id(): void {
		$result = MRT_rest_update_timetable( 999, array( 'title' => 'Ny titel' ) );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'not_found', $result->get_error_code() );
	}

	public function test_update_timetable_saves_only_valid_dates(): void {
		$this->boot_timetable_posts();
		$result = MRT_rest_update_timetable(
			10,
			array(
				'dates' => array( '2026-07-04', 'not-a-date', '2026-07-04' ),
			)
		);

		self::assertTrue( $result );
		self::assertSame(
			array( '2026-07-04' ),
			get_post_meta( 10, 'mrt_timetable_dates', true )
		);
	}

	public function test_create_timetable_stores_type(): void {
		$id = MRT_rest_create_timetable(
			array(
				'title' => 'Green 2026',
				'type'  => 'green',
			)
		);

		self::assertIsInt( $id );
		self::assertGreaterThan( 0, $id );
		self::assertSame( 'green', get_post_meta( $id, 'mrt_timetable_type', true ) );
	}

	public function test_add_timetable_service_stores_service_number(): void {
		$this->boot_timetable_posts();

		$result = MRT_rest_add_timetable_service(
			10,
			array(
				'route_id'       => 50,
				'service_number' => '77',
			)
		);

		self::assertIsArray( $result );
		$service_id = (int) $result['service_id'];
		self::assertSame( '77', get_post_meta( $service_id, 'mrt_service_number', true ) );
	}

	public function test_add_timetable_service_requires_route(): void {
		$this->boot_timetable_posts();

		$result = MRT_rest_add_timetable_service( 10, array() );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'route', $result->get_error_code() );
	}

	public function test_add_timetable_service_creates_service_with_route(): void {
		$this->boot_timetable_posts();
		$GLOBALS['mrt_test_posts'][50] = new WP_Post(
			(object) array(
				'ID'         => 50,
				'post_title' => 'Uppsala – Faringe',
				'post_type'  => MRT_POST_TYPE_ROUTE,
			)
		);
		$GLOBALS['mrt_test_posts'][120] = new WP_Post(
			(object) array(
				'ID'         => 120,
				'post_title' => 'Faringe',
				'post_type'  => MRT_POST_TYPE_STATION,
			)
		);
		$GLOBALS['mrt_test_post_meta']['50|mrt_route_stations']    = array( 110, 120 );
		$GLOBALS['mrt_test_post_meta']['50|mrt_route_end_station']  = 120;

		$result = MRT_rest_add_timetable_service(
			10,
			array(
				'route_id'       => 50,
				'end_station_id' => 120,
			)
		);

		self::assertIsArray( $result );
		self::assertGreaterThan( 0, $result['service_id'] );
		self::assertSame( 'Faringe', $result['destination'] );
		self::assertSame( 10, (int) get_post_meta( (int) $result['service_id'], 'mrt_service_timetable_id', true ) );
	}

	public function test_update_timetable_service_rejects_wrong_timetable(): void {
		$this->boot_timetable_posts();

		$result = MRT_rest_update_timetable_service( 99, 701, array( 'route_id' => 50 ) );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'not_found', $result->get_error_code() );
	}

	public function test_update_timetable_service_requires_route(): void {
		$this->boot_timetable_posts();

		$result = MRT_rest_update_timetable_service( 10, 701, array() );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'route', $result->get_error_code() );
	}

	public function test_update_timetable_service_updates_number_and_route(): void {
		$this->boot_timetable_posts();
		$GLOBALS['mrt_test_posts'][120] = new WP_Post(
			(object) array(
				'ID'         => 120,
				'post_title' => 'Faringe',
				'post_type'  => MRT_POST_TYPE_STATION,
			)
		);
		$GLOBALS['mrt_test_post_meta']['50|mrt_route_stations']   = array( 110, 120 );
		$GLOBALS['mrt_test_post_meta']['50|mrt_route_end_station'] = 120;

		$result = MRT_rest_update_timetable_service(
			10,
			701,
			array(
				'route_id'       => 50,
				'end_station_id' => 120,
				'service_number' => 'B1',
			)
		);

		self::assertIsArray( $result );
		self::assertSame( 701, $result['id'] );
		self::assertSame( 'B1', $result['service_number'] );
		self::assertSame( 'B1', get_post_meta( 701, 'mrt_service_number', true ) );
	}

	public function test_update_timetable_service_saves_highlight(): void {
		$this->boot_timetable_posts();

		$result = MRT_rest_update_timetable_service(
			10,
			701,
			array(
				'route_id'        => 50,
				'highlight_label' => "Thun's-expressen",
				'highlight_color' => '#fff9c4',
				'highlight_note'  => 'Till klädvaruhuset.',
			)
		);

		self::assertIsArray( $result );
		self::assertSame( "Thun's-expressen", $result['highlight_label'] );
		self::assertSame(
			"Thun's-expressen",
			get_post_meta( 701, 'mrt_service_highlight_label', true )
		);
	}

	public function test_format_train_type_options_ignores_wp_errors(): void {
		$rows = MRT_rest_format_train_type_options( new WP_Error( 'fail', 'broken' ) );

		self::assertSame( array(), $rows );
	}

	private function boot_timetable_posts(): void {
		$GLOBALS['mrt_test_posts'] = array(
			10 => new WP_Post(
				(object) array(
					'ID'         => 10,
					'post_title' => 'Green 2026',
					'post_type'  => MRT_POST_TYPE_TIMETABLE,
				)
			),
			50 => new WP_Post(
				(object) array(
					'ID'         => 50,
					'post_title' => 'Main line',
					'post_type'  => MRT_POST_TYPE_ROUTE,
				)
			),
			701 => new WP_Post(
				(object) array(
					'ID'         => 701,
					'post_title' => 'Tur 71',
					'post_type'  => MRT_POST_TYPE_SERVICE,
				)
			),
		);
		$GLOBALS['mrt_test_post_meta'] = array(
			'10|mrt_timetable_dates'       => array( '2026-06-06', '2026-06-07' ),
			'10|mrt_timetable_type'        => 'green',
			'701|mrt_service_timetable_id' => 10,
			'701|mrt_service_route_id'     => 50,
			'701|mrt_service_end_station_id' => 120,
			'701|mrt_service_number'       => '71',
		);
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ): array {
			$post_type = (string) ( $args['post_type'] ?? '' );
			if ( $post_type === MRT_POST_TYPE_TIMETABLE ) {
				return array( $GLOBALS['mrt_test_posts'][10] );
			}
			if ( $post_type === MRT_POST_TYPE_ROUTE ) {
				return array( $GLOBALS['mrt_test_posts'][50] );
			}
			if ( $post_type === MRT_POST_TYPE_SERVICE && ( $args['meta_query'][0]['key'] ?? '' ) === 'mrt_service_timetable_id' ) {
				$timetable_id = (int) ( $args['meta_query'][0]['value'] ?? 0 );
				if ( $timetable_id === 10 ) {
					return array( $GLOBALS['mrt_test_posts'][701] );
				}
			}
			return array();
		};
		$GLOBALS['mrt_test_get_terms'] = static function (): array {
			return array();
		};
	}
}
