<?php

declare(strict_types=1);

/**
 * Vue bundle for public shortcodes (month, overview, journey wizard).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
 * Shared REST / i18n bootstrap for Vue apps.
 *
 * @return array<string, mixed>
 */
function MRT_vue_shared_client_config(): array {
	$fe = function_exists( 'MRT_frontend_script_localization' )
		? MRT_frontend_script_localization()
		: array();

	$shared = array_merge(
		MRT_rest_client_config(),
		array( 'strings' => $fe )
	);

	$trip_pdf_url = MRT_vue_trip_pdf_script_url();
	if ( null !== $trip_pdf_url ) {
		$shared['tripPdfUrl'] = $trip_pdf_url;
	}

	return $shared;
}

/**
 * URL for lazy-loaded trip PDF helper script, when built.
 */
function MRT_vue_trip_pdf_script_url(): ?string {
	$path = MRT_vue_dist_dir() . 'assets/trip-pdf.js';
	if ( ! is_readable( $path ) ) {
		return null;
	}
	return MRT_assets_base_url() . 'dist/vue/assets/trip-pdf.js';
}

/**
 * Render mount node for a Vue app.
 *
 * @param string               $app    month|overview|wizard|index
 * @param array<string, mixed> $config App-specific config (merged with shared).
 * @return string HTML
 */
function MRT_render_vue_mount( string $app, array $config ): string {
	$allowed = array( 'month', 'overview', 'wizard', 'index', 'traffic_notices' );
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
		'<div class="mrt-vue-root mrt-vue-root--%1$s%3$s" data-mrt-vue-app="%1$s"><script type="application/json" class="mrt-vue-config">%2$s</script></div>',
		esc_attr( $app ),
		$json,
		'overview' === $app || 'month' === $app || 'index' === $app || 'traffic_notices' === $app ? ' alignwide' : ''
	);
}

/**
 * Enqueue Vite-built Vue bundle (JS + bundled public CSS).
 *
 * Bundled CSS replaces separate mrt-frontend-public / mrt-journey-wizard handles.
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
	$css          = isset( $entry['css'] ) && is_array( $entry['css'] ) ? $entry['css'] : array();
	$last_css_hdl = '';
	foreach ( $css as $i => $file ) {
		$handle = 'mrt-vue-public-' . $i;
		wp_enqueue_style(
			$handle,
			$base_url . $file,
			array(),
			MRT_VERSION
		);
		$last_css_hdl = $handle;
	}
	if ( function_exists( 'MRT_enqueue_brand_token_overrides' ) ) {
		MRT_enqueue_brand_token_overrides( $last_css_hdl );
	}

	$js_file = isset( $entry['file'] ) ? (string) $entry['file'] : '';
	if ( $js_file === '' ) {
		return;
	}

	// ES module entry must not get ?ver= — lazy chunks import ./main-*.js without query args;
	// a mismatched URL would load Vue twice and break mount (runtime null refs).
	wp_enqueue_script(
		'mrt-vue-public',
		$base_url . $js_file,
		array(),
		null,
		true
	);
	wp_script_add_data( 'mrt-vue-public', 'type', 'module' );
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
