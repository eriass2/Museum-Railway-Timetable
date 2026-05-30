<?php
/**
 * WordPress CPT admin tweaks (data still stored as posts; Vue is the UI).
 *
 * CPTs remain registered for storage and REST. Legacy post.php screens redirect
 * to Vue, but these hooks keep direct CPT URLs safe (no block editor, etc.).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function (): void {
		remove_post_type_support( 'mrt_station', 'editor' );
		remove_post_type_support( 'mrt_service', 'editor' );
		remove_post_type_support( 'mrt_route', 'editor' );
		remove_post_type_support( 'mrt_timetable', 'editor' );
	},
	20
);

add_filter(
	'use_block_editor_for_post_type',
	static function ( $use_block_editor, $post_type ) {
		if ( in_array( $post_type, array( 'mrt_station', 'mrt_service', 'mrt_route', 'mrt_timetable' ), true ) ) {
			return false;
		}
		return $use_block_editor;
	},
	10,
	2
);

add_filter(
	'manage_edit-mrt_train_type_columns',
	static function ( $columns ) {
		if ( isset( $columns['description'] ) ) {
			unset( $columns['description'] );
		}
		return $columns;
	}
);
