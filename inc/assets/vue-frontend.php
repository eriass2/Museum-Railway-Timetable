<?php

declare(strict_types=1);

/**
 * Optional Vue bundle for public shortcodes (experiment branch).
 *
 * Enable with define( 'MRT_VUE_FRONTEND', true ); in wp-config.php
 * or filter mrt_use_vue_frontend.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether shortcodes should mount the Vue experiment instead of legacy HTML/JS.
 */
function MRT_use_vue_frontend(): bool {
	if ( defined( 'MRT_VUE_FRONTEND' ) && MRT_VUE_FRONTEND ) {
		return true;
	}
	return (bool) apply_filters( 'mrt_use_vue_frontend', false );
}

/**
 * Path to built Vue manifest (Vite).
 */
function MRT_vue_dist_dir(): string {
	return MRT_PATH . 'assets/dist/vue/';
}

/**
 * @return array<string, mixed>|null
 */
function MRT_vue_read_manifest(): ?array {
	$path = MRT_vue_dist_dir() . '.vite/manifest.json';
	if ( ! is_readable( $path ) ) {
		return null;
	}
	$raw = file_get_contents( $path );
	if ( ! is_string( $raw ) ) {
		return null;
	}
	$data = json_decode( $raw, true );
	return is_array( $data ) ? $data : null;
}

/**
 * Shared AJAX / i18n bootstrap for Vue apps.
 *
 * @return array<string, mixed>
 */
function MRT_vue_shared_client_config(): array {
	$fe = function_exists( 'MRT_frontend_script_localization' )
		? MRT_frontend_script_localization()
		: array();

	return array(
		'ajaxurl' => isset( $fe['ajaxurl'] ) ? (string) $fe['ajaxurl'] : admin_url( 'admin-ajax.php' ),
		'nonce'   => isset( $fe['nonce'] ) ? (string) $fe['nonce'] : wp_create_nonce( 'mrt_frontend' ),
		'strings' => $fe,
	);
}

/**
 * Render mount node for a Vue app.
 *
 * @param string               $app    month|overview|wizard
 * @param array<string, mixed> $config App-specific config (merged with shared).
 * @return string HTML
 */
function MRT_render_vue_mount( string $app, array $config ): string {
	$allowed = array( 'month', 'overview', 'wizard' );
	if ( ! in_array( $app, $allowed, true ) ) {
		return '';
	}

	$payload = array_merge(
		array( 'app' => $app ),
		MRT_vue_shared_client_config(),
		$config
	);

	$json = wp_json_encode( $payload );
	if ( ! is_string( $json ) ) {
		$json = '{}';
	}

	$notice = '';
	if ( null === MRT_vue_read_manifest() ) {
		$notice = '<p class="mrt-alert mrt-alert-warning">' . esc_html__(
			'Vue build missing. Run npm install && npm run build in frontend/vue/.',
			'museum-railway-timetable'
		) . '</p>';
	}

	return $notice . sprintf(
		'<div class="mrt-vue-root" data-mrt-vue-app="%1$s" data-mrt-config="%2$s"></div>',
		esc_attr( $app ),
		esc_attr( $json )
	);
}

/**
 * Enqueue Vite-built Vue bundle when manifest exists.
 *
 * @param string $public_handle Base public CSS handle.
 */
function MRT_enqueue_vue_frontend_assets( string $public_handle ): void {
	$manifest = MRT_vue_read_manifest();
	if ( null === $manifest ) {
		return;
	}

	$entry = $manifest['src/main.ts'] ?? $manifest['src/main.js'] ?? null;
	if ( ! is_array( $entry ) ) {
		return;
	}

	$base_url = MRT_assets_base_url() . 'dist/vue/';
	$css      = isset( $entry['css'] ) && is_array( $entry['css'] ) ? $entry['css'] : array();
	foreach ( $css as $i => $file ) {
		wp_enqueue_style(
			'mrt-vue-public-' . $i,
			$base_url . $file,
			array( $public_handle ),
			MRT_VERSION
		);
	}

	$js_file = isset( $entry['file'] ) ? (string) $entry['file'] : '';
	if ( $js_file === '' ) {
		return;
	}

	wp_enqueue_script(
		'mrt-vue-public',
		$base_url . $js_file,
		array(),
		MRT_VERSION,
		true
	);
}
