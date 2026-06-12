<?php
/**
 * Summarize PHPUnit Clover coverage for inc/ (used by scripts/coverage.ps1).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

$clover_path = $argv[1] ?? 'coverage/clover.xml';
if (! is_readable($clover_path)) {
	fwrite(STDERR, "Clover file not found: {$clover_path}\n");
	exit(1);
}

$xml = simplexml_load_file($clover_path);
if ($xml === false) {
	fwrite(STDERR, "Failed to parse Clover XML.\n");
	exit(1);
}

$total_stmts = 0;
$total_covered = 0;
$zero = array();
$low = array();

foreach ($xml->project->file as $file) {
	$path = (string) $file['name'];
	$path = preg_replace('#^/app/#', '', $path) ?? $path;
	if (strpos($path, 'inc/') === false && strpos($path, 'inc\\') === false) {
		continue;
	}

	$metrics = $file->metrics;
	$stmts = (int) $metrics['statements'];
	$covered = (int) $metrics['coveredstatements'];
	if ($stmts <= 0) {
		continue;
	}

	$total_stmts += $stmts;
	$total_covered += $covered;
	$pct = round(100 * $covered / $stmts, 1);

	if ($covered === 0) {
		$zero[] = $path;
	} elseif ($pct < 25) {
		$low[] = array('path' => $path, 'pct' => $pct, 'covered' => $covered, 'stmts' => $stmts);
	}
}

sort($zero);
usort(
	$low,
	static function (array $a, array $b): int {
		return $a['pct'] <=> $b['pct'];
	}
);

$pct_total = $total_stmts > 0 ? round(100 * $total_covered / $total_stmts, 2) : 0.0;

echo "PHP coverage (inc/): {$pct_total}% ({$total_covered}/{$total_stmts} statements)\n\n";
echo 'Zero coverage (' . count($zero) . " files):\n";
foreach ($zero as $path) {
	echo "  {$path}\n";
}

echo "\nUnder 25% (" . count($low) . " files, top 20):\n";
foreach (array_slice($low, 0, 20) as $row) {
	echo "  {$row['pct']}% ({$row['covered']}/{$row['stmts']}) {$row['path']}\n";
}
