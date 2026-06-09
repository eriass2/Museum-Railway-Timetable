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

function MRT_timetable_overview_columns_json( array $view ): array {
	$columns = array();
	foreach ( $view['services_list'] as $idx => $service_data ) {
		$info    = $view['service_info'][ $idx ];
		$service = $service_data['service'] ?? null;
		$tt      = $info['train_type'] ?? null;
		$default_tt = $info['default_train_type'] ?? null;
		$columns[]  = array(
			'serviceId'              => $service instanceof WP_Post ? (int) $service->ID : 0,
			'serviceNumber'        => (string) ( $info['service_number'] ?? '' ),
			'trainTypeName'          => $tt ? $tt->name : '',
			'trainTypeSlug'          => $tt ? $tt->slug : '',
			'iconKey'                => $tt ? MRT_get_train_type_symbol_key( $tt ) : 'diesel',
			'plannedTrainTypeName'   => $default_tt ? $default_tt->name : '',
			'isDeviation'            => ! empty( $info['is_deviation'] ),
			'isCancelled'            => ! empty( $info['is_cancelled'] ),
			'deviationNotice'        => (string) ( $info['deviation_notice'] ?? '' ),
			'isSpecial'              => ! empty( $info['highlight_label'] ),
			'specialName'            => (string) ( $info['highlight_label'] ?? '' ),
			'highlightColor'         => (string) ( $info['highlight_color'] ?? '' ),
		);
	}
	return $columns;
}
