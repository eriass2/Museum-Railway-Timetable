<?php

declare(strict_types=1);

/**
 * Frontend asset enqueuing for Museum Railway Timetable.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Month names and weekday abbreviations for the journey wizard calendar.
 *
 * @return array{monthNames: array<int, string>, weekdayAbbrev: array<int, string>}
 */
function MRT_journey_wizard_calendar_i18n_arrays(): array {
	return array(
		'monthNames'    => array(
			__( 'januari', 'museum-railway-timetable' ),
			__( 'februari', 'museum-railway-timetable' ),
			__( 'mars', 'museum-railway-timetable' ),
			__( 'april', 'museum-railway-timetable' ),
			__( 'maj', 'museum-railway-timetable' ),
			__( 'juni', 'museum-railway-timetable' ),
			__( 'juli', 'museum-railway-timetable' ),
			__( 'augusti', 'museum-railway-timetable' ),
			__( 'september', 'museum-railway-timetable' ),
			__( 'oktober', 'museum-railway-timetable' ),
			__( 'november', 'museum-railway-timetable' ),
			__( 'december', 'museum-railway-timetable' ),
		),
		'weekdayAbbrev' => array(
			__( 'sön', 'museum-railway-timetable' ),
			__( 'mån', 'museum-railway-timetable' ),
			__( 'tis', 'museum-railway-timetable' ),
			__( 'ons', 'museum-railway-timetable' ),
			__( 'tor', 'museum-railway-timetable' ),
			__( 'fre', 'museum-railway-timetable' ),
			__( 'lör', 'museum-railway-timetable' ),
		),
	);
}

/**
 * Wizard step labels and connection UI strings.
 *
 * @return array<string, string>
 */
function MRT_journey_wizard_l10n_steps_and_trip(): array {
	return array(
		'stepRoute'        => __( 'Sök resa', 'museum-railway-timetable' ),
		'stepDate'         => __( 'Välj datum', 'museum-railway-timetable' ),
		'stepOutbound'     => __( 'Välj utresa', 'museum-railway-timetable' ),
		'stepReturn'       => __( 'Välj återresa', 'museum-railway-timetable' ),
		'stepSummary'      => __( 'Din resa', 'museum-railway-timetable' ),
		'loading'          => __( 'Laddar...', 'museum-railway-timetable' ),
		'errorGeneric'     => __( 'Något gick fel. Försök igen.', 'museum-railway-timetable' ),
		'noConnections'    => __( 'Inga anslutningar detta datum.', 'museum-railway-timetable' ),
		'showStops'        => __( 'Visa passerade stationer', 'museum-railway-timetable' ),
		'hideStops'        => __( 'Dölj passerade stationer', 'museum-railway-timetable' ),
		'defaultTrainType' => __( 'Tåg', 'museum-railway-timetable' ),
		'selectTrip'       => __( 'Välj →', 'museum-railway-timetable' ),
		'noticeLabel'      => __( 'Trafikmeddelande', 'museum-railway-timetable' ),
		'durationMinutes'  => __( '%d min', 'museum-railway-timetable' ),
		'outboundHeading'  => __( 'Utresa', 'museum-railway-timetable' ),
		'returnHeading'    => __( 'Återresa', 'museum-railway-timetable' ),
		'onDate'           => __( 'den %s', 'museum-railway-timetable' ),
		'pleaseStations'   => __( 'Välj både avrese- och ankomststation.', 'museum-railway-timetable' ),
		'tripTypeSingle'   => __( 'Enkel resa', 'museum-railway-timetable' ),
		'tripTypeReturn'   => __( 'Tur och retur', 'museum-railway-timetable' ),
		'routeContext'     => __( '%1$s → %2$s | %3$s', 'museum-railway-timetable' ),
		'routeDateContext' => __( '%1$s → %2$s | %3$s\n%4$s', 'museum-railway-timetable' ),
		'directTrip'       => __( 'Direktresa', 'museum-railway-timetable' ),
		'transferTrip'     => __( 'Byte', 'museum-railway-timetable' ),
		'selectedOutbound' => __( 'Vald utresa', 'museum-railway-timetable' ),
		'towards'          => __( 'mot %s', 'museum-railway-timetable' ),
		'changeAt'         => __( 'Byte vid %s', 'museum-railway-timetable' ),
		'transferWait'     => __( '%d min byte', 'museum-railway-timetable' ),
		'passedStations'   => __( 'passerade stationer', 'museum-railway-timetable' ),
	);
}

