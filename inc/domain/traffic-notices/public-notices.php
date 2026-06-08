<?php
/**
 * General public traffic notices (option-backed).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MRT_OPTION_PUBLIC_NOTICES     = 'mrt_public_notices';
const MRT_PUBLIC_NOTICE_MAX_LENGTH  = 500;
const MRT_PUBLIC_NOTICE_SORT_STEP     = 10;

/**
 * @return list<array{id: string, text: string, enabled: bool, active_from: string, active_to: string, sort_order: int}>
 */
function MRT_public_notices_get_all(): array {
	$raw = get_option( MRT_OPTION_PUBLIC_NOTICES, array() );
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$out = array();
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$sanitized = MRT_public_notice_sanitize_row( $row, false );
		if ( is_wp_error( $sanitized ) ) {
			continue;
		}
		$out[] = $sanitized;
	}
	usort(
		$out,
		static function ( array $a, array $b ): int {
			$cmp = (int) $a['sort_order'] <=> (int) $b['sort_order'];
			return $cmp !== 0 ? $cmp : strcmp( (string) $a['id'], (string) $b['id'] );
		}
	);
	return $out;
}

/**
 * @param list<array<string, mixed>> $messages Messages from REST/admin.
 * @return list<array{id: string, text: string, enabled: bool, active_from: string, active_to: string, sort_order: int}>|WP_Error
 */
function MRT_public_notices_save_all( array $messages ) {
	$out = array();
	foreach ( $messages as $row ) {
		if ( ! is_array( $row ) ) {
			return new WP_Error( 'mrt_notice_invalid', __( 'Ogiltigt meddelande.', 'museum-railway-timetable' ) );
		}
		$sanitized = MRT_public_notice_sanitize_row( $row, true );
		if ( is_wp_error( $sanitized ) ) {
			return $sanitized;
		}
		$out[] = $sanitized;
	}
	update_option( MRT_OPTION_PUBLIC_NOTICES, $out, false );
	return $out;
}

/**
 * @param array<string, mixed> $row Raw row.
 * @return array{id: string, text: string, enabled: bool, active_from: string, active_to: string, sort_order: int}|WP_Error
 */
function MRT_public_notice_sanitize_row( array $row, bool $strict ) {
	$id = isset( $row['id'] ) ? sanitize_text_field( (string) $row['id'] ) : '';
	if ( $id === '' ) {
		$id = MRT_public_notice_new_id();
	}
	$text = sanitize_textarea_field( (string) ( $row['text'] ?? '' ) );
	if ( $strict && $text === '' ) {
		return new WP_Error( 'mrt_notice_empty', __( 'Meddelandetext saknas.', 'museum-railway-timetable' ) );
	}
	if ( mb_strlen( $text ) > MRT_PUBLIC_NOTICE_MAX_LENGTH ) {
		return new WP_Error(
			'mrt_notice_too_long',
			sprintf(
				/* translators: %d: max characters */
				__( 'Meddelandet får vara högst %d tecken.', 'museum-railway-timetable' ),
				MRT_PUBLIC_NOTICE_MAX_LENGTH
			)
		);
	}
	$enabled     = ! empty( $row['enabled'] );
	$active_from = MRT_public_notice_sanitize_date( (string) ( $row['active_from'] ?? '' ) );
	$active_to   = MRT_public_notice_sanitize_date( (string) ( $row['active_to'] ?? '' ) );
	if ( is_wp_error( $active_from ) ) {
		return $active_from;
	}
	if ( is_wp_error( $active_to ) ) {
		return $active_to;
	}
	if ( $active_from !== '' && $active_to !== '' && strcmp( $active_from, $active_to ) > 0 ) {
		return new WP_Error( 'mrt_notice_dates', __( 'Gäller till kan inte vara före gäller från.', 'museum-railway-timetable' ) );
	}
	$sort_order = isset( $row['sort_order'] ) ? (int) $row['sort_order'] : MRT_public_notice_next_sort_order();

	return array(
		'id'          => $id,
		'text'        => $text,
		'enabled'     => $enabled,
		'active_from' => $active_from,
		'active_to'   => $active_to,
		'sort_order'  => $sort_order,
	);
}

