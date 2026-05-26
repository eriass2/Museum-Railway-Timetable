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
	$month_names = array();
	for ( $m = 1; $m <= 12; $m++ ) {
		$month_names[] = date_i18n( 'F', mktime( 0, 0, 0, $m, 15, 2020 ) );
	}
	$weekday_abbrev = array();
	$sun            = strtotime( '2024-01-07 UTC' );
	for ( $d = 0; $d < 7; $d++ ) {
		$weekday_abbrev[] = date_i18n( 'D', $sun + $d * DAY_IN_SECONDS );
	}
	return array(
		'monthNames'    => $month_names,
		'weekdayAbbrev' => $weekday_abbrev,
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
		'loading'          => __( 'Loading...', MRT_TEXT_DOMAIN ),
		'errorGeneric'     => __( 'Something went wrong. Please try again.', MRT_TEXT_DOMAIN ),
		'noConnections'    => __( 'No connections on this date.', MRT_TEXT_DOMAIN ),
		'showStops'        => __( 'Visa passerade stationer', MRT_TEXT_DOMAIN ),
		'hideStops'        => __( 'Dölj passerade stationer', MRT_TEXT_DOMAIN ),
		'selectTrip'       => __( 'Välj →', MRT_TEXT_DOMAIN ),
		'noticeLabel'      => __( 'Trafikmeddelande', MRT_TEXT_DOMAIN ),
		'durationMinutes'  => __( '%d min', MRT_TEXT_DOMAIN ),
		'outboundHeading'  => __( 'Utresa', MRT_TEXT_DOMAIN ),
		'returnHeading'    => __( 'Återresa', MRT_TEXT_DOMAIN ),
		'onDate'           => __( 'on %s', MRT_TEXT_DOMAIN ),
		'pleaseStations'   => __( 'Please select both departure and arrival stations.', MRT_TEXT_DOMAIN ),
		'tripTypeSingle'   => __( 'Enkel', MRT_TEXT_DOMAIN ),
		'tripTypeReturn'   => __( 'Tur- och retur', MRT_TEXT_DOMAIN ),
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
		'colService'           => __( 'Service', MRT_TEXT_DOMAIN ),
		'colTrainType'         => __( 'Train Type', MRT_TEXT_DOMAIN ),
		'colDeparture'         => __( 'Departure', MRT_TEXT_DOMAIN ),
		'colArrival'           => __( 'Arrival', MRT_TEXT_DOMAIN ),
		'colStation'           => __( 'Station', MRT_TEXT_DOMAIN ),
		'colActions'           => __( 'Actions', MRT_TEXT_DOMAIN ),
		'calendarGridLabel'    => __( 'Travel dates calendar', MRT_TEXT_DOMAIN ),
		'dayDateOk'            => __( '%s — connection available', MRT_TEXT_DOMAIN ),
		'dayDateTraffic'       => __( '%s — traffic, no direct connection on this route', MRT_TEXT_DOMAIN ),
		'dayDateNone'          => __( '%s — no traffic', MRT_TEXT_DOMAIN ),
		'tripsCaptionOutbound' => __( 'Outbound trips for %s', MRT_TEXT_DOMAIN ),
		'tripsCaptionReturn'   => __( 'Return trips on the same day', MRT_TEXT_DOMAIN ),
		'btnChooseTripAria'    => __( 'Choose this trip: %1$s, departure %2$s, arrival %3$s', MRT_TEXT_DOMAIN ),
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
		'priceTableTypeColumn' => __( 'Ticket type', MRT_TEXT_DOMAIN ),
		'priceTitle'           => __( 'Priser', MRT_TEXT_DOMAIN ),
		'priceNote'            => __( 'Price is based on the cheapest valid zone count for the selected journey.', MRT_TEXT_DOMAIN ),
		'priceDash'            => '—',
		'priceMatrix'          => MRT_price_matrix_for_zone( MRT_get_price_matrix(), 4 ),
		'priceMatrixByZone'    => MRT_get_price_matrix(),
		'priceStationZones'    => MRT_get_station_price_zones_map(),
		'priceZoneLabel'       => __( '%d zones', MRT_TEXT_DOMAIN ),
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
			'ajaxurl'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'mrt_frontend' ),
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
	$flags['has_overview'] = (bool) apply_filters( 'mrt_should_enqueue_frontend_overview_css', $flags['has_overview'] );
	return $flags;
}

