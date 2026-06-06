<?php
/**
 * Admin Vue i18n: editor, mobile, dev tools, setup checklist, route preview, stop times.
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
function MRT_admin_vue_l10n_editor(): array {
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
		'editorSaveMeta'             => __( 'Spara namn och typ', 'museum-railway-timetable' ),
		'editorDeleteTimetable'      => __( 'Ta bort tidtabell', 'museum-railway-timetable' ),
		'editorTabDates'             => __( 'Trafikdagar', 'museum-railway-timetable' ),
		'editorTabTrips'             => __( 'Turer', 'museum-railway-timetable' ),
		'editorTabStoptimes'         => __( 'Stopptider', 'museum-railway-timetable' ),
		'editorTabDeviations'        => __( 'Avvikelser', 'museum-railway-timetable' ),
		'editorTabPreview'           => __( 'Förhandsvisning', 'museum-railway-timetable' ),
		'editorDatesUnsaved'         => __(
			'Osparade trafikdagar — klicka «Spara» för att spara listan.',
			'museum-railway-timetable'
		),
		'editorDatesAdd'             => __( 'Lägg till datum', 'museum-railway-timetable' ),
		'editorDeviationsUnsaved'    => __(
			'Osparade avvikelser — klicka «Spara avvikelser».',
			'museum-railway-timetable'
		),
		'editorDeviationsEmpty'      => __( 'Inga avvikelser ännu. Välj datum och tur nedan.', 'museum-railway-timetable' ),
		'editorAddDeviation'         => __( 'Lägg till avvikelse', 'museum-railway-timetable' ),
		'editorDeviationDatePrompt'  => __( '— Datum —', 'museum-railway-timetable' ),
		'editorSavedDates'           => __( 'Trafikdagar sparade', 'museum-railway-timetable' ),
		'editorSavedMeta'            => __( 'Namn och typ sparade', 'museum-railway-timetable' ),
		'editorSavedDeviations'      => __( 'Avvikelser sparade', 'museum-railway-timetable' ),
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
		'editorSelectTrip'             => __( '— Välj tur —', 'museum-railway-timetable' ),
		'editorColRoute'               => __( 'Rutt', 'museum-railway-timetable' ),
		'editorColTrainType'           => __( 'Tågtyp', 'museum-railway-timetable' ),
		'editorColDestination'         => __( 'Destination', 'museum-railway-timetable' ),
		'editorColDate'                => __( 'Datum', 'museum-railway-timetable' ),
		'editorColTrip'                => __( 'Tur', 'museum-railway-timetable' ),
		'editorColMessage'             => __( 'Meddelande', 'museum-railway-timetable' ),
		'editorStopptimes'             => __( 'Stopptider', 'museum-railway-timetable' ),
		'editorAddTrip'                => __( 'Lägg till tur', 'museum-railway-timetable' ),
		'editorEditTrip'               => __( 'Redigera', 'museum-railway-timetable' ),
		'editorEditTripTitle'          => __( 'Redigera tur', 'museum-railway-timetable' ),
		'editorColServiceNumber'       => __( 'Tågnummer', 'museum-railway-timetable' ),
		'editorServiceNumberHint'      => __(
			'Lämna tomt för att använda tur-ID som nummer.',
			'museum-railway-timetable'
		),
		'editorSaveTrip'               => __( 'Spara tur', 'museum-railway-timetable' ),
		'editorSavedTrip'              => __( 'Tur sparad', 'museum-railway-timetable' ),
		'editorCancelEdit'             => __( 'Avbryt', 'museum-railway-timetable' ),
		'editorRoutePrompt'            => __( '— Rutt —', 'museum-railway-timetable' ),
		'editorTrainTypePrompt'        => __( '— Tågtyp —', 'museum-railway-timetable' ),
		'editorDestinationPrompt'      => __( '— Destination —', 'museum-railway-timetable' ),
		'editorStandardTrainType'      => __( '— Standard —', 'museum-railway-timetable' ),
		'editorSaveDeviations'         => __( 'Spara avvikelser', 'museum-railway-timetable' ),
	);
}

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_mobile(): array {
	return array(
		'mobileLoading'              => __( 'Laddar…', 'museum-railway-timetable' ),
		'mobileDeviationsTitle'      => __( 'Avvikelser', 'museum-railway-timetable' ),
		'mobileNoDeviations'         => __( 'Inga avvikelser för denna tidtabell.', 'museum-railway-timetable' ),
		'mobileQuickDepartureTitle'  => __( 'Snabb avgångstid', 'museum-railway-timetable' ),
		'mobileQuickDepartureHint'   => __( 'Ändra avgångstid vid första hållplatsen (mobil).', 'museum-railway-timetable' ),
		'mobileTripLabel'            => __( 'Tur', 'museum-railway-timetable' ),
		'mobileDepartureSuffix'      => __( 'avgång', 'museum-railway-timetable' ),
		'mobileSaveDeparture'        => __( 'Spara avgångstid', 'museum-railway-timetable' ),
		'mobileDepartureSaved'       => __( 'Avgångstid sparad', 'museum-railway-timetable' ),
		'mobileStopTimesLoadFailed'  => __( 'Kunde inte ladda stopptider', 'museum-railway-timetable' ),
		'mobileSaveFailed'           => __( 'Kunde inte spara', 'museum-railway-timetable' ),
		'mobileCancelTitle'          => __( 'Inställ trafik', 'museum-railway-timetable' ),
		'mobileCancelHint'           => __(
			'Sätter meddelandet «Inställd» på alla turer som gäller %s.',
			'museum-railway-timetable'
		),
		'mobileCancelAllCancelled'   => __( 'All trafik är redan markerad som inställd.', 'museum-railway-timetable' ),
		'mobileCancelButton'         => __( 'Inställ trafik idag', 'museum-railway-timetable' ),
		'mobileCancelNoPermission'   => __( 'Du har inte behörighet att ställa in trafik.', 'museum-railway-timetable' ),
		'mobileCancelConfirmTitle'   => __( 'Inställ trafik', 'museum-railway-timetable' ),
		'mobileCancelConfirmMessage' => __(
			'Alla %1$s turer den %2$s får meddelandet «Inställd».',
			'museum-railway-timetable'
		),
		'mobileCancelSuccess'        => __( '%s turer markerade som inställda.', 'museum-railway-timetable' ),
		'mobileCancelNone'           => __( 'Ingen trafik att ställa in för detta datum.', 'museum-railway-timetable' ),
	);
}

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_stop_times(): array {
	return array(
		'stopTimesLoading'     => __( 'Laddar stopptider…', 'museum-railway-timetable' ),
		'stopTimesSaved'       => __( 'Stopptider sparade', 'museum-railway-timetable' ),
		'stopTimesSaveButton'  => __( 'Spara stopptider', 'museum-railway-timetable' ),
		'stopTimesColStops'    => __( 'Stannar', 'museum-railway-timetable' ),
		'stopTimesColStation'  => __( 'Station', 'museum-railway-timetable' ),
		'stopTimesColArrival'  => __( 'Ankomst', 'museum-railway-timetable' ),
		'stopTimesColDeparture' => __( 'Avgång', 'museum-railway-timetable' ),
		'stopTimesColPickup'   => __( 'På', 'museum-railway-timetable' ),
		'stopTimesColDropoff'  => __( 'Av', 'museum-railway-timetable' ),
		'stopTimesPickupLabel' => __( 'Påstigning', 'museum-railway-timetable' ),
		'stopTimesDropoffLabel' => __( 'Avstigning', 'museum-railway-timetable' ),
		'stopTimesGridEditTitle' => __( '%1$s · tur %2$s', 'museum-railway-timetable' ),
	);
}

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_dev(): array {
	return array(
		'devTitle'              => __( 'Utvecklingsverktyg', 'museum-railway-timetable' ),
		'devNotAvailable'       => __(
			'Endast tillgängligt när WP_DEBUG eller MRT_DEVELOPMENT är aktivt.',
			'museum-railway-timetable'
		),
		'devDescription'        => __(
			'Reset, import och demosidor för lokal QA. Visas inte på produktion.',
			'museum-railway-timetable'
		),
		'devClearTitle'         => __( 'Radera all plugin-data', 'museum-railway-timetable' ),
		'devClearMessage'       => __(
			'Alla stationer, rutter, tidtabeller, turer och inställningar tas bort. Detta går inte att ångra.',
			'museum-railway-timetable'
		),
		'devClearConfirm'       => __( 'Radera allt', 'museum-railway-timetable' ),
		'devClearSuccess'       => __( 'All plugin-data har raderats.', 'museum-railway-timetable' ),
		'devImportSuccess'      => __( 'Lennakatten-demo har importerats.', 'museum-railway-timetable' ),
		'devDemoSuccess'        => __( 'Demosida skapad eller uppdaterad.', 'museum-railway-timetable' ),
		'devNavSuccess'         => __( 'Utvecklingsmeny uppdaterad.', 'museum-railway-timetable' ),
		'devPagesSuccess'       => __( 'Tidtabellssidor skapade eller uppdaterade.', 'museum-railway-timetable' ),
		'devDone'               => __( 'Klart.', 'museum-railway-timetable' ),
		'devClearButton'        => __( 'Rensa plugin-databas', 'museum-railway-timetable' ),
		'devImportButton'       => __( 'Importera Lennakatten-demo', 'museum-railway-timetable' ),
		'devDemoButton'         => __( 'Skapa demosida', 'museum-railway-timetable' ),
		'devNavButton'          => __( 'Sätt upp utvecklingsmeny', 'museum-railway-timetable' ),
		'devPagesButton'        => __( 'Skapa/uppdatera tidtabellssidor', 'museum-railway-timetable' ),
		'devComponentDemoLink'  => __( 'Komponentdemosida (PHP-admin)', 'museum-railway-timetable' ),
	);
}

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_setup(): array {
	return array(
		'setupTitle'         => __( 'Kom igång', 'museum-railway-timetable' ),
		'setupProgress'      => __(
			'%1$s av %2$s steg klara — följ ordningen nedan innan du publicerar tidtabeller på webbplatsen.',
			'museum-railway-timetable'
		),
		'setupGo'            => __( 'Gå till', 'museum-railway-timetable' ),
		'setupStepStations'  => __( 'Skapa minst en station', 'museum-railway-timetable' ),
		'setupStepRoutes'    => __( 'Skapa minst en rutt med stationer', 'museum-railway-timetable' ),
		'setupStepTimetables' => __( 'Skapa en tidtabell', 'museum-railway-timetable' ),
		'setupStepServices'  => __( 'Lägg till turer i en tidtabell', 'museum-railway-timetable' ),
	);
}

/**
 * @return array<string, string>
 */
function MRT_admin_vue_l10n_route_preview(): array {
	return array(
		'routePreviewLabel' => __( 'Ruttens stationer', 'museum-railway-timetable' ),
		'routePreviewEmpty' => __( 'Inga stationer på rutten.', 'museum-railway-timetable' ),
		'routePreviewStart' => __( 'Start', 'museum-railway-timetable' ),
		'routePreviewEnd'   => __( 'Slut', 'museum-railway-timetable' ),
		'routePreviewBoth'  => __( 'Start/slut', 'museum-railway-timetable' ),
	);
}
