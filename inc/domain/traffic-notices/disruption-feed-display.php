<?php
/**
 * Disruption feed presentation helpers (headlines, intro, detail sections).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Body text to show under headline (omit text already covered by the headline).
 *
 * @param array<string, mixed> $item Feed item.
 */
function MRT_disruption_feed_item_body_display( array $item ): string {
	$intro = trim( (string) ( $item['detail_intro'] ?? '' ) );
	if ( $intro !== '' ) {
		return $intro;
	}
	$body     = trim( (string) ( $item['body'] ?? '' ) );
	$headline = trim( (string) ( $item['headline'] ?? '' ) );
	if ( $body === '' || $body === $headline ) {
		return '';
	}
	$source = (string) ( $item['source'] ?? '' );
	if ( $source === 'deviation' && stripos( $headline, $body ) !== false ) {
		return '';
	}
	if ( $source === 'general' ) {
		$lines      = preg_split( '/\R/u', $body );
		$first_line = trim( (string) ( $lines[0] ?? '' ) );
		if ( $first_line === $headline ) {
			array_shift( $lines );
			return trim( implode( "\n", $lines ) );
		}
	}
	return $body;
}

function MRT_disruption_feed_notice_headline(
	string $text,
	string $date_from,
	string $date_to,
	string $reference_date
): string {
	if ( $text === '' ) {
		return __( 'Trafikmeddelande', 'museum-railway-timetable' );
	}
	$first = preg_split( '/\R/u', $text )[0] ?? $text;
	$first = trim( (string) $first );
	if ( mb_strlen( $first ) > 120 ) {
		$first = mb_substr( $first, 0, 117 ) . '…';
	}
	if ( $date_from !== $date_to && ! MRT_disruption_feed_text_contains_range( $first, $date_from, $date_to ) ) {
		$first .= ' — ' . MRT_disruption_feed_range_label( $date_from, $date_to, $reference_date );
	}
	return $first;
}

function MRT_disruption_feed_text_contains_range( string $text, string $from, string $to ): bool {
	return str_contains( $text, $from ) || str_contains( $text, $to );
}

/**
 * @param list<array<string, mixed>> $group
 */
function MRT_disruption_feed_deviation_headline_from_group( array $group, string $reference_date ): string {
	$notice  = trim( (string) ( $group[0]['notice'] ?? '' ) );
	$cancel  = ! empty( $group[0]['is_cancelled'] ) || MRT_notice_indicates_cancelled( $notice );
	$numbers = MRT_disruption_feed_unique_train_numbers( $group );
	$route   = MRT_disruption_feed_primary_route_label( $group );
	$event   = MRT_disruption_feed_deviation_event_label( $notice, $cancel );
	$parts   = array( $event );
	if ( $route !== '' ) {
		$parts[] = $route;
	}
	$trains = implode( ', ', $numbers );
	if ( $trains !== '' ) {
		$parts[] = sprintf(
			/* translators: %s: comma-separated train numbers */
			__( 'Tåg %s', 'museum-railway-timetable' ),
			$trains
		);
	}
	return implode( ' — ', $parts );
}

function MRT_disruption_feed_deviation_event_label( string $notice, bool $cancelled ): string {
	if ( $cancelled && ( $notice === '' || MRT_notice_indicates_cancelled( $notice ) ) ) {
		return __( 'Inställd trafik', 'museum-railway-timetable' );
	}
	if ( $notice !== '' ) {
		return $notice;
	}
	return __( 'Tur-avvikelse', 'museum-railway-timetable' );
}

/**
 * @param list<array<string, mixed>> $group
 */
function MRT_disruption_feed_primary_route_label( array $group ): string {
	$routes = MRT_disruption_feed_route_labels_from_group( $group );
	if ( count( $routes ) !== 1 ) {
		return '';
	}
	return $routes[0];
}

/**
 * @param list<array<string, mixed>> $group
 * @return list<string>
 */
function MRT_disruption_feed_route_labels_from_group( array $group ): array {
	$routes = array();
	foreach ( $group as $row ) {
		$route = trim( (string) ( $row['route_label'] ?? '' ) );
		if ( $route !== '' ) {
			$routes[] = $route;
		}
	}
	$routes = array_values( array_unique( $routes ) );
	sort( $routes, SORT_NATURAL );
	return $routes;
}

/**
 * @param list<array<string, mixed>> $group
 */
function MRT_disruption_feed_deviation_detail_intro( array $group, string $headline ): string {
	$notice = trim( (string) ( $group[0]['notice'] ?? '' ) );
	if ( $notice !== '' && ! MRT_disruption_feed_notice_in_headline( $notice, $headline ) ) {
		return $notice;
	}
	$cancel = ! empty( $group[0]['is_cancelled'] ) || MRT_notice_indicates_cancelled( $notice );
	if ( $cancel ) {
		return __( 'Tågen trafikerar inte enligt ordinarie tidtabell denna dag.', 'museum-railway-timetable' );
	}
	$routes = MRT_disruption_feed_route_labels_from_group( $group );
	if ( count( $routes ) > 1 ) {
		return __( 'Avvikelsen berör flera sträckor.', 'museum-railway-timetable' );
	}
	return '';
}

