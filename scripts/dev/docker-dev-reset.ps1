# Reset Museum Railway Timetable plugin data and re-import Lennakatten test data.
# Also creates smoke pages and adds them to the site menu (development setup).
#
# Usage (from repo root):
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-dev-reset.ps1
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-dev-reset.ps1 -SkipCompose

param(
    [switch] $SkipCompose,
    [switch] $Build,
    [switch] $Timings
)

$ErrorActionPreference = 'Stop'
$scriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
. (Join-Path $scriptsRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $scriptsRoot
Initialize-MrtScriptTimings -Timings:$Timings

Write-Host "`n=== MRT dev reset (clear + import + smoke menu) ===" -ForegroundColor Cyan

if (-not $SkipCompose) {
    Start-MrtDockerStack -ExitOnError -Build:$Build
    Wait-MrtWordPressReady
}

Write-MrtStep -Title 'Build Vue public bundle (CSS + JS)'
Invoke-MrtDockerVue -Mode BuildVerify -StreamOutput -ExitOnError

Invoke-MrtEnsureSvLocale
Set-MrtWpDebug
Invoke-MrtDevResetImport

Write-Host "`nDone. Front: $script:MrtDevSiteUrl  Admin: $script:MrtDevSiteUrl/wp-admin (admin / admin)`n" -ForegroundColor Green
Complete-MrtScriptTimings
