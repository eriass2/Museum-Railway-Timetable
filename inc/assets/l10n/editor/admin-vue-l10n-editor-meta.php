<?php
/**
 * Admin Vue l10n: editor meta and tabs
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
function MRT_admin_vue_l10n_editor_meta(): array {
	return array(
		'editorTitle'                => __( 'Tidtabell', 'museum-railway-timetable' ),
		'editorLoading'              => __( 'Laddar tidtabell…', 'museum-railway-timetable' ),
		'editorOverviewLoadFailed'   => __( 'Kunde inte ladda översikt.', 'museum-railway-timetable' ),
		'editorDeviationsLoadFailed' => __( 'Kunde inte ladda avvikelser.', 'museum-railway-timetable' ),
		'editorMetaUnsaved'          => __(
			'Osparade ändringar i titel eller typ — spara innan du lämnar sidan.',
			'museum-railway-timetable'
		),
		'editorTitleLabel'           => __( 'Titel', 'museum-railway-timetable' ),
		'editorTypeLabel'            => __( 'Typ (färg i översikt)', 'museum-railway-timetable' ),
		'editorTypeNone'             => __( '— Ingen färgrubrik —', 'museum-railway-timetable' ),
		'editorTypeGreen'            => __( 'Grön tidtabell', 'museum-railway-timetable' ),
		'editorTypeYellow'           => __( 'Gul tidtabell', 'museum-railway-timetable' ),
		'editorTypeRed'              => __( 'Röd tidtabell', 'museum-railway-timetable' ),
		'editorTypeOrange'           => __( 'Orange tidtabell', 'museum-railway-timetable' ),
		'editorTypeHint'             => __(
			'Typen styr färg på trafikdagar i månadskalendern och reseplaneraren (grön, gul, röd, orange).',
			'museum-railway-timetable'
		),
		'editorSaveMeta'             => __( 'Spara namn och typ', 'museum-railway-timetable' ),
		'editorDeleteTimetable'      => __( 'Ta bort tidtabell', 'museum-railway-timetable' ),
		'editorTabDates'             => __( 'Trafikdagar', 'museum-railway-timetable' ),
		'editorTabGrid'              => __( 'Turvy', 'museum-railway-timetable' ),
		'editorTabTrips'             => __( 'Turer', 'museum-railway-timetable' ),
		'editorTabStoptimes'         => __( 'Stopptider', 'museum-railway-timetable' ),
		'editorTabDeviations'        => __( 'Avvikelser', 'museum-railway-timetable' ),
		'editorTabPreview'           => __( 'Förhandsvisning', 'museum-railway-timetable' ),
		'editorSavedMeta'            => __( 'Namn och typ sparade', 'museum-railway-timetable' ),
	);
}
