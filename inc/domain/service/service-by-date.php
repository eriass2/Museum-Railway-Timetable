<?php
/**
 * Services running on a date (timetable linkage and filters).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get timetable IDs that include a given date.
 *
 * @param string $dateYmd Date in YYYY-MM-DD format.
 * @return array<int> Array of timetable post IDs.
 */
function MRT_get_timetables_for_date( $dateYmd ) {
	return get_posts(
		array(
			'post_type'      => 'mrt_timetable',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'mrt_timetable_dates',
					'value'   => $dateYmd,
					'compare' => 'LIKE',
				),
			),
		)
	);
}

/**
 * Filter service IDs to those actually running on date (verify timetable dates).
 *
 * @param array<int> $service_ids    Candidate service IDs.
 * @param array<int> $timetable_ids  Timetable IDs that include the date.
 * @param string     $dateYmd        Date in YYYY-MM-DD format.
 * @return array<int> Valid service IDs.
 */
function MRT_filter_services_verified_for_date( $service_ids, $timetable_ids, $dateYmd ) {
	$valid = array();
	foreach ( $service_ids as $service_id ) {
		$timetable_id = get_post_meta( $service_id, 'mrt_service_timetable_id', true );
		if ( ! $timetable_id || ! in_array( $timetable_id, $timetable_ids ) ) {
			continue;
		}
		$timetable_dates = MRT_get_timetable_dates( $timetable_id );
		if ( in_array( $dateYmd, $timetable_dates, true ) ) {
			$valid[] = $service_id;
		}
	}
	return $valid;
}

/**
 * Service IDs linked to any of the given timetables.
 *
 * @param array<int> $timetable_ids Timetable post IDs.
 * @return array<int>
 */
function MRT_query_service_ids_for_timetables( array $timetable_ids ) {
	if ( $timetable_ids === array() ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'      => 'mrt_service',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'mrt_service_timetable_id',
					'value'   => $timetable_ids,
					'compare' => 'IN',
				),
			),
		)
	);
}

/**
 * Keep only services whose post title matches exactly.
 *
 * @param array<int> $service_ids Candidate service IDs.
 * @param string     $title       Exact service title.
 * @return array<int>
 */
function MRT_filter_service_ids_by_exact_title( array $service_ids, string $title ) {
	if ( $title === '' ) {
		return $service_ids;
	}

	$post = MRT_get_post_by_title( $title, 'mrt_service' );
	if ( ! $post ) {
		return array();
	}

	return array_values( array_intersect( $service_ids, array( (int) $post->ID ) ) );
}

/**
 * Filter service IDs by train type taxonomy slug.
 *
 * @param array<int> $service_ids       Candidate service IDs.
 * @param string     $train_type_slug   Taxonomy slug.
 * @return array<int>
 */
function MRT_filter_service_ids_by_train_type_slug( array $service_ids, string $train_type_slug ) {
	if ( $train_type_slug === '' || $service_ids === array() ) {
		return $service_ids;
	}

	$q = new WP_Query(
		array(
			'post_type' => 'mrt_service',
			'post__in'  => $service_ids,
			'fields'    => 'ids',
			'nopaging'  => true,
			'tax_query' => array(
				array(
					'taxonomy' => 'mrt_train_type',
					'field'    => 'slug',
					'terms'    => sanitize_title( $train_type_slug ),
				),
			),
		)
	);

	return $q->posts;
}

/**
 * Resolve which services run on a given date (using Timetables).
 *
 * @param string $dateYmd             Date in YYYY-MM-DD format.
 * @param string $train_type_slug     Optional train type taxonomy slug.
 * @param string $service_title_exact Optional exact service title.
 * @return array<int> Array of service post IDs.
 */
function MRT_services_running_on_date( $dateYmd, $train_type_slug = '', $service_title_exact = '' ) {
	if ( ! MRT_validate_date( $dateYmd ) ) {
		return array();
	}

	$timetables = MRT_get_timetables_for_date( $dateYmd );
	if ( empty( $timetables ) ) {
		return array();
	}

	$service_ids       = MRT_query_service_ids_for_timetables( $timetables );
	$valid_service_ids = MRT_filter_services_verified_for_date( $service_ids, $timetables, $dateYmd );
	if ( empty( $valid_service_ids ) ) {
		return array();
	}

	$valid_service_ids = MRT_filter_service_ids_by_exact_title( $valid_service_ids, $service_title_exact );
	if ( empty( $valid_service_ids ) ) {
		return array();
	}

	if ( $train_type_slug ) {
		return MRT_filter_service_ids_by_train_type_slug( $valid_service_ids, $train_type_slug );
	}

	return array_values( array_unique( $valid_service_ids ) );
}
