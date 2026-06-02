<?php
/**
 * Structured help content for the Vue admin Help page.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string, mixed>
 */
function MRT_admin_vue_help_content(): array {
	return array(
		'title'              => __( 'Hjälp', 'museum-railway-timetable' ),
		'intro'              => __(
			'Museum Railway Timetable hanterar tidtabellsdata i WordPress och viser den på webbplatsen via shortcodes. I admin skapar du stationer, rutter, tidtabeller och turer. Besökare kan se tidtabeller, kalender och söka resor med reseplaneraren.',
			'museum-railway-timetable'
		),
		'panelWhat'          => __( 'Vad pluginet gör', 'museum-railway-timetable' ),
		'colPart'            => __( 'Del', 'museum-railway-timetable' ),
		'colDescription'     => __( 'Beskrivning', 'museum-railway-timetable' ),
		'partAdmin'          => __( 'Administration', 'museum-railway-timetable' ),
		'partAdminDesc'      => __(
			'Data under menyn Tidtabell — stationer, rutter, tidtabeller, drift och (för administratör) priser och import.',
			'museum-railway-timetable'
		),
		'partPublic'         => __( 'Webbplats', 'museum-railway-timetable' ),
		'partPublicDesc'     => __(
			'Shortcodes på WordPress-sidor: lista, tidtabellsöversikt, månadskalender och reseplanerare.',
			'museum-railway-timetable'
		),
		'panelAdmin'         => __( 'Administration', 'museum-railway-timetable' ),
		'panelAdminHint'     => __( 'Vad varje menyval gör i admin.', 'museum-railway-timetable' ),
		'panelWorkflow'      => __( 'Arbetsflöde', 'museum-railway-timetable' ),
		'panelOperations'    => __( 'Drift och avvikelser', 'museum-railway-timetable' ),
		'panelShortcodes'    => __( 'Shortcodes', 'museum-railway-timetable' ),
		'panelFaq'           => __( 'Vanliga frågor', 'museum-railway-timetable' ),
		'panelMore'          => __( 'Mer information', 'museum-railway-timetable' ),
		'shortcodesIntro'    => __(
			'Shortcodes läggs in i innehållet på en WordPress-sida (block «Anpassad HTML» eller klassisk redigerare). Varje shortcode visar en del av tidtabellen på webbplatsen.',
			'museum-railway-timetable'
		),
		'shortcodesDevHint'  => __(
			'I utvecklingsläge: använd Utvecklingsverktyg → Skapa/uppdatera tidtabellssidor för att skapa en indexsida och en sida per tidtabell med rätt shortcodes.',
			'museum-railway-timetable'
		),
		'shortcodeExample'   => __( 'Exempel:', 'museum-railway-timetable' ),
		'paramName'          => __( 'Parameter', 'museum-railway-timetable' ),
		'operationsNote'     => __(
			'Avvikelser och dagens drift syns i tidtabellsöversikten och i reseplaneraren för berörda datum.',
			'museum-railway-timetable'
		),
		'moreInfoBody'       => __(
			'Full steg-för-steg-guide finns i plugin-dokumentationen: docs/ADMIN_WORKFLOW.md i projektets källkod.',
			'museum-railway-timetable'
		),
		'moreInfoDocs'       => __(
			'Mer detaljer om shortcodes, CSV och utvecklingsverktyg finns i docs/SHORTCODES.md och övriga filer under docs/.',
			'museum-railway-timetable'
		),
		'adminSections'      => MRT_admin_vue_help_admin_sections(),
		'workflowSteps'      => MRT_admin_vue_help_workflow_steps(),
		'operations'         => MRT_admin_vue_help_operations(),
		'shortcodes'         => MRT_admin_vue_help_shortcodes(),
		'faq'                => MRT_admin_vue_help_faq_items(),
	);
}

/**
 * @return list<array{title: string, body: string, adminOnly?: bool, devOnly?: bool}>
 */
