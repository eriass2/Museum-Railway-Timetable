<?php
/**
 * Shortcode: Month view [museum_timetable_month] — Vue mount + context builder.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Apply ?mrt_month=YYYY-MM from the request (for month navigation links)
 *
 * @param array<string, mixed> $atts Shortcode attributes
 * @return array<string, mixed>
 */
function MRT_month_shortcode_apply_query_month( array $atts ) {
	if ( empty( $_GET['mrt_month'] ) || ! is_string( $_GET['mrt_month'] ) ) {
		return $atts;
	}
	$gm = sanitize_text_field( wp_unslash( $_GET['mrt_month'] ) );
	if ( ! preg_match( '/^(\d{4})-(\d{2})$/', $gm, $m ) ) {
		return $atts;
	}
	$y  = (int) $m[1];
	$mo = (int) $m[2];
	if ( $y < 1970 || $y > 2100 || $mo < 1 || $mo > 12 ) {
		return $atts;
	}
	$atts['month'] = sprintf( '%04d-%02d', $y, $mo );

	return $atts;
}

/**
 * Prev/next month URLs for shortcode navigation (preserves other query args)
 *
 * @param int|false $first_ts Timestamp of first day of displayed month
 * @return array{0:string,1:string}
 */
function MRT_month_shortcode_nav_link_urls( $first_ts ) {
	if ( false === $first_ts ) {
		$t  = current_time( 'timestamp' );
		$ym = date( 'Y-m', $t );
		$u  = add_query_arg( 'mrt_month', $ym, home_url( '/' ) );

		return array( $u, $u );
	}
	$prev_ts = strtotime( '-1 month', $first_ts );
	$next_ts = strtotime( '+1 month', $first_ts );
	if ( false === $prev_ts ) {
		$prev_ts = $first_ts;
	}
	if ( false === $next_ts ) {
		$next_ts = $first_ts;
	}

	return array(
		add_query_arg( 'mrt_month', date( 'Y-m', $prev_ts ) ),
		add_query_arg( 'mrt_month', date( 'Y-m', $next_ts ) ),
	);
}

/**
 * Build per-day running/count data for the month grid
 *
 * @param int                  $year Year
 * @param int                  $month Month 1–12
 * @param int                  $daysInMonth Length of month
 * @param array<string, mixed> $atts Shortcode atts
 * @return array<int, array{ymd:string,count:int,running:bool}>
 */
function MRT_month_shortcode_collect_day_meta( int $year, int $month, int $daysInMonth, array $atts ): array {
	$dates = array();
	for ( $d = 1; $d <= $daysInMonth; $d++ ) {
		$ymd         = sprintf( '%04d-%02d-%02d', $year, $month, $d );
		$service_ids = MRT_services_running_on_date( $ymd, $atts['train_type'], $atts['service'] );
		$dates[ $d ] = array(
			'ymd'     => $ymd,
			'count'   => count( $service_ids ),
			'running' => ! empty( $service_ids ),
		);
	}

	return $dates;
}

/**
 * Resolve calendar month start timestamp from shortcode atts.
 *
 * @param array<string, mixed> $atts
 * @return int|false
 */
function MRT_month_shortcode_resolve_month_start( array $atts, int $now_ts ) {
	if ( ! empty( $atts['month'] ) && preg_match( '/^\d{4}-\d{2}$/', (string) $atts['month'] ) ) {
		$firstDay = $atts['month'] . '-01';
		$first_ts = strtotime( $firstDay . ' 00:00:00', $now_ts );
		if ( false === $first_ts ) {
			return strtotime( date( 'Y-m-01', $now_ts ) );
		}
		return $first_ts;
	}
	return strtotime( date( 'Y-m-01', $now_ts ) );
}

/**
 * Parse shortcode atts and build month view render context.
 *
 * @param array|string $atts Shortcode attributes
 * @return array<string, mixed>|string Context array, or error HTML string
 */
function MRT_month_shortcode_build_context( $atts ) {
	$atts = shortcode_atts(
		array(
			'month'        => '',
			'train_type'   => '',
			'service'      => '',
			'legend'       => 1,
			'show_counts'  => 1,
			'start_monday' => 1,
			'nav'          => 1,
		),
		$atts,
		'museum_timetable_month'
	);

	$atts = MRT_month_shortcode_apply_query_month( $atts );

	$datetime = MRT_get_current_datetime();
	$now_ts   = $datetime['timestamp'];
	$first_ts = MRT_month_shortcode_resolve_month_start( $atts, $now_ts );

	if ( false === $first_ts ) {
		return MRT_render_alert( __( 'Invalid date.', 'museum-railway-timetable' ), 'error' );
	}

	$year        = (int) date( 'Y', $first_ts );
	$month       = (int) date( 'm', $first_ts );
	$daysInMonth = (int) date( 't', $first_ts );
	if ( $year <= 0 || $month <= 0 || $month > 12 || $daysInMonth <= 0 ) {
		return MRT_render_alert( __( 'Invalid date.', 'museum-railway-timetable' ), 'error' );
	}

	return array(
		'first_ts'      => $first_ts,
		'atts'          => $atts,
		'dates'         => MRT_month_shortcode_collect_day_meta( $year, $month, $daysInMonth, $atts ),
		'daysInMonth'   => $daysInMonth,
		'weekdayFirst'  => (int) date( 'N', $first_ts ),
		'startMonday'   => ! empty( $atts['start_monday'] ),
		'month_uid'     => wp_unique_id( 'mrtmonth' ),
		'month_title'   => date_i18n( 'F Y', $first_ts ),
	);
}

/**
 * Render month view shortcode (Vue).
 *
 * @param array|string $atts Shortcode attributes
 * @return string HTML
 */
function MRT_render_shortcode_month( $atts ) {
	$context = MRT_month_shortcode_build_context( $atts );
	if ( is_string( $context ) ) {
		return $context;
	}

	return MRT_render_vue_mount( 'month', MRT_vue_month_config( $context ) );
}
