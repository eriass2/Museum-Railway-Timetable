<?php
/**
 * Shared helpers for plugin validation sections.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

/**
 * @return list<string>
 */
function mrt_validate_php_files(): array {
	$php_files = array_merge(
		glob('*.php') ?: array(),
		glob('inc/*.php') ?: array(),
		glob('inc/*/*.php') ?: array(),
		glob('inc/*/*/*.php') ?: array(),
		glob('inc/*/*/*/*.php') ?: array()
	);

	return array_values(
		array_filter(
			array_unique($php_files),
			static function ( string $file ): bool {
				return ! mrt_validate_is_skipped_file( $file );
			}
		)
	);
}

function mrt_validate_is_skipped_file( string $file ): bool {
	if ( strpos( $file, 'scripts/validate.php' ) !== false ) {
		return true;
	}
	if ( strpos( $file, 'scripts/php/validate.php' ) !== false ) {
		return true;
	}
	if ( strpos( $file, 'scripts/php/validate/' ) !== false ) {
		return true;
	}
	return false;
}

/**
 * @param array<int, string> $errors
 * @param array<int, string> $warnings
 */
function mrt_validate_print_summary( array $errors, array $warnings, int $checks ): void {
	echo "\n" . str_repeat( '=', 60 ) . "\n";
	echo "📊 Validation Summary\n";
	echo str_repeat( '=', 60 ) . "\n";
	echo "Total checks: $checks\n";
	echo 'Errors: ' . count( $errors ) . "\n";
	echo 'Warnings: ' . count( $warnings ) . "\n\n";

	if ( $errors !== array() ) {
		echo "❌ ERRORS FOUND:\n";
		foreach ( $errors as $error ) {
			echo "  - $error\n";
		}
		echo "\n";
	}

	if ( $warnings !== array() ) {
		echo "⚠️  WARNINGS:\n";
		foreach ( $warnings as $warning ) {
			echo "  - $warning\n";
		}
		echo "\n";
	}

	if ( $errors === array() && $warnings === array() ) {
		echo "✅ All validations passed! Project is ready to deploy.\n";
		exit( 0 );
	}
	if ( $errors === array() ) {
		echo "⚠️  Project has warnings but no errors. Review warnings before deploying.\n";
		exit( 0 );
	}

	echo "❌ Validation failed! Please fix errors before deploying.\n";
	exit( 1 );
}
