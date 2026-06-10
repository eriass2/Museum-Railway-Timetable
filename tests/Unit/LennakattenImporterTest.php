<?php
/**
 * Tests for Lennakatten CSV re-import entry point.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/import/lennakatten/importer.php';

final class LennakattenImporterTest extends TestCase {

	public function test_lennakatten_import_uses_override_mode(): void {
		self::assertSame( 'override', MRT_lennakatten_import_mode() );
	}
}
