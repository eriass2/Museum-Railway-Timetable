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
 * Enqueue admin JavaScript files.
 */
function MRT_enqueue_admin_js(): void {
	$a = MRT_assets_base_url();
	wp_register_script( 'mrt-string-utils', $a . 'mrt-string-utils.js', array(), MRT_VERSION, true );
	wp_register_script( 'mrt-date-utils', $a . 'mrt-date-utils.js', array(), MRT_VERSION, true );
	MRT_enqueue_admin_feature_scripts( $a );
	MRT_enqueue_admin_entry_script( $a );
}

/**
 * Enqueue admin feature scripts.
 */
function MRT_enqueue_admin_feature_scripts( string $asset_url ): void {
	wp_enqueue_script( 'mrt-admin-utils', $asset_url . 'admin-utils.js', array( 'jquery', 'mrt-date-utils', 'mrt-string-utils' ), MRT_VERSION, true );
	wp_enqueue_script( 'mrt-admin-route-ui', $asset_url . 'admin-route-ui.js', array( 'mrt-admin-utils', 'jquery' ), MRT_VERSION, true );
	wp_enqueue_script( 'mrt-admin-stoptimes-ui', $asset_url . 'admin-stoptimes-ui.js', array( 'mrt-admin-utils', 'jquery' ), MRT_VERSION, true );
	wp_enqueue_script( 'mrt-admin-timetable-services', $asset_url . 'admin-timetable-services-ui.js', array( 'mrt-admin-utils', 'jquery' ), MRT_VERSION, true );
	wp_enqueue_script( 'mrt-admin-service-edit', $asset_url . 'admin-service-edit.js', array( 'mrt-admin-utils', 'jquery' ), MRT_VERSION, true );
	wp_enqueue_script( 'mrt-admin-timetable-preview', $asset_url . 'admin-timetable-preview.js', array( 'mrt-admin-utils', 'jquery' ), MRT_VERSION, true );
	wp_enqueue_script( 'mrt-admin-timetable-workspace', $asset_url . 'admin-timetable-workspace.js', array( 'mrt-admin-utils', 'mrt-admin-timetable-preview', 'jquery' ), MRT_VERSION, true );
}

/**
 * Enqueue admin entry script.
 */
function MRT_enqueue_admin_entry_script( string $asset_url ): void {
	wp_enqueue_script(
		'mrt-admin',
		$asset_url . 'admin.js',
		array(
			'mrt-admin-utils',
			'mrt-admin-route-ui',
			'mrt-admin-stoptimes-ui',
			'mrt-admin-timetable-services',
			'mrt-admin-service-edit',
			'mrt-admin-timetable-preview',
			'mrt-admin-timetable-workspace',
			'jquery',
		),
		MRT_VERSION,
		true
	);
}

/**
 * Strings passed to mrtAdmin (admin bundle).
 *
 * @return array<string, string>
 */
