<?php
/**
 * CSV export/import for wizard ticket copy (J15).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int, array<string, string>>
 */
function MRT_csv_export_ticket_copy_notes(): array {
	if ( ! function_exists( 'MRT_get_ticket_copy_notes' ) ) {
		require_once MRT_PATH . 'inc/domain/pricing/ticket-copy.php';
	}
	$rows = array();
	foreach ( MRT_get_ticket_copy_notes() as $note ) {
		$rows[] = array(
			'id'        => (string) ( $note['id'] ?? '' ),
			'condition' => (string) ( $note['condition'] ?? 'always' ),
			'text'      => (string) ( $note['text'] ?? '' ),
			'enabled'   => ! empty( $note['enabled'] ) ? '1' : '0',
		);
	}
	return $rows;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 */
function MRT_csv_import_ticket_copy_notes( array $files ): void {
	if ( ! isset( $files['ticket_copy_notes.csv'] ) ) {
		return;
	}
	if ( ! function_exists( 'MRT_save_ticket_copy_notes' ) ) {
		require_once MRT_PATH . 'inc/domain/pricing/ticket-copy.php';
	}
	$notes = array();
	foreach ( (array) $files['ticket_copy_notes.csv'] as $row ) {
		$text = trim( (string) ( $row['text'] ?? '' ) );
		if ( $text === '' ) {
			continue;
		}
		$notes[] = array(
			'id'        => (string) ( $row['id'] ?? '' ),
			'condition' => (string) ( $row['condition'] ?? 'always' ),
			'text'      => $text,
			'enabled'   => in_array( strtolower( (string) ( $row['enabled'] ?? '1' ) ), array( '1', 'true', 'yes' ), true ),
		);
	}
	if ( $notes === array() ) {
		return;
	}
	MRT_save_ticket_copy_notes( $notes );
}
