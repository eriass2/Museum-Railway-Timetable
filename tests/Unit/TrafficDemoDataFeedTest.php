<?php
/**
 * Lennakatten traffic demo feed enrichment tests.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/domain/traffic-notices/disruption-feed.php';
require_once ABSPATH . 'inc/import/lennakatten/traffic-demo-data.php';

final class TrafficDemoDataFeedTest extends TestCase {
	protected function tearDown(): void {
		delete_option( MRT_OPTION_PUBLIC_NOTICES );
		parent::tearDown();
	}

	public function test_reference_demo_notices_produce_ul_summaries_and_panels(): void {
		update_option(
			MRT_OPTION_PUBLIC_NOTICES,
			MRT_lennakatten_reference_public_notices(),
			false
		);

		$result = MRT_disruption_feed_build( '2026-06-06', 90 );
		self::assertIsArray( $result );
		self::assertFalse( $result['is_empty'] );
		self::assertArrayHasKey( 'panels', $result );
		self::assertNotEmpty( $result['panels'] );

		$ongoing = $result['ongoing'];
		self::assertCount( 1, $ongoing );
		self::assertSame( 'Glassrea på Faringe station kl 14.', $ongoing[0]['summary'] );
		self::assertSame( 'Gäller Idag', $ongoing[0]['validity_label'] );
		self::assertSame( 'Glassrea på stationen idag.', $ongoing[0]['detail_intro'] );

		$upcoming = $result['upcoming'];
		self::assertCount( 1, $upcoming );
		self::assertSame( 'Buss ersätter tåg vid Selkné.', $upcoming[0]['summary'] );
		self::assertStringContainsString( 'Gäller', $upcoming[0]['validity_label'] );
		self::assertStringNotContainsString( '2026-07-01', $upcoming[0]['summary'] );
	}

	public function test_reference_demo_deviation_keys_include_bus_service(): void {
		$deviations = MRT_lennakatten_reference_service_deviations();
		self::assertArrayHasKey( 'green-b3-bus-out', $deviations );
		self::assertSame( 'Försenad trafik', $deviations['green-b3-bus-out']['2026-06-06'] );
	}
}
