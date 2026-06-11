<?php
/**
 * Admin help shortcode reference data.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_help_shortcodes(): array {
	return array(
		array(
			'tag'     => 'museum_traffic_notices',
			'title'   => __( 'Trafikmeddelanden', 'museum-railway-timetable' ),
			'summary' => __(
				'UL-lik trafikinfo-feed: pågående och kommande störningar (90 dagar som standard). Tom vy: «Inga meddelanden».',
				'museum-railway-timetable'
			),
			'example' => '[museum_traffic_notices]',
			'params'  => array(
				array(
					'name' => 'horizon_days',
					'desc' => __( 'Antal dagar framåt från referensdatum (standard 90, max 365)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'date',
					'desc' => __( 'Referensdatum YYYY-MM-DD (standard idag)', 'museum-railway-timetable' ),
				),
				array(
					'name' => 'title',
					'desc' => __( 'Valfri rubrik ovanför listan', 'museum-railway-timetable' ),
				),
			),
		),
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
					'name' => 'hero_background_url',
					'desc' => __( 'Bakgrundsbild för hero. Standard sätts under Inställningar; attributet har företräde. Ignoreras i inbäddat läge.', 'museum-railway-timetable' ),
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
