<?php
/**
 * Configurable price matrix schema (ticket types, categories, zones).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default flat afternoon-return fares (tur och retur efter kl 15).
 *
 * @return array<string, int>
 */
function MRT_get_default_afternoon_return_prices(): array {
	return array(
		'adult'          => 0,
		'child_4_15'     => 0,
		'child_0_3'      => 0,
		'student_senior' => 0,
	);
}

/**
 * @return array{
 *     ticket_types: array<int, array{key: string, label: string}>,
 *     categories: array<int, array{key: string, label: string}>,
 *     zones: array<int, int>,
 *     zone_cap: int,
 *     afternoon_return: array<string, int>
 * }
 */
function MRT_get_default_price_schema(): array {
	return array(
		'ticket_types' => array(
			array(
				'key'   => 'single',
				'label' => __( 'Enkelbiljett', 'museum-railway-timetable' ),
			),
			array(
				'key'   => 'return',
				'label' => __( 'Returbiljett', 'museum-railway-timetable' ),
			),
			array(
				'key'   => 'day',
				'label' => __( 'Dagskort', 'museum-railway-timetable' ),
			),
		),
		'categories'   => array(
			array(
				'key'   => 'adult',
				'label' => __( 'Vuxen', 'museum-railway-timetable' ),
			),
			array(
				'key'   => 'child_4_15',
				'label' => __( 'Barn 4–15', 'museum-railway-timetable' ),
			),
			array(
				'key'   => 'child_0_3',
				'label' => __( 'Barn 0–3', 'museum-railway-timetable' ),
			),
			array(
				'key'   => 'student_senior',
				'label' => __( 'Student / pensionär', 'museum-railway-timetable' ),
			),
		),
		'zones'            => array( 1, 2, 3, 4 ),
		'zone_cap'         => 1,
		'afternoon_return' => MRT_get_default_afternoon_return_prices(),
	);
}

/**
 * @return array{
 *     ticket_types: array<int, array{key: string, label: string}>,
 *     categories: array<int, array{key: string, label: string}>,
 *     zones: array<int, int>,
 *     zone_cap: int,
 *     afternoon_return: array<string, int>
 * }
 */
function MRT_get_price_schema(): array {
	$stored = get_option( 'mrt_price_schema', array() );
	if ( ! is_array( $stored ) || $stored === array() ) {
		return MRT_get_default_price_schema();
	}
	return MRT_sanitize_price_schema( $stored );
}

/**
 * @param mixed $key Raw key.
 */
function MRT_sanitize_price_schema_key( $key ): string {
	$key = sanitize_key( (string) $key );
	if ( $key === '' || ! preg_match( '/^[a-z][a-z0-9_]{0,31}$/', $key ) ) {
		return '';
	}
	return $key;
}

/**
 * @param array<int, array<string, mixed>> $rows Label rows.
 * @return array<int, array{key: string, label: string}>
 */
function MRT_sanitize_price_schema_label_rows( array $rows ): array {
	$clean = array();
	$seen  = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$key = MRT_sanitize_price_schema_key( $row['key'] ?? '' );
		if ( $key === '' || isset( $seen[ $key ] ) ) {
			continue;
		}
		$label = sanitize_text_field( (string) ( $row['label'] ?? '' ) );
		if ( $label === '' ) {
			continue;
		}
		$seen[ $key ]  = true;
		$clean[]       = array(
			'key'   => $key,
			'label' => $label,
		);
	}
	return $clean;
}

/**
 * @param mixed $zones Raw zone list.
 * @return array<int, int>
 */
function MRT_sanitize_price_schema_zones( $zones ): array {
	if ( ! is_array( $zones ) ) {
		return array();
	}
	$clean = array();
	foreach ( $zones as $zone ) {
		$z = (int) $zone;
		if ( $z >= 1 && $z <= 99 && ! in_array( $z, $clean, true ) ) {
			$clean[] = $z;
		}
	}
	sort( $clean );
	return $clean;
}

/**
 * @param mixed $value Raw zone cap.
 */
function MRT_sanitize_price_schema_zone_cap( $value ): int {
	$cap = (int) $value;
	if ( $cap < 1 ) {
		return 3;
	}
	return min( 99, $cap );
}

/**
 * @param mixed              $input Raw afternoon-return map.
 * @param array<int, string> $category_keys Valid category keys.
 * @return array<string, int>
 */
function MRT_sanitize_price_schema_afternoon_return( $input, array $category_keys ): array {
	$defaults = MRT_get_default_afternoon_return_prices();
	$raw      = is_array( $input ) ? $input : array();
	$out      = array();
	foreach ( $category_keys as $key ) {
		if ( ! array_key_exists( $key, $raw ) ) {
			$out[ $key ] = $defaults[ $key ] ?? 0;
			continue;
		}
		$n = (int) $raw[ $key ];
		$out[ $key ] = max( 0, $n );
	}
	return $out;
}

