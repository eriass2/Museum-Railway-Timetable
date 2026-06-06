<?php
/**
 * Admin Vue l10n: editor stop times tab
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
function MRT_admin_vue_l10n_editor_stoptimes(): array {
	return array(
		'editorStoptimesHint'        => __(
			'Välj tur och redigera stopptider i tabellen nedan. Klicka «Spara stopptider» när du är klar.',
			'museum-railway-timetable'
		),
		'editorStoptimesGridSummary' => __( 'Matrisvy (alla turer)', 'museum-railway-timetable' ),
		'editorStoptimesGridHint'    => __(
			'Klicka på en tid i matrisen för att redigera stopptiden.',
			'museum-railway-timetable'
		),
		'editorStoptimesTripLabel'   => __( 'Tur:', 'museum-railway-timetable' ),
		'editorSelectTrip'           => __( '— Välj tur —', 'museum-railway-timetable' ),
	);
}
