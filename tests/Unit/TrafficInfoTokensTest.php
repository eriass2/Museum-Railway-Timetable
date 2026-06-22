<?php
/**
 * Traffic info token enqueue profile.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/assets/brand-tokens-data.php';
require_once ABSPATH . 'inc/assets/brand-tokens.php';

if ( ! defined( 'MRT_LENNAKATTEN_BRAND' ) ) {
	define( 'MRT_LENNAKATTEN_BRAND', true );
}

require_once ABSPATH . 'inc/assets/traffic-info-tokens.php';

final class TrafficInfoTokensTest extends TestCase {

	protected function tearDown(): void {
		delete_option( MRT_OPTION_BRAND_TOKENS );
		unset( $GLOBALS['mrt_test_filters']['mrt_use_lennakatten_traffic_info_tokens'] );
		parent::tearDown();
	}

	public function test_lennakatten_traffic_profile_when_brand_constant_set(): void {
		self::assertTrue( MRT_use_lennakatten_traffic_info_tokens() );
	}

	public function test_lennakatten_traffic_profile_with_imported_brand_tokens(): void {
		update_option(
			MRT_OPTION_BRAND_TOKENS,
			array(
				'google_fonts' => '',
				'tokens'       => array( '--mrt-color-brand-green' => '#296310' ),
			)
		);
		self::assertFalse( MRT_use_lennakatten_brand_tokens() );
		self::assertTrue( MRT_use_lennakatten_traffic_info_tokens() );
	}

	public function test_filter_can_disable_lennakatten_traffic_profile(): void {
		$GLOBALS['mrt_test_filters']['mrt_use_lennakatten_traffic_info_tokens'] = static function (): bool {
			return false;
		};
		self::assertFalse( MRT_use_lennakatten_traffic_info_tokens() );
	}
}
