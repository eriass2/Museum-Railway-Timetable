# Docker smoke test for Museum Railway Timetable
# Usage: powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-smoke.ps1

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

Write-Host "`n=== Docker smoke ===" -ForegroundColor Cyan

docker compose up -d --build
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "Waiting for WordPress init..." -ForegroundColor Gray
Start-Sleep -Seconds 20

Write-Host "`n--- Build Vue public bundle (CSS + JS) ---" -ForegroundColor Cyan
docker compose --profile tools run --rm vue sh -c "npm ci && npm run build && npm run verify" 2>&1 | Out-Host
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "`n--- Import demo data ---" -ForegroundColor Cyan
docker compose run --rm wordpress-init wp --allow-root eval "MRT_run_lennakatten_import();" 2>&1 | Out-Host

Write-Host "`n--- Demo page ---" -ForegroundColor Cyan
$demoOut = docker compose run --rm wordpress-init wp --allow-root eval "`$r = MRT_ensure_components_demo_page_cli(); if (is_wp_error(`$r)) { echo `$r->get_error_message(); } else { wp_update_post(array('ID'=>(int)`$r,'post_status'=>'publish')); echo get_permalink((int)`$r); }" 2>&1
$demoOut | Out-Host
$demoUrl = ($demoOut | Select-String -Pattern 'http\S+' | Select-Object -Last 1).Matches.Value

Write-Host "`n--- Composer check (PHP 8.2) ---" -ForegroundColor Cyan
docker compose --profile tools run --rm composer install --no-interaction 2>&1 | Out-Host
docker compose --profile tools run --rm composer check 2>&1 | Out-Host
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

$pages = @(
    @{ Name = "Wizard test"; Url = "http://localhost:8080/?p=39" },
    @{ Name = "Planner test"; Url = "http://localhost:8080/?p=41" }
)
if ($demoUrl) {
    $pages += @{ Name = "Component demo"; Url = $demoUrl }
}

Write-Host "`n--- HTTP checks ---" -ForegroundColor Cyan
foreach ($p in $pages) {
    try {
        $r = Invoke-WebRequest -Uri $p.Url -UseBasicParsing -TimeoutSec 20
        $vueBundle = $r.Content -match 'assets/dist/vue|mrt-vue-public'
        $vueMount = $r.Content -match 'data-mrt-vue-app'
        Write-Host ("  OK {0} ({1}) vue-bundle={2} vue-mount={3}" -f $p.Name, $r.StatusCode, $vueBundle, $vueMount) -ForegroundColor Green
    } catch {
        Write-Host ("  FAIL {0}: {1}" -f $p.Name, $_.Exception.Message) -ForegroundColor Red
    }
}

Write-Host "`nAdmin: http://localhost:8080/wp-admin/admin.php?page=mrt_app (admin / admin)" -ForegroundColor Gray
Write-Host "Done.`n" -ForegroundColor Cyan
