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

require_once MRT_PATH . 'inc/assets/data/admin-help/loader.php';

/**
 * @return array<string, mixed>
 */
function MRT_admin_vue_help_content(): array {
	return array(
		'title'              => __( 'Hjälp', 'museum-railway-timetable' ),
		'tocTitle'           => __( 'Innehåll', 'museum-railway-timetable' ),
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
		'shortcodesPageTitle'    => __( 'Shortcodes', 'museum-railway-timetable' ),
		'shortcodesPageIntro'    => __(
			'Shortcodes visar tidtabellsdata på webbplatsen. Lägg in dem på WordPress-sidor så besökare kan se listor, tidtabeller, kalender och söka resor.',
			'museum-railway-timetable'
		),
		'shortcodesHowToTitle'   => __( 'Så här lägger du in en shortcode', 'museum-railway-timetable' ),
		'shortcodesHowToSteps'   => MRT_admin_vue_help_shortcodes_howto_steps(),
		'shortcodesQuickRefTitle' => __( 'Snabböversikt', 'museum-railway-timetable' ),
		'shortcodesQuickRefHint' => __(
			'Alla fyra publika shortcodes. Klicka vidare i avsnittet nedan för parametrar och exempel.',
			'museum-railway-timetable'
		),
		'shortcodesColShortcode' => __( 'Shortcode', 'museum-railway-timetable' ),
		'shortcodesColUse'       => __( 'Typisk användning', 'museum-railway-timetable' ),
		'shortcodesSetupTitle'   => __( 'Rekommenderad sidstruktur', 'museum-railway-timetable' ),
		'shortcodesSetupSteps'   => MRT_admin_vue_help_shortcodes_setup_steps(),
		'shortcodesWidgetTitle'  => __( 'Widgets och block', 'museum-railway-timetable' ),
		'shortcodesWidgetNote'   => __(
			'Shortcodes fungerar i WordPress text-widget, «Anpassad HTML»-block och klassisk redigerare. Det finns inga färdiga Gutenberg-block — klistra in shortcoden som text.',
			'museum-railway-timetable'
		),
		'helpLinkToShortcodes'   => __(
			'Parametrar, exempel och rekommenderad sidstruktur finns i guiden under menyn Shortcodes.',
			'museum-railway-timetable'
		),
		'panelPriceZones'    => __( 'Priszoner och biljetter', 'museum-railway-timetable' ),
		'priceZonesIntro'    => __(
			'Priser i reseplaneraren bygger på stationers priszoner och en prismatris. Zonantalet beräknas längs utresans hållplatser (gränsstationer kan ha två zoner). Se även docs/PRICE_ZONES.md i pluginets källkod.',
			'museum-railway-timetable'
		),
		'priceZonesSteps'    => MRT_admin_vue_help_price_zones_steps(),
		'adminSections'      => MRT_admin_vue_help_admin_sections(),
		'workflowSteps'      => MRT_admin_vue_help_workflow_steps(),
		'operations'         => MRT_admin_vue_help_operations(),
		'shortcodes'         => MRT_admin_vue_help_shortcodes(),
		'faq'                => MRT_admin_vue_help_faq_items(),
	);
}
