<?php
/**
 * REST client bootstrap (restUrl + nonce) for Vue apps.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin REST base URL for the current WordPress site.
 *
 * Uses WordPress rest_url() so pretty permalinks, plain permalinks,
 * localhost, staging, and production all resolve correctly.
 */
function MRT_rest_base_url(): string {
	return esc_url_raw( rest_url( MRT_REST_NAMESPACE ) );
}

/**
 * Shared REST bootstrap for public and admin Vue clients.
 *
 * @return array{restUrl: string, restNonce: string}
 */
function MRT_rest_client_config(): array {
	return array(
		'restUrl'   => MRT_rest_base_url(),
		'restNonce' => wp_create_nonce( 'wp_rest' ),
	);
}
