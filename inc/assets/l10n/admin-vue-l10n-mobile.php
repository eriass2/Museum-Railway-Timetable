<?php
/**
 * Admin Vue l10n: mobile
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		'stopTimesOperatorHint' => __(
			'Som operatör kan du ändra tider och om tåget stannar. På/Av (påstigning/avstigning) kräver administratörsbehörighet.',
			'museum-railway-timetable'
		),
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
