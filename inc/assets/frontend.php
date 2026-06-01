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
			__( 'januari', MRT_TEXT_DOMAIN ),
			__( 'februari', MRT_TEXT_DOMAIN ),
			__( 'mars', MRT_TEXT_DOMAIN ),
			__( 'april', MRT_TEXT_DOMAIN ),
			__( 'maj', MRT_TEXT_DOMAIN ),
			__( 'juni', MRT_TEXT_DOMAIN ),
			__( 'juli', MRT_TEXT_DOMAIN ),
			__( 'augusti', MRT_TEXT_DOMAIN ),
			__( 'september', MRT_TEXT_DOMAIN ),
			__( 'oktober', MRT_TEXT_DOMAIN ),
			__( 'november', MRT_TEXT_DOMAIN ),
			__( 'december', MRT_TEXT_DOMAIN ),
		),
		'weekdayAbbrev' => array(
			__( 'sön', MRT_TEXT_DOMAIN ),
			__( 'mån', MRT_TEXT_DOMAIN ),
			__( 'tis', MRT_TEXT_DOMAIN ),
			__( 'ons', MRT_TEXT_DOMAIN ),
			__( 'tor', MRT_TEXT_DOMAIN ),
			__( 'fre', MRT_TEXT_DOMAIN ),
			__( 'lör', MRT_TEXT_DOMAIN ),
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
		'stepRoute'        => __( 'Sök resa', MRT_TEXT_DOMAIN ),
		'stepDate'         => __( 'Datum', MRT_TEXT_DOMAIN ),
		'stepOutbound'     => __( 'Utresa', MRT_TEXT_DOMAIN ),
		'stepReturn'       => __( 'Återresa', MRT_TEXT_DOMAIN ),
		'stepSummary'      => __( 'Sammanfattning', MRT_TEXT_DOMAIN ),
		'loading'          => __( 'Laddar...', MRT_TEXT_DOMAIN ),
		'errorGeneric'     => __( 'Något gick fel. Försök igen.', MRT_TEXT_DOMAIN ),
		'noConnections'    => __( 'Inga anslutningar detta datum.', MRT_TEXT_DOMAIN ),
		'showStops'        => __( 'Visa passerade stationer', MRT_TEXT_DOMAIN ),
		'hideStops'        => __( 'Dölj passerade stationer', MRT_TEXT_DOMAIN ),
		'selectTrip'       => __( 'Välj →', MRT_TEXT_DOMAIN ),
		'noticeLabel'      => __( 'Trafikmeddelande', MRT_TEXT_DOMAIN ),
		'durationMinutes'  => __( '%d min', MRT_TEXT_DOMAIN ),
		'outboundHeading'  => __( 'Utresa', MRT_TEXT_DOMAIN ),
		'returnHeading'    => __( 'Återresa', MRT_TEXT_DOMAIN ),
		'onDate'           => __( 'den %s', MRT_TEXT_DOMAIN ),
		'pleaseStations'   => __( 'Välj både avrese- och ankomststation.', MRT_TEXT_DOMAIN ),
		'tripTypeSingle'   => __( 'Enkel resa', MRT_TEXT_DOMAIN ),
		'tripTypeReturn'   => __( 'Tur och retur', MRT_TEXT_DOMAIN ),
		'routeContext'     => __( '%1$s → %2$s | %3$s', MRT_TEXT_DOMAIN ),
		'routeDateContext' => __( '%1$s → %2$s | %3$s\n%4$s', MRT_TEXT_DOMAIN ),
		'directTrip'       => __( 'Direktresa', MRT_TEXT_DOMAIN ),
		'transferTrip'     => __( 'Byte', MRT_TEXT_DOMAIN ),
		'selectedOutbound' => __( 'Vald utresa', MRT_TEXT_DOMAIN ),
		'towards'          => __( 'mot %s', MRT_TEXT_DOMAIN ),
		'changeAt'         => __( 'Byte vid %s', MRT_TEXT_DOMAIN ),
		'transferWait'     => __( '%d min byte', MRT_TEXT_DOMAIN ),
		'passedStations'   => __( 'passerade stationer', MRT_TEXT_DOMAIN ),
	);
}

/**
 * Table column labels, calendar day strings, and trip captions.
 *
 * @return array<string, string>
 */