/**
 * Table column labels, calendar day strings, and trip captions.
 *
 * @return array<string, string>
 */
function MRT_journey_wizard_l10n_table_calendar(): array {
	return array(
		'colService'           => __( 'Tåg', 'museum-railway-timetable' ),
		'colTrainType'         => __( 'Fordonstyp', 'museum-railway-timetable' ),
		'colDeparture'         => __( 'Avgång', 'museum-railway-timetable' ),
		'colArrival'           => __( 'Ankomst', 'museum-railway-timetable' ),
		'colStation'           => __( 'Station', 'museum-railway-timetable' ),
		'colActions'           => __( 'Åtgärder', 'museum-railway-timetable' ),
		'calendarGridLabel'    => __( 'Kalender för resdatum', 'museum-railway-timetable' ),
		'dayDateOk'            => __( '%s — anslutning finns', 'museum-railway-timetable' ),
		'dayDateTraffic'       => __( '%s — trafik, ingen direktanslutning på rutten', 'museum-railway-timetable' ),
		'dayDateNone'          => __( '%s — ingen trafik', 'museum-railway-timetable' ),
		'tripsCaptionOutbound' => __( 'Utresor den %s', 'museum-railway-timetable' ),
		'tripsCaptionReturn'   => __( 'Återresor samma dag', 'museum-railway-timetable' ),
		'btnChooseTripAria'    => __( 'Välj resa: %1$s, avgång %2$s, ankomst %3$s', 'museum-railway-timetable' ),
		'btnShowStopsAria'     => __( 'Visa hållplatser och tider för %s', 'museum-railway-timetable' ),
		'legSegmentLabel'      => __( 'Delsträcka %d', 'museum-railway-timetable' ),
	);
}

/**
 * Price matrix labels for the wizard summary.
 *
 * @return array<string, mixed>
 */
function MRT_journey_wizard_l10n_price(): array {
	return array(
		'priceTableTypeColumn' => __( 'Biljettyp', 'museum-railway-timetable' ),
		'priceTitle'           => __( 'Priser', 'museum-railway-timetable' ),
		'priceNote'            => __( 'Priset bygger på lägsta giltiga zontal för den valda resan (max tre zoner enligt taxa 2026). Pensionär gäller från 65 år.', 'museum-railway-timetable' ),
		'priceDash'            => '—',
		'priceMatrix'          => MRT_price_matrix_for_zone( MRT_get_price_matrix(), MRT_price_zone_cap() ),
		'priceMatrixByZone'    => MRT_get_price_matrix(),
		'priceStationZones'    => MRT_get_station_price_zones_map(),
		'afternoonReturnPrices' => MRT_get_afternoon_return_prices(),
		'priceDayTitle'        => __( 'Heldagsbiljett', 'museum-railway-timetable' ),
		'priceAfternoonReturnLabel' => __( 'Eftermiddagsbiljett (tur och retur)', 'museum-railway-timetable' ),
		'priceAfternoonNote'   => __( 'Eftermiddagsbiljett gäller tur och retur med avgång efter kl 15 (ej evenemangsdagar).', 'museum-railway-timetable' ),
		'priceZoneLabel'       => __( '%d zoner', 'museum-railway-timetable' ),
		'priceTickets'         => MRT_price_ticket_type_labels(),
		'priceCategories'      => MRT_price_category_labels(),
	);
}

/**
 * Localized strings and labels for [museum_journey_wizard] script.
 *
 * @return array<string, mixed>
 */
