<?php
/**
 * Admin Vue l10n: editor trips
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
function MRT_admin_vue_l10n_editor_trips(): array {
	return array(
		'editorColRoute'          => __( 'Rutt', 'museum-railway-timetable' ),
		'editorColTrainType'      => __( 'Tågtyp', 'museum-railway-timetable' ),
		'editorColDestination'    => __( 'Destination', 'museum-railway-timetable' ),
		'editorStopptimes'        => __( 'Stopptider', 'museum-railway-timetable' ),
		'editorAddTrip'           => __( 'Lägg till tur', 'museum-railway-timetable' ),
		'editorEditTrip'          => __( 'Redigera', 'museum-railway-timetable' ),
		'editorEditTripTitle'     => __( 'Redigera tur', 'museum-railway-timetable' ),
		'editorColServiceNumber'  => __( 'Tågnummer', 'museum-railway-timetable' ),
		'editorServiceNumberHint' => __(
			'Lämna tomt för att använda tur-ID som nummer.',
			'museum-railway-timetable'
		),
		'editorSaveTrip'          => __( 'Spara tur', 'museum-railway-timetable' ),
		'editorSavedTrip'         => __( 'Tur sparad', 'museum-railway-timetable' ),
		'editorCancelEdit'        => __( 'Avbryt', 'museum-railway-timetable' ),
		'editorHighlightSummary'  => __( 'Special markering i tidtabell', 'museum-railway-timetable' ),
		'editorHighlightLabel'    => __( 'Etikett', 'museum-railway-timetable' ),
		'editorHighlightColor'    => __( 'Färg', 'museum-railway-timetable' ),
		'editorHighlightNote'     => __( 'Förklaring (utskrift)', 'museum-railway-timetable' ),
		'editorHighlightHint'     => __(
			'Tom etikett tar bort markeringen. Färg visas som vertikal rand i översikten.',
			'museum-railway-timetable'
		),
		'editorRoutePrompt'       => __( '— Rutt —', 'museum-railway-timetable' ),
		'editorTrainTypePrompt'   => __( '— Tågtyp —', 'museum-railway-timetable' ),
		'editorDestinationPrompt' => __( '— Destination —', 'museum-railway-timetable' ),
		'editorStandardTrainType' => __( '— Standard —', 'museum-railway-timetable' ),
	);
}
