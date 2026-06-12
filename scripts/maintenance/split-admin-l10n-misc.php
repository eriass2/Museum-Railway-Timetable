<?php
$root   = dirname( __DIR__, 2 );
$source = file_get_contents( $root . '/inc/assets/admin-vue-l10n-misc.php' );
$outDir = $root . '/inc/assets/l10n/';
$map    = array(
	'editor'        => 'MRT_admin_vue_l10n_editor',
	'mobile'        => 'MRT_admin_vue_l10n_mobile',
	'stop_times'    => 'MRT_admin_vue_l10n_stop_times',
	'dev'           => 'MRT_admin_vue_l10n_dev',
	'setup'         => 'MRT_admin_vue_l10n_setup',
	'route_preview' => 'MRT_admin_vue_l10n_route_preview',
);

function extract_function( string $source, string $name ): ?string {
	if ( ! preg_match( '/function ' . preg_quote( $name, '/' ) . '\s*\([^)]*\)[^{]*\{.*\n\}/s', $source, $m ) ) {
		return null;
	}
	return trim( $m[0] );
}

$header = <<<'PHP'
<?php
/**
 * Admin Vue l10n: %s
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


PHP;

foreach ( $map as $slug => $name ) {
	$body = extract_function( $source, $name );
	if ( $body === null ) {
		fwrite( STDERR, "Missing $name\n" );
		exit( 1 );
	}
	$desc = str_replace( '_', ' ', $slug );
	file_put_contents(
		$outDir . 'admin-vue-l10n-' . $slug . '.php',
		sprintf( $header, $desc ) . $body . "\n"
	);
	echo "wrote $slug\n";
}
