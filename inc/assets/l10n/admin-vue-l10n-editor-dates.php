<?php
/**
 * Admin Vue l10n: editor traffic dates
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_editor_dates(): array {
	return array(
		'editorDatesUnsaved' => __(
			'Osparade trafikdagar — klicka «Spara» för att spara listan.',
			'museum-railway-timetable'
		),
		'editorDatesAdd'     => __( 'Lägg till datum', 'museum-railway-timetable' ),
		'editorSavedDates'   => __( 'Trafikdagar sparade', 'museum-railway-timetable' ),
	);
}
