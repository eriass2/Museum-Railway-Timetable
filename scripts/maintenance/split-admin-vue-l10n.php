<?php
$root = dirname( __DIR__, 2 );
$cmd  = 'git -C ' . escapeshellarg($root) . ' show HEAD:inc/assets/admin-vue.php';
$source = shell_exec($cmd);
if (!is_string($source) || $source === '') {
	fwrite(STDERR, "git show HEAD:inc/assets/admin-vue.php failed\n");
	exit(1);
}
$lines  = preg_split('/\R/', $source) ?: array();
$outDir = dirname( __DIR__, 2 ) . '/inc/assets/l10n/';
$functions = array(
	'common'        => 'MRT_admin_vue_l10n_common',
	'nav'           => 'MRT_admin_vue_l10n_nav',
	'settings'      => 'MRT_admin_vue_l10n_settings',
	'prices'        => 'MRT_admin_vue_l10n_prices',
	'dashboard'     => 'MRT_admin_vue_l10n_dashboard',
	'stations'      => 'MRT_admin_vue_l10n_stations',
	'timetables'    => 'MRT_admin_vue_l10n_timetables',
	'train_types'   => 'MRT_admin_vue_l10n_train_types',
	'import_export' => 'MRT_admin_vue_l10n_import_export',
	'traffic'       => 'MRT_admin_vue_l10n_traffic',
);

function extract_function(array $lines, string $name): ?string {
	$start = null;
	for ($i = 0, $n = count($lines); $i < $n; ++$i) {
		if (preg_match('/^\s*function\s+' . preg_quote($name, '/') . '\s*\(/', rtrim($lines[$i]))) {
			$start = $i;
			break;
		}
	}
	if ($start === null) {
		return null;
	}
	$depth = 0;
	$in    = false;
	$chunk = array();
	for ($i = $start; $i < $n; ++$i) {
		$line = $lines[$i];
		$chunk[] = $line;
		if (preg_match('/^\s*function\s+/', $line)) {
			$in = true;
		}
		if ($in) {
			$depth += substr_count($line, '{') - substr_count($line, '}');
			if ($depth <= 0 && strpos($line, '}') !== false) {
				return implode("\n", $chunk);
			}
		}
	}
	return null;
}

foreach ($functions as $slug => $name) {
	$body = extract_function($lines, $name);
	if ($body === null) {
		fwrite(STDERR, "Missing $name\n");
		continue;
	}
	$header = <<<PHP
<?php
/**
 * Admin Vue l10n: $slug
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


PHP;
	file_put_contents($outDir . 'admin-vue-l10n-' . $slug . '.php', $header . $body . "\n");
	echo "wrote $slug\n";
}
