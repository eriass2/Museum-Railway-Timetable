<?php
/**
 * Service train type helpers (default, per-date, deviation).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get train type for a service on a specific date.
 *
 * @param int         $service_id Service post ID.
 * @param string|null $dateYmd    Date in YYYY-MM-DD format (optional, defaults to today).
 * @return WP_Term|null Train type term object or null if not found.
 */
function MRT_get_service_train_type_for_date( $service_id, $dateYmd = null ) {
	if ( ! $service_id ) {
		return null;
	}

	if ( $dateYmd === null ) {
		$datetime = MRT_get_current_datetime();
		$dateYmd  = $datetime['date'];
	}

	if ( ! MRT_validate_date( $dateYmd ) ) {
		return null;
	}

	$train_types_by_date = get_post_meta( $service_id, 'mrt_service_train_types_by_date', true );
	if ( is_array( $train_types_by_date ) && isset( $train_types_by_date[ $dateYmd ] ) ) {
		$train_type_id = intval( $train_types_by_date[ $dateYmd ] );
		if ( $train_type_id > 0 ) {
			$train_type = get_term( $train_type_id, 'mrt_train_type' );
			if ( $train_type && ! is_wp_error( $train_type ) ) {
				return $train_type;
			}
		}
	}

	$train_types = wp_get_post_terms( $service_id, 'mrt_train_type', array( 'fields' => 'all' ) );
	if ( ! empty( $train_types ) && ! is_wp_error( $train_types ) ) {
		return $train_types[0];
	}

	return null;
}

/**
 * Default train type for a service (taxonomy, ignoring date overrides).
 *
 * @param int $service_id Service post ID.
 * @return WP_Term|null
 */
function MRT_get_service_default_train_type( $service_id ) {
	if ( ! $service_id ) {
		return null;
	}
	$train_types = wp_get_post_terms( $service_id, 'mrt_train_type', array( 'fields' => 'all' ) );
	if ( ! empty( $train_types ) && ! is_wp_error( $train_types ) ) {
		return $train_types[0];
	}
	return null;
}

/**
 * Whether the effective train type on a date differs from the service default.
 *
 * @param int    $service_id Service post ID.
 * @param string $dateYmd    Date YYYY-MM-DD.
 */
function MRT_service_has_train_type_deviation( $service_id, $dateYmd ): bool {
	if ( ! $service_id || ! MRT_validate_date( $dateYmd ) ) {
		return false;
	}
	$default   = MRT_get_service_default_train_type( $service_id );
	$effective = MRT_get_service_train_type_for_date( $service_id, $dateYmd );
	if ( ! $default && ! $effective ) {
		return false;
	}
	if ( ! $default || ! $effective ) {
		return true;
	}
	return (int) $default->term_id !== (int) $effective->term_id;
}

/**
 * Human-readable train-type deviation for print keys and notices.
 *
 * @param WP_Term      $effective Actual train type on the date.
 * @param WP_Term|null $default   Planned default train type.
 */
function MRT_format_train_type_deviation_text( WP_Term $effective, $default ): string {
	$planned = $default ? $default->name : __( 'default', 'museum-railway-timetable' );
	return sprintf(
		/* translators: 1: actual train type, 2: planned train type */
		__( '%1$s replaces planned %2$s.', 'museum-railway-timetable' ),
		$effective->name,
		$planned
	);
}
