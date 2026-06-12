<?php
/**
 * ABSPATH and inline-style validation sections.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

/**
 * @param array<int, string> $warnings
 * @param list<string>       $php_files
 */
function mrt_validate_abspath_checks( array &$warnings, int &$checks, array $php_files ): void {
	echo "\n3. Checking ABSPATH protection...\n";
	foreach ( $php_files as $file ) {
		if ( strpos( $file, 'uninstall.php' ) !== false ) {
			continue;
		}
		++$checks;
		$content = file_get_contents( $file );
		if ( ! is_string( $content ) || ! preg_match( '/if\s*\(\s*!\s*defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)\s*\)/', $content ) ) {
			$warnings[] = "Missing ABSPATH check in $file";
			echo "  ⚠️  $file (missing ABSPATH check)\n";
		} else {
			echo "  ✅ $file\n";
		}
	}
}

/**
 * @param array<int, string> $warnings
 * @param list<string>       $php_files
 */
function mrt_validate_inline_styles( array &$warnings, int &$checks, array $php_files ): void {
	echo "\n4. Checking for inline styles...\n";
	foreach ( $php_files as $file ) {
		++$checks;
		$content = file_get_contents( $file );
		if ( ! is_string( $content ) ) {
			continue;
		}
		if ( preg_match( '/style\s*=\s*["\']/', $content ) ) {
			if ( preg_match( '/style\s*=\s*[^>]*--[a-zA-Z-]+\s*:/', $content ) ) {
				echo "  ✅ $file (CSS custom property only)\n";
			} else {
				$warnings[] = "Inline style found in $file";
				echo "  ⚠️  $file (contains inline styles)\n";
			}
		} else {
			echo "  ✅ $file\n";
		}
	}
}
