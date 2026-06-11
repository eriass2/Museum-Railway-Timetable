# Deploy Museum Railway Timetable plugin to Local WordPress
# Usage: .\local\deploy.ps1 [-OpenBrowser]
# Run: .\local\deploy.ps1 -OpenBrowser   # to also open localhost after deploy

param(
    [switch]$OpenBrowser = $false
)

$ErrorActionPreference = 'Stop'
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
. (Join-Path (Split-Path -Parent $scriptDir) 'scripts/lib/Mrt.Plugin.ps1')
$projectRoot = Split-Path -Parent $scriptDir
Set-Location $projectRoot

$configPath = Join-Path $scriptDir 'deploy.config.json'

# Default paths - Local by Flywheel typical structure
$defaultLocalPath = "$env:USERPROFILE\Local Sites\test\app\public\wp-content\plugins\museum-railway-timetable"
$defaultUrl = 'http://test.local'

# Load config if exists
$localPath = $defaultLocalPath
$localUrl = $defaultUrl

if (Test-Path $configPath) {
    try {
        $config = Get-Content $configPath -Raw | ConvertFrom-Json
        if ($config.localPath) { $localPath = $config.localPath }
        if ($config.localUrl) { $localUrl = $config.localUrl }
    } catch {
        Write-Host 'Warning: Could not parse deploy.config.json, using defaults' -ForegroundColor Yellow
    }
}

$script:MrtRepoRoot = $projectRoot

Write-Host "`nDeploying Museum Railway Timetable to Local..." -ForegroundColor Cyan
Write-Host "  Source: $projectRoot" -ForegroundColor Gray
Write-Host "  Target: $localPath" -ForegroundColor Gray
Write-Host ''

# Ensure target directory exists
$targetParent = Split-Path -Parent $localPath
if (-not (Test-Path $targetParent)) {
    Write-Host "ERROR: Local plugins folder not found: $targetParent" -ForegroundColor Red
    Write-Host 'Create a site in Local first, or edit local/deploy.config.json with your site path.' -ForegroundColor Yellow
    Write-Host "Example path: $env:USERPROFILE\Local Sites\YOUR-SITE-NAME\app\public\wp-content\plugins\museum-railway-timetable" -ForegroundColor Gray
    exit 1
}

if (-not (Test-Path $localPath)) {
    New-Item -ItemType Directory -Path $localPath -Force | Out-Null
    Write-Host 'Created plugin folder' -ForegroundColor Green
}

$copied = Copy-MrtPluginTree -SourceRoot $projectRoot -TargetRoot $localPath

Write-Host "`nDeploy complete! ($copied items)" -ForegroundColor Green

if ($OpenBrowser) {
    Write-Host "Opening $localUrl in browser..." -ForegroundColor Cyan
    Start-Process $localUrl
}
