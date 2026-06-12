# Bootstrap host PHP + Node deps for CI-parity local gates (Fas 3 S3).
param()

$ErrorActionPreference = 'Stop'
$scriptsRoot = $PSScriptRoot
. (Join-Path $scriptsRoot 'lib/Mrt.Plugin.ps1')
$root = Set-MrtRepoRoot -ScriptsDirectory $scriptsRoot

Write-Host '=== MRT setup-dev (host) ===' -ForegroundColor Cyan

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host 'PHP not in PATH. Install PHP 8.2+ or use Docker gates (.\scripts\mrt.ps1 check).' -ForegroundColor Red
    exit 1
}
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Host 'Composer not in PATH.' -ForegroundColor Red
    exit 1
}

Write-Host 'Installing Composer dependencies...'
& composer install --no-interaction
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

if (Get-Command npm -ErrorAction SilentlyContinue) {
    & php (Join-Path $scriptsRoot 'npm-ci-if-needed.php') frontend/vue
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
} else {
    Write-Host 'npm not in PATH - skip frontend/vue (use Docker: .\scripts\mrt.ps1 vue-check).'
}

Write-Host ''
Write-Host 'Host dev ready.'
Write-Host '  composer check; composer vue:check   # same as GitHub Actions validate job'
Write-Host '  .\scripts\mrt.ps1 check              # Docker gates'
Write-Host '  .\scripts\mrt.ps1 dev reset          # full WordPress dev stack'
