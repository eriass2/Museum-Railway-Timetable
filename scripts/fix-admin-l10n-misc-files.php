<?php
$root   = dirname( __DIR__ );
$source = shell_exec( 'git -C ' . escapeshellarg( $root ) . ' show 52e3c6b~1:inc/assets/admin-vue-l10n-misc.php' );
if ( ! is_string( $source ) || $source === '' ) {
	fwrite( STDERR, "Could not read misc.php from git\n" );
	exit( 1 );
}

$out_dir = $root . '/inc/assets/l10n/';
$map     = array(
	'editor'        => 'MRT_admin_vue_l10n_editor',
	'mobile'        => 'MRT_admin_vue_l10n_mobile',
	'stop_times'    => 'MRT_admin_vue_l10n_stop_times',
	'dev'           => 'MRT_admin_vue_l10n_dev',
	'setup'         => 'MRT_admin_vue_l10n_setup',
	'route_preview' => 'MRT_admin_vue_l10n_route_preview',
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
		$depth = 0;
		for ( $k = $j; $k < $count; $k++ ) {
			$piece = $tokens[ $k ];
			if ( $piece === '{' || ( is_array( $piece ) && $piece[0] === '{' ) ) {
				++$depth;
			} elseif ( $piece === '}' || ( is_array( $piece ) && $piece[0] === '}' ) ) {
				--$depth;
				if ( $depth === 0 ) {
					$out = '';
					for ( $t = $start; $t <= $k; $t++ ) {
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
 * Admin Vue l10n: %s
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


PHP;

foreach ( $map as $slug => $name ) {
	$body = mrt_extract_function( $source, $name );
	$desc = str_replace( '_', ' ', $slug );
	file_put_contents( $out_dir . 'admin-vue-l10n-' . $slug . '.php', sprintf( $header, $desc ) . $body . "\n" );
	echo "fixed $slug\n";
}
