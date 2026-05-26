# Reset Museum Railway Timetable plugin data and re-import Lennakatten test data.
# Also creates smoke pages and adds them to the site menu (development setup).
#
# Usage (from repo root):
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-dev-reset.ps1
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-dev-reset.ps1 -SkipCompose

param(
    [switch] $SkipCompose
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

Write-Host "`n=== MRT dev reset (clear + import + smoke menu) ===" -ForegroundColor Cyan

if (-not $SkipCompose) {
    docker compose up -d
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    Write-Host "Waiting for WordPress..." -ForegroundColor Gray
    Start-Sleep -Seconds 12
}

Write-Host "`n--- Build Vue public bundle (CSS + JS) ---" -ForegroundColor Cyan
docker compose --profile tools run --rm vue 2>&1 | ForEach-Object { Write-Host $_ }
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "`n--- Enable WP_DEBUG + Vue frontend (experiment) ---" -ForegroundColor Cyan
docker compose run --rm --user root wordpress-init wp --allow-root config set WP_DEBUG true --raw 2>&1 | ForEach-Object { Write-Host $_ }
docker compose run --rm --user root wordpress-init wp --allow-root config set WP_DEBUG_LOG true --raw 2>&1 | ForEach-Object { Write-Host $_ }
docker compose run --rm --user root wordpress-init wp --allow-root config set MRT_VUE_FRONTEND true --raw 2>&1 | ForEach-Object { Write-Host $_ }

Write-Host "`n--- Reset and import ---" -ForegroundColor Cyan
$eval = "if (!function_exists('MRT_dev_reset_and_import_cli')) { fwrite(STDERR, 'Plugin not active or dev-cli not loaded'.PHP_EOL); exit(1); } MRT_dev_reset_and_import_cli();"
$prevEap = $ErrorActionPreference
$ErrorActionPreference = 'Continue'
docker compose run --rm wordpress-init wp --allow-root eval $eval 2>&1 | ForEach-Object { Write-Host $_ }
$exitCode = $LASTEXITCODE
$ErrorActionPreference = $prevEap
if ($exitCode -ne 0) { exit $exitCode }

Write-Host "`nDone. Front: http://localhost:8080  Admin: http://localhost:8080/wp-admin (admin / admin)`n" -ForegroundColor Green
