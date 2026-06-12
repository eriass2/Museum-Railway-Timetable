<?php
$root = dirname( __DIR__, 2 );
$src  = shell_exec( 'git -C ' . escapeshellarg( $root ) . ' show HEAD:inc/domain/journey/engine/search.php' );
if ( ! is_string( $src ) || $src === '' ) {
	fwrite( STDERR, "Could not read search.php from git HEAD\n" );
	exit( 1 );
}

$dir = $root . '/inc/domain/journey/engine/';

$groups = array(
	'search-results.php' => array(
		'MRT_journey_engine_build_result',
		'MRT_journey_engine_dedupe_key',
		'MRT_journey_engine_compare_results',
		'MRT_journey_engine_append_result',
	),
	'search-bfs.php' => array(
		'MRT_journey_engine_extend_state',
		'MRT_journey_engine_apply_edge',
		'MRT_journey_engine_find_with_transfers',
		'MRT_journey_engine_apply_first_edge',
	),
	'search-find.php' => array(
		'MRT_journey_engine_find_direct',
		'MRT_journey_engine_find',
		'MRT_find_multi_leg_connections',
		'MRT_journey_engine_has_connection',
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
 * Journey search engine: %s
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
	$label = str_replace( array( 'search-', '.php' ), '', $file );
	file_put_contents( $dir . $file, sprintf( $header, $label ) . implode( "\n\n", $parts ) . "\n" );
	echo "wrote $file\n";
}

$loader = <<<'PHP'
<?php
/**
 * Journey search engine (BFS, configurable max transfers).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/journey/engine/constraints.php';
require_once MRT_PATH . 'inc/domain/journey/engine/graph.php';
require_once __DIR__ . '/search-results.php';
require_once __DIR__ . '/search-bfs.php';
require_once __DIR__ . '/search-find.php';

/**
 * Build one leg payload from a graph edge.
 *
 * @param array{service_id: int, to_station_id: int, departure: string, arrival: string, connection: array<string, mixed>} $edge
 * @return array<string, mixed>
 */
function MRT_journey_engine_leg_from_edge(
	array $edge,
	int $from_station_id,
	string $dateYmd
): array {
	$leg = MRT_journey_build_leg_segment(
		(int) $edge['service_id'],
		$from_station_id,
		(int) $edge['to_station_id'],
		$dateYmd
	);
	if ( $leg !== null ) {
		return $leg;
	}
	return MRT_journey_leg_from_connection_row(
		$edge['connection'],
		$dateYmd,
		$from_station_id,
		(int) $edge['to_station_id']
	);
}

PHP;

file_put_contents( $dir . 'search.php', $loader );
echo "wrote search.php loader\n";
