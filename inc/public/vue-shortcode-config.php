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
	$month_title  = (string) ( $context['month_title'] ?? '' );
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
		'initialDate'        => (string) ( $context['atts']['initial_date'] ?? '' ),
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
 * @param array<string, mixed> $parsed Wizard shortcode parse result.
 * @return array<string, mixed>|null
 */
function MRT_vue_wizard_beta_banner( array $parsed ): ?array {
	$enabled = function_exists( 'MRT_plugin_wizard_beta_enabled' )
		? MRT_plugin_wizard_beta_enabled()
		: false;
	$enabled = (bool) apply_filters( 'mrt_journey_wizard_beta_banner_enabled', $enabled, $parsed );
	if ( ! $enabled ) {
		return null;
	}

	return array(
		'label' => __( 'Beta', 'museum-railway-timetable' ),
		'text'  => __(
			'Reseplaneraren testas under säsongen. Tider och priser kan ändras.',
			'museum-railway-timetable'
		),
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

	$route_title = isset( $parsed['route_title'] ) ? trim( (string) $parsed['route_title'] ) : '';
	if ( $route_title === '' ) {
		$operator = MRT_plugin_operator_name();
		if ( $operator !== '' ) {
			$route_title = sprintf(
				/* translators: %s: railway operator name */
				__( 'Planera resa med %s', 'museum-railway-timetable' ),
				$operator
			);
		} else {
			$route_title = __( 'Planera resa', 'museum-railway-timetable' );
		}
	}

	$ticket_url = isset( $parsed['ticket_url'] ) ? trim( (string) $parsed['ticket_url'] ) : '';
	if ( $ticket_url === '' ) {
		$ticket_url = MRT_plugin_ticket_url();
	}

	$beta_banner = MRT_vue_wizard_beta_banner( $parsed );

	$hero_background_url = isset( $parsed['hero_background_url'] ) ? trim( (string) $parsed['hero_background_url'] ) : '';

	return array(
		'stations'     => $station_rows,
		'ticketUrl'    => $ticket_url,
		'betaBanner'   => $beta_banner,
		'feedbackEnabled' => function_exists( 'MRT_plugin_wizard_feedback_enabled' )
			? MRT_plugin_wizard_feedback_enabled()
			: false,
		'timetableId'      => isset( $parsed['timetable_id'] ) ? (int) $parsed['timetable_id'] : 0,
		'timetablePageUrl' => isset( $parsed['timetable_page_url'] ) ? (string) $parsed['timetable_page_url'] : '',
		'embedded'         => ! empty( $parsed['embedded'] ),
		'debug'        => isset( $parsed['debug'] ) ? (string) $parsed['debug'] : '',
		'heroSubtitle'       => isset( $parsed['hero_subtitle'] ) ? (string) $parsed['hero_subtitle'] : '',
		'heroBackgroundUrl'  => $hero_background_url,
		'startOfWeek'  => (int) get_option( 'start_of_week', 1 ),
		'wizard'       => $wizard_l10n,
		'labels'       => array(
			'noStations'      => __( 'Inga stationer är tillgängliga.', 'museum-railway-timetable' ),
			'needsJs'         => __( 'Reseplaneraren kräver JavaScript.', 'museum-railway-timetable' ),
			'stepNavAria'     => __( 'Steg i reseplaneraren', 'museum-railway-timetable' ),
			'stepGoTo'        => __( 'Gå till steg: %s', 'museum-railway-timetable' ),
			'routeTitle'         => $route_title,
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
			'calendarEmptyMonth'  => __( 'Ingen trafik för din resa denna månad.', 'museum-railway-timetable' ),
			'calendarEmptyHint'   => __( 'Byt månad med pilarna ovan.', 'museum-railway-timetable' ),
			'legendOk'            => __( 'Trafik för din resa', 'museum-railway-timetable' ),
			'legendTraffic'       => __( 'Trafik, ej din resa', 'museum-railway-timetable' ),
			'legendNone'          => __( 'Ingen trafik', 'museum-railway-timetable' ),
			'stepOutbound'    => __( 'Välj utresa', 'museum-railway-timetable' ),
			'stepReturn'      => __( 'Välj återresa', 'museum-railway-timetable' ),
			'stepSummary'     => __( 'Din resa', 'museum-railway-timetable' ),
			'ticketCta'       => __( 'Fortsätt till biljetter', 'museum-railway-timetable' ),
			'summaryPrint'    => __( 'Skriv ut', 'museum-railway-timetable' ),
			'summaryDownloadPdf' => __( 'Ladda ner som PDF', 'museum-railway-timetable' ),
			'summaryPdfError' => __( 'Kunde inte skapa PDF. Försök igen eller använd Skriv ut.', 'museum-railway-timetable' ),
			'summaryPricesHeading' => __( 'Priser', 'museum-railway-timetable' ),
			'calPrevAria'          => __( 'Föregående månad', 'museum-railway-timetable' ),
			'calNextAria'          => __( 'Nästa månad', 'museum-railway-timetable' ),
			'feedbackButton'       => __( 'Rapportera fel eller förslag', 'museum-railway-timetable' ),
			'feedbackTitle'        => __( 'Rapportera fel eller förslag', 'museum-railway-timetable' ),
			'feedbackTypeBug'      => __( 'Fel / bugg', 'museum-railway-timetable' ),
			'feedbackTypeSuggestion' => __( 'Förslag', 'museum-railway-timetable' ),
			'feedbackMessage'      => __( 'Beskrivning', 'museum-railway-timetable' ),
			'feedbackEmail'        => __( 'E-post (valfritt)', 'museum-railway-timetable' ),
			'feedbackPrivacy'      => __( 'Vi sparar din rapport för felsökning. E-post används bara om du fyller i den.', 'museum-railway-timetable' ),
			'feedbackSubmit'       => __( 'Skicka', 'museum-railway-timetable' ),
			'feedbackCancel'       => __( 'Avbryt', 'museum-railway-timetable' ),
			'feedbackThanks'       => __( 'Tack! Vi har tagit emot din rapport.', 'museum-railway-timetable' ),
			'feedbackError'        => __( 'Kunde inte skicka rapporten. Försök igen.', 'museum-railway-timetable' ),
		),
	);
}

/**
 * @param array{show_intro: bool, show_dates: bool, items: array<int, array{url: string, label: string, meta: string, modifier: string, aria_hint: string}>} $context
 * @return array<string, mixed>
 */
function MRT_vue_index_config( array $context ): array {
	$items = array();
	foreach ( $context['items'] as $item ) {
		$items[] = array(
			'url'       => (string) $item['url'],
			'label'     => (string) $item['label'],
			'meta'      => (string) $item['meta'],
			'modifier'  => (string) $item['modifier'],
			'ariaHint'  => (string) $item['aria_hint'],
		);
	}

	return array(
		'showIntro' => ! empty( $context['show_intro'] ),
		'items'     => $items,
		'labels'    => array(
			'intro'   => __( 'Choose a timetable to see departures and traffic days.', 'museum-railway-timetable' ),
			'navAria' => __( 'Timetables', 'museum-railway-timetable' ),
		),
	);
}

/**
 * @param array<string, mixed> $context From MRT_traffic_notices_build_context().
 * @return array<string, mixed>
 */
function MRT_vue_traffic_notices_config( array $context ): array {
	$payload = isset( $context['payload'] ) && is_array( $context['payload'] ) ? $context['payload'] : array();
	$atts    = isset( $context['atts'] ) && is_array( $context['atts'] ) ? $context['atts'] : array();
	$horizon = (int) ( $payload['horizon_days'] ?? $atts['horizon_days'] ?? MRT_DISRUPTION_FEED_DEFAULT_HORIZON );

	return array(
		'referenceDate' => (string) ( $payload['reference_date'] ?? '' ),
		'horizonDays'   => max( 1, min( MRT_DISRUPTION_FEED_MAX_HORIZON, $horizon ) ),
		'title'         => trim( (string) ( $atts['title'] ?? '' ) ),
		'labels'        => array(
			'empty'           => __( 'Inga meddelanden', 'museum-railway-timetable' ),
			'loading'         => __( 'Laddar meddelanden…', 'museum-railway-timetable' ),
			'error'           => __( 'Kunde inte ladda meddelanden.', 'museum-railway-timetable' ),
			'sectionOngoing'  => __( 'Pågår nu', 'museum-railway-timetable' ),
			'sectionUpcoming' => __( 'Kommande', 'museum-railway-timetable' ),
		),
	);
}
