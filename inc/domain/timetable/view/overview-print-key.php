<?php
/**
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<int, WP_Post> $services
 * @return array<int, array{symbol: string, text: string}>
 */
function MRT_timetable_print_key_data( array $services = array(), string $dateYmd = '' ): array {
	return array_merge(
		MRT_timetable_print_key_base_rows(),
		MRT_timetable_print_key_highlight_rows( $services ),
		MRT_timetable_print_key_deviation_rows( $services, $dateYmd )
	);
}

/**
 * @return array<int, array{symbol: string, text: string}>
 */
function MRT_timetable_print_key_base_rows(): array {
	return array(
		array(
			'symbol' => 'X',
			'text'   => __(
				'Stannar vid av- och påstigning när någon resenär ska på eller av.',
				'museum-railway-timetable'
			),
		),
		array(
			'symbol' => 'P',
			'text'   => __(
				'Stannar endast vid påstigning när någon resenär ska på.',
				'museum-railway-timetable'
			),
		),
		array(
			'symbol' => '*',
			'text'   => __(
				'Busshållplats; anslutande bussar visas i egen tabell i tidtabellen.',
				'museum-railway-timetable'
			),
		),
	);
}

/**
 * @param array<int, WP_Post> $services
 * @return array<int, array{symbol: string, text: string}>
 */
function MRT_timetable_print_key_highlight_rows( array $services ): array {
	$rows = array();
	$seen = array();
	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$highlight = MRT_get_service_highlight( (int) $service->ID );
		if ( $highlight === null || isset( $seen[ $highlight['label'] ] ) ) {
			continue;
		}
		$seen[ $highlight['label'] ] = true;
		$rows[]                      = array(
			'symbol' => $highlight['label'],
			'text'   => $highlight['note'] !== '' ? $highlight['note'] : $highlight['label'],
		);
	}
	return $rows;
}

/**
 * Print-key rows for train-type deviations and date-specific notices.
 *
 * @param array<int, WP_Post> $services
 * @return array<int, array{symbol: string, text: string}>
 */
function MRT_timetable_print_key_deviation_rows( array $services, string $dateYmd ): array {
	if ( $dateYmd === '' || ! MRT_validate_date( $dateYmd ) || $services === array() ) {
		return array();
	}

	$rows               = array();
	$has_type_deviation = false;

	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$row = MRT_timetable_deviation_print_key_row( $service, $dateYmd );
		if ( $row === null ) {
			continue;
		}
		if ( ! empty( $row['has_type_deviation'] ) ) {
			$has_type_deviation = true;
		}
		unset( $row['has_type_deviation'] );
		$rows[] = $row;
	}

	if ( $has_type_deviation ) {
		array_unshift(
			$rows,
			array(
				'symbol' => '†',
				'text'   => __(
					'Deviation from planned train type on the selected day.',
					'museum-railway-timetable'
				),
			)
		);
	}

	return $rows;
}

/**
 * @return array{symbol: string, text: string, has_type_deviation: bool}|null
 */
function MRT_timetable_deviation_print_key_row( WP_Post $service, string $dateYmd ): ?array {
	$service_id   = (int) $service->ID;
	$type_dev     = MRT_service_has_train_type_deviation( $service_id, $dateYmd );
	$notice       = MRT_get_service_notice_for_date( $service_id, $dateYmd );
	if ( ! $type_dev && $notice === '' ) {
		return null;
	}

	$number = (string) get_post_meta( $service_id, 'mrt_service_number', true );
	if ( $number === '' ) {
		$number = (string) $service_id;
	}

	$parts = array();
	if ( $type_dev ) {
		$default   = MRT_get_service_default_train_type( $service_id );
		$effective = MRT_get_service_train_type_for_date( $service_id, $dateYmd );
		if ( $effective instanceof WP_Term ) {
			$parts[] = MRT_format_train_type_deviation_text( $effective, $default );
		}
	}
	if ( $notice !== '' ) {
		$parts[] = $notice;
	}

	return array(
		'symbol'              => $type_dev ? $number . '†' : $number,
		'text'                => implode( ' ', $parts ),
		'has_type_deviation'  => $type_dev,
	);
}
