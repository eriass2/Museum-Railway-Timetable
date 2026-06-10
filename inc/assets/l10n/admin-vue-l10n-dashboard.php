<?php
/**
 * Admin Vue l10n: dashboard
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_dashboard(): array {
	return array(
		'dashboardTitle'           => __( 'Museum Railway Timetable', 'museum-railway-timetable' ),
		'dashboardLoading'         => __( 'Laddar översikt…', 'museum-railway-timetable' ),
		'dashboardLoadFailed'      => __( 'Kunde inte ladda översikt.', 'museum-railway-timetable' ),
		'dashboardLimitedRole'     => __(
			'Begränsad behörighet: du kan ändra avvikelser och avgångstider, inte grunddata.',
			'museum-railway-timetable'
		),
		'dashboardStatStations'    => __( 'Stationer', 'museum-railway-timetable' ),
		'dashboardStatRoutes'      => __( 'Rutter', 'museum-railway-timetable' ),
		'dashboardStatTimetables'  => __( 'Tidtabeller', 'museum-railway-timetable' ),
		'dashboardStatServices'    => __( 'Turer', 'museum-railway-timetable' ),
		'dashboardStatTrainTypes'  => __( 'Tågtyper', 'museum-railway-timetable' ),
		'dashboardStatsAria'       => __( 'Statistik', 'museum-railway-timetable' ),
		'dashboardStatsSummary'    => __(
			'%1$s stationer · %2$s rutter · %3$s tidtabeller · %4$s turer · %5$s tågtyper',
			'museum-railway-timetable'
		),
		'dashboardWarningsTitle'   => __( 'Varningar', 'museum-railway-timetable' ),
		'dashboardNextTrafficTitle' => __( 'Nästa trafik', 'museum-railway-timetable' ),
		'dashboardColDate'         => __( 'Datum', 'museum-railway-timetable' ),
		'dashboardColTimetable'    => __( 'Tidtabell', 'museum-railway-timetable' ),
		'dashboardQuickstartTitle' => __( 'Snabbstart', 'museum-railway-timetable' ),
		'dashboardQuickStations'   => __( 'Stationer & rutter', 'museum-railway-timetable' ),
		'dashboardQuickTimetables' => __( 'Hantera tidtabeller', 'museum-railway-timetable' ),
		'dashboardQuickHelp'       => __( 'Hjälp & FAQ', 'museum-railway-timetable' ),
		'dashboardViewSite'        => __( 'Visa webbplats', 'museum-railway-timetable' ),
	);
}
