<?php
/**
 * Required files validation section.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

/**
 * @param array<int, string> $errors
 */
function mrt_validate_required_files( array &$errors, int &$checks ): void {
	echo "1. Checking required files...\n";
	$required_files = array(
		'museum-railway-timetable.php',
		'uninstall.php',
		'inc/assets.php',
		'inc/admin.php',
		'inc/admin/menu.php',
		'inc/admin/tools/clear-db.php',
		'inc/admin/tools/demo-page.php',
		'inc/admin/tools/dev/dev-navigation.php',
		'inc/admin/tools/dev/dev-cli.php',
		'scripts/docker-dev-reset.ps1',
		'inc/admin/tools/import-lennakatten.php',
		'inc/infrastructure/wordpress/environment.php',
		'inc/infrastructure/wordpress/log.php',
		'inc/infrastructure/wordpress/plugin-settings.php',
		'inc/assets/loader.php',
		'inc/shortcodes.php',
		'inc/infrastructure/post-types.php',
		'inc/infrastructure/rest/loader.php',
		'inc/admin/app.php',
		'inc/infrastructure/post-types/cpt-admin-tweaks.php',
		'inc/bootstrap.php',
		'inc/bootstrap/domain.php',
		'inc/infrastructure/wordpress/helpers-utils.php',
		'inc/domain/service/stop-time-display.php',
		'assets/train-type-icons.css',
		'languages/museum-railway-timetable.pot',
		'languages/museum-railway-timetable-sv_SE.po',
	);

	foreach ( $required_files as $file ) {
		++$checks;
		if ( ! file_exists( $file ) ) {
			$errors[] = "Missing required file: $file";
			echo "  ❌ Missing: $file\n";
		} else {
			echo "  ✅ $file\n";
		}
	}
}
