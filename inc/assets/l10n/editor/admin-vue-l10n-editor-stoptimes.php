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
			'Välj tur i listan och redigera stopptider i tabellen. Samma tider kan också ändras i matrisvyn nedan.',
			'museum-railway-timetable'
		),
		'editorStoptimesGridSummary' => __( 'Matrisvy — redigera alla turer', 'museum-railway-timetable' ),
		'editorStoptimesGridHint'    => __(
			'Klicka på en tid i tidtabellsgriden för att redigera stopptiden för den turen.',
			'museum-railway-timetable'
		),
		'editorStoptimesPaLegend'    => __(
			'På = påstigning tillåten, Av = avstigning tillåten (gäller reseplaneraren).',
			'museum-railway-timetable'
		),
		'editorStoptimesTripLabel'   => __( 'Tur:', 'museum-railway-timetable' ),
		'editorSelectTrip'           => __( '— Välj tur —', 'museum-railway-timetable' ),
	);
}
