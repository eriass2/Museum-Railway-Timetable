<?php
/**
 * Per-departure highlight (label, colour) for timetable overview.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array{label: string, color: string, note: string}|null
 */
function MRT_get_service_highlight( int $service_id ): ?array {
	if ( $service_id <= 0 ) {
		return null;
	}
	$label = trim( (string) get_post_meta( $service_id, 'mrt_service_highlight_label', true ) );
	if ( $label === '' ) {
		return null;
	}
	$color = MRT_sanitize_highlight_color(
		(string) get_post_meta( $service_id, 'mrt_service_highlight_color', true )
	);
	$note = trim( (string) get_post_meta( $service_id, 'mrt_service_highlight_note', true ) );
	return array(
		'label' => $label,
		'color' => $color !== '' ? $color : '#fff9c4',
		'note'  => $note,
	);
}

function MRT_sanitize_highlight_color( string $color ): string {
	$color = trim( $color );
	if ( $color === '' ) {
		return '';
	}
	if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $color ) ) {
		return strtolower( $color );
	}
	return '';
}

/**
 * Save or clear per-departure highlight meta.
 *
 * @param array<string, mixed> $fields highlight_label, highlight_color, highlight_note.
 */
function MRT_apply_service_highlight_fields( int $service_id, array $fields ): void {
	$label = sanitize_text_field( (string) ( $fields['highlight_label'] ?? '' ) );
	if ( $label === '' ) {
		delete_post_meta( $service_id, 'mrt_service_highlight_label' );
		delete_post_meta( $service_id, 'mrt_service_highlight_color' );
		delete_post_meta( $service_id, 'mrt_service_highlight_note' );
		return;
	}
	$color = MRT_sanitize_highlight_color( (string) ( $fields['highlight_color'] ?? '' ) );
	$note  = sanitize_textarea_field( (string) ( $fields['highlight_note'] ?? '' ) );
	update_post_meta( $service_id, 'mrt_service_highlight_label', $label );
	update_post_meta( $service_id, 'mrt_service_highlight_color', $color !== '' ? $color : '#fff9c4' );
	if ( $note !== '' ) {
		update_post_meta( $service_id, 'mrt_service_highlight_note', $note );
	} else {
		delete_post_meta( $service_id, 'mrt_service_highlight_note' );
	}
}

/**
 * @param array<string, string> $row services.csv row
 */
function MRT_csv_update_service_highlight_from_row( int $service_id, array $row ): void {
	MRT_apply_service_highlight_fields( $service_id, $row );
}
