<?php
/**
 * Rewrite localhost URLs when Docker host port changes (development only).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * localhost:PORT from the current HTTP request (development Docker only).
 */
function MRT_dev_request_localhost_host(): ?string {
	if ( ! isset( $_SERVER['HTTP_HOST'] ) ) {
		return null;
	}

	$host = strtolower( sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_HOST'] ) ) );
	if ( ! preg_match( '/^localhost:\d+$/', $host ) ) {
		return null;
	}

	return $host;
}

/**
 * Rewrite localhost URLs to the port the developer is actually using (MRT_WP_PORT).
 */
function MRT_rewrite_localhost_dev_url( string $url ): string {
	if ( $url === '' || ! MRT_is_development_mode() ) {
		return $url;
	}

	$host = MRT_dev_request_localhost_host();
	if ( $host === null ) {
		return $url;
	}

	if ( preg_match( '#^(https?)://localhost:\d+(.*)$#i', $url, $matches ) ) {
		return esc_url( 'http://' . $host . $matches[2] );
	}

	return $url;
}

/**
 * Persist siteurl/home when Docker port changes without a full dev reset.
 */
function MRT_sync_dev_site_url_from_request(): void {
	if ( ! MRT_is_development_mode() ) {
		return;
	}

	$host = MRT_dev_request_localhost_host();
	if ( $host === null ) {
		return;
	}

	$expected = 'http://' . $host;

	foreach ( array( 'home', 'siteurl' ) as $option ) {
		$current = (string) get_option( $option );
		if ( $current === $expected ) {
			continue;
		}
		if ( $current !== '' && preg_match( '#^https?://localhost:\d+#i', $current ) ) {
			update_option( $option, $expected );
		}
	}
}

/**
 * @param string              $url     Generated URL.
 * @param string              $path    Request path.
 * @param string|null         $scheme  URL scheme.
 * @param int|string|null     $blog_id Blog ID.
 */
function MRT_filter_rewrite_localhost_dev_url( string $url, $path = '', $scheme = null, $blog_id = null ): string {
	unset( $path, $scheme, $blog_id );
	return MRT_rewrite_localhost_dev_url( $url );
}

/**
 * @param array<int, object> $items Menu items.
 * @return array<int, object>
 */
function MRT_filter_nav_menu_localhost_urls( array $items ): array {
	foreach ( $items as $item ) {
		if ( isset( $item->url ) && is_string( $item->url ) ) {
			$item->url = MRT_rewrite_localhost_dev_url( $item->url );
		}
	}

	return $items;
}

function MRT_bootstrap_dev_localhost_url_filters(): void {
	if ( ! MRT_is_development_mode() ) {
		return;
	}

	add_filter( 'home_url', 'MRT_filter_rewrite_localhost_dev_url', 10, 4 );
	add_filter( 'site_url', 'MRT_filter_rewrite_localhost_dev_url', 10, 4 );
	add_filter( 'wp_nav_menu_objects', 'MRT_filter_nav_menu_localhost_urls', 10, 1 );
}

add_action( 'init', 'MRT_sync_dev_site_url_from_request', 1 );
add_action( 'init', 'MRT_bootstrap_dev_localhost_url_filters', 1 );
