<?php
/**
 * PHP syntax validation section.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

/**
 * @param array<int, string> $errors
 * @param list<string>       $php_files
 */
function mrt_validate_php_syntax( array &$errors, int &$checks, array $php_files ): void {
	echo "\n2. Checking PHP syntax...\n";
	foreach ( $php_files as $file ) {
		++$checks;
		$output     = array();
		$return_var = 0;
		exec( "php -l \"$file\" 2>&1", $output, $return_var );
		if ( $return_var !== 0 ) {
			$errors[] = "Syntax error in $file: " . implode( "\n", $output );
			echo "  ❌ $file\n";
			echo '     ' . implode( "\n     ", $output ) . "\n";
		} else {
			echo "  ✅ $file\n";
		}
	}
}
