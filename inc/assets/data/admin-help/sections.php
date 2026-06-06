<?php
/**
 * Admin help structured sections (workflow, shortcode steps, admin areas).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
				'Grunddata: stationer (namn, typ, koordinater, buss-suffix, priszoner) och rutter med stationer i ordning. Rutten styr vilka hållplatser som finns i stopptidsrutnätet.',
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
				'Säkerhetskopiera eller flytta all data som CSV i zip. Sidan har steg-för-steg-guide för att bygga importpaket.',
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

function MRT_admin_vue_help_shortcodes_howto_steps(): array {
	return array(
		__( 'Skapa eller redigera en sida under Sidor i WordPress.', 'museum-railway-timetable' ),
		__( 'Lägg till ett «Anpassad HTML»-block (blockredigeraren) eller klistra in shortcoden i klassisk redigerare.', 'museum-railway-timetable' ),
		__( 'Kopiera shortcoden från tabellen nedan — t.ex. [museum_timetable_overview timetable_id="123"].', 'museum-railway-timetable' ),
		__( 'Ersätt timetable_id med ID från tidtabellseditorns URL (siffran efter /timetables/).', 'museum-railway-timetable' ),
		__( 'Publicera sidan och kontrollera att tidtabellen visas som i förhandsvisningen i admin.', 'museum-railway-timetable' ),
	);
}

function MRT_admin_vue_help_shortcodes_setup_steps(): array {
	return array(
		__( 'Indexsida med [museum_timetable_index] — länkar till alla tidtabeller.', 'museum-railway-timetable' ),
		__( 'En sida per tidtabell med [museum_timetable_overview timetable_id="…"] — hela rutnätet.', 'museum-railway-timetable' ),
		__( 'Valfritt: månadskalender [museum_timetable_month] på samma sida eller separat.', 'museum-railway-timetable' ),
		__( 'Valfritt: reseplanerare [museum_journey_wizard] med länk till tidtabellssidan.', 'museum-railway-timetable' ),
		__( 'I utvecklingsläge: Utvecklingsverktyg → Skapa/uppdatera tidtabellssidor skapar sidorna automatiskt.', 'museum-railway-timetable' ),
	);
}
