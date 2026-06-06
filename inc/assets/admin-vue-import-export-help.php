<?php
/**
 * Structured import/export guide for the Vue admin Import page.
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
function MRT_admin_vue_import_export_guide(): array {
	return array(
		'intro'              => __(
			'Flytta eller säkerhetskopiera tidtabellsdata med en zip-fil. Du behöver inte skapa manifest.json manuellt — pluginet känner igen CSV-filerna automatiskt.',
			'museum-railway-timetable'
		),
		'workflowTitle'      => __( 'Snabbstart', 'museum-railway-timetable' ),
		'workflowSteps'      => MRT_admin_vue_import_export_workflow_steps(),
		'manifestAutoNote'   => __(
			'manifest.json skapas automatiskt vid import om den saknas. Vid export ingår den redan.',
			'museum-railway-timetable'
		),
		'guideDisclosureSummary' => __( 'Visa filformat, nycklar och detaljer', 'museum-railway-timetable' ),
		'buildTitle'         => __( 'Så bygger du en import', 'museum-railway-timetable' ),
		'buildSteps'         => MRT_admin_vue_import_export_build_steps(),
		'packageTitle'       => __( 'Filer i zip-paketet', 'museum-railway-timetable' ),
		'packageHint'        => __(
			'CSV-filer i zip-roten (UTF-8, komma som avgränsare). manifest.json är valfri — inkluderas i mall och export.',
			'museum-railway-timetable'
		),
		'colFile'            => __( 'Fil', 'museum-railway-timetable' ),
		'colRequired'        => __( 'Krävs', 'museum-railway-timetable' ),
		'colDescription'     => __( 'Beskrivning', 'museum-railway-timetable' ),
		'requiredYes'        => __( 'Ja', 'museum-railway-timetable' ),
		'requiredNo'         => __( 'Nej', 'museum-railway-timetable' ),
		'packageFiles'       => MRT_admin_vue_import_export_package_files(),
		'orderTitle'         => __( 'Beroendeordning', 'museum-railway-timetable' ),
		'orderHint'          => __(
			'Importören läser filerna i rätt ordning automatiskt. När du skapar data manuellt måste du respektera samma kedja — t.ex. kan inga turer skapas utan rutter och stationer.',
			'museum-railway-timetable'
		),
		'orderSteps'         => MRT_admin_vue_import_export_order_steps(),
		'keysTitle'          => __( 'Stabila nycklar (*_code)', 'museum-railway-timetable' ),
		'keysIntro'          => __(
			'WordPress-ID används inte i CSV. Alla kopplingar går via station_code, route_code, timetable_code och service_code.',
			'museum-railway-timetable'
		),
		'keysTips'           => MRT_admin_vue_import_export_keys_tips(),
		'modesTitle'         => __( 'Importlägen', 'museum-railway-timetable' ),
		'modeMergeDetail'    => __(
			'Poster med matchande *_code uppdateras. Barn (t.ex. stopptider för en tur) ersätts för den posten. Data som finns i databasen men saknas i zip-filen lämnas orörd.',
			'museum-railway-timetable'
		),
		'modeOverrideDetail' => __(
			'Samma uppdatering som ovan, men poster av entitetstyper som listas i manifest.json → includes och som saknas i CSV tas bort. Scope styrs av includes — en fil med bara tidtabeller raderar inte stationer.',
			'museum-railway-timetable'
		),
		'modeOverrideWarning' => __(
			'Varning: Ersätt kan radera tidtabeller och turer som inte finns i filen. Exportera alltid en säkerhetskopia först.',
			'museum-railway-timetable'
		),
		'tipsTitle'          => __( 'Tips', 'museum-railway-timetable' ),
		'tips'               => MRT_admin_vue_import_export_tips(),
		'docsNote'           => __(
			'Full kolumnspecifikation: docs/CSV_FORMAT.md i plugin-källkoden.',
			'museum-railway-timetable'
		),
	);
}

/**
 * @return list<string>
 */
function MRT_admin_vue_import_export_workflow_steps(): array {
	return array(
		__( 'Ladda ner tom mall eller befintlig export.', 'museum-railway-timetable' ),
		__( 'Redigera CSV-filer i Excel eller LibreOffice (börja med stationer → rutter → tidtabell → turer → stopptider).', 'museum-railway-timetable' ),
		__( 'Zip:a filerna och ladda upp — eller ladda upp en enskild CSV för små fixar (t.ex. stoptimes.csv).', 'museum-railway-timetable' ),
	);
}

