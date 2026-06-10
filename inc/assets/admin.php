<?php

declare(strict_types=1);

/**
 * Admin asset enqueuing for Museum Railway Timetable.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if admin assets should be loaded for current page.
 *
 * @param string $hook Current admin page hook
 */
function MRT_should_load_admin_assets( string $hook ): bool {
	$is_plugin_page   = strpos( $hook, 'mrt_' ) !== false;
	$is_edit_page     = in_array( $hook, array( 'post.php', 'post-new.php' ), true );
	$is_list_page     = $hook === 'edit.php';
	$is_taxonomy_page = in_array( $hook, array( 'edit-tags.php', 'term.php' ), true );

	if ( $is_taxonomy_page && MRT_is_train_type_taxonomy_request() ) {
		return true;
	}
	if ( ! $is_plugin_page && ! $is_edit_page && ! $is_list_page ) {
		return false;
	}
	return MRT_admin_screen_post_type_allowed( $is_edit_page, $is_list_page );
}

/**
 * Whether current taxonomy admin request targets the plugin train type taxonomy.
 */
function MRT_is_train_type_taxonomy_request(): bool {
	$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : '';
	return $taxonomy === MRT_TAXONOMY_TRAIN_TYPE;
}

/**
 * Validate edit/list admin screens against plugin post types.
 */
function MRT_admin_screen_post_type_allowed( bool $is_edit_page, bool $is_list_page ): bool {
	if ( $is_edit_page ) {
		return in_array( get_post_type(), MRT_POST_TYPES, true );
	}
	if ( $is_list_page ) {
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : 'post';
		return in_array( $post_type, MRT_POST_TYPES, true );
	}
	return true;
}

/**
 * Enqueue admin CSS files.
 */
function MRT_enqueue_admin_css( string $hook ): void {
	unset( $hook );
	$a           = MRT_assets_base_url();
	$icon_handle = MRT_enqueue_train_type_icon_styles();
	wp_enqueue_style(
		'mrt-admin',
		$a . 'admin.css',
		array( $icon_handle ),
		MRT_VERSION
	);
}

/**
 * Enqueue admin assets (CSS only; Vue admin bundle via admin-vue.php).
 */
function MRT_enqueue_admin_assets( string $hook ): void {
	if ( ! MRT_should_load_admin_assets( $hook ) ) {
		return;
	}
	MRT_enqueue_admin_css( $hook );
}
add_action( 'admin_enqueue_scripts', 'MRT_enqueue_admin_assets' );