function MRT_journey_wizard_l10n_table_calendar(): array {
	return array(
		'colService'           => __( 'Tåg', MRT_TEXT_DOMAIN ),
		'colTrainType'         => __( 'Fordonstyp', MRT_TEXT_DOMAIN ),
		'colDeparture'         => __( 'Avgång', MRT_TEXT_DOMAIN ),
		'colArrival'           => __( 'Ankomst', MRT_TEXT_DOMAIN ),
		'colStation'           => __( 'Station', MRT_TEXT_DOMAIN ),
		'colActions'           => __( 'Åtgärder', MRT_TEXT_DOMAIN ),
		'calendarGridLabel'    => __( 'Kalender för resdatum', MRT_TEXT_DOMAIN ),
		'dayDateOk'            => __( '%s — anslutning finns', MRT_TEXT_DOMAIN ),
		'dayDateTraffic'       => __( '%s — trafik, ingen direktanslutning på rutten', MRT_TEXT_DOMAIN ),
		'dayDateNone'          => __( '%s — ingen trafik', MRT_TEXT_DOMAIN ),
		'tripsCaptionOutbound' => __( 'Utresor den %s', MRT_TEXT_DOMAIN ),
		'tripsCaptionReturn'   => __( 'Återresor samma dag', MRT_TEXT_DOMAIN ),
		'btnChooseTripAria'    => __( 'Välj resa: %1$s, avgång %2$s, ankomst %3$s', MRT_TEXT_DOMAIN ),
		'btnShowStopsAria'     => __( 'Visa hållplatser och tider för %s', MRT_TEXT_DOMAIN ),
		'legSegmentLabel'      => __( 'Delsträcka %d', MRT_TEXT_DOMAIN ),
	);
}

/**
 * Price matrix labels for the wizard summary.
 *
 * @return array<string, mixed>
 */
function MRT_journey_wizard_l10n_price(): array {
	return array(
		'priceTableTypeColumn' => __( 'Biljettyp', MRT_TEXT_DOMAIN ),
		'priceTitle'           => __( 'Priser', MRT_TEXT_DOMAIN ),
		'priceNote'            => __( 'Priset bygger på lägsta giltiga zontal för den valda resan (max tre zoner enligt taxa 2026). Pensionär gäller från 65 år.', MRT_TEXT_DOMAIN ),
		'priceDash'            => '—',
		'priceMatrix'          => MRT_price_matrix_for_zone( MRT_get_price_matrix(), MRT_price_zone_cap() ),
		'priceMatrixByZone'    => MRT_get_price_matrix(),
		'priceStationZones'    => MRT_get_station_price_zones_map(),
		'afternoonReturnPrices' => MRT_get_afternoon_return_prices(),
		'priceDayTitle'        => __( 'Heldagsbiljett', MRT_TEXT_DOMAIN ),
		'priceAfternoonReturnLabel' => __( 'Eftermiddagsbiljett (tur och retur)', MRT_TEXT_DOMAIN ),
		'priceAfternoonNote'   => __( 'Eftermiddagsbiljett gäller tur och retur med avgång efter kl 15 (ej evenemangsdagar).', MRT_TEXT_DOMAIN ),
		'priceZoneLabel'       => __( '%d zoner', MRT_TEXT_DOMAIN ),
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
 * @return array{has_any: bool, has_overview: bool, has_journey_wizard: bool}
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
 * @return array{has_any: bool, has_overview: bool, has_journey_wizard: bool}
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
 * @return array{has_any: bool, has_overview: bool, has_journey_wizard: bool}
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
		'search'            => __( 'Sök', MRT_TEXT_DOMAIN ),
		'searching'         => __( 'Söker...', MRT_TEXT_DOMAIN ),
		'loading'           => __( 'Laddar...', MRT_TEXT_DOMAIN ),
		'errorSearching'    => __( 'Kunde inte söka anslutningar.', MRT_TEXT_DOMAIN ),
		'errorLoading'      => __( 'Kunde inte ladda tidtabellen.', MRT_TEXT_DOMAIN ),
		'errorSameStations' => __( 'Välj olika stationer för avresa och ankomst.', MRT_TEXT_DOMAIN ),
		'networkError'      => __( 'Nätverksfel. Försök igen.', MRT_TEXT_DOMAIN ),
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
