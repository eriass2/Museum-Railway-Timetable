<?php
/**
 * Run npm ci only when node_modules is missing or package-lock.json changed.
 *
 * Usage: php scripts/npm-ci-if-needed.php [npm-prefix]
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

$root   = dirname(__DIR__);
$prefix = $argv[1] ?? 'frontend/vue';
$dir    = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $prefix);
$lock   = $dir . DIRECTORY_SEPARATOR . 'package-lock.json';
$marker = $dir . DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR . '.package-lock.json';

if (! is_file($lock)) {
	fwrite(STDERR, "Missing package-lock.json in {$prefix}\n");
	exit(1);
}

if (MRT_npm_ci_needed($dir, $lock, $marker)) {
	echo "Running npm ci...\n";
	$cmd = 'npm --prefix ' . escapeshellarg($prefix) . ' ci';
	passthru($cmd, $code);
	if ($code !== 0) {
		exit($code);
	}
	if (! copy($lock, $marker)) {
		fwrite(STDERR, "Failed to write npm ci marker in {$prefix}/node_modules\n");
		exit(1);
	}
	exit(0);
}

echo "Skipped npm ci (node_modules matches package-lock.json)\n";

/**
 * @param string $dir    npm project directory.
 * @param string $lock   package-lock.json path.
 * @param string $marker node_modules/.package-lock.json path.
 */
function MRT_npm_ci_needed(string $dir, string $lock, string $marker): bool {
	$modules = $dir . DIRECTORY_SEPARATOR . 'node_modules';
	if (! is_dir($modules)) {
		return true;
	}
	if (! is_file($marker)) {
		return true;
	}
	return ! MRT_lock_files_match($lock, $marker);
}

/**
 * @param string $lock   package-lock.json path.
 * @param string $marker cached lock copy path.
 */
function MRT_lock_files_match(string $lock, string $marker): bool {
	$lock_size = filesize($lock);
	$mark_size = filesize($marker);
	if ($lock_size === false || $mark_size === false || $lock_size !== $mark_size) {
		return false;
	}
	return hash_file('sha256', $lock) === hash_file('sha256', $marker);
}
