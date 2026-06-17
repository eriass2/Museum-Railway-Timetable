<?php
/**
 * Wizard ticket copy — footnotes (prices admin) and station purchase info.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post meta key for station-specific ticket purchase copy.
 */
function MRT_station_ticket_purchase_meta_key(): string {
	return 'mrt_ticket_purchase_info';
}

/**
 * Allowed footnote conditions.
 *
 * @return array<int, string>
 */
function MRT_ticket_copy_note_conditions(): array {
	return array( 'always', 'afternoon', 'day_ticket' );
}

/**
 * Default public footnotes (Jesper J15).
 *
 * @return list<array{id: string, condition: string, text: string, enabled: bool}>
 */
function MRT_default_ticket_copy_notes(): array {
	return array(
		array(
			'id'        => 'student',
			'condition' => 'always',
			'text'      => __(
				'Student ska kunna uppvisa giltig studentlegitimation (Mecenat, WeStudents eller ISIC-kortet).',
				'museum-railway-timetable'
			),
			'enabled'   => true,
		),
		array(
			'id'        => 'season',
			'condition' => 'always',
			'text'      => __( 'Biljetterna gäller hela trafiksäsongen.', 'museum-railway-timetable' ),
			'enabled'   => true,
		),
		array(
			'id'        => 'day_ticket',
			'condition' => 'day_ticket',
			'text'      => __(
				'Heldagsbiljett gäller för obegränsat resande på alla Lennakattens tåg och bussar under en hel dag.',
				'museum-railway-timetable'
			),
			'enabled'   => true,
		),
		array(
			'id'        => 'afternoon_reminder',
			'condition' => 'afternoon',
			'text'      => __(
				'Tänk dock på att eftermiddagsbiljett endast gäller vid resa efter kl %1$s.',
				'museum-railway-timetable'
			),
			'enabled'   => true,
		),
	);
}

/**
 * @return list<array{id: string, condition: string, text: string, enabled: bool}>
 */
function MRT_get_ticket_copy_notes(): array {
	$opts  = MRT_get_plugin_settings();
	$raw   = $opts['ticket_copy_notes'] ?? null;
	$notes = is_array( $raw ) && $raw !== array() ? $raw : MRT_default_ticket_copy_notes();
	return MRT_sanitize_ticket_copy_notes( $notes );
}

/**
 * @param mixed $notes Raw notes.
 * @return list<array{id: string, condition: string, text: string, enabled: bool}>
 */
function MRT_sanitize_ticket_copy_notes( $notes ): array {
	if ( ! is_array( $notes ) ) {
		return MRT_default_ticket_copy_notes();
	}
	$allowed = MRT_ticket_copy_note_conditions();
	$out     = array();
	foreach ( $notes as $index => $note ) {
		if ( ! is_array( $note ) ) {
			continue;
		}
		$text = trim( (string) ( $note['text'] ?? '' ) );
		if ( $text === '' ) {
			continue;
		}
		$condition = (string) ( $note['condition'] ?? 'always' );
		if ( ! in_array( $condition, $allowed, true ) ) {
			$condition = 'always';
		}
		$id = sanitize_key( (string) ( $note['id'] ?? 'note_' . $index ) );
		if ( $id === '' ) {
			$id = 'note_' . $index;
		}
		$out[] = array(
			'id'        => $id,
			'condition' => $condition,
			'text'      => $text,
			'enabled'   => ! empty( $note['enabled'] ),
		);
	}
	return $out !== array() ? $out : MRT_default_ticket_copy_notes();
}

/**
 * Built-in purchase copy when station meta is empty (Lennakatten defaults).
 */
