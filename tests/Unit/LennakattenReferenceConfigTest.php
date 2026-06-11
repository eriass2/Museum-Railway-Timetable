<?php
/**
 * Lennakatten reference config (settings, prices) — guard against drift from taxa 2026.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/import/lennakatten/reference-data.php';
require_once ABSPATH . 'inc/import/lennakatten/traffic-demo-data.php';
require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/loader.php';

final class LennakattenReferenceConfigTest extends TestCase {
	use MRT_Lennakatten_Test_Fixture;

	protected function tearDown(): void {
		$this->mrt_clear_test_options();
		parent::tearDown();
	}

	public function test_reference_plugin_settings_match_taxa_2026_profile(): void {
		$settings = MRT_lennakatten_reference_plugin_settings();
		self::assertSame( 'Lennakatten', $settings['operator_name'] );
		self::assertSame( 'https://www.lennakatten.se/biljetter/', $settings['ticket_url'] );
		self::assertSame( 2, $settings['max_transfers'] );
		self::assertSame( 900, $settings['afternoon_return_threshold_minutes'] );
	}

	public function test_reference_price_schema_has_lennakatten_afternoon_and_zone_cap(): void {
		$schema = MRT_lennakatten_reference_price_schema();
		self::assertSame( array( 1, 2, 3 ), $schema['zones'] );
		self::assertSame( 3, $schema['zone_cap'] );
		self::assertSame( 160, $schema['afternoon_return']['adult'] );
		self::assertSame( 140, $schema['afternoon_return']['student_senior'] );
		self::assertSame( 60, $schema['afternoon_return']['child_4_15'] );
	}

	public function test_fixture_price_schema_has_three_zones_only(): void {
		$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
		self::assertIsArray( $package );
		$zones = array();
		foreach ( (array) ( $package['files']['price_schema.csv'] ?? array() ) as $row ) {
			if ( ( $row['kind'] ?? '' ) === 'zone' ) {
				$zones[] = (int) ( $row['value'] ?? 0 );
			}
		}
		sort( $zones );
		self::assertSame( array( 1, 2, 3 ), $zones );
	}

	public function test_reference_price_matrix_matches_builtin(): void {
		self::assertSame(
			MRT_get_builtin_price_matrix(),
			MRT_lennakatten_reference_price_matrix()
		);
	}

	public function test_apply_lennakatten_options_wires_wp_options(): void {
		$this->mrt_apply_lennakatten_options();
		self::assertSame( 'Lennakatten', MRT_plugin_operator_name() );
		self::assertSame( 160, MRT_get_afternoon_return_prices()['adult'] );
		self::assertSame( 3, MRT_price_zone_cap() );
		self::assertSame( 80, MRT_get_price_matrix()['single']['adult'][1] );
	}

	public function test_fixture_settings_csv_matches_reference(): void {
		$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
		self::assertIsArray( $package );
		$rows = (array) ( $package['files']['settings.csv'] ?? array() );
		self::assertNotEmpty( $rows );
		$map = array();
		foreach ( $rows as $row ) {
			$map[ $row['key'] ?? '' ] = $row['value'] ?? '';
		}
		$ref = MRT_lennakatten_reference_plugin_settings();
		self::assertSame( (string) $ref['operator_name'], $map['operator_name'] );
		self::assertSame( (string) $ref['ticket_url'], $map['ticket_url'] );
		self::assertSame( (string) $ref['hero_background_url'], $map['hero_background_url'] );
		self::assertSame( (string) $ref['max_transfers'], $map['max_transfers'] );
		self::assertSame(
			(string) $ref['afternoon_return_threshold_minutes'],
			$map['afternoon_return_threshold_minutes']
		);
	}

	public function test_fixture_includes_settings_and_prices_in_manifest(): void {
		$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
		$includes = (array) ( $package['manifest']['includes'] ?? array() );
		self::assertContains( 'settings', $includes );
		self::assertContains( 'brand_tokens', $includes );
		self::assertContains( 'prices', $includes );
		self::assertNotEmpty( $package['files']['price_schema.csv'] ?? array() );
		self::assertNotEmpty( $package['files']['prices.csv'] ?? array() );
		self::assertNotEmpty( $package['files']['brand_tokens.csv'] ?? array() );
	}

	public function test_fixture_brand_tokens_csv_matches_reference(): void {
		$package = MRT_csv_load_package( ABSPATH . 'testdata/fixtures/lennakatten' );
		$rows    = (array) ( $package['files']['brand_tokens.csv'] ?? array() );
		self::assertNotEmpty( $rows );
		$ref = MRT_lennakatten_reference_brand_tokens();
		$google = '';
		$tokens = array();
		foreach ( $rows as $row ) {
			$key = $row['token'] ?? '';
			if ( $key === 'google_fonts' ) {
				$google = (string) ( $row['value'] ?? '' );
				continue;
			}
			$tokens[ $key ] = (string) ( $row['value'] ?? '' );
		}
		self::assertSame( $ref['google_fonts'], $google );
		ksort( $tokens );
		$expected = $ref['tokens'];
		ksort( $expected );
		self::assertSame( $expected, $tokens );
	}

	public function test_reference_traffic_demo_has_notices_and_deviations(): void {
		$notices = MRT_lennakatten_reference_public_notices();
		self::assertCount( 2, $notices );
		self::assertSame( 'demo-glassrea', $notices[1]['id'] );
		self::assertSame( '2026-06-06', $notices[1]['active_from'] );

		$deviations = MRT_lennakatten_reference_service_deviations();
		self::assertArrayHasKey( 'green-71-out', $deviations );
		self::assertSame( 'Inställd', $deviations['green-71-out']['2026-06-06'] );
		self::assertSame( 'Inställd', $deviations['green-97-out']['2026-06-06'] );
		self::assertSame( 'Ersättningsbuss', $deviations['green-75-out']['2026-06-06'] );
	}

	public function test_csv_import_resolves_plugin_relative_hero_background_url(): void {
		if ( ! defined( 'MRT_URL' ) ) {
			define( 'MRT_URL', 'https://example.test/wp-content/plugins/museum-railway-timetable/' );
		}
		$resolved = MRT_csv_resolve_hero_background_url( MRT_testdata_wizard_hero_background_relative_path() );
		self::assertStringContainsString( 'testdata/images/wizard-hero-bosshus.jpg', $resolved );
	}

	public function test_production_defaults_stay_neutral(): void {
		$schema = MRT_get_default_price_schema();
		self::assertSame( 1, $schema['zone_cap'] );
		self::assertSame( 0, $schema['afternoon_return']['adult'] );
		self::assertSame( '', MRT_plugin_operator_name() );
	}
}
