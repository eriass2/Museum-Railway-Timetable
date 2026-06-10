<?php
/**
 * Admin Vue l10n: nav
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_admin_vue_l10n_nav(): array {
	return array(
		'navBrand'           => __( 'Tidtabell', 'museum-railway-timetable' ),
		'navAria'            => __( 'Tidtabell admin', 'museum-railway-timetable' ),
		'navOverview'        => __( 'Översikt', 'museum-railway-timetable' ),
		'navStationsRoutes'  => __( 'Stationer & rutter', 'museum-railway-timetable' ),
		'navTimetables'      => __( 'Tidtabeller', 'museum-railway-timetable' ),
		'navShortcodes'      => __( 'Shortcodes', 'museum-railway-timetable' ),
		'navHelp'            => __( 'Hjälp', 'museum-railway-timetable' ),
		'navTrainTypes'      => __( 'Tågtyper', 'museum-railway-timetable' ),
		'navSettings'        => __( 'Inställningar', 'museum-railway-timetable' ),
		'navPrices'          => __( 'Priser', 'museum-railway-timetable' ),
		'navImportExport'    => __( 'Import/export', 'museum-railway-timetable' ),
		'navDev'             => __( 'Dev', 'museum-railway-timetable' ),
		'navComponentDemo'   => __( 'Komponentdemo', 'museum-railway-timetable' ),
	);
}
