<?php
$root   = dirname( __DIR__, 2 );
$cmd    = 'git -C ' . escapeshellarg($root) . ' show HEAD:inc/assets/admin-vue-help.php';
$source = shell_exec($cmd);
if (!is_string($source) || $source === '') {
	fwrite(STDERR, "git show HEAD:inc/assets/admin-vue-help.php failed\n");
	exit(1);
}
$lines = preg_split('/\R/', $source) ?: array();

function extract_function(array $lines, string $name): ?string {
	$start = null;
	$n     = count($lines);
	for ($i = 0; $i < $n; ++$i) {
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
		$line    = $lines[$i];
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

function write_data_file(string $path, string $slug, string $description, string $body): void {
	$header = <<<PHP
<?php
/**
 * $description
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


PHP;
	file_put_contents($path, $header . $body . "\n");
}

$outDir = $root . '/inc/assets/data/admin-help/';
$groups = array(
	'sections.php' => array(
		'description' => 'Admin help structured sections (workflow, shortcode steps, admin areas).',
		'functions'   => array(
			'MRT_admin_vue_help_admin_sections',
			'MRT_admin_vue_help_workflow_steps',
			'MRT_admin_vue_help_operations',
			'MRT_admin_vue_help_shortcodes_howto_steps',
			'MRT_admin_vue_help_shortcodes_setup_steps',
		),
	),
	'shortcodes.php' => array(
		'description' => 'Admin help shortcode reference data.',
		'functions'   => array( 'MRT_admin_vue_help_shortcodes' ),
	),
	'faq.php' => array(
		'description' => 'Admin help FAQ items.',
		'functions'   => array( 'MRT_admin_vue_help_faq_items' ),
	),
);

foreach ($groups as $file => $spec) {
	$parts = array();
	foreach ($spec['functions'] as $name) {
		$body = extract_function($lines, $name);
		if ($body === null) {
			fwrite(STDERR, "Missing $name in $file\n");
			exit(1);
		}
		$parts[] = $body;
	}
	write_data_file($outDir . $file, $file, $spec['description'], implode("\n\n", $parts));
	echo "wrote $file\n";
}
