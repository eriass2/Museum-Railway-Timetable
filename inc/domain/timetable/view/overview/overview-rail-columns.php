<?php
/**
 * Timetable overview rail JSON: columns
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}> $display_columns
 */
function MRT_timetable_overview_columns_json( array $view, array $display_columns ): array {
	$columns      = array();
	$services     = $view['services_list'];
	$service_info = $view['service_info'];

	foreach ( $display_columns as $col ) {
		$idx     = (int) $col['primary_idx'];
		$info    = $service_info[ $idx ];
		$service = $services[ $idx ]['service'] ?? null;
		$tt      = $info['train_type'] ?? null;
		$default = $info['default_train_type'] ?? null;
		$column  = array(
			'serviceId'            => $service instanceof WP_Post ? (int) $service->ID : 0,
			'serviceNumber'        => (string) ( $info['service_number'] ?? '' ),
			'trainTypeName'        => $tt ? $tt->name : '',
			'trainTypeSlug'        => $tt ? $tt->slug : '',
			'iconKey'              => $tt ? MRT_get_train_type_symbol_key( $tt ) : 'diesel',
			'plannedTrainTypeName' => $default ? $default->name : '',
			'isDeviation'          => ! empty( $info['is_deviation'] ),
			'isCancelled'          => ! empty( $info['is_cancelled'] ),
			'deviationNotice'      => (string) ( $info['deviation_notice'] ?? '' ),
			'isSpecial'            => ! empty( $info['highlight_label'] ),
			'specialName'          => (string) ( $info['highlight_label'] ?? '' ),
			'highlightColor'         => (string) ( $info['highlight_color'] ?? '' ),
		);
		$continuation_idx = $col['continuation_idx'];
		if ( $continuation_idx !== null ) {
			$cont_info    = $service_info[ (int) $continuation_idx ];
			$cont_service = $services[ (int) $continuation_idx ]['service'] ?? null;
			$cont_tt      = $cont_info['train_type'] ?? null;
			$column['continuation'] = array(
				'serviceId'       => $cont_service instanceof WP_Post ? (int) $cont_service->ID : 0,
				'serviceNumber'   => (string) ( $cont_info['service_number'] ?? '' ),
				'trainTypeName'   => $cont_tt ? $cont_tt->name : '',
				'iconKey'         => $cont_tt ? MRT_get_train_type_symbol_key( $cont_tt ) : 'diesel',
			);
		}
		$columns[] = $column;
	}
	return $columns;
}
