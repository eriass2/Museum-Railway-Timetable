<?php
/**
 * Hardcoded journey wizard presets for development UI pages.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/import/csv/fixture-read.php';

/**
 * Resolve Lennakatten demo station pair (after import).
 *
 * @return array{from: int, to: int, from_title: string, to_title: string}
 */
function MRT_debug_lennakatten_station_pair(): array {
	$pair = array(
		'from'        => 0,
		'to'          => 0,
		'from_title'  => 'Uppsala Östra',
		'to_title'    => 'Faringe',
	);
	foreach ( MRT_get_all_stations() as $station_id ) {
		$title = get_the_title( (int) $station_id );
		if ( $title === $pair['from_title'] ) {
			$pair['from'] = (int) $station_id;
		}
		if ( $title === $pair['to_title'] ) {
			$pair['to'] = (int) $station_id;
		}
	}
	return $pair;
}

/**
 * Example traffic date from Lennakatten import.
 */
function MRT_debug_lennakatten_sample_date(): string {
	$dates = MRT_csv_fixture_green_dates();
	return ! empty( $dates[0] ) ? (string) $dates[0] : '2026-05-30';
}

/**
 * @return array<string, mixed>
 */
function MRT_debug_sample_direct_connection(): array {
	return array(
		'service_id'          => 90001,
		'service_name'        => '71',
		'train_type'          => 'Ångtåg',
		'train_type_slug'     => 'angtag',
		'train_type_icon'     => 'steam',
		'connection_type'     => 'direct',
		'from_departure'      => '10:00',
		'to_arrival'          => '10:57',
		'duration_minutes'    => 57,
		'notice'              => '',
		'from_station_id'     => 0,
		'to_station_id'       => 0,
		'legs'                => array(
			array(
				'service_id'       => 90001,
				'service_name'     => '71',
				'train_type'       => 'Ångtåg',
				'train_type_slug'  => 'angtag',
				'train_type_icon'  => 'steam',
				'from_departure'   => '10:00',
				'to_arrival'       => '10:57',
				'destination'      => 'Faringe',
				'duration_minutes' => 57,
			),
		),
	);
}

/**
 * @return array<string, mixed>
 */
function MRT_debug_sample_transfer_connection(): array {
	return array(
		'service_id'          => 0,
		'service_name'        => __( 'Transfer via Marielund', 'museum-railway-timetable' ),
		'train_type'          => '',
		'connection_type'     => 'transfer',
		'from_departure'      => '14:46',
		'to_arrival'          => '15:37',
		'duration_minutes'    => 51,
		'transfer_wait_minutes' => 7,
		'notice'              => __( 'Behovsuppehåll, ge ett tecken till föraren om du vill stiga på', 'museum-railway-timetable' ),
		'legs'                => array(
			array(
				'service_id'      => 90002,
				'service_name'    => '96',
				'train_type'      => 'Rälsbuss',
				'train_type_slug' => 'ralsbuss',
				'train_type_icon' => 'railbus',
				'from_departure'  => '14:46',
				'to_arrival'      => '14:58',
				'destination'     => 'Marielund',
				'duration_minutes' => 12,
			),
			array(
				'service_id'      => 90003,
				'service_name'    => '64',
				'train_type'      => 'Dieseltåg',
				'train_type_slug' => 'dieseltag',
				'train_type_icon' => 'diesel',
				'from_departure'  => '15:05',
				'to_arrival'      => '15:37',
				'destination'     => 'Uppsala Östra',
				'duration_minutes' => 32,
			),
		),
	);
}

/**
 * Return trip with service notice (mockup warning).
 *
 * @return array<string, mixed>
 */
function MRT_debug_sample_return_warning_connection(): array {
	return array(
		'service_id'          => 90004,
		'service_name'        => '78',
		'train_type'          => 'Dieseltåg',
		'train_type_slug'     => 'dieseltag',
		'train_type_icon'     => 'diesel',
		'connection_type'     => 'direct',
		'from_departure'      => '16:41',
		'to_arrival'          => '17:47',
		'duration_minutes'    => 66,
		'notice'              => __( 'Ångloket ersatt med diesellok pga brandrisk', 'museum-railway-timetable' ),
		'legs'                => array(
			array(
				'service_id'       => 90004,
				'service_name'     => '78',
				'train_type'       => 'Dieseltåg',
				'train_type_slug'  => 'dieseltag',
				'train_type_icon'  => 'diesel',
				'from_departure'   => '16:41',
				'to_arrival'       => '17:47',
				'duration_minutes' => 66,
			),
		),
	);
}

