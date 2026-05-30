<?php
/**
 * Train type REST serializers.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int, array<string, mixed>>
 */
function MRT_rest_list_train_types(): array {
	$terms = get_terms(
		array(
			'taxonomy'   => MRT_TAXONOMY_TRAIN_TYPE,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$rows = array();
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$rows[] = MRT_rest_format_train_type( $term );
	}
	return $rows;
}

/**
 * @return array<string, mixed>
 */
function MRT_rest_format_train_type( WP_Term $term ): array {
	return array(
		'id'       => (int) $term->term_id,
		'name'     => (string) $term->name,
		'slug'     => (string) $term->slug,
		'icon_key' => MRT_get_train_type_symbol_key( $term ),
	);
}

/**
 * @param array<string, mixed> $body Request body.
 * @return int|WP_Error
 */
function MRT_rest_create_train_type( array $body ) {
	$name = isset( $body['name'] ) ? sanitize_text_field( (string) $body['name'] ) : '';
	if ( $name === '' ) {
		return new WP_Error( 'invalid_name', __( 'Name is required.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$slug = isset( $body['slug'] ) ? sanitize_title( (string) $body['slug'] ) : sanitize_title( $name );
	$term = wp_insert_term( $name, MRT_TAXONOMY_TRAIN_TYPE, array( 'slug' => $slug ) );
	if ( is_wp_error( $term ) ) {
		return $term;
	}
	$id = (int) ( $term['term_id'] ?? 0 );
	MRT_rest_apply_train_type_icon_key( $id, $body['icon_key'] ?? '' );
	return $id;
}

/**
 * @param array<string, mixed> $body Request body.
 * @return true|WP_Error
 */
function MRT_rest_update_train_type( int $term_id, array $body ) {
	$term = get_term( $term_id, MRT_TAXONOMY_TRAIN_TYPE );
	if ( ! $term instanceof WP_Term ) {
		return new WP_Error( 'not_found', __( 'Train type not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	$args = array();
	if ( isset( $body['name'] ) ) {
		$name = sanitize_text_field( (string) $body['name'] );
		if ( $name !== '' ) {
			$args['name'] = $name;
		}
	}
	if ( isset( $body['slug'] ) ) {
		$slug = sanitize_title( (string) $body['slug'] );
		if ( $slug !== '' ) {
			$args['slug'] = $slug;
		}
	}
	if ( $args !== array() ) {
		$result = wp_update_term( $term_id, MRT_TAXONOMY_TRAIN_TYPE, $args );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
	}
	if ( array_key_exists( 'icon_key', $body ) ) {
		MRT_rest_apply_train_type_icon_key( $term_id, $body['icon_key'] ?? '' );
	}
	return true;
}

/**
 * @param mixed $icon_key Icon slug from client.
 */
function MRT_rest_apply_train_type_icon_key( int $term_id, $icon_key ): void {
	$key = sanitize_key( (string) $icon_key );
	if ( $key !== '' && in_array( $key, MRT_train_type_icon_keys(), true ) ) {
		update_term_meta( $term_id, 'mrt_icon_key', $key );
		return;
	}
	delete_term_meta( $term_id, 'mrt_icon_key' );
}
