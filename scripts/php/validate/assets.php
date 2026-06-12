<?php
/**
 * CSS and accessibility marker validation sections.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

/**
 * @param array<int, string> $errors
 */
function mrt_validate_css_files( array &$errors, int &$checks ): void {
	echo "\n7. Checking CSS files...\n";
	$css_files = array(
		'assets/train-type-icons.css',
		'assets/frontend-public.css',
		'frontend/vue/src/styles/month-calendar.css',
		'frontend/vue/src/styles/journey-wizard.css',
		'assets/admin.css',
	);

	foreach ( $css_files as $css_file ) {
		++$checks;
		if ( ! file_exists( $css_file ) ) {
			$errors[] = "CSS file missing: $css_file";
			echo "  ❌ $css_file missing\n";
			continue;
		}
		$css = file_get_contents( $css_file );
		if ( ! is_string( $css ) || strlen( $css ) === 0 ) {
			$errors[] = "CSS file is empty: $css_file";
			echo "  ❌ $css_file is empty\n";
		} else {
			echo "  ✅ $css_file\n";
		}
	}

	$legacy_ui_files = array(
		'assets/frontend/ui/trips.css',
		'assets/frontend/ui/price-table.css',
		'assets/frontend/ui/panels-headings.css',
	);
	foreach ( $legacy_ui_files as $legacy_file ) {
		++$checks;
		if ( ! file_exists( $legacy_file ) ) {
			$errors[] = "Legacy UI CSS missing: $legacy_file";
			echo "  ❌ $legacy_file missing\n";
			continue;
		}
		$legacy_css = file_get_contents( $legacy_file );
		if ( ! is_string( $legacy_css ) ) {
			$errors[] = "Cannot read legacy UI CSS: $legacy_file";
			echo "  ❌ Unreadable: $legacy_file\n";
			continue;
		}
		if ( preg_match( '/\.mrt-[a-z0-9_-]+\s*[{,]/i', $legacy_css ) === 1 ) {
			$errors[] = "Legacy UI CSS must not define .mrt-* rules (move to scoped Vue SFC): $legacy_file";
			echo "  ❌ $legacy_file contains .mrt-* class rules\n";
		} else {
			echo "  ✅ $legacy_file (no .mrt-* rules)\n";
		}
	}
}

/**
 * @param array<int, string> $errors
 */
function mrt_validate_accessibility_markers( array &$errors, int &$checks ): void {
	echo "\n8. Checking accessibility markers in public modules...\n";
	$a11y_markers = array(
		'inc/public/journey-wizard/shell.php'     => array( 'MRT_render_vue_mount', 'museum_journey_wizard' ),
		'inc/public/month-calendar/shortcode.php' => array( 'MRT_render_vue_mount', 'museum_timetable_month' ),
		'assets/frontend/ui/primitives.css'       => array( ':focus-visible' ),
	);

	foreach ( $a11y_markers as $file => $needles ) {
		++$checks;
		if ( ! file_exists( $file ) ) {
			$errors[] = "A11y check file missing: $file";
			echo "  ❌ Missing: $file\n";
			continue;
		}
		$contents = file_get_contents( $file );
		if ( false === $contents ) {
			$errors[] = "Cannot read: $file";
			echo "  ❌ Unreadable: $file\n";
			continue;
		}
		$missing = array();
		foreach ( $needles as $needle ) {
			if ( strpos( $contents, $needle ) === false ) {
				$missing[] = $needle;
			}
		}
		if ( $missing !== array() ) {
			$errors[] = "A11y markers missing in $file: " . implode( ', ', $missing );
			echo '  ❌ ' . $file . ' missing: ' . implode( ', ', $missing ) . "\n";
		} else {
			echo "  ✅ $file\n";
		}
	}
}
