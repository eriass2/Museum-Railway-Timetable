<?php
/**
 * Lennakatten reference data for dev import and tests (not runtime defaults).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/journey/train-change.php';
require_once MRT_PATH . 'inc/import/csv/fixture-read.php';

/**
 * Station price zones per Lennakatten taxa 2026 (see docs/PRICE_ZONES.md).
 *
 * @return array<string, int[]>
 */
function MRT_lennakatten_reference_station_price_zones_by_title(): array {
	return array(
		'Uppsala Östra'   => array( 1 ),
		'Fyrislund'       => array( 1 ),
		'Årsta'           => array( 1 ),
		'Skölsta'         => array( 1 ),
		'Bärby'           => array( 1 ),
		'Gunsta'          => array( 1, 2 ),
		'Marielund'       => array( 2 ),
		'Lövstahagen'     => array( 2 ),
		'Selknä'          => array( 2 ),
		'Löt'             => array( 2 ),
		'Länna'           => array( 2 ),
		'Fjällnora'       => array( 2 ),
		'Almunge'         => array( 2, 3 ),
		'Moga'            => array( 3 ),
		'Faringe'         => array( 3 ),
		'Linnés Hammarby' => array( 3 ),
	);
}

/**
 * Train-change rows at transfer stations (overview + journey hub hints).
 *
 * @return array<string, array<string, array{typeName: string, serviceNumber: string}>>
 */
function MRT_lennakatten_reference_train_change_maps_by_station_title(): array {
	/** @var list<array{station: string, from: string, type: string, to: string}> $rows */
	$rows = array(
		array(
			'station' => 'Marielund',
			'from'    => '71',
			'type'    => 'Dieseltåg',
			'to'      => '61',
		),
		array(
			'station' => 'Marielund',
			'from'    => '63',
			'type'    => 'Rälsbuss',
			'to'      => '97',
		),
		array(
			'station' => 'Marielund',
			'from'    => '60',
			'type'    => 'Ångtåg',
			'to'      => '74',
		),
		array(
			'station' => 'Marielund',
			'from'    => '96',
			'type'    => 'Dieseltåg',
			'to'      => '64',
		),
	);
	$map  = array();
	foreach ( $rows as $row ) {
		$map[ $row['station'] ][ $row['from'] ] = MRT_train_change_map_entry( $row['type'], $row['to'] );
	}

	return $map;
}

/**
 * Lennakatten plugin settings for dev import and tests (not runtime defaults).
 *
 * @return array<string, mixed>
 */
function MRT_lennakatten_reference_plugin_settings(): array {
	return array(
		'enabled'                            => true,
		'note'                               => '',
		'operator_name'                      => 'Lennakatten',
		'ticket_url'                         => 'https://www.lennakatten.se/biljetter/',
		'hero_background_url'                => MRT_testdata_wizard_hero_background_relative_path(),
		'min_transfer_minutes'               => 0,
		'max_transfer_minutes'               => 120,
		'max_transfers'                      => 2,
		'afternoon_return_threshold_minutes' => 900,
	);
}

/**
 * Flat afternoon-return fares (Lennakatten taxa 2026).
 *
 * @return array<string, int>
 */
function MRT_lennakatten_reference_afternoon_return_prices(): array {
	return array(
		'adult'          => 160,
		'child_4_15'     => 60,
		'child_0_3'      => 0,
		'student_senior' => 140,
	);
}

/**
 * Price schema for Lennakatten reference (taxa 2026, zone cap 3).
 *
 * @return array{
 *     ticket_types: array<int, array{key: string, label: string}>,
 *     categories: array<int, array{key: string, label: string}>,
 *     zones: array<int, int>,
 *     zone_cap: int,
 *     afternoon_return: array<string, int>
 * }
 */
function MRT_lennakatten_reference_price_schema(): array {
	require_once MRT_PATH . 'inc/domain/pricing/price-schema.php';
	$schema                      = MRT_get_default_price_schema();
	$schema['zones']             = array( 1, 2, 3 );
	$schema['zone_cap']          = 3;
	$schema['afternoon_return']  = MRT_lennakatten_reference_afternoon_return_prices();
	return $schema;
}

/**
 * Full Lennakatten fare matrix (see price-matrix-builtin.php).
 *
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_lennakatten_reference_price_matrix(): array {
	require_once MRT_PATH . 'inc/domain/pricing/prices.php';
	return MRT_get_builtin_price_matrix();
}

/**
 * Lennakatten brand CSS tokens for dev import and tests (not runtime defaults).
 *
 * @return array{google_fonts: string, tokens: array<string, string>}
 */
function MRT_lennakatten_reference_brand_tokens(): array {
	require_once MRT_PATH . 'inc/assets/brand-tokens-data.php';
	return MRT_sanitize_brand_tokens_storage(
		array(
			'google_fonts' => 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@700;800&family=Roboto:wght@400;500;700&display=swap',
			'tokens'       => array(
				'--mrt-color-brand-green'     => '#296310',
				'--mrt-color-brand-gold'      => '#ddd24c',
				'--mrt-color-brand-olive'     => '#807c1c',
				'--mrt-color-green-900'       => '#183809',
				'--mrt-color-green-800'       => '#214f0c',
				'--mrt-color-green-700'       => '#245610',
				'--mrt-color-green-600'       => 'var(--mrt-color-brand-green)',
				'--mrt-color-green-500'       => '#358015',
				'--mrt-color-green-400'       => '#42961a',
				'--mrt-color-accent-700'      => '#c5bd44',
				'--mrt-color-accent-600'      => 'var(--mrt-color-brand-gold)',
				'--mrt-color-accent-500'      => '#e3dc65',
				'--mrt-color-accent-400'      => '#eae483',
				'--mrt-color-neutral-200'     => '#b4b4b4',
				'--mrt-color-neutral-500'     => '#787878',
				'--mrt-color-neutral-600'     => '#787878',
				'--mrt-color-neutral-700'     => '#505050',
				'--mrt-color-neutral-900'     => '#000000',
				'--mrt-color-traffic-green'   => 'var(--mrt-color-brand-green)',
				'--mrt-color-traffic-yellow'  => 'var(--mrt-color-brand-gold)',
				'--mrt-color-on-dark-muted'   => '#e4efe2',
				'--mrt-color-on-dark-link'    => '#ffecb8',
				'--mrt-color-focus-ring'      => '#fff4d6',
				'--mrt-wizard-border'         => 'var(--mrt-color-neutral-200)',
				'--mrt-font-body'             => '"Roboto", system-ui, -apple-system, "Segoe UI", sans-serif',
				'--mrt-font-heading'          => '"Open Sans", system-ui, -apple-system, "Segoe UI", sans-serif',
			),
		)
	);
}