function MRT_admin_vue_help_admin_sections(): array {
	return array(
		array(
			'title' => __( 'Översikt', 'museum-railway-timetable' ),
			'body'  => __(
				'Statistik, varningar om dataproblem, nästa trafikdag och snabbstart. På mobil: inställ trafik idag och snabb avgångstid när det finns trafik.',
				'museum-railway-timetable'
			),
		),
		array(
			'title' => __( 'Stationer & rutter', 'museum-railway-timetable' ),
			'body'  => __(
				'Grunddata: stationer (namn, typ, koordinater, buss-suffix) och rutter med stationer i ordning. Rutten styr vilka hållplatser som finns i stopptidsrutnätet.',
				'museum-railway-timetable'
			),
		),
		array(
			'title' => __( 'Tidtabeller', 'museum-railway-timetable' ),
			'body'  => __(
				'Skapa och redigera tidtabeller. I editorn: titel och typ (färg), trafikdagar, turer, stopptider (rutnät eller tabell), avvikelser och förhandsvisning som på webbplatsen.',
				'museum-railway-timetable'
			),
		),
		array(
			'title'     => __( 'Tågtyper', 'museum-railway-timetable' ),
			'body'      => __(
				'Kategorier med namn, slug och ikon — visas i tidtabellsöversikt och kan filtrera månadskalendern.',
				'museum-railway-timetable'
			),
			'adminOnly' => true,
		),
		array(
			'title'     => __( 'Inställningar', 'museum-railway-timetable' ),
			'body'      => __(
				'Aktivera/inaktivera plugin, intern anteckning och min/max väntetid vid byte i reseplaneraren.',
				'museum-railway-timetable'
			),
			'adminOnly' => true,
		),
		array(
			'title'     => __( 'Priser', 'museum-railway-timetable' ),
			'body'      => __(
				'Prismatris (biljettyp × kategori × zoner) som reseplaneraren använder i sammanfattningssteget.',
				'museum-railway-timetable'
			),
			'adminOnly' => true,
		),
		array(
			'title'     => __( 'Import / export', 'museum-railway-timetable' ),
			'body'      => __(
				'Säkerhetskopiera eller flytta all data som CSV i zip. Välj slå ihop eller ersätt vid import.',
				'museum-railway-timetable'
			),
			'adminOnly' => true,
		),
		array(
			'title'     => __( 'Utvecklingsverktyg', 'museum-railway-timetable' ),
			'body'      => __(
				'Demoimport, demosida, tidtabellssidor och databasrensning — endast i utvecklingsläge, syns inte på produktion.',
				'museum-railway-timetable'
			),
			'adminOnly' => true,
			'devOnly'   => true,
		),
	);
}

/**
 * @return list<string>
 */
function MRT_admin_vue_help_workflow_steps(): array {
	return array(
		__( 'Skapa stationer under Stationer & rutter', 'museum-railway-timetable' ),
		__( 'Skapa rutter och lägg stationer i ordning', 'museum-railway-timetable' ),
		__( 'Valfritt: skapa tågtyper med ikoner (Tågtyper)', 'museum-railway-timetable' ),
		__( 'Skapa en tidtabell och lägg till trafikdagar', 'museum-railway-timetable' ),
		__( 'Lägg till turer (koppla rutt och destination)', 'museum-railway-timetable' ),
		__( 'Fyll i stopptider i editorn (rutnät eller tabellvy)', 'museum-railway-timetable' ),
		__( 'Kontrollera förhandsvisningen — samma vy som på webbplatsen', 'museum-railway-timetable' ),
		__( 'Publicera: lägg shortcodes på sidor (eller skapa sidor i utvecklingsläge)', 'museum-railway-timetable' ),
	);
}

/**
 * @return list<array{title: string, body: string}>
 */
function MRT_admin_vue_help_operations(): array {
	return array(
		array(
			'title' => __( 'Inställ trafik idag', 'museum-railway-timetable' ),
			'body'  => __(
				'(Översikt) — sätter meddelandet «Inställd» på alla dagens turer.',
				'museum-railway-timetable'
			),
		),
		array(
			'title' => __( 'Avvikelser', 'museum-railway-timetable' ),
			'body'  => __(
				'(tidtabellseditor) — ändra tågtyp eller meddelande för en specifik tur och datum.',
				'museum-railway-timetable'
			),
		),
		array(
			'title' => __( 'Snabb avgångstid', 'museum-railway-timetable' ),
			'body'  => __(
				'(mobil) — uppdatera avgång vid första hållplats utan att öppna hela rutnätet.',
				'museum-railway-timetable'
			),
		),
	);
}

/**
 * @return list<array<string, mixed>>
 */
