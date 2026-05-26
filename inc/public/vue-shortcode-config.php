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
	return array(
		'monthUid'     => (string) ( $context['month_uid'] ?? '' ),
		'monthTitle'   => (string) ( $context['month_title'] ?? '' ),
		'year'         => (int) date( 'Y', (int) ( $context['first_ts'] ?? 0 ) ),
		'month'        => (int) date( 'n', (int) ( $context['first_ts'] ?? 0 ) ),
		'daysInMonth'  => (int) ( $context['daysInMonth'] ?? 0 ),
		'weekdayFirst' => (int) ( $context['weekdayFirst'] ?? 1 ),
		'startMonday'  => ! empty( $context['startMonday'] ),
		'atts'         => isset( $context['atts'] ) && is_array( $context['atts'] ) ? $context['atts'] : array(),
		'dates'        => isset( $context['dates'] ) && is_array( $context['dates'] ) ? $context['dates'] : array(),
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
		'wizard'       => $wizard_l10n,
	);
}
