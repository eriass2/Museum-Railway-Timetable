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
			'Välj tur i listan och redigera stopptider i tabellen. För alla turer sida vid sida, använd fliken Turvy.',
			'museum-railway-timetable'
		),
		'editorGridHint'             => __(
			'Redigera ankomst- och avgångstider för alla turer i samma vy. Klicka på en cell för tid, Ca (ungefärlig), stannar och P/A. Fet tid = fast, normal vikt = Ca (som i anslagstidtabellen).',
			'museum-railway-timetable'
		),
		'editorGridEmpty'            => __(
			'Ingen turvy ännu — lägg till turer under fliken Turer och fyll sedan i tider här.',
			'museum-railway-timetable'
		),
		'editorGridEmptyCellHint'    => __(
			'Tom cell utan tid betyder att tåget inte stannar vid stationen.',
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
