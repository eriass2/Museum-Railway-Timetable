<?php

declare(strict_types=1);

/**
 * Serialize shortcode context for Vue experiment mounts.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, mixed> $context From MRT_month_shortcode_build_context().
 * @return array<string, mixed>
 */
function MRT_vue_month_config( array $context ): array {
	$first_ts      = (int) ( $context['first_ts'] ?? 0 );
	$start_monday  = ! empty( $context['startMonday'] );
	$month_title   = (string) ( $context['month_title'] ?? '' );
	$nav_urls      = MRT_month_shortcode_nav_link_urls( $first_ts ?: false );
	$weekday_heads = $start_monday
		? array(
			__( 'Mon', 'museum-railway-timetable' ),
			__( 'Tue', 'museum-railway-timetable' ),
			__( 'Wed', 'museum-railway-timetable' ),
			__( 'Thu', 'museum-railway-timetable' ),
			__( 'Fri', 'museum-railway-timetable' ),
			__( 'Sat', 'museum-railway-timetable' ),
			__( 'Sun', 'museum-railway-timetable' ),
		)
		: array(
			__( 'Sun', 'museum-railway-timetable' ),
			__( 'Mon', 'museum-railway-timetable' ),
			__( 'Tue', 'museum-railway-timetable' ),
			__( 'Wed', 'museum-railway-timetable' ),
			__( 'Thu', 'museum-railway-timetable' ),
			__( 'Fri', 'museum-railway-timetable' ),
			__( 'Sat', 'museum-railway-timetable' ),
		);

	return array(
		'monthUid'           => (string) ( $context['month_uid'] ?? '' ),
		'monthTitle'         => $month_title,
		'monthAriaLabel'     => sprintf(
			/* translators: %s: month and year */
			__( 'Timetable month view, %s', 'museum-railway-timetable' ),
			$month_title
		),
		'tableCaption'       => sprintf(
			/* translators: %s: month and year */
			__( 'Operating days for %s', 'museum-railway-timetable' ),
			$month_title
		),
		'prevMonthUrl'       => $nav_urls[0],
		'nextMonthUrl'       => $nav_urls[1],
		'weekdayHeaders'     => $weekday_heads,
		'weekdayFirst'       => (int) ( $context['weekdayFirst'] ?? 1 ),
		'weekdayFirstSunday' => $first_ts ? (int) date( 'w', $first_ts ) : 0,
		'year'               => (int) date( 'Y', $first_ts ),
		'month'              => (int) date( 'n', $first_ts ),
		'daysInMonth'        => (int) ( $context['daysInMonth'] ?? 0 ),
		'startMonday'        => $start_monday,
		'atts'               => isset( $context['atts'] ) && is_array( $context['atts'] ) ? $context['atts'] : array(),
		'dates'              => isset( $context['dates'] ) && is_array( $context['dates'] ) ? $context['dates'] : array(),
		'stringsPrevMonth'   => __( 'Previous month', 'museum-railway-timetable' ),
		'stringsNextMonth'   => __( 'Next month', 'museum-railway-timetable' ),
		'legendServiceDay'   => __( 'Service day', 'museum-railway-timetable' ),
		'legendCountHint'    => __( 'count per day', 'museum-railway-timetable' ),
		'legendClickHint'    => __( 'Click to view timetable', 'museum-railway-timetable' ),
	);
}

/**
 * @return array<string, mixed>
 */
function MRT_vue_overview_config( int $timetable_id ): array {
	return array(
		'timetableId' => $timetable_id,
	);
}

/**
 * @param array<int>             $stations Station post IDs from MRT_get_all_stations().
 * @param array<string, mixed> $parsed   Wizard shortcode parse result.
 * @return array<string, mixed>
 */
function MRT_vue_wizard_config( array $stations, array $parsed ): array {
	$station_rows = array();
	foreach ( $stations as $station_id ) {
		$id = (int) $station_id;
		if ( $id <= 0 ) {
			continue;
		}
		$station_rows[] = array(
			'id'    => $id,
			'title' => (string) get_the_title( $id ),
		);
	}

	$wizard_l10n = function_exists( 'MRT_journey_wizard_script_localization' )
		? MRT_journey_wizard_script_localization()
		: array();

	return array(
		'stations'     => $station_rows,
		'ticketUrl'    => isset( $parsed['ticket_url'] ) ? (string) $parsed['ticket_url'] : '',
		'timetableId'  => isset( $parsed['timetable_id'] ) ? (int) $parsed['timetable_id'] : 0,
		'embedded'     => ! empty( $parsed['embedded'] ),
		'debug'        => isset( $parsed['debug'] ) ? (string) $parsed['debug'] : '',
		'heroSubtitle' => isset( $parsed['hero_subtitle'] ) ? (string) $parsed['hero_subtitle'] : '',
		'startOfWeek'  => (int) get_option( 'start_of_week', 1 ),
		'wizard'       => $wizard_l10n,
		'labels'       => array(
			'needsJs'         => __( 'Reseplaneraren kräver JavaScript.', 'museum-railway-timetable' ),
			'stepNavAria'     => __( 'Steg i reseplaneraren', 'museum-railway-timetable' ),
			'routeTitle'      => __( 'Sök din resa med Lennakatten', 'museum-railway-timetable' ),
			'from'            => __( 'Från', 'museum-railway-timetable' ),
			'to'              => __( 'Till', 'museum-railway-timetable' ),
			'fromPlaceholder' => __( 'Var börjar du din resa?', 'museum-railway-timetable' ),
			'toPlaceholder'   => __( 'Vart vill du resa?', 'museum-railway-timetable' ),
			'tripTypeLegend'  => __( 'Restyp', 'museum-railway-timetable' ),
			'tripSingle'      => __( 'Enkel', 'museum-railway-timetable' ),
			'tripReturn'      => __( 'Tur- och retur', 'museum-railway-timetable' ),
			'searchTrip'      => __( 'Sök resa', 'museum-railway-timetable' ),
			'showTimetable'   => __( 'Visa tidtabell', 'museum-railway-timetable' ),
			'back'            => __( '← Tillbaka', 'museum-railway-timetable' ),
			'stepDate'        => __( 'Välj datum', 'museum-railway-timetable' ),
			'thisMonth'       => __( 'Denna månad', 'museum-railway-timetable' ),
			'legendOk'        => __( 'Lennakatten trafikerar den valda resan', 'museum-railway-timetable' ),
			'legendTraffic'   => __( 'Lennakatten trafikerar, men ej den valda resan', 'museum-railway-timetable' ),
			'legendNone'      => __( 'Ingen trafik', 'museum-railway-timetable' ),
			'stepOutbound'    => __( 'Välj utresa', 'museum-railway-timetable' ),
			'stepReturn'      => __( 'Välj återresa', 'museum-railway-timetable' ),
			'stepSummary'     => __( 'Din resa', 'museum-railway-timetable' ),
			'ticketCta'       => __( 'Fortsätt till biljetter', 'museum-railway-timetable' ),
		),
	);
}