function MRT_journey_wizard_script_localization(): array {
	$cal = MRT_journey_wizard_calendar_i18n_arrays();
	$l10n = array_merge(
		array(
			'monthNames'     => $cal['monthNames'],
			'weekdayAbbrev'  => $cal['weekdayAbbrev'],
			'trainTypeIcons'     => MRT_train_type_icon_urls(),
			'trainTypeSlugIcons' => MRT_train_type_slug_icon_map(),
		),
		MRT_journey_wizard_l10n_steps_and_trip(),
		MRT_journey_wizard_l10n_table_calendar(),
		MRT_journey_wizard_l10n_price()
	);
	if ( MRT_is_development_mode() && function_exists( 'MRT_journey_wizard_debug_presets' ) ) {
		$l10n['debugPresets'] = MRT_journey_wizard_debug_presets();
	}
	return $l10n;
}

/**
 * Detect which frontend shortcodes are present in post content.
 *
 * @return array{has_any: bool, has_overview: bool, has_journey_wizard: bool, has_timetable_index: bool}
 */
function MRT_frontend_shortcode_flags_from_post(): array {
	global $post;
	$flags = MRT_empty_frontend_shortcode_flags();

	if ( is_a( $post, 'WP_Post' ) && ! empty( $post->post_content ) ) {
		$flags = MRT_frontend_shortcode_flags_from_content( $post->post_content );
	}
	if ( ! $flags['has_any'] ) {
		$flags['has_any'] = (bool) apply_filters( 'mrt_should_enqueue_frontend_assets', false );
	}
	if ( $flags['has_journey_wizard'] ) {
		$flags['has_overview'] = true;
	}
	return $flags;
}

/**
 * Empty frontend shortcode flags.
 *
 * @return array{has_any: bool, has_overview: bool, has_journey_wizard: bool, has_timetable_index: bool}
 */
function MRT_empty_frontend_shortcode_flags(): array {
	return array(
		'has_any'             => false,
		'has_overview'        => false,
		'has_journey_wizard'  => false,
		'has_timetable_index' => false,
	);
}

/**
 * Detect frontend shortcode flags from content.
 *
 * @return array{has_any: bool, has_overview: bool, has_journey_wizard: bool, has_timetable_index: bool}
 */
function MRT_frontend_shortcode_flags_from_content( string $content ): array {
	$flags      = MRT_empty_frontend_shortcode_flags();
	$shortcodes = array( 'museum_timetable_month', 'museum_timetable_overview', 'museum_journey_wizard', 'museum_timetable_index' );
	foreach ( $shortcodes as $shortcode ) {
		if ( ! has_shortcode( $content, $shortcode ) ) {
			continue;
		}
		if ( $shortcode === 'museum_timetable_index' ) {
			$flags['has_timetable_index'] = true;
			continue;
		}
		$flags['has_any'] = true;
		if ( $shortcode === 'museum_timetable_overview' ) {
			$flags['has_overview'] = true;
		}
		if ( $shortcode === 'museum_journey_wizard' ) {
			$flags['has_journey_wizard'] = true;
		}
	}
	return $flags;
}

/**
 * Styles for [museum_timetable_index] (PHP-only shortcode).
 */
function MRT_enqueue_timetable_index_styles(): void {
	$a = MRT_assets_base_url();
	wp_enqueue_style(
		'mrt-color-tokens',
		$a . 'mrt-color-tokens.css',
		array(),
		MRT_VERSION
	);
	wp_enqueue_style(
		'mrt-typography',
		$a . 'mrt-typography.css',
		array( 'mrt-color-tokens' ),
		MRT_VERSION
	);
	wp_enqueue_style(
		'mrt-frontend-base',
		$a . 'frontend/components-base.css',
		array( 'mrt-color-tokens', 'mrt-typography' ),
		MRT_VERSION
	);
	wp_enqueue_style(
		'mrt-public-layout',
		$a . 'frontend/public-layout.css',
		array(),
		MRT_VERSION
	);
	wp_enqueue_style(
		'mrt-timetable-index',
		$a . 'frontend/timetable-index.css',
		array( 'mrt-color-tokens', 'mrt-frontend-base' ),
		MRT_VERSION
	);
}

/**
 * Enqueue index styles once per request.
 */
