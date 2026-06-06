<?php
/**
 * Admin Vue l10n: dev
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