/**
 * @return string|WP_Error Empty string when open-ended.
 */
function MRT_public_notice_sanitize_date( string $value ) {
	$value = trim( $value );
	if ( $value === '' ) {
		return '';
	}
	if ( ! MRT_validate_date( $value ) ) {
		return new WP_Error( 'mrt_notice_date', __( 'Ogiltigt datum.', 'museum-railway-timetable' ) );
	}
	return $value;
}

/**
 * Whether a notice is active on a reference date.
 */
function MRT_public_notice_active_on_date( array $notice, string $date_ymd ): bool {
	if ( empty( $notice['enabled'] ) ) {
		return false;
	}
	$from = (string) ( $notice['active_from'] ?? '' );
	$to   = (string) ( $notice['active_to'] ?? '' );
	if ( $from !== '' && strcmp( $date_ymd, $from ) < 0 ) {
		return false;
	}
	if ( $to !== '' && strcmp( $date_ymd, $to ) > 0 ) {
		return false;
	}
	return trim( (string) ( $notice['text'] ?? '' ) ) !== '';
}

/**
 * @return list<array{id: string, text: string}>
 */
function MRT_public_notices_active_for_date( string $date_ymd, ?array $all = null ): array {
	$all = $all ?? MRT_public_notices_get_all();
	$out = array();
	foreach ( $all as $notice ) {
		if ( ! MRT_public_notice_active_on_date( $notice, $date_ymd ) ) {
			continue;
		}
		$out[] = array(
			'id'   => (string) $notice['id'],
			'text' => (string) $notice['text'],
		);
	}
	return $out;
}

/**
 * @return list<array{id: string, text: string, enabled: bool, active_from: string, active_to: string, sort_order: int}>
 */
function MRT_public_notices_active_in_range( string $start_ymd, string $end_ymd ): array {
	$all = MRT_public_notices_get_all();
	$out = array();
	foreach ( $all as $notice ) {
		for ( $ts = strtotime( $start_ymd ); $ts !== false && $ts <= strtotime( $end_ymd ); $ts += DAY_IN_SECONDS ) {
			$day = gmdate( 'Y-m-d', $ts );
			if ( MRT_public_notice_active_on_date( $notice, $day ) ) {
				$out[] = $notice;
				break;
			}
		}
	}
	return $out;
}

function MRT_public_notice_new_id(): string {
	if ( function_exists( 'wp_generate_uuid4' ) ) {
		return wp_generate_uuid4();
	}
	return 'notice-' . wp_generate_password( 12, false, false );
}

function MRT_public_notice_next_sort_order(): int {
	$max = 0;
	foreach ( MRT_public_notices_get_all() as $row ) {
		$max = max( $max, (int) ( $row['sort_order'] ?? 0 ) );
	}
	return $max + MRT_PUBLIC_NOTICE_SORT_STEP;
}

/**
 * Reorder messages by id list (admin up/down).
 *
 * @param list<string> $ordered_ids Message ids in desired order.
 * @return list<array<string, mixed>>|WP_Error
 */
function MRT_public_notices_reorder( array $ordered_ids ) {
	$by_id = array();
	foreach ( MRT_public_notices_get_all() as $row ) {
		$by_id[ (string) $row['id'] ] = $row;
	}
	if ( count( $ordered_ids ) !== count( $by_id ) ) {
		return new WP_Error( 'mrt_notice_reorder', __( 'Ogiltig ordning.', 'museum-railway-timetable' ) );
	}
	$order = MRT_PUBLIC_NOTICE_SORT_STEP;
	$out   = array();
	foreach ( $ordered_ids as $id ) {
		$key = sanitize_text_field( (string) $id );
		if ( ! isset( $by_id[ $key ] ) ) {
			return new WP_Error( 'mrt_notice_reorder', __( 'Ogiltig ordning.', 'museum-railway-timetable' ) );
		}
		$row                = $by_id[ $key ];
		$row['sort_order']  = $order;
		$out[]              = $row;
		$order             += MRT_PUBLIC_NOTICE_SORT_STEP;
	}
	update_option( MRT_OPTION_PUBLIC_NOTICES, $out, false );
	return $out;
}
