<?php
$root = dirname( __DIR__ );
$src  = shell_exec( 'git -C ' . escapeshellarg( $root ) . ' show HEAD:inc/domain/journey/journey-normalize.php' );
if ( ! is_string( $src ) || $src === '' ) {
	fwrite( STDERR, "Could not read journey-normalize.php from git HEAD\n" );
	exit( 1 );
}

$dir = $root . '/inc/domain/journey/';

$groups = array(
	'journey-normalize-labels.php' => array(
		'MRT_normalize_total_duration_from_legs',
		'MRT_journey_multi_leg_train_type_label',
		'MRT_journey_multi_leg_service_label',
	),
	'journey-normalize-segments.php' => array(
		'MRT_normalize_segments_single_service',
		'MRT_flatten_wrapped_direct_connection',
	),
	'journey-normalize-api.php' => array(
		'MRT_normalize_multi_leg_for_api',
		'MRT_normalize_connection_for_api',
	),
	'journey-normalize-filter.php' => array(
		'MRT_journey_filter_wizard_connections',
		'MRT_journey_filter_transfer_connections',
		'MRT_journey_earliest_departure_hhmm',
		'MRT_journey_transfer_dominated_by_direct',
		'MRT_journey_find_normalized_connections',
	),
);

/**
 * @return string
 */
function mrt_extract_function( string $src, string $name ): string {
	$tokens = token_get_all( $src );
	$count  = count( $tokens );
	for ( $i = 0; $i < $count; $i++ ) {
		$tok = $tokens[ $i ];
		if ( ! is_array( $tok ) || $tok[0] !== T_FUNCTION ) {
			continue;
		}
		$j = $i + 1;
		while ( $j < $count && is_array( $tokens[ $j ] ) && $tokens[ $j ][0] === T_WHITESPACE ) {
			++$j;
		}
		if (
			$j >= $count
			|| ! is_array( $tokens[ $j ] )
			|| $tokens[ $j ][0] !== T_STRING
			|| $tokens[ $j ][1] !== $name
		) {
			continue;
		}
		$start = $i;
		if ( $start > 0 && is_array( $tokens[ $start - 1 ] ) && $tokens[ $start - 1 ][0] === T_DOC_COMMENT ) {
			--$start;
		}
		while ( $j < $count && ( is_array( $tokens[ $j ] ) ? $tokens[ $j ][0] !== '{' : $tokens[ $j ] !== '{' ) ) {
			++$j;
		}
		if ( $j >= $count ) {
			throw new RuntimeException( "Missing body for $name" );
		}
		$depth = 0;
		for ( $k = $j; $k < $count; $k++ ) {
			$piece = $tokens[ $k ];
			if ( $piece === '{' || ( is_array( $piece ) && $piece[0] === '{' ) ) {
				++$depth;
			} elseif ( $piece === '}' || ( is_array( $piece ) && $piece[0] === '}' ) ) {
				--$depth;
				if ( $depth === 0 ) {
					$end = $k;
					$out = '';
					for ( $t = $start; $t <= $end; $t++ ) {
						$out .= is_array( $tokens[ $t ] ) ? $tokens[ $t ][1] : $tokens[ $t ];
					}
					return trim( $out );
				}
			}
		}
		throw new RuntimeException( "Unbalanced braces for $name" );
	}
	throw new RuntimeException( "Missing $name" );
}

$header = <<<'PHP'
<?php
/**
 * Journey normalize: %s
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


PHP;

foreach ( $groups as $file => $names ) {
	$parts = array();
	foreach ( $names as $name ) {
		$parts[] = mrt_extract_function( $src, $name );
	}
	$label = str_replace( array( 'journey-normalize-', '.php' ), '', $file );
	file_put_contents( $dir . $file, sprintf( $header, $label ) . implode( "\n\n", $parts ) . "\n" );
	echo "wrote $file\n";
}

$loader = <<<'PHP'
<?php
/**
 * Normalize journey results for JSON API / frontends.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/journey-normalize-labels.php';
require_once __DIR__ . '/journey-normalize-segments.php';
require_once __DIR__ . '/journey-normalize-api.php';
require_once __DIR__ . '/journey-normalize-filter.php';

PHP;

file_put_contents( $dir . 'journey-normalize.php', $loader );
echo "wrote journey-normalize.php loader\n";