function MRT_disruption_feed_notice_in_headline( string $notice, string $headline ): bool {
	return stripos( $headline, $notice ) !== false;
}

/**
 * @param list<array<string, mixed>> $group
 * @return list<array{title: string, lines: list<string>}>
 */
function MRT_disruption_feed_deviation_detail_sections( array $group ): array {
	$by_route = array();
	foreach ( $group as $row ) {
		$route = trim( (string) ( $row['route_label'] ?? '' ) );
		if ( $route === '' ) {
			$route = __( 'Berörda turer', 'museum-railway-timetable' );
		}
		$line = trim( (string) ( $row['trip_label'] ?? '' ) );
		if ( $line === '' ) {
			$number = trim( (string) ( $row['service_number'] ?? '' ) );
			$line   = $number !== '' ? sprintf(
				/* translators: %s: train number */
				__( 'Tåg %s', 'museum-railway-timetable' ),
				$number
			) : '';
		}
		if ( $line === '' ) {
			continue;
		}
		if ( ! isset( $by_route[ $route ] ) ) {
			$by_route[ $route ] = array();
		}
		$by_route[ $route ][] = $line;
	}
	return MRT_disruption_feed_sections_from_map( $by_route );
}

/**
 * @param array<string, list<string>> $by_title
 * @return list<array{title: string, lines: list<string>}>
 */
function MRT_disruption_feed_sections_from_map( array $by_title ): array {
	$sections = array();
	foreach ( $by_title as $title => $lines ) {
		$lines = array_values( array_unique( $lines ) );
		sort( $lines, SORT_NATURAL );
		if ( $lines === array() ) {
			continue;
		}
		$sections[] = array(
			'title' => (string) $title,
			'lines' => $lines,
		);
	}
	return $sections;
}

function MRT_disruption_feed_general_detail_intro( string $text, string $headline ): string {
	$body = MRT_disruption_feed_item_body_display(
		array(
			'source'   => 'general',
			'headline' => $headline,
			'body'     => $text,
		)
	);
	if ( $body === '' ) {
		return '';
	}
	$paragraphs = preg_split( '/\R\R+/u', $body );
	$first      = trim( (string) ( $paragraphs[0] ?? '' ) );
	return $first;
}

/**
 * @return list<array{title: string, lines: list<string>}>
 */
function MRT_disruption_feed_general_detail_sections( string $text, string $headline ): array {
	$body = MRT_disruption_feed_item_body_display(
		array(
			'source'   => 'general',
			'headline' => $headline,
			'body'     => $text,
		)
	);
	if ( $body === '' ) {
		return array();
	}
	$paragraphs = preg_split( '/\R\R+/u', $body );
	array_shift( $paragraphs );
	$sections = array();
	foreach ( $paragraphs as $paragraph ) {
		$paragraph = trim( (string) $paragraph );
		if ( $paragraph === '' ) {
			continue;
		}
		$title = '';
		$lines = array( $paragraph );
		$parts = preg_split( '/\R/u', $paragraph );
		$first = trim( (string) ( $parts[0] ?? '' ) );
		if ( count( $parts ) > 1 && MRT_disruption_feed_line_looks_like_heading( $first ) ) {
			$title = $first;
			$lines = array_values( array_filter( array_map( 'trim', array_slice( $parts, 1 ) ) ) );
			if ( $lines === array() ) {
				$lines = array( $paragraph );
				$title = '';
			}
		}
		$sections[] = array(
			'title' => $title,
			'lines' => $lines,
		);
	}
	return $sections;
}

function MRT_disruption_feed_line_looks_like_heading( string $line ): bool {
	if ( $line === '' ) {
		return false;
	}
	if ( str_ends_with( $line, ':' ) ) {
		return true;
	}
	return mb_strlen( $line ) <= 48 && ! str_contains( $line, '.' );
}

function MRT_disruption_feed_range_label( string $from, string $to, string $reference_date ): string {
	if ( $from === $to ) {
		return MRT_disruption_feed_compact_date_label( $from, $reference_date );
	}
	return MRT_disruption_feed_compact_date_label( $from, $reference_date ) . ' – ' . MRT_disruption_feed_compact_date_label( $to, $reference_date );
}

function MRT_disruption_feed_compact_date_label( string $date_ymd, string $reference_date ): string {
	if ( $date_ymd === $reference_date ) {
		return __( 'Idag', 'museum-railway-timetable' );
	}
	$tomorrow = gmdate( 'Y-m-d', strtotime( $reference_date . ' +1 day' ) );
	if ( $date_ymd === $tomorrow ) {
		return __( 'Imorgon', 'museum-railway-timetable' );
	}
	return $date_ymd;
}

/**
 * @param array<string, mixed> $item Feed item.
 */
function MRT_disruption_feed_item_has_expandable_content( array $item ): bool {
	$intro = trim( (string) ( $item['detail_intro'] ?? '' ) );
	if ( $intro !== '' ) {
		return true;
	}
	$sections = $item['detail_sections'] ?? array();
	return is_array( $sections ) && $sections !== array();
}
