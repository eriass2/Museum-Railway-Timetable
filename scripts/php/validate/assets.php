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

	++$checks;
	$public_css_path = 'assets/frontend-public.css';
	if ( file_exists( $public_css_path ) ) {
		$public_css = file_get_contents( $public_css_path );
		if ( is_string( $public_css ) && strpos( $public_css, 'ui-components.css' ) !== false ) {
			$errors[] = 'frontend-public.css must not import ui-components.css (CSS encapsulation complete)';
			echo "  ❌ $public_css_path still imports ui-components.css\n";
		} else {
			echo "  ✅ $public_css_path (no ui-components.css import)\n";
		}
	}

	$removed_legacy = array(
		'assets/frontend/ui-components.css',
		'assets/frontend/ui/primitives.css',
		'assets/frontend/ui/calendar-tokens.css',
		'assets/frontend/ui/calendar-nav-legend.css',
		'assets/frontend/ui/panels-headings.css',
		'assets/frontend/ui/price-table.css',
		'assets/frontend/ui/trips.css',
		'assets/frontend/ui/wizard-steps.css',
		'frontend/vue/src/styles/journey-wizard.css',
		'frontend/vue/src/styles/month-calendar.css',
	);
	foreach ( $removed_legacy as $removed_file ) {
		++$checks;
		if ( file_exists( $removed_file ) ) {
			$errors[] = "Deprecated CSS file should be removed: $removed_file";
			echo "  ❌ $removed_file still exists (should be deleted)\n";
		} else {
			echo "  ✅ removed: $removed_file\n";
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
		'frontend/vue/src/components/ui/mrtFocusRing.css' => array( ':focus-visible' ),
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
