<?php
/**
 * Brand token storage and CSV import.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/assets/brand-tokens-data.php';
require_once ABSPATH . 'inc/assets/brand-tokens.php';
require_once dirname( __DIR__, 2 ) . '/scripts/csv-cli-stubs.php';
require_once ABSPATH . 'inc/import/csv/loader.php';

final class BrandTokensTest extends TestCase {

	protected function tearDown(): void {
		delete_option( MRT_OPTION_BRAND_TOKENS );
		parent::tearDown();
	}

	public function test_sanitize_rejects_unsafe_values(): void {
		self::assertSame( '', MRT_sanitize_brand_token_value( '--mrt-color-brand-green', '#296310;}' ) );
		self::assertSame( '', MRT_sanitize_brand_token_key( 'evil-token' ) );
		self::assertSame( '', MRT_sanitize_brand_google_fonts_url( 'https://evil.example/font.css' ) );
	}

	public function test_sanitize_accepts_hex_var_and_font_stack(): void {
		self::assertSame( '#296310', MRT_sanitize_brand_token_value( '--mrt-color-brand-green', '#296310' ) );
		self::assertSame(
			'var(--mrt-color-brand-green)',
			MRT_sanitize_brand_token_value( '--mrt-color-green-600', 'var(--mrt-color-brand-green)' )
		);
		self::assertSame(
			'"Roboto", system-ui, sans-serif',
			MRT_sanitize_brand_token_value( '--mrt-font-body', '"Roboto", system-ui, sans-serif' )
		);
	}

	public function test_inline_css_builds_root_block(): void {
		update_option(
			MRT_OPTION_BRAND_TOKENS,
			array(
				'google_fonts' => '',
				'tokens'       => array(
					'--mrt-color-brand-green' => '#296310',
				),
			)
		);
		$css = MRT_brand_tokens_inline_css();
		self::assertStringContainsString( ':root {', $css );
		self::assertStringContainsString( '--mrt-color-brand-green: #296310;', $css );
	}

	public function test_csv_import_stores_tokens_and_google_fonts(): void {
		$files = array(
			'brand_tokens.csv' => array(
				array(
					'token' => 'google_fonts',
					'value' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@400&display=swap',
				),
				array(
					'token' => '--mrt-color-brand-green',
					'value' => '#296310',
				),
				array(
					'token' => 'mrt-font-body',
					'value' => '"Roboto", sans-serif',
				),
				array(
					'token' => 'ignored',
					'value' => '#000000',
				),
			),
		);
		MRT_csv_import_brand_tokens( $files );
		self::assertTrue( MRT_has_imported_brand_tokens() );
		self::assertSame(
			'https://fonts.googleapis.com/css2?family=Roboto:wght@400&display=swap',
			MRT_get_brand_google_fonts_url()
		);
		self::assertSame( '#296310', MRT_get_brand_css_tokens()['--mrt-color-brand-green'] );
		self::assertSame( '"Roboto", sans-serif', MRT_get_brand_css_tokens()['--mrt-font-body'] );
	}

	public function test_imported_tokens_disable_lennakatten_pack(): void {
		update_option(
			MRT_OPTION_BRAND_TOKENS,
			array(
				'google_fonts' => '',
				'tokens'       => array( '--mrt-color-brand-green' => '#296310' ),
			)
		);
		self::assertFalse( MRT_use_lennakatten_brand_tokens() );
	}
}
