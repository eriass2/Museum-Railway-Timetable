<?php
/**
 * Find function declarations where body span (start line → closing brace line) > 50.
 *
 * @noinspection PhpUnusedLocalVariableInspection
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$skipDirs = ['vendor', 'node_modules', '.git'];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$results = [];

foreach ($it as $f) {
    $path = $f->getPathname();
    $rel = substr($path, strlen($root) + 1);
    foreach ($skipDirs as $sd) {
        if (str_contains($rel, $sd . DIRECTORY_SEPARATOR) || $rel === $sd) {
            continue 2;
        }
    }
    if (!preg_match('/\.(php|js)$/', $rel)) {
        continue;
    }
    if (str_starts_with($rel, 'tests' . DIRECTORY_SEPARATOR)) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }
    $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));

    $n = count($lines);
    for ($i = 0; $i < $n; $i++) {
        $line = $lines[$i];
        if (!preg_match('/(?:^|\s)(?:async\s+)?function\s*(\w*)\s*\(/', $line, $m)) {
            continue;
        }
        $name = $m[1] !== '' ? $m[1] : '(anon)';
        $startLine = $i;
        $chunk = '';
        for ($j = $i; $j < $n; $j++) {
            $chunk .= $lines[$j] . "\n";
            if (str_contains($lines[$j], '{')) {
                break;
            }
        }
        if (!str_contains($chunk, '{')) {
            continue;
        }
        $depth = 0;
        $started = false;
        $endLine = $i;
        for ($j = $i; $j < $n; $j++) {
            $ln = $lines[$j];
            $depth += substr_count($ln, '{') - substr_count($ln, '}');
            if (str_contains($ln, '{')) {
                $started = true;
            }
            if ($started && $depth === 0) {
                $endLine = $j;
                break;
            }
        }
        if (!$started || $depth !== 0) {
            continue;
        }
        $len = $endLine - $startLine + 1;
        if ($len > 50) {
            $results[] = [$len, $rel, $startLine + 1, $name];
        }
        $i = $endLine;
    }
}

usort($results, static fn ($a, $b) => $b[0] <=> $a[0]);

foreach ($results as $r) {
    echo $r[0] . "\t" . $r[1] . ':' . $r[2] . ' ' . $r[3] . "\n";
}

echo 'Count: ' . count($results) . "\n";
