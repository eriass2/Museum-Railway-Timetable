<?php
/**
 * Plugin header and text-domain validation sections.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

/**
 * @param array<int, string> $errors
 */
function mrt_validate_plugin_header( array &$errors, int &$checks ): void {
	echo "\n5. Checking plugin header...\n";
	++$checks;
	$main_file = file_get_contents( 'museum-railway-timetable.php' );
	if ( ! is_string( $main_file ) ) {
		$errors[] = 'Cannot read museum-railway-timetable.php';
		echo "  ❌ Cannot read main plugin file\n";
		return;
	}

	$required_headers = array( 'Plugin Name', 'Description', 'Version', 'Text Domain' );
	foreach ( $required_headers as $header ) {
		if ( strpos( $main_file, $header . ':' ) === false ) {
			$errors[] = "Missing plugin header: $header";
			echo "  ❌ Missing header: $header\n";
		} else {
			echo "  ✅ Header: $header\n";
		}
	}
}

/**
 * @param array<int, string> $warnings
 * @param list<string>       $php_files
 */
function mrt_validate_text_domain( array &$warnings, int &$checks, array $php_files ): void {
	echo "\n6. Checking text domain consistency...\n";
	++$checks;
	$text_domain     = 'museum-railway-timetable';
	$domain_matches  = 0;
	$domain_issues   = 0;

	foreach ( $php_files as $file ) {
		$content = file_get_contents( $file );
		if ( ! is_string( $content ) ) {
			continue;
		}
		preg_match_all( '/__(\(|[\'"])[^\'"\)]*[\'"],\s*[\'"]([^\'"]+)[\'"]/', $content, $matches );
		if ( empty( $matches[2] ) ) {
			continue;
		}
		foreach ( $matches[2] as $domain ) {
			if ( $domain === $text_domain ) {
				++$domain_matches;
			} else {
				++$domain_issues;
				$warnings[] = "Inconsistent text domain in $file: found '$domain', expected '$text_domain'";
			}
		}
	}

	if ( $domain_issues === 0 ) {
		echo "  ✅ Text domain consistent ($domain_matches matches)\n";
	} else {
		echo "  ⚠️  Found $domain_issues inconsistent text domain(s)\n";
	}
}
