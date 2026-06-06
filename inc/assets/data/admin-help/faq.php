<?php
/**
 * Admin help FAQ items.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_help_faq_items(): array {
	return array(
		array(
			'q' => __( 'Var börjar jag?', 'museum-railway-timetable' ),
			'a' => __(
				'Följ ordningen stationer → rutter → tidtabell → turer → stopptider. På mobil räcker det ofta att ändra avvikelser och avgångstider via Översikt eller tidtabellseditorn.',
				'museum-railway-timetable'
			),
		),
		array(
			'q' => __( 'Vad betyder varningarna på översikten?', 'museum-railway-timetable' ),
			'a' => __(
				'De är dataproblem (t.ex. tidtabell utan datum, tur utan stopptider, tom prismatris eller station utan priszon). Klicka på varningen för att gå till rätt sida och åtgärda.',
				'museum-railway-timetable'
			),
		),
		array(
			'q' => __( 'Hur ställer jag in trafik för idag?', 'museum-railway-timetable' ),
			'a' => __(
				'På Översikt: Inställ trafik idag sätter meddelandet «Inställd» på alla dagens turer. Du kan också ändra en enskild avgångstid eller avvikelse i tidtabellseditorn.',
				'museum-railway-timetable'
			),
		),
		array(
			'q' => __( 'Kan jag radera stationer och rutter?', 'museum-railway-timetable' ),
			'a' => __(
				'Ja, om de inte används. En station som ingår i en rutt eller har stopptider kan inte raderas förrän kopplingen tagits bort. Rutter med turer kan inte raderas.',
				'museum-railway-timetable'
			),
		),
		array(
			'q' => __( 'Vad är skillnaden mellan admin och redaktör?', 'museum-railway-timetable' ),
			'a' => __(
				'Du har full behörighet: grunddata, priser, import och inställningar.',
				'museum-railway-timetable'
			),
			'aEditor' => __(
				'Som redaktör kan du läsa allt och ändra avvikelser samt snabb avgångstid. Grunddata (stationer, tidtabeller, priser m.m.) kräver administratör.',
				'museum-railway-timetable'
			),
		),
		array(
			'q' => __( 'Hur importerar jag data?', 'museum-railway-timetable' ),
			'a' => __(
				'Gå till Import / export — sidan beskriver steg för steg hur du bygger zip-paketet. CSV zip kan slås samman med befintlig data eller ersätta den. I utvecklingsläge finns även Lennakatten-demo under Utvecklingsverktyg.',
				'museum-railway-timetable'
			),
			'aEditor' => __(
				'Import och export kräver administratörsbehörighet. Be en administratör importera eller exportera åt dig.',
				'museum-railway-timetable'
			),
		),
		array(
			'q' => __( 'Var syns tidtabellen på webbplatsen?', 'museum-railway-timetable' ),
			'a' => __(
				'Via shortcodes på WordPress-sidor — se fliken Shortcodes i menyn. I utvecklingsläge kan du synka färdiga sidor under Utvecklingsverktyg.',
				'museum-railway-timetable'
			),
		),
		array(
			'q' => __( 'Fungerar admin på mobil?', 'museum-railway-timetable' ),
			'a' => __(
				'Ja för drift: inställ trafik, avvikelser och snabb avgångstid. Full redigering (datum, turer, stopptidsrutnät) görs enklast på desktop.',
				'museum-railway-timetable'
			),
		),
		array(
			'q' => __( 'Vad gör tidtabellseditorns flikar?', 'museum-railway-timetable' ),
			'a' => __(
				'Trafikdagar: vilka datum tidtabellen gäller. Turer: lägg till eller ta bort avgångar. Stopptider: tider per hållplats. Avvikelser: ändra tågtyp eller meddelande för ett visst datum. Förhandsvisning: hur det ser ut för besökare.',
				'museum-railway-timetable'
			),
		),
		array(
			'q' => __( 'Hur fungerar priser och inställningar?', 'museum-railway-timetable' ),
			'a' => __(
				'Priser styr reseplanerarens prissammanfattning (matris + stationzoner). Inställningar styr plugin, byten och eftermiddagsgräns; returpris per kategori fylls i under Priser. Båda kan exporteras med CSV. Se avsnittet Priszoner och biljetter på denna sida.',
				'museum-railway-timetable'
			),
			'aEditor' => __( 'Priser och inställningar kräver administratörsbehörighet.', 'museum-railway-timetable' ),
		),
		array(
			'q' => __( 'Kan jag radera en hel tidtabell?', 'museum-railway-timetable' ),
			'a' => __(
				'Ja — i tidtabellslistan eller editorn. Alla turer och stopptider i tidtabellen tas bort. Publicerade WordPress-sidor med shortcode påverkas inte automatiskt; uppdatera eller ta bort sidor manuellt.',
				'museum-railway-timetable'
			),
		),
	);
}