function MRT_admin_script_localization(): array {
	return array(
		'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
		'invalidTimeFormat'            => __( 'Invalid format. Use HH:MM (e.g., 09:15)', MRT_TEXT_DOMAIN ),
		'fixTimeFormats'               => __( 'Please fix invalid time formats before saving. Use HH:MM format (e.g., 09:15).', MRT_TEXT_DOMAIN ),
		'saveServiceToUpdateStations'  => __( 'Please save the service to update available stations from the selected route.', MRT_TEXT_DOMAIN ),
		'pleaseSelectStation'          => __( 'Please select a station.', MRT_TEXT_DOMAIN ),
		'stationAlreadyOnRoute'        => __( 'This station is already on the route.', MRT_TEXT_DOMAIN ),
		'pleaseFillStationAndSequence' => __( 'Please fill in Station and Sequence.', MRT_TEXT_DOMAIN ),
		'errorSavingStopTime'          => __( 'Error saving stop time.', MRT_TEXT_DOMAIN ),
		'errorAddingStopTime'          => __( 'Error adding stop time.', MRT_TEXT_DOMAIN ),
		'confirmDeleteStopTime'        => __( 'Are you sure you want to delete this stop time?', MRT_TEXT_DOMAIN ),
		'errorDeletingStopTime'        => __( 'Error deleting stop time.', MRT_TEXT_DOMAIN ),
		'pleaseSelectRoute'            => __( 'Please select a route.', MRT_TEXT_DOMAIN ),
		'securityTokenMissing'         => __( 'Security token missing. Please refresh the page.', MRT_TEXT_DOMAIN ),
		'confirmRemoveTrip'            => __( 'Are you sure you want to remove this trip from the timetable?', MRT_TEXT_DOMAIN ),
		'errorRemovingTrip'            => __( 'Error removing trip.', MRT_TEXT_DOMAIN ),
		'networkError'                 => __( 'Network error. Please try again.', MRT_TEXT_DOMAIN ),
		'moveUp'                       => __( 'Move up', MRT_TEXT_DOMAIN ),
		'moveDown'                     => __( 'Move down', MRT_TEXT_DOMAIN ),
		'remove'                       => __( 'Remove', MRT_TEXT_DOMAIN ),
		'loadingStations'              => __( 'Loading stations...', MRT_TEXT_DOMAIN ),
		'noRouteSelected'              => __( 'No route selected. Select a route to configure stop times.', MRT_TEXT_DOMAIN ),
		'noStationsOnRoute'            => __( 'No stations found on this route.', MRT_TEXT_DOMAIN ),
		'errorLoadingStations'         => __( 'Error loading stations. Please refresh the page.', MRT_TEXT_DOMAIN ),
		'stopTimeSavedSuccessfully'    => __( 'Stop time saved successfully.', MRT_TEXT_DOMAIN ),
		'stopTimeAddedSuccessfully'    => __( 'Stop time added successfully.', MRT_TEXT_DOMAIN ),
		'endStationsSavedSuccessfully' => __( 'End stations saved successfully.', MRT_TEXT_DOMAIN ),
		'selectDestination'            => __( '— Select Destination —', MRT_TEXT_DOMAIN ),
		'selectRouteFirst'             => __( 'Select a route first', MRT_TEXT_DOMAIN ),
		'loading'                      => __( 'Loading...', MRT_TEXT_DOMAIN ),
		'errorLoadingDestinations'     => __( 'Error loading destinations', MRT_TEXT_DOMAIN ),
		'saving'                       => __( 'Saving...', MRT_TEXT_DOMAIN ),
		'adding'                       => __( 'Adding...', MRT_TEXT_DOMAIN ),
		'timeHint'                     => __( 'Leave empty if train stops but time is not fixed', MRT_TEXT_DOMAIN ),
		'pickup'                       => __( 'Pickup', MRT_TEXT_DOMAIN ),
		'dropoff'                      => __( 'Dropoff', MRT_TEXT_DOMAIN ),
		'edit'                         => __( 'Edit', MRT_TEXT_DOMAIN ),
		'tripAdded'                    => __( 'Trip added successfully.', MRT_TEXT_DOMAIN ),
		'tripRemoved'                  => __( 'Trip removed successfully.', MRT_TEXT_DOMAIN ),
		'saved'                        => __( '✓ Saved', MRT_TEXT_DOMAIN ),
		'defaultTrainType'             => __( '— Default train type —', MRT_TEXT_DOMAIN ),
		'deviationNoticePlaceholder'   => __( 'Message to travellers (e.g. steam loco replaced by diesel)', MRT_TEXT_DOMAIN ),
		'previewLoaded'                => __( 'Preview loaded', MRT_TEXT_DOMAIN ),
		'selectTrafficDay'             => __( '— Select traffic day —', MRT_TEXT_DOMAIN ),
		'deviationNoticeShort'         => __( 'Message to travellers', MRT_TEXT_DOMAIN ),
	);
}

/**
 * Enqueue admin assets.
 */
function MRT_enqueue_admin_assets( string $hook ): void {
	if ( ! MRT_should_load_admin_assets( $hook ) ) {
		return;
	}
	MRT_enqueue_admin_css( $hook );
	MRT_enqueue_admin_js();
	$l10n = MRT_admin_script_localization();
	if ( MRT_allow_script_debug() ) {
		$l10n['developmentMode'] = '1';
	}
	wp_localize_script( 'mrt-admin', 'mrtAdmin', $l10n );
	wp_add_inline_script(
		'mrt-admin',
		'window.mrtDebug = !!(window.mrtAdmin && window.mrtAdmin.developmentMode);',
		'before'
	);
}
add_action( 'admin_enqueue_scripts', 'MRT_enqueue_admin_assets' );