function MRT_admin_vue_help_shortcodes(): array {
	return array(
		array(
			'tag'     => 'museum_timetable_index',
			'title'   => __( 'Lista tidtabeller', 'museum-railway-timetable' ),
			'summary' => __(
				'Visar klickbara kort för alla publicerade tidtabeller med länkar till respektive tidtabellssida. Passar som startsida eller hubb.',
				'museum-railway-timetable'
			),
			'example' => '[museum_timetable_index show_dates="1" intro="1"]',
			'params'  => array(
				array(
					'name' => 'show_dates',
					'desc' => __( 'Visa antal trafikdagar och datumspann (1/0, standard 1)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'intro',
					'desc' => __( 'Visa kort introduktionstext (1/0, standard 1)', 'museum-railway-timetable' ),
				),
			),
		),
		array(
			'tag'     => 'museum_timetable_overview',
			'title'   => __( 'Tidtabellsöversikt', 'museum-railway-timetable' ),
			'summary' => __(
				'Visar hela tidtabellen som rutnät: turer grupperade per rutt och riktning, med tider, tågtyper och avvikelser för vald dag.',
				'museum-railway-timetable'
			),
			'example' => '[museum_timetable_overview timetable_id="123"]',
			'params'  => array(
				array(
					'name' => 'timetable_id',
					'desc' => __( 'Tidtabellens ID (rekommenderat — syns i editorns URL)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'timetable',
					'desc' => __( 'Alternativt: exakt titel på tidtabellen', 'museum-railway-timetable' ),
				),
			),
		),
		array(
			'tag'     => 'museum_timetable_month',
			'title'   => __( 'Månadskalender', 'museum-railway-timetable' ),
			'summary' => __(
				'Kalender som visar vilka dagar som har trafik. Besökare kan bläddra månad och se antal turer per dag.',
				'museum-railway-timetable'
			),
			'example' => '[museum_timetable_month month="2026-06" train_type="angtag"]',
			'params'  => array(
				array(
					'name' => 'month',
					'desc' => __( 'Månad som YYYY-MM (standard: aktuell månad)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'train_type',
					'desc' => __( 'Filtrera på tågtypens slug (se Tågtyper)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'service',
					'desc' => __( 'Filtrera på exakt tur-titel (valfritt)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'legend',
					'desc' => __( 'Visa förklaring (1/0, standard 1)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'show_counts',
					'desc' => __(
						'Visa antal turer per dag (1/0, standard 0). Siffran gäller alla linjer och riktningar.',
						'museum-railway-timetable'
					),
				),
				array(
					'name' => 'start_monday',
					'desc' => __( 'Vecka börjar måndag (1/0, standard 1)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'nav',
					'desc' => __( 'Länkar föregående/nästa månad (1/0, standard 1)', 'museum-railway-timetable' ),
				),
			),
		),
		array(
			'tag'     => 'museum_journey_wizard',
			'title'   => __( 'Reseplanerare', 'museum-railway-timetable' ),
			'summary' => __(
				'Flerstegsflöde: välj rutt, datum, utresa och ev. retur. Visar priser och anslutningar. Kräver JavaScript.',
				'museum-railway-timetable'
			),
			'example' => '[museum_journey_wizard ticket_url="https://example.com/biljetter" timetable_page_url="https://example.com/tidtabeller"]',
			'params'  => array(
				array(
					'name' => 'ticket_url',
					'desc' => __( 'Reserverat (inaktiverat) — biljettknapp visas inte i nuvarande version', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'timetable_page_url',
					'desc' => __( 'Länk till tidtabellssida (visas under sök på steg 1)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'embedded',
					'desc' => __( 'Kompakt layout inuti sidinnehåll (1/true)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'timetable_id',
					'desc' => __( 'Legacy — inbäddad översikt under steg 1 (rekommenderas sällan)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'timetable',
					'desc' => __( 'Samma som timetable_id men med tidtabellstitel', 'museum-railway-timetable' ),
				),
			),
		),
	);
}

/**
 * @return list<array{q: string, a: string, aEditor?: string}>
 */
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
				'De är dataproblem (t.ex. tidtabell utan datum, tur utan stopptider). Klicka på varningen för att gå till rätt sida och åtgärda.',
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
				'Gå till Import / export. CSV zip kan slås samman med befintlig data eller ersätta den. I utvecklingsläge finns även Lennakatten-demo under Utvecklingsverktyg.',
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
				'Via shortcodes på WordPress-sidor — se avsnittet Shortcodes nedan. I utvecklingsläge kan du synka färdiga sidor under Utvecklingsverktyg.',
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
				'Priser styr reseplanerarens prissammanfattning. Inställningar styr om pluginet är aktivt och hur lång väntetid som accepteras vid byte. Båda nås via menyn och kan exporteras med CSV.',
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
