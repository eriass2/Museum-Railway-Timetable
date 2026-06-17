# Docker smoke test for Museum Railway Timetable
# Usage: powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-smoke.ps1

param(
    [switch] $Timings
)

$ErrorActionPreference = 'Stop'
$scriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
. (Join-Path $scriptsRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $scriptsRoot
Initialize-MrtScriptTimings -Timings:$Timings

Write-Host "`n=== Docker smoke ===" -ForegroundColor Cyan

Assert-MrtDockerAvailable
Start-MrtDockerStack -ExitOnError
Wait-MrtWordPressReady

Write-MrtStep -Title 'Build Vue public bundle (CSS + JS)'
Invoke-MrtDockerVue -Mode BuildVerify -StreamOutput -ExitOnError

Write-MrtStep -Title 'Import demo data + sync public pages'
$syncEval = @(
	"if (function_exists('MRT_run_lennakatten_import')) { MRT_run_lennakatten_import(); }",
	"if (function_exists('MRT_dev_cli_set_admin_user')) { MRT_dev_cli_set_admin_user(); }",
	"if (function_exists('MRT_sync_timetable_public_pages')) { MRT_sync_timetable_public_pages(); }",
	"if (function_exists('MRT_dev_activate_twentytwentyfive_theme')) { MRT_dev_activate_twentytwentyfive_theme(); }"
) -join ' '
Invoke-MrtWpCli -WpArgs @('eval', $syncEval) -StreamOutput

Write-MrtStep -Title 'Smoke page URLs (WP-CLI)'
$pages = Get-MrtSmokePageUrlEntries
if ($pages.Count -eq 0) {
    Write-Host '  WARN: No smoke page URLs from WP-CLI.' -ForegroundColor Yellow
} else {
    foreach ($entry in $pages) {
        Write-Host ("  {0}: {1}" -f $entry.Name, $entry.Url) -ForegroundColor Gray
    }
}

Write-MrtStep -Title 'Composer check (PHP 8.3)'
Ensure-MrtVendor
Invoke-MrtDockerComposer -ComposerArgs @('check') -StreamOutput -ExitOnError

Write-MrtStep -Title 'HTTP checks'
$httpPages = @($pages)
$frontEval = 'echo home_url("/");'
$frontOut = Invoke-MrtWpCli -WpArgs @('eval', $frontEval) -ReturnOutput -NoTty
if ($LASTEXITCODE -eq 0 -and $frontOut) {
    $frontUrl = ($frontOut | Where-Object { $_ -match '^https?://' } | Select-Object -Last 1)
    if ($frontUrl) {
        $httpPages += @{ Name = 'Trafikkalender (front)'; Url = [string] $frontUrl }
    }
}
foreach ($p in $httpPages) {
    try {
        $r = Invoke-WebRequest -Uri $p.Url -UseBasicParsing -TimeoutSec 20
        $vueBundle = $r.Content -match 'assets/dist/vue|mrt-vue-public'
        $vueMount = $r.Content -match 'data-mrt-vue-app'
        $wizardMount = $r.Content -match 'mrt-vue-root--wizard'
        $extra = if ($p.Name -match 'Trafikkalender') { " wizard={0}" -f $wizardMount } else { '' }
        Write-Host ("  OK {0} ({1}) vue-bundle={2} vue-mount={3}{4}" -f $p.Name, $r.StatusCode, $vueBundle, $vueMount, $extra) -ForegroundColor Green
    } catch {
        Write-Host ("  FAIL {0}: {1}" -f $p.Name, $_.Exception.Message) -ForegroundColor Red
    }
}

Write-Host "`nAdmin: $script:MrtDevSiteUrl/wp-admin/admin.php?page=mrt_app (admin / admin)" -ForegroundColor Gray
Write-Host "Done.`n" -ForegroundColor Cyan
Complete-MrtScriptTimings
