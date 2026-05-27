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
 * Public shortcodes always use the Vue frontend on this branch.
 */
function MRT_use_vue_frontend(): bool {
	return (bool) apply_filters( 'mrt_use_vue_frontend', true );
}

/**
 * Mark that a Vue shortcode rendered (for late asset enqueue).
 */
function MRT_vue_shortcode_mark_used(): void {
	$GLOBALS['mrt_vue_shortcode_used'] = true;
}

/**
 * @return bool
 */
function MRT_vue_shortcode_was_used(): bool {
	return ! empty( $GLOBALS['mrt_vue_shortcode_used'] );
}

/**
 * Enqueue Vue bundle once (idempotent).
 */
function MRT_enqueue_vue_frontend_assets_if_needed(): void {
	if ( wp_script_is( 'mrt-vue-public', 'enqueued' ) || wp_script_is( 'mrt-vue-public', 'done' ) ) {
		return;
	}
	MRT_enqueue_vue_frontend_assets();
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
	// Prevent </script> breaking the inline JSON block.
	$json = str_replace( '</', '<\\/', $json );

	MRT_vue_shortcode_mark_used();
	MRT_enqueue_vue_frontend_assets_if_needed();

	$notice = '';
	if ( null === MRT_vue_read_manifest() ) {
		$notice = '<p class="mrt-alert mrt-alert-warning">' . esc_html__(
			'Vue build missing. Run npm install && npm run build in frontend/vue/.',
			'museum-railway-timetable'
		) . '</p>';
	}

	return $notice . sprintf(
		'<div class="mrt-vue-root" data-mrt-vue-app="%1$s"><script type="application/json" class="mrt-vue-config">%2$s</script></div>',
		esc_attr( $app ),
		$json
	);
}

/**
 * Enqueue Vite-built Vue bundle (JS + bundled public CSS).
 *
 * Legacy handles mrt-frontend-public / mrt-journey-wizard are not loaded in Vue mode.
 */
function MRT_enqueue_vue_frontend_assets(): void {
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
			array(),
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

/**
 * Ensure Vite ES-module builds load with type="module" when used (legacy path).
 *
 * @param string $tag    Script tag HTML.
 * @param string $handle Script handle.
 * @param string $src    Script URL.
 * @return string
 */
function MRT_vue_script_loader_tag( string $tag, string $handle, string $src ): string {
	unset( $src );
	if ( 'mrt-vue-public' !== $handle || str_contains( $tag, 'type=' ) ) {
		return $tag;
	}
	$extra = wp_scripts()->get_data( $handle, 'type' );
	if ( 'module' === $extra ) {
		return str_replace( '<script ', '<script type="module" ', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'MRT_vue_script_loader_tag', 10, 3 );