/**
 * @return list<string>
 */
function MRT_admin_vue_import_export_build_steps(): array {
	return array(
		__( 'Ladda ner tom mall eller en export som utgångspunkt.', 'museum-railway-timetable' ),
		__( 'Packa upp zip-filen och redigera CSV-filerna. Spara som UTF-8.', 'museum-railway-timetable' ),
		__( 'Fyll i stations.csv och train_types.csv först.', 'museum-railway-timetable' ),
		__( 'Lägg till routes.csv och route_stations.csv (stationer i ordning per rutt).', 'museum-railway-timetable' ),
		__( 'Skapa timetables.csv, timetable_dates.csv, services.csv och stoptimes.csv.', 'museum-railway-timetable' ),
		__( 'Zip:a CSV-filerna i zip-roten och ladda upp. manifest.json behövs inte — skapas vid import.', 'museum-railway-timetable' ),
	);
}

/**
 * @return list<array{file: string, required: bool, desc: string}>
 */
function MRT_admin_vue_import_export_package_files(): array {
	return array(
		array(
			'file'     => 'manifest.json',
			'required' => false,
			'desc'     => __( 'Formatversion och includes — skapas automatiskt vid import om den saknas', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'stations.csv',
			'required' => true,
			'desc'     => __( 'Hållplatser med namn, typ och koordinater', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'train_types.csv',
			'required' => true,
			'desc'     => __( 'Tågtyper (slug, namn, valfri ikon)', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'routes.csv',
			'required' => true,
			'desc'     => __( 'Rutter mellan stationer', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'route_stations.csv',
			'required' => true,
			'desc'     => __( 'Stationer per rutt i ordning (sequence)', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'timetables.csv',
			'required' => true,
			'desc'     => __( 'Tidtabeller (titel, typ/färg)', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'timetable_dates.csv',
			'required' => true,
			'desc'     => __( 'Trafikdagar per tidtabell', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'services.csv',
			'required' => true,
			'desc'     => __( 'Turer (rutt, destination, tågnummer)', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'service_train_types.csv',
			'required' => false,
			'desc'     => __( 'Avvikande tågtyp per tur (annars standard)', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'stoptimes.csv',
			'required' => true,
			'desc'     => __( 'Ankomst/avgång per hållplats och tur', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'settings.csv',
			'required' => false,
			'desc'     => __( 'Plugin-inställningar (export/import valfritt)', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'prices.csv',
			'required' => false,
			'desc'     => __( 'Prismatris för reseplaneraren', 'museum-railway-timetable' ),
		),
		array(
			'file'     => 'icons/',
			'required' => false,
			'desc'     => __( 'Tågtypsikoner refererade från train_types.csv', 'museum-railway-timetable' ),
		),
	);
}

/**
 * @return list<string>
 */
function MRT_admin_vue_import_export_order_steps(): array {
	return array(
		'stations.csv → train_types.csv',
		'routes.csv + route_stations.csv',
		'timetables.csv + timetable_dates.csv',
		'services.csv + service_train_types.csv',
		'stoptimes.csv',
		'settings.csv → prices.csv (valfritt)',
	);
}

/**
 * @return list<string>
 */
function MRT_admin_vue_import_export_keys_tips(): array {
	return array(
		__( 'Första gången kan *_code lämnas tomma — importören skapar codes från namn/titel.', 'museum-railway-timetable' ),
		__( 'Efter export ska du använda samma codes vid uppdatering så poster matchas rätt.', 'museum-railway-timetable' ),
		__( 'Tågtyper identifieras via slug (t.ex. angtag, buss) — inte en egen code-kolumn.', 'museum-railway-timetable' ),
		__( 'Vid kollision (samma auto-slug, olika namn) stoppas importen med felmeddelande på radnivå.', 'museum-railway-timetable' ),
	);
}

/**
 * @return list<string>
 */
function MRT_admin_vue_import_export_tips(): array {
	return array(
		__( 'Exportera alltid innan du kör Ersätt eller Radera all data.', 'museum-railway-timetable' ),
		__( 'Testa med Slå ihop först om du är osäker — befintlig data som saknas i filen behålls.', 'museum-railway-timetable' ),
		__( 'Kontrollera tidtabellen i admin efter import (förhandsvisning och varningar på översikten).', 'museum-railway-timetable' ),
		__( 'I utvecklingsläge finns Lennakatten-demo under Utvecklingsverktyg som referensexempel.', 'museum-railway-timetable' ),
	);
}
