<?php
/**
 * Plugin validation orchestrator.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

require __DIR__ . '/helpers.php';
require __DIR__ . '/required-files.php';
require __DIR__ . '/syntax.php';
require __DIR__ . '/security-markers.php';
require __DIR__ . '/plugin-meta.php';
require __DIR__ . '/assets.php';

function mrt_validate_run(): void {
	$errors   = array();
	$warnings = array();
	$checks   = 0;

	echo "🔍 Validating Museum Railway Timetable Plugin...\n\n";

	mrt_validate_required_files( $errors, $checks );

	$php_files = mrt_validate_php_files();
	mrt_validate_php_syntax( $errors, $checks, $php_files );
	mrt_validate_abspath_checks( $warnings, $checks, $php_files );
	mrt_validate_inline_styles( $warnings, $checks, $php_files );
	mrt_validate_plugin_header( $errors, $checks );
	mrt_validate_text_domain( $warnings, $checks, $php_files );
	mrt_validate_css_files( $errors, $checks );
	mrt_validate_accessibility_markers( $errors, $checks );

	mrt_validate_print_summary( $errors, $warnings, $checks );
}

mrt_validate_run();