function MRT_default_station_ticket_purchase_by_code( string $station_code ): string {
	$map = array(
		'uppsala-ostra' => __(
			'Din resa börjar på Uppsala Östra. Där köper du din biljett i biljettluckan på stationen före avgång (kort/kontant).',
			'museum-railway-timetable'
		),
		'marielund'     => __(
			'Din resa börjar i Marielund. Där köper du din biljett i Marielunds jernvägscafé (kort/kontant/swish) före avgång eller av konduktören ombord på tåget (kontant/swish OBS! ej kort).',
			'museum-railway-timetable'
		),
		'almunge'       => __(
			'Din resa börjar i Almunge. Där köper du din biljett i Almunge jernvägscafé (kort/kontant/swish) före avgång eller av konduktören ombord på tåget (kontant/swish OBS! ej kort).',
			'museum-railway-timetable'
		),
	);
	if ( isset( $map[ $station_code ] ) ) {
		return $map[ $station_code ];
	}
	return __(
		'Du börjar din resa på en station eller hållplats som saknar biljettförsäljning. Köp din biljett av konduktören ombord på tåget (kontant/swish OBS! ej kort).',
		'museum-railway-timetable'
	);
}

/**
 * Purchase info for one station (custom meta or code default).
 */
function MRT_get_station_ticket_purchase_info( int $station_id ): string {
	if ( $station_id <= 0 ) {
		return '';
	}
	$custom = trim( (string) get_post_meta( $station_id, MRT_station_ticket_purchase_meta_key(), true ) );
	if ( $custom !== '' ) {
		return $custom;
	}
	$code = trim( (string) get_post_meta( $station_id, 'mrt_station_code', true ) );
	if ( $code === '' ) {
		return '';
	}
	return MRT_default_station_ticket_purchase_by_code( $code );
}

/**
 * @return array<int, string> Station post ID => purchase copy.
 */
function MRT_ticket_purchase_info_map(): array {
	$out   = array();
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_STATION,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		)
	);
	foreach ( $posts as $station_id ) {
		$id = (int) $station_id;
		$text = MRT_get_station_ticket_purchase_info( $id );
		if ( $text !== '' ) {
			$out[ $id ] = $text;
		}
	}
	return $out;
}

/**
 * Resolve footnotes for wizard summary context.
 *
 * @param array{is_afternoon?: bool, has_day_ticket?: bool, afternoon_clock?: string} $context
 * @return list<string>
 */
function MRT_resolve_ticket_copy_footnotes( array $context ): array {
	$is_afternoon    = ! empty( $context['is_afternoon'] );
	$has_day_ticket  = ! empty( $context['has_day_ticket'] );
	$afternoon_clock = (string) ( $context['afternoon_clock'] ?? '' );
	$out             = array();
	foreach ( MRT_get_ticket_copy_notes() as $note ) {
		if ( empty( $note['enabled'] ) ) {
			continue;
		}
		if ( ! MRT_ticket_copy_note_matches_context( (string) $note['condition'], $is_afternoon, $has_day_ticket ) ) {
			continue;
		}
		$text = (string) $note['text'];
		if ( str_contains( $text, '%1$s' ) && $afternoon_clock !== '' ) {
			$text = sprintf( $text, $afternoon_clock );
		}
		$out[] = $text;
	}
	return $out;
}

function MRT_ticket_copy_note_matches_context(
	string $condition,
	bool $is_afternoon,
	bool $has_day_ticket
): bool {
	if ( $condition === 'always' ) {
		return true;
	}
	if ( $condition === 'afternoon' ) {
		return $is_afternoon;
	}
	if ( $condition === 'day_ticket' ) {
		return $has_day_ticket;
	}
	return false;
}

/**
 * Persist ticket copy notes into mrt_settings.
 *
 * @param mixed $notes Raw notes from REST.
 */
function MRT_save_ticket_copy_notes( $notes ): void {
	$settings                      = MRT_get_plugin_settings();
	$settings['ticket_copy_notes'] = MRT_sanitize_ticket_copy_notes( $notes );
	update_option( 'mrt_settings', MRT_sanitize_plugin_settings( $settings ) );
}
