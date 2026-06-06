<?php
/**
 * Admin Vue l10n: editor deviations
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
function MRT_admin_vue_l10n_editor_deviations(): array {
	return array(
		'editorDeviationsUnsaved'   => __(
			'Osparade avvikelser — klicka «Spara avvikelser».',
			'museum-railway-timetable'
		),
		'editorDeviationsEmpty'     => __( 'Inga avvikelser ännu. Välj datum och tur nedan.', 'museum-railway-timetable' ),
		'editorAddDeviation'        => __( 'Lägg till avvikelse', 'museum-railway-timetable' ),
		'editorDeviationDatePrompt' => __( '— Datum —', 'museum-railway-timetable' ),
		'editorColDate'             => __( 'Datum', 'museum-railway-timetable' ),
		'editorColTrip'             => __( 'Tur', 'museum-railway-timetable' ),
		'editorColMessage'          => __( 'Meddelande', 'museum-railway-timetable' ),
		'editorDeviationCancelled'  => __( 'Inställt tåg', 'museum-railway-timetable' ),
		'editorSaveDeviations'      => __( 'Spara avvikelser', 'museum-railway-timetable' ),
		'editorSavedDeviations'     => __( 'Avvikelser sparade', 'museum-railway-timetable' ),
	);
}