function MRT_enqueue_timetable_index_styles_if_needed(): void {
	if ( wp_style_is( 'mrt-timetable-index', 'enqueued' ) || wp_style_is( 'mrt-timetable-index', 'done' ) ) {
		return;
	}
	MRT_enqueue_timetable_index_styles();
}

/**
 * Localized strings for the Vue public frontend (REST).
 *
 * @return array<string, string>
 */
function MRT_frontend_script_localization(): array {
	return array(
		'search'            => __( 'Sök', 'museum-railway-timetable' ),
		'searching'         => __( 'Söker...', 'museum-railway-timetable' ),
		'loading'           => __( 'Laddar...', 'museum-railway-timetable' ),
		'errorSearching'    => __( 'Kunde inte söka anslutningar.', 'museum-railway-timetable' ),
		'errorLoading'      => __( 'Kunde inte ladda tidtabellen.', 'museum-railway-timetable' ),
		'errorSameStations' => __( 'Välj olika stationer för avresa och ankomst.', 'museum-railway-timetable' ),
		'networkError'      => __( 'Nätverksfel. Försök igen.', 'museum-railway-timetable' ),
		'ovPrintKeyTitle'       => __( 'Förklaringar', 'museum-railway-timetable' ),
		'ovPrintKeySymbolCol'   => __( 'Tecken', 'museum-railway-timetable' ),
		'ovPrintKeyMeaningCol'  => __( 'Betydelse', 'museum-railway-timetable' ),
		'ovPrintKeyNote'        => __( 'Med reservation för ändring av tågtyp.', 'museum-railway-timetable' ),
		'ovDeviationPlanned'    => __( 'Planerat: %s', 'museum-railway-timetable' ),
		'ovDeviationFromPlan'   => __( 'Avvikelse från planerad tågtyp', 'museum-railway-timetable' ),
		'ovDeparturesAria'      => __( 'Avgångar %s', 'museum-railway-timetable' ),
		'ovBranchNote'          => __( 'Anslutningsbuss', 'museum-railway-timetable' ),
		'ovColTrip'             => __( 'Tur', 'museum-railway-timetable' ),
		'ovColConnectingTrain'  => __( 'Anslutande tåg', 'museum-railway-timetable' ),
		'ovTrainConnecting'     => __( 'Tåg %1$s %2$s', 'museum-railway-timetable' ),
		'ovCardTrip'            => __( 'Tur %s', 'museum-railway-timetable' ),
	);
}

/**
 * Enqueue frontend assets for shortcodes (Vue bundle only).
 */
function MRT_enqueue_frontend_assets(): void {
	$flags = MRT_frontend_shortcode_flags_from_post();
	if ( $flags['has_timetable_index'] ) {
		MRT_enqueue_timetable_index_styles_if_needed();
	}
	if ( $flags['has_any'] ) {
		MRT_enqueue_vue_frontend_assets_if_needed();
	}
}

/**
 * Enqueue Vue assets when shortcodes rendered after wp_enqueue_scripts (e.g. block themes).
 */
function MRT_enqueue_frontend_assets_late(): void {
	if ( MRT_vue_shortcode_was_used() ) {
		MRT_enqueue_vue_frontend_assets_if_needed();
	}
	if ( function_exists( 'MRT_timetable_index_was_used' ) && MRT_timetable_index_was_used() ) {
		MRT_enqueue_timetable_index_styles_if_needed();
	}
}
/**
 * Widen theme content area on pages that use plugin shortcodes.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function MRT_frontend_body_class( array $classes ): array {
	$flags = MRT_frontend_shortcode_flags_from_post();
	if ( $flags['has_any'] || $flags['has_timetable_index'] ) {
		$classes[] = 'mrt-has-shortcodes';
	}
	if ( $flags['has_overview'] ) {
		$classes[] = 'mrt-has-overview';
	}
	return $classes;
}
add_filter( 'body_class', 'MRT_frontend_body_class' );
add_action( 'wp_enqueue_scripts', 'MRT_enqueue_frontend_assets' );
add_action( 'wp_footer', 'MRT_enqueue_frontend_assets_late', 1 );
