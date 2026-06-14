<?php
/**
 * UL-style disruption feed enrichment (summary, validity, panels).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/train-type/icons.php';

/**
 * @param array<string, mixed> $item
 * @return array<string, mixed>
 */
function MRT_disruption_feed_enrich_item( array $item, string $reference_date ): array {
	$source = (string) ( $item['source'] ?? '' );
	$kind   = (string) ( $item['kind'] ?? 'info' );
	$from   = (string) ( $item['date_from'] ?? '' );
	$to     = (string) ( $item['date_to'] ?? '' );

	if ( $source === 'general' ) {
		$summary  = MRT_disruption_feed_general_summary( (string) ( $item['body'] ?? '' ) );
		$category = MRT_disruption_feed_general_category();
	} else {
		$summary  = MRT_disruption_feed_deviation_summary( $item );
		$category = MRT_disruption_feed_deviation_category( $item );
	}

	$item['summary']         = $summary;
	$item['validity_label']  = MRT_disruption_feed_validity_label( $from, $to, $reference_date );
	$item['line_label']      = MRT_disruption_feed_line_label( $item );
	$item['severity']        = MRT_disruption_feed_item_severity( $kind );
	$item['category_key']    = $category['key'];
	$item['category_label']  = $category['label'];
	$item['icon_key']        = $category['icon_key'];
	$item['headline']        = $summary;

	return $item;
}

function MRT_disruption_feed_general_summary( string $text ): string {
	if ( $text === '' ) {
		return __( 'Trafikmeddelande', 'museum-railway-timetable' );
	}
	$first = preg_split( '/\R/u', $text )[0] ?? $text;
	$first = trim( (string) $first );
	if ( mb_strlen( $first ) > 120 ) {
		$first = mb_substr( $first, 0, 117 ) . '…';
	}
	return $first;
}

/**
 * @param array<string, mixed> $item
 */
function MRT_disruption_feed_deviation_summary( array $item ): string {
	$notice = trim( (string) ( $item['body'] ?? '' ) );
	$kind   = (string) ( $item['kind'] ?? '' );
	$cancel = $kind === 'cancelled';
	return MRT_disruption_feed_deviation_event_label( $notice, $cancel );
}

/**
 * @param array<string, mixed> $item
 * @return array{key: string, label: string, icon_key: string}
 */
function MRT_disruption_feed_general_category(): array {
	return array(
		'key'       => 'general',
		'label'     => __( 'Information', 'museum-railway-timetable' ),
		'icon_key'  => 'diesel',
	);
}

/**
 * @param array<string, mixed> $item
 * @return array{key: string, label: string, icon_key: string}
 */
function MRT_disruption_feed_deviation_category( array $item ): array {
	$train_type_id = (int) ( $item['train_type_id'] ?? 0 );
	$icon_key      = MRT_disruption_feed_icon_key_for_train_type( $train_type_id );
	if ( $icon_key === 'bus' ) {
		return array(
			'key'       => 'bus',
			'label'     => __( 'Buss', 'museum-railway-timetable' ),
			'icon_key'  => 'bus',
		);
	}
	return array(
		'key'       => 'train',
		'label'     => __( 'Tåg', 'museum-railway-timetable' ),
		'icon_key'  => $icon_key !== '' ? $icon_key : 'diesel',
	);
}

function MRT_disruption_feed_icon_key_for_train_type( int $train_type_id ): string {
	if ( $train_type_id <= 0 ) {
		return 'diesel';
	}
	$term = get_term( $train_type_id, 'mrt_train_type' );
	if ( ! $term instanceof WP_Term ) {
		return 'diesel';
	}
	return MRT_get_train_type_symbol_key( $term );
}

/**
 * @param array<string, mixed> $item
 */
function MRT_disruption_feed_line_label( array $item ): string {
	$numbers = $item['train_numbers'] ?? array();
	if ( ! is_array( $numbers ) || count( $numbers ) !== 1 ) {
		return '';
	}
	return trim( (string) $numbers[0] );
}

function MRT_disruption_feed_item_severity( string $kind ): string {
	return $kind === 'info' ? 'info' : 'warning';
}

