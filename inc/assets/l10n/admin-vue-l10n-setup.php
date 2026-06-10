<?php
/**
 * Admin Vue l10n: setup
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		'setupStepPrices'    => __( 'Konfigurera biljettpriser', 'museum-railway-timetable' ),
		'setupStepStationZones' => __( 'Tilldela priszoner till alla stationer', 'museum-railway-timetable' ),
		'setupCompleteSummary'  => __( 'Alla grundsteg är klara. Du kan fortfarande öppna checklistan här.', 'museum-railway-timetable' ),
	);
}
