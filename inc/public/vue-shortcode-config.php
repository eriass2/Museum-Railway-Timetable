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
 * Swedish weekday abbreviations for month calendar (Mon–Sun or Sun–Sat order).
 *
 * @return array<int, string>
 */
function MRT_vue_month_swedish_weekday_headers( bool $start_monday ): array {
	$weekdays = array(
		__( 'sön', 'museum-railway-timetable' ),
		__( 'mån', 'museum-railway-timetable' ),
		__( 'tis', 'museum-railway-timetable' ),
		__( 'ons', 'museum-railway-timetable' ),
		__( 'tor', 'museum-railway-timetable' ),
		__( 'fre', 'museum-railway-timetable' ),
		__( 'lör', 'museum-railway-timetable' ),
	);
	if ( $start_monday ) {
		return array( $weekdays[1], $weekdays[2], $weekdays[3], $weekdays[4], $weekdays[5], $weekdays[6], $weekdays[0] );
	}
	return $weekdays;
}

/**
 * @param array<string, mixed> $context From MRT_month_shortcode_build_context().
 * @return array<string, mixed>
 */
function MRT_vue_month_config( array $context ): array {
	$first_ts     = (int) ( $context['first_ts'] ?? 0 );
	$start_monday = ! empty( $context['startMonday'] );
	$nav_urls     = MRT_month_shortcode_nav_link_urls( $first_ts ?: false );
	$cal          = MRT_journey_wizard_calendar_i18n_arrays();
	$month_title  = (string) ( $context['month_title'] ?? '' );
	if ( $first_ts > 0 && ! empty( $cal['monthNames'] ) ) {
		$mo_index    = (int) date( 'n', $first_ts ) - 1;
		$month_title = $cal['monthNames'][ $mo_index ] . ' ' . date( 'Y', $first_ts );
	}
	$weekday_heads = MRT_vue_month_swedish_weekday_headers( $start_monday );
	$dates         = isset( $context['dates'] ) && is_array( $context['dates'] ) ? $context['dates'] : array();
	$legend_types  = MRT_month_calendar_legend_types( $dates );

	return array(
		'monthUid'           => (string) ( $context['month_uid'] ?? '' ),
		'monthTitle'         => $month_title,
		'monthAriaLabel'     => sprintf(
			/* translators: %s: month and year */
			__( 'Månadskalender, %s', 'museum-railway-timetable' ),
			$month_title
		),
		'tableCaption'       => sprintf(
			/* translators: %s: month and year */
			__( 'Trafikdagar för %s', 'museum-railway-timetable' ),
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
		'dates'              => $dates,
		'legendTimetableTypes' => $legend_types,
		'stringsPrevMonth'   => __( 'Föregående månad', 'museum-railway-timetable' ),
		'stringsNextMonth'   => __( 'Nästa månad', 'museum-railway-timetable' ),
		'legendServiceDay'   => __( 'Trafikdag', 'museum-railway-timetable' ),
		'legendCountHint'    => __( 'Siffran visar antal turer som trafikerar den dagen (alla linjer och riktningar).', 'museum-railway-timetable' ),
		'dayServiceCountTitle' => __( '%d turer (alla linjer)', 'museum-railway-timetable' ),
		'dayRunningAria'     => __( 'Trafikdag', 'museum-railway-timetable' ),
		'legendClickHint'    => __( 'Klicka för att visa tidtabell', 'museum-railway-timetable' ),
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
		'timetableId'      => isset( $parsed['timetable_id'] ) ? (int) $parsed['timetable_id'] : 0,
		'timetablePageUrl' => isset( $parsed['timetable_page_url'] ) ? (string) $parsed['timetable_page_url'] : '',
		'embedded'         => ! empty( $parsed['embedded'] ),
		'debug'        => isset( $parsed['debug'] ) ? (string) $parsed['debug'] : '',
		'heroSubtitle' => isset( $parsed['hero_subtitle'] ) ? (string) $parsed['hero_subtitle'] : '',
		'startOfWeek'  => (int) get_option( 'start_of_week', 1 ),
		'wizard'       => $wizard_l10n,
		'labels'       => array(
			'noStations'      => __( 'No stations are available.', 'museum-railway-timetable' ),
			'needsJs'         => __( 'Reseplaneraren kräver JavaScript.', 'museum-railway-timetable' ),
			'stepNavAria'     => __( 'Steg i reseplaneraren', 'museum-railway-timetable' ),
			'routeTitle'         => __( 'Planera resa med Lennakatten', 'museum-railway-timetable' ),
			'routeIntro'         => __( 'Välj avgång, ankomst och om du reser enkel eller tur och retur.', 'museum-railway-timetable' ),
			'from'               => __( 'Från', 'museum-railway-timetable' ),
			'to'                 => __( 'Till', 'museum-railway-timetable' ),
			'fromPlaceholder'    => __( 'Sök eller välj station…', 'museum-railway-timetable' ),
			'toPlaceholder'      => __( 'Sök eller välj station…', 'museum-railway-timetable' ),
			'stationSearchAria'  => __( 'Sök avgångsstation', 'museum-railway-timetable' ),
			'stationSearchAriaTo' => __( 'Sök ankomststation', 'museum-railway-timetable' ),
			'tripTypeLegend'     => __( 'Restyp', 'museum-railway-timetable' ),
			'tripSingle'         => __( 'Enkel resa', 'museum-railway-timetable' ),
			'tripReturn'         => __( 'Tur och retur', 'museum-railway-timetable' ),
			'searchTrip'         => __( 'Sök resa', 'museum-railway-timetable' ),
			'timetablePageLink'  => __( 'Visa hela tidtabellen', 'museum-railway-timetable' ),
			'back'            => __( '← Tillbaka', 'museum-railway-timetable' ),
			'stepDate'        => __( 'Välj datum', 'museum-railway-timetable' ),
			'goToToday'           => __( 'Idag', 'museum-railway-timetable' ),
			'thisMonth'           => __( 'Idag', 'museum-railway-timetable' ),
			'calendarEmptyMonth'  => __( 'Inga bokningsbara dagar denna månad för din resa.', 'museum-railway-timetable' ),
			'calendarEmptyHint'   => __( 'Byt månad med pilarna ovan.', 'museum-railway-timetable' ),
			'legendOk'            => __( 'Kan bokas för din resa', 'museum-railway-timetable' ),
			'legendTraffic'       => __( 'Trafik, ej din resa', 'museum-railway-timetable' ),
			'legendNone'          => __( 'Ingen trafik', 'museum-railway-timetable' ),
			'stepOutbound'    => __( 'Välj utresa', 'museum-railway-timetable' ),
			'stepReturn'      => __( 'Välj återresa', 'museum-railway-timetable' ),
			'stepSummary'     => __( 'Din resa', 'museum-railway-timetable' ),
			'ticketCta'       => __( 'Fortsätt till biljetter', 'museum-railway-timetable' ),
		),
	);
}