function MRT_disruption_feed_validity_label( string $from, string $to, string $reference_date ): string {
	$from_label = MRT_disruption_feed_swedish_date_label( $from, $reference_date );
	if ( $from === '' ) {
		return '';
	}
	if ( $from === $to ) {
		return sprintf(
			/* translators: %s: date label e.g. Idag or 14 juni */
			__( 'Gäller %s', 'museum-railway-timetable' ),
			$from_label
		);
	}
	$to_label = MRT_disruption_feed_swedish_date_label( $to, $reference_date );
	return sprintf(
		/* translators: 1: start date label, 2: end date label */
		__( 'Gäller %1$s – %2$s', 'museum-railway-timetable' ),
		$from_label,
		$to_label
	);
}

function MRT_disruption_feed_swedish_date_label( string $date_ymd, string $reference_date ): string {
	if ( $date_ymd === $reference_date ) {
		return __( 'Idag', 'museum-railway-timetable' );
	}
	$tomorrow = gmdate( 'Y-m-d', strtotime( $reference_date . ' +1 day' ) );
	if ( $date_ymd === $tomorrow ) {
		return __( 'Imorgon', 'museum-railway-timetable' );
	}
	$ts = strtotime( $date_ymd );
	if ( $ts === false ) {
		return $date_ymd;
	}
	return wp_date( 'j F', $ts );
}

/**
 * @param list<array<string, mixed>> $ongoing
 * @param list<array<string, mixed>> $upcoming
 * @return list<array<string, mixed>>
 */
function MRT_disruption_feed_build_panels( array $ongoing, array $upcoming ): array {
	$panels = array();
	if ( $ongoing !== array() ) {
		$panels[] = MRT_disruption_feed_build_panel( 'ongoing', $ongoing );
	}
	if ( $upcoming !== array() ) {
		$panels[] = MRT_disruption_feed_build_panel( 'upcoming', $upcoming );
	}
	return $panels;
}

/**
 * @param list<array<string, mixed>> $items
 * @return array<string, mixed>
 */
function MRT_disruption_feed_build_panel( string $key, array $items ): array {
	$is_ongoing = $key === 'ongoing';
	return array(
		'key'        => $key,
		'title'      => $is_ongoing
			? __( 'Aktuellt trafikläge', 'museum-railway-timetable' )
			: __( 'Planerade avvikelser', 'museum-railway-timetable' ),
		'icon'       => $is_ongoing ? 'clock' : 'calendar',
		'categories' => MRT_disruption_feed_build_categories( $items ),
	);
}

/**
 * @param list<array<string, mixed>> $items
 * @return list<array<string, mixed>>
 */
function MRT_disruption_feed_build_categories( array $items ): array {
	$order = array( 'train', 'bus', 'general' );
	$groups = array();
	foreach ( $items as $item ) {
		$key = (string) ( $item['category_key'] ?? 'general' );
		if ( ! isset( $groups[ $key ] ) ) {
			$groups[ $key ] = array();
		}
		$groups[ $key ][] = $item;
	}
	$categories = array();
	foreach ( $order as $key ) {
		if ( ! isset( $groups[ $key ] ) || $groups[ $key ] === array() ) {
			continue;
		}
		$bucket = $groups[ $key ];
		$categories[] = MRT_disruption_feed_build_category( $key, $bucket );
	}
	return $categories;
}

/**
 * @param list<array<string, mixed>> $items
 * @return array<string, mixed>
 */
function MRT_disruption_feed_build_category( string $key, array $items ): array {
	$first = $items[0];
	return array(
		'key'       => $key,
		'label'     => (string) ( $first['category_label'] ?? $key ),
		'icon_key'  => (string) ( $first['icon_key'] ?? 'diesel' ),
		'counts'    => MRT_disruption_feed_category_counts( $items ),
		'items'     => $items,
	);
}

/**
 * @param list<array<string, mixed>> $items
 * @return array{info: int, warning: int}
 */
function MRT_disruption_feed_category_counts( array $items ): array {
	$info    = 0;
	$warning = 0;
	foreach ( $items as $item ) {
		$severity = (string) ( $item['severity'] ?? 'info' );
		if ( $severity === 'warning' ) {
			++$warning;
		} else {
			++$info;
		}
	}
	return array(
		'info'    => $info,
		'warning' => $warning,
	);
}
