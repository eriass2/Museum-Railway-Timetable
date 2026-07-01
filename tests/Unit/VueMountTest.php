<?php
/**
 * Vue mount layout helpers (inc/assets/vue-mount-layout.php).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once ABSPATH . 'inc/assets/vue-mount-layout.php';

final class VueMountTest extends TestCase {

	public function test_mount_extra_classes_alignwide_for_public_apps(): void {
		self::assertSame( ' alignwide', MRT_vue_mount_extra_classes( 'month', array() ) );
		self::assertSame( ' alignwide', MRT_vue_mount_extra_classes( 'index', array() ) );
		self::assertSame( ' alignwide', MRT_vue_mount_extra_classes( 'overview', array() ) );
		self::assertSame( ' alignwide', MRT_vue_mount_extra_classes( 'traffic_notices', array() ) );
		self::assertSame( ' alignfull', MRT_vue_mount_extra_classes( 'wizard', array() ) );
		self::assertSame( ' alignfull', MRT_vue_mount_extra_classes( 'wizard', array( 'embedded' => false ) ) );
	}

	public function test_mount_extra_classes_empty_for_embedded_wizard(): void {
		self::assertSame( '', MRT_vue_mount_extra_classes( 'wizard', array( 'embedded' => true ) ) );
	}
}
