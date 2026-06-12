# Docker smoke test for Museum Railway Timetable
# Usage: powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-smoke.ps1

param(
    [switch] $Timings
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot
Initialize-MrtScriptTimings -Timings:$Timings

Write-Host "`n=== Docker smoke ===" -ForegroundColor Cyan

Assert-MrtDockerAvailable
Start-MrtDockerStack -ExitOnError
Wait-MrtWordPressReady

Write-MrtStep -Title 'Build Vue public bundle (CSS + JS)'
Invoke-MrtDockerVue -Mode BuildVerify -StreamOutput -ExitOnError

Write-MrtStep -Title 'Import demo data'
Invoke-MrtWpCli -WpArgs @('eval', 'MRT_run_lennakatten_import();') -StreamOutput

Write-MrtStep -Title 'Smoke page URLs (WP-CLI)'
$pages = Get-MrtSmokePageUrlEntries
if ($pages.Count -eq 0) {
    Write-Host '  WARN: No smoke page URLs from WP-CLI.' -ForegroundColor Yellow
} else {
    foreach ($entry in $pages) {
        Write-Host ("  {0}: {1}" -f $entry.Name, $entry.Url) -ForegroundColor Gray
    }
}

Write-MrtStep -Title 'Composer check (PHP 8.2)'
Ensure-MrtVendor
Invoke-MrtDockerComposer -ComposerArgs @('check') -StreamOutput -ExitOnError

Write-MrtStep -Title 'HTTP checks'
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

Write-Host "`nAdmin: $script:MrtDevSiteUrl/wp-admin/admin.php?page=mrt_app (admin / admin)" -ForegroundColor Gray
Write-Host "Done.`n" -ForegroundColor Cyan
Complete-MrtScriptTimings
