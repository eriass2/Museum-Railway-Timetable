<?php
/**
 * Disruption feed domain tests (J11 fas 1).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/traffic-notices/disruption-feed.php';

final class DisruptionFeedTest extends TestCase {
	protected function tearDown(): void {
		delete_option( MRT_OPTION_PUBLIC_NOTICES );
		unset( $GLOBALS['mrt_test_get_posts'], $GLOBALS['mrt_test_posts'], $GLOBALS['mrt_test_post_meta'] );
		parent::tearDown();
	}

	public function test_build_rejects_invalid_date(): void {
		$result = MRT_disruption_feed_build( 'not-a-date' );
		self::assertInstanceOf( WP_Error::class, $result );
	}

	public function test_build_includes_general_notice_in_ongoing_and_upcoming(): void {
		update_option(
			MRT_OPTION_PUBLIC_NOTICES,
			array(
				array(
					'id'          => 'today',
					'text'        => 'Glassrea idag',
					'enabled'     => true,
					'active_from' => '2026-06-06',
					'active_to'   => '2026-06-06',
					'sort_order'  => 10,
				),
				array(
					'id'          => 'summer',
					'text'        => 'Sommarbaninfo',
					'enabled'     => true,
					'active_from' => '2026-07-01',
					'active_to'   => '2026-08-16',
					'sort_order'  => 20,
				),
			),
			false
		);

		$result = MRT_disruption_feed_build( '2026-06-06', 90 );
		self::assertIsArray( $result );
		self::assertFalse( $result['is_empty'] );
		self::assertCount( 1, $result['ongoing'] );
		self::assertCount( 1, $result['upcoming'] );
		self::assertSame( 'general', $result['ongoing'][0]['source'] );
		self::assertSame( 'general', $result['upcoming'][0]['source'] );
		self::assertSame( 'Glassrea idag', $result['ongoing'][0]['summary'] );
		self::assertSame( 'Sommarbaninfo', $result['upcoming'][0]['summary'] );
		self::assertStringContainsString( 'Gäller', $result['upcoming'][0]['validity_label'] );
		self::assertStringContainsString( '16', $result['upcoming'][0]['validity_label'] );
		self::assertArrayHasKey( 'panels', $result );
		self::assertCount( 2, $result['panels'] );
	}

	public function test_build_groups_deviations_with_same_notice_on_same_date(): void {
		$this->stub_timetable_with_services(
			array(
				71 => array(
					'number'  => '71',
					'notices' => array( '2026-06-06' => 'Inställd' ),
				),
				97 => array(
					'number'  => '97',
					'notices' => array( '2026-06-06' => 'Inställd' ),
				),
			)
		);

		$result = MRT_disruption_feed_build( '2026-06-06', 7 );
		self::assertIsArray( $result );
		$deviations = array_values(
			array_filter(
				$result['items'],
				static fn( array $item ): bool => ( $item['source'] ?? '' ) === 'deviation'
			)
		);
		self::assertCount( 1, $deviations );
		self::assertSame( array( '71', '97' ), $deviations[0]['train_numbers'] );
		self::assertSame( 'Inställd trafik', $deviations[0]['summary'] );
		self::assertSame( 'warning', $deviations[0]['severity'] );
		self::assertSame( 'train', $deviations[0]['category_key'] );
		self::assertSame( 'cancelled', $deviations[0]['kind'] );
		self::assertNotEmpty( $deviations[0]['detail_intro'] );
		self::assertNotEmpty( $deviations[0]['detail_sections'] );
	}

	public function test_build_clamps_horizon_days(): void {
		$result = MRT_disruption_feed_build( '2026-06-06', 999 );
		self::assertIsArray( $result );
		self::assertSame( MRT_DISRUPTION_FEED_MAX_HORIZON, $result['horizon_days'] );
	}

	public function test_item_body_display_hides_redundant_deviation_notice(): void {
		$item = array(
			'source'   => 'deviation',
			'headline' => 'Inställd trafik — Tåg 71',
			'body'     => 'Inställd',
		);
		self::assertSame( '', MRT_disruption_feed_item_body_display( $item ) );
	}

	public function test_item_body_display_strips_first_line_for_general_notice(): void {
		$item = array(
			'source'   => 'general',
			'headline' => 'Baninfo sommar',
			'body'     => "Baninfo sommar\nBerörda anslutningar: Uppsala",
		);
		self::assertSame( 'Berörda anslutningar: Uppsala', MRT_disruption_feed_item_body_display( $item ) );
	}

	public function test_item_body_display_returns_full_body_when_not_redundant(): void {
		$item = array(
			'source'   => 'general',
			'headline' => 'Glassrea',
			'body'     => 'Glassrea på stationen idag.',
		);
		self::assertSame( 'Glassrea på stationen idag.', MRT_disruption_feed_item_body_display( $item ) );
	}

	/**
	 * @param array<int, array{number: string, notices: array<string, string>}> $services
	 */
	private function stub_timetable_with_services( array $services ): void {
		$timetable = new WP_Post(
			(object) array(
				'ID'         => 10,
				'post_title' => 'Green',
				'post_type'  => 'mrt_timetable',
			)
		);
		$posts     = array( 10 => $timetable );
		$service_posts = array();
		$meta          = array();
		foreach ( $services as $id => $spec ) {
			$post = new WP_Post(
				(object) array(
					'ID'         => $id,
					'post_title' => $spec['number'] . ' test',
					'post_type'  => 'mrt_service',
				)
			);
			$posts[ $id ]         = $post;
			$service_posts[]      = $post;
			$meta[ $id . '|mrt_service_notices_by_date' ] = $spec['notices'];
			$meta[ $id . '|mrt_service_number' ]          = $spec['number'];
		}
		$GLOBALS['mrt_test_posts']     = $posts;
		$GLOBALS['mrt_test_post_meta'] = $meta;
		$GLOBALS['mrt_test_get_posts'] = static function ( array $args ) use ( $timetable, $service_posts ): array {
			if ( ( $args['post_type'] ?? '' ) === 'mrt_timetable' ) {
				return array( $timetable );
			}
			if ( ( $args['post_type'] ?? '' ) === 'mrt_service' ) {
				return $service_posts;
			}
			return array();
		};
	}
}