/**
 * Outbound transfer for return-step summary (Ångtåg 71 → Dieseltåg 61).
 *
 * @return array<string, mixed>
 */
function MRT_debug_sample_outbound_transfer_summary(): array {
	return array(
		'service_id'            => 0,
		'service_name'          => __( 'Transfer', 'museum-railway-timetable' ),
		'connection_type'       => 'transfer',
		'from_departure'        => '10:00',
		'to_arrival'            => '10:57',
		'duration_minutes'      => 57,
		'transfer_wait_minutes' => 5,
		'legs'                  => array(
			array(
				'service_id'       => 90010,
				'service_name'     => '71',
				'train_type'       => 'Ångtåg',
				'train_type_slug'  => 'angtag',
				'train_type_icon'  => 'steam',
				'from_departure'   => '10:00',
				'to_arrival'       => '10:30',
				'duration_minutes' => 30,
			),
			array(
				'service_id'       => 90011,
				'service_name'     => '61',
				'train_type'       => 'Dieseltåg',
				'train_type_slug'  => 'dieseltag',
				'train_type_icon'  => 'diesel',
				'from_departure'   => '10:35',
				'to_arrival'       => '10:57',
				'duration_minutes' => 22,
			),
		),
	);
}

/**
 * Presets keyed by debug shortcode attribute (development only).
 *
 * @return array<string, array<string, mixed>>
 */
function MRT_journey_wizard_debug_presets(): array {
	$pair  = MRT_debug_lennakatten_station_pair();
	$date  = MRT_debug_lennakatten_sample_date();
	$direct   = MRT_debug_sample_direct_connection();
	$xfer     = MRT_debug_sample_transfer_connection();
	$return   = MRT_debug_sample_direct_connection();
	$return['from_departure']     = '12:54';
	$return['to_arrival']         = '13:47';
	$return['duration_minutes']   = 53;
	$return['service_name']       = '62';
	$return['train_type']         = 'Dieseltåg';
	$return['train_type_icon']    = 'diesel';
	$outbound_xfer = MRT_debug_sample_outbound_transfer_summary();

	return array(
		'date'     => array(
			'step'           => 'date',
			'tripType'       => 'single',
			'from'           => $pair['from'],
			'to'             => $pair['to'],
			'fromTitle'      => $pair['from_title'],
			'toTitle'        => $pair['to_title'],
			'date'           => '',
			'calendarDays'   => array( $date => 'ok' ),
			'calendarYear'   => (int) gmdate( 'Y', strtotime( $date . ' UTC' ) ),
			'calendarMonth'  => (int) gmdate( 'n', strtotime( $date . ' UTC' ) ),
		),
		'outbound' => array(
			'step'                 => 'outbound',
			'tripType'             => 'single',
			'from'                 => $pair['from'],
			'to'                   => $pair['to'],
			'fromTitle'            => $pair['from_title'],
			'toTitle'              => $pair['to_title'],
			'date'                 => $date,
			'outboundConnections'  => array( $direct, $xfer ),
		),
		'return'   => array(
			'step'                 => 'return',
			'tripType'             => 'return',
			'from'                 => $pair['from'],
			'to'                   => $pair['to'],
			'fromTitle'            => $pair['from_title'],
			'toTitle'              => $pair['to_title'],
			'date'                 => $date,
			'outbound'             => $outbound_xfer,
			'returnConnections'    => array(
				$return,
				$xfer,
				MRT_debug_sample_return_warning_connection(),
			),
		),
		'summary'  => array(
			'step'      => 'summary',
			'tripType'  => 'return',
			'from'      => $pair['from'],
			'to'        => $pair['to'],
			'fromTitle' => $pair['from_title'],
			'toTitle'   => $pair['to_title'],
			'date'      => $date,
			'outbound'  => $outbound_xfer,
			'inbound'   => $xfer,
		),
	);
}
