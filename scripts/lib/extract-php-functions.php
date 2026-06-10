<?php
/**
 * @return string
 */
function mrt_extract_php_function( string $src, string $name ): string {
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
