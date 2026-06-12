# Sync Museum Railway Timetable plugin to a live/staging WordPress site.
#
# Usage (from repo root):
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\live-deploy.ps1
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\live-deploy.ps1 -SkipBuild
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\live-deploy.ps1 -Watch
#
# Config: copy local/live-deploy.config.example.json to local/live-deploy.config.json

param(
    [switch] $SkipBuild = $false,
    [switch] $Watch = $false,
    [switch] $UseDocker = $false,
    [string] $ConfigPath = ''
)

$ErrorActionPreference = 'Stop'
$scriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
. (Join-Path $scriptsRoot 'lib/Mrt.Docker.ps1')
. (Join-Path $scriptsRoot 'lib/Mrt.LiveDeploy.ps1')
$projectRoot = Set-MrtRepoRoot -ScriptsDirectory $scriptsRoot

if (-not $ConfigPath) {
    $ConfigPath = Join-Path $projectRoot 'local/live-deploy.config.json'
}

$deployParams = @{
    ProjectRoot = $projectRoot
    ConfigPath  = $ConfigPath
    PluginItems = $script:MrtPluginItems
    SkipBuild   = [bool] $SkipBuild
    UseDocker   = [bool] $UseDocker
}

if ($Watch) {
    Start-MrtLiveDeployWatch @deployParams -PluginSlug $script:MrtPluginSlug
} else {
    Invoke-MrtLiveDeploy @deployParams -BuildVue (-not $SkipBuild)
}
