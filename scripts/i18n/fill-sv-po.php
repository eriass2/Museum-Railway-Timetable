<?php
/**
 * Copy msgid into empty msgstr for Swedish catalog.
 * Multiline msgid blocks are filled too (e.g. English dev/QA strings stay as-is).
 *
 * Usage: php scripts/fill-sv-po.php [path-to.po]
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

$path = $argv[1] ?? dirname( __DIR__, 2 ) . '/languages/museum-railway-timetable-sv_SE.po';
if ( ! is_readable( $path ) ) {
	fwrite( STDERR, "Cannot read: {$path}\n" );
	exit( 1 );
}

$raw   = file_get_contents( $path );
$lines = preg_split( '/\r\n|\n|\r/', $raw );
$out   = array();
$i     = 0;
$n     = count( $lines );

while ( $i < $n ) {
	$line = $lines[ $i ];
	if ( $line === 'msgid ""' ) {
		$msgid_lines = array( $line );
		++$i;
		while ( $i < $n && isset( $lines[ $i ][0] ) && $lines[ $i ][0] === '"' ) {
			$msgid_lines[] = $lines[ $i ];
			++$i;
		}
		$msgid_text = po_read_po_string( $msgid_lines, 'msgid' );
		foreach ( $msgid_lines as $l ) {
			$out[] = $l;
		}
		if ( $i < $n && preg_match( '/^msgstr /', $lines[ $i ] ) ) {
			$msgstr_lines = array( $lines[ $i ] );
			++$i;
			while ( $i < $n && isset( $lines[ $i ][0] ) && $lines[ $i ][0] === '"' ) {
				$msgstr_lines[] = $lines[ $i ];
				++$i;
			}
			$msgstr_text = po_read_po_string( $msgstr_lines, 'msgstr' );
			if ( $msgstr_text === '' && $msgid_text !== '' ) {
				$out = array_merge( $out, po_quote_msgstr( $msgid_text ) );
			} else {
				foreach ( $msgstr_lines as $l ) {
					$out[] = $l;
				}
			}
		}
		continue;
	}

	if ( preg_match( '/^msgid /', $line ) ) {
		$msgid_lines = array( $line );
		++$i;
		while ( $i < $n && isset( $lines[ $i ][0] ) && $lines[ $i ][0] === '"' ) {
			$msgid_lines[] = $lines[ $i ];
			++$i;
		}
		$msgid_text = po_read_po_string( $msgid_lines, 'msgid' );
		foreach ( $msgid_lines as $l ) {
			$out[] = $l;
		}
		if ( $i < $n && preg_match( '/^msgstr /', $lines[ $i ] ) ) {
			$msgstr_lines = array( $lines[ $i ] );
			++$i;
			while ( $i < $n && isset( $lines[ $i ][0] ) && $lines[ $i ][0] === '"' ) {
				$msgstr_lines[] = $lines[ $i ];
				++$i;
			}
			$msgstr_text = po_read_po_string( $msgstr_lines, 'msgstr' );
			if ( $msgstr_text === '' && $msgid_text !== '' ) {
				$out = array_merge( $out, po_quote_msgstr( $msgid_text ) );
			} else {
				foreach ( $msgstr_lines as $l ) {
					$out[] = $l;
				}
			}
		}
		continue;
	}

	$out[] = $line;
	++$i;
}

file_put_contents( $path, implode( "\n", $out ) . "\n" );
fwrite( STDOUT, "Filled empty msgstr in {$path}\n" );

/**
 * @param array<int, string> $lines PO lines for one msgid/msgstr block.
 * @param string             $prefix msgid or msgstr.
 */
function po_read_po_string( array $lines, string $prefix ): string {
	$text = '';
	foreach ( $lines as $line ) {
		if ( preg_match( '/^' . preg_quote( $prefix, '/' ) . ' "(.*)"\s*$/', $line, $m ) ) {
			$text .= stripcslashes( $m[1] );
		} elseif ( isset( $line[0] ) && $line[0] === '"' ) {
			$text .= stripcslashes( trim( $line, '"' ) );
		}
	}
	return $text;
}

/**
 * @return array<int, string>
 */
function po_quote_msgstr( string $text ): array {
	if ( strpos( $text, "\n" ) === false ) {
		return array( 'msgstr "' . addcslashes( $text, "\\\"\t" ) . '"' );
	}
	$escaped = addcslashes( $text, "\0\\\"" );
	$lines   = array( 'msgstr ""' );
	foreach ( explode( "\n", $escaped ) as $chunk ) {
		$lines[] = '"' . $chunk . '"';
	}
	return $lines;
}
