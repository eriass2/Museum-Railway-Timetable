<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/inc/functions/helpers-utils.php';

final class TrainTypeIconTest extends TestCase {

	protected function setUp(): void {
		if ( ! defined( 'MRT_PATH' ) ) {
			define( 'MRT_PATH', dirname( __DIR__, 2 ) . '/' );
		}
		if ( ! defined( 'MRT_URL' ) ) {
			define( 'MRT_URL', 'http://example.test/wp-content/plugins/museum-railway-timetable/' );
		}
	}

	public function test_resolve_railbus_before_bus(): void {
		self::assertSame( 'railbus', MRT_resolve_train_type_symbol_key( 'Rälsbuss', 'ralsbuss' ) );
		self::assertSame( 'bus', MRT_resolve_train_type_symbol_key( 'Buss', 'buss' ) );
	}

	public function test_resolve_steam_and_diesel(): void {
		self::assertSame( 'steam', MRT_resolve_train_type_symbol_key( 'Ångtåg', 'angtag' ) );
		self::assertSame( 'diesel', MRT_resolve_train_type_symbol_key( 'Dieseltåg', 'dieseltag' ) );
	}

	public function test_icon_url_contains_key(): void {
		self::assertStringContainsString( 'steam.png', MRT_train_type_icon_url( 'steam' ) );
	}
}
