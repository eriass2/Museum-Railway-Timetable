<?php
/**
 * Soft warning: Vue SFC scoped style blocks over line budget.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

/**
 * @param array<int, string> $warnings
 */
function mrt_validate_vue_style_blocks( array &$warnings, int &$checks ): void {
	echo "\n9. Checking Vue SFC style block size (soft limit)...\n";

	$max_lines  = 150;
	$root       = dirname( __DIR__, 3 );
	$vue_root   = $root . '/frontend/vue/src';
	$exceptions = array(
		'frontend/vue/src/wizard/components/WizardSummaryStep.vue' => 'print block',
	);
	$style_pattern = '/<style\b[^>]*>([\s\S]*?)<\/style>/';
	$root_prefix   = str_replace( '\\', '/', $root );

	if ( ! is_dir( $vue_root ) ) {
		echo "  ⚠️  Skipped — $vue_root not found\n";
		return;
	}

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $vue_root, FilesystemIterator::SKIP_DOTS )
	);

	foreach ( $iterator as $file_info ) {
		if ( ! $file_info instanceof SplFileInfo || $file_info->getExtension() !== 'vue' ) {
			continue;
		}

		$path = str_replace( '\\', '/', $file_info->getPathname() );
		$rel  = ltrim( str_replace( $root_prefix, '', $path ), '/' );
		$contents = file_get_contents( $path );
		if ( ! is_string( $contents ) || ! preg_match_all( $style_pattern, $contents, $matches, PREG_SET_ORDER ) ) {
			continue;
		}

		foreach ( $matches as $match ) {
			++$checks;
			$style_body = trim( (string) $match[1] );
			if ( $style_body === '' ) {
				continue;
			}

			$line_count = substr_count( $style_body, "\n" ) + 1;
			if ( $line_count <= $max_lines ) {
				continue;
			}

			if ( isset( $exceptions[ $rel ] ) ) {
				echo "  ⚠️  $rel style block {$line_count} lines (allowed: {$exceptions[ $rel ]})\n";
				continue;
			}

			$warnings[] = "Vue style block exceeds {$max_lines} lines in {$rel} ({$line_count} lines) — split component CSS";
			echo "  ⚠️  $rel style block {$line_count} lines (> {$max_lines})\n";
		}
	}

	if ( $checks === 0 ) {
		echo "  ✅ no Vue style blocks scanned\n";
	}
}