/**
 * Empty frontend shortcode flags.
 *
 * @return array{has_any: bool, has_overview: bool, has_journey_wizard: bool}
 */
function MRT_empty_frontend_shortcode_flags(): array {
	return array(
		'has_any'            => false,
		'has_overview'       => false,
		'has_journey_wizard' => false,
	);
}

/**
 * Detect frontend shortcode flags from content.
 *
 * @return array{has_any: bool, has_overview: bool, has_journey_wizard: bool}
 */
function MRT_frontend_shortcode_flags_from_content( string $content ): array {
	$flags      = MRT_empty_frontend_shortcode_flags();
	$shortcodes = array( 'museum_timetable_month', 'museum_timetable_overview', 'museum_journey_wizard' );
	foreach ( $shortcodes as $shortcode ) {
		if ( ! has_shortcode( $content, $shortcode ) ) {
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
 * Shared public CSS for month calendar, tables, and alerts.
 *
 * @return string Style handle
 */
function MRT_enqueue_frontend_public_styles(): string {
	$a           = MRT_assets_base_url();
	$icon_handle = MRT_enqueue_train_type_icon_styles();
	wp_enqueue_style(
		'mrt-frontend-public',
		$a . 'frontend-public.css',
		array( $icon_handle ),
		MRT_VERSION
	);
	return 'mrt-frontend-public';
}

/**
 * Timetable overview grid styles (overview shortcode and wizard timetable drawer).
 */
function MRT_enqueue_frontend_overview_styles( string $public_handle ): void {
	$a = MRT_assets_base_url();
	wp_enqueue_style(
		'mrt-frontend-overview',
		$a . 'frontend-overview.css',
		array( $public_handle ),
		MRT_VERSION
	);
}

/**
 * Enqueue stacked frontend CSS (shared shortcode bundle).
 *
 * @return string Public bundle handle for dependent styles (e.g. wizard).
 */
function MRT_enqueue_frontend_shortcode_styles( bool $has_overview_shortcode ): string {
	$public_handle = MRT_enqueue_frontend_public_styles();
	if ( $has_overview_shortcode ) {
		MRT_enqueue_frontend_overview_styles( $public_handle );
	}
	return $public_handle;
}

/**
 * Enqueue shared frontend JS and mrtFrontend localization.
 */
function MRT_enqueue_frontend_base_scripts(): void {
	$a = MRT_assets_base_url();
	wp_register_script( 'mrt-string-utils', $a . 'mrt-string-utils.js', array(), MRT_VERSION, true );
	wp_register_script( 'mrt-frontend-api', $a . 'mrt-frontend-api.js', array( 'jquery' ), MRT_VERSION, true );
	wp_enqueue_script( 'mrt-frontend', $a . 'frontend.js', array( 'jquery', 'mrt-string-utils', 'mrt-frontend-api' ), MRT_VERSION, true );
	wp_localize_script( 'mrt-frontend', 'mrtFrontend', MRT_frontend_script_localization() );
}

/**
 * Localized strings for the shared frontend script.
 *
 * @return array<string, string>
 */
function MRT_frontend_script_localization(): array {
	return array(
		'ajaxurl'           => admin_url( 'admin-ajax.php' ),
		'nonce'             => wp_create_nonce( 'mrt_frontend' ),
		'search'            => __( 'Search', MRT_TEXT_DOMAIN ),
		'searching'         => __( 'Searching...', MRT_TEXT_DOMAIN ),
		'loading'           => __( 'Loading...', MRT_TEXT_DOMAIN ),
		'errorSearching'    => __( 'Error searching for connections.', MRT_TEXT_DOMAIN ),
		'errorLoading'      => __( 'Error loading timetable.', MRT_TEXT_DOMAIN ),
		'errorSameStations' => __( 'Please select different stations for departure and arrival.', MRT_TEXT_DOMAIN ),
		'networkError'      => __( 'Network error. Please try again.', MRT_TEXT_DOMAIN ),
	);
}

/**
 * Register journey wizard JS modules (load order = dependency chain).
 *
 * @param string $assets_url Plugin assets base URL.
 * @return string Handle of the bootstrap script (last in chain).
 */
function MRT_register_journey_wizard_script_modules( string $assets_url ): string {
	$dir     = $assets_url . 'journey-wizard/';
	$shared  = array( 'jquery', 'mrt-string-utils', 'mrt-date-utils', 'mrt-frontend-api' );
	$modules = array(
		'mrt-jw-namespace' => 'namespace.js',
		'mrt-jw-constants' => 'constants.js',
		'mrt-jw-connection' => 'connection.js',
		'mrt-jw-context'   => 'context.js',
		'mrt-jw-prices'    => 'prices.js',
		'mrt-jw-vehicle'   => 'vehicle.js',
		'mrt-jw-calendar'  => 'calendar.js',
		'mrt-jw-trip-card' => 'trip-card.js',
		'mrt-jw-detail'    => 'connection-detail.js',
		'mrt-jw-summary'   => 'summary.js',
		'mrt-jw-runtime'   => 'runtime.js',
		'mrt-jw-events'    => 'events.js',
	);
	$prev    = $shared;
	foreach ( $modules as $handle => $file ) {
		wp_register_script( $handle, $dir . $file, $prev, MRT_VERSION, true );
		$prev = array( $handle );
	}
	wp_register_script(
		'mrt-journey-wizard',
		$dir . 'bootstrap.js',
		array( 'mrt-jw-events', 'mrt-frontend' ),
		MRT_VERSION,
		true
	);
	return 'mrt-journey-wizard';
}

/**
 * Enqueue journey wizard CSS/JS and localization (depends on frontend bundle).
 *
 * @param string $public_handle Enqueued public shortcode styles.
 */
function MRT_enqueue_journey_wizard_assets( string $public_handle ): void {
	$a = MRT_assets_base_url();
	wp_enqueue_style(
		'mrt-journey-wizard',
		$a . 'journey-wizard.css',
		array( $public_handle ),
		MRT_VERSION
	);
	wp_register_script( 'mrt-date-utils', $a . 'mrt-date-utils.js', array(), MRT_VERSION, true );
	$wizard_handle = MRT_register_journey_wizard_script_modules( $a );
	if ( MRT_is_development_mode() ) {
		wp_register_script(
			'mrt-jw-debug',
			$a . 'journey-wizard/debug.js',
			array( 'mrt-journey-wizard' ),
			MRT_VERSION,
			true
		);
	}
	wp_enqueue_script( $wizard_handle );
	if ( MRT_is_development_mode() ) {
		wp_enqueue_script( 'mrt-jw-debug' );
	}
	wp_localize_script( $wizard_handle, 'mrtJourneyWizard', MRT_journey_wizard_script_localization() );
}

/**
 * Enqueue frontend assets for shortcodes.
 */
function MRT_enqueue_frontend_assets(): void {
	$flags = MRT_frontend_shortcode_flags_from_post();
	if ( ! $flags['has_any'] ) {
		return;
	}
	$public_handle = MRT_enqueue_frontend_shortcode_styles( $flags['has_overview'] );
	MRT_enqueue_frontend_base_scripts();
	if ( $flags['has_journey_wizard'] ) {
		MRT_enqueue_journey_wizard_assets( $public_handle );
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
	if ( $flags['has_any'] ) {
		$classes[] = 'mrt-has-shortcodes';
	}
	return $classes;
}
add_filter( 'body_class', 'MRT_frontend_body_class' );
add_action( 'wp_enqueue_scripts', 'MRT_enqueue_frontend_assets' );