/**
 * @param array<string, mixed> $input Stored or request schema.
 * @return array{
 *     ticket_types: array<int, array{key: string, label: string}>,
 *     categories: array<int, array{key: string, label: string}>,
 *     zones: array<int, int>,
 *     zone_cap: int,
 *     afternoon_return: array<string, int>
 * }
 */
function MRT_sanitize_price_schema( array $input ): array {
	$defaults = MRT_get_default_price_schema();
	$tickets  = isset( $input['ticket_types'] ) && is_array( $input['ticket_types'] )
		? MRT_sanitize_price_schema_label_rows( $input['ticket_types'] )
		: array();
	$cats     = isset( $input['categories'] ) && is_array( $input['categories'] )
		? MRT_sanitize_price_schema_label_rows( $input['categories'] )
		: array();
	$zones    = MRT_sanitize_price_schema_zones( $input['zones'] ?? array() );
	$tickets  = $tickets !== array() ? $tickets : $defaults['ticket_types'];
	$cats     = $cats !== array() ? $cats : $defaults['categories'];
	$zones    = $zones !== array() ? $zones : $defaults['zones'];
	$cat_keys = array_map(
		static function ( array $row ): string {
			return $row['key'];
		},
		$cats
	);
	return array(
		'ticket_types'     => $tickets,
		'categories'       => $cats,
		'zones'            => $zones,
		'zone_cap'         => MRT_sanitize_price_schema_zone_cap( $input['zone_cap'] ?? $defaults['zone_cap'] ),
		'afternoon_return' => MRT_sanitize_price_schema_afternoon_return(
			$input['afternoon_return'] ?? $defaults['afternoon_return'],
			$cat_keys
		),
	);
}

/**
 * @param array<string, string>      $ticket_types Key => label.
 * @param array<string, string>      $categories Key => label.
 * @param array<int, int>            $zones Zone numbers.
 * @param array<string, mixed>|null  $extras zone_cap, afternoon_return.
 * @return array{
 *     ticket_types: array<int, array{key: string, label: string}>,
 *     categories: array<int, array{key: string, label: string}>,
 *     zones: array<int, int>,
 *     zone_cap: int,
 *     afternoon_return: array<string, int>
 * }
 */
function MRT_sanitize_price_schema_from_admin_maps(
	array $ticket_types,
	array $categories,
	array $zones,
	?array $extras = null
): array {
	$ticket_rows = array();
	foreach ( $ticket_types as $key => $label ) {
		$ticket_rows[] = array(
			'key'   => (string) $key,
			'label' => (string) $label,
		);
	}
	$category_rows = array();
	foreach ( $categories as $key => $label ) {
		$category_rows[] = array(
			'key'   => (string) $key,
			'label' => (string) $label,
		);
	}
	$payload = array(
		'ticket_types' => $ticket_rows,
		'categories'   => $category_rows,
		'zones'        => $zones,
	);
	if ( is_array( $extras ) ) {
		if ( array_key_exists( 'zone_cap', $extras ) ) {
			$payload['zone_cap'] = $extras['zone_cap'];
		}
		if ( isset( $extras['afternoon_return'] ) && is_array( $extras['afternoon_return'] ) ) {
			$payload['afternoon_return'] = $extras['afternoon_return'];
		}
	}
	return MRT_sanitize_price_schema( $payload );
}

/**
 * @return string[]
 */
function MRT_price_schema_ticket_keys(): array {
	$schema = MRT_get_price_schema();
	$keys   = array();
	foreach ( $schema['ticket_types'] as $row ) {
		$keys[] = $row['key'];
	}
	return $keys;
}

/**
 * @return string[]
 */
function MRT_price_schema_category_keys(): array {
	$schema = MRT_get_price_schema();
	$keys   = array();
	foreach ( $schema['categories'] as $row ) {
		$keys[] = $row['key'];
	}
	return $keys;
}

/**
 * @return int[]
 */
function MRT_price_schema_zone_keys(): array {
	return MRT_get_price_schema()['zones'];
}

/**
 * @return array<string, string>
 */
function MRT_price_schema_ticket_labels(): array {
	$labels = array();
	foreach ( MRT_get_price_schema()['ticket_types'] as $row ) {
		$labels[ $row['key'] ] = $row['label'];
	}
	return $labels;
}

/**
 * @return array<string, string>
 */
function MRT_price_schema_category_labels(): array {
	$labels = array();
	foreach ( MRT_get_price_schema()['categories'] as $row ) {
		$labels[ $row['key'] ] = $row['label'];
	}
	return $labels;
}

/**
 * Max zones used for fare lookup (may be lower than matrix column count).
 */
function MRT_price_schema_zone_cap(): int {
	return MRT_get_price_schema()['zone_cap'];
}

/**
 * Flat afternoon-return fares keyed by passenger category.
 *
 * @return array<string, int>
 */
function MRT_price_schema_afternoon_return_prices(): array {
	return MRT_get_price_schema()['afternoon_return'];
}
