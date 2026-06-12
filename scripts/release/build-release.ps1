# Build Vue bundles and pack a production-ready plugin zip for live WordPress.
#
# Usage (from repo root):
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\release\build-release.ps1
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\release\build-release.ps1 -SkipBuild
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\release\build-release.ps1 -UseDocker

param(
    [switch] $SkipBuild = $false,
    [switch] $SkipValidate = $false,
    [switch] $UseDocker = $false
)

$ErrorActionPreference = 'Stop'
$scriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
. (Join-Path $scriptsRoot 'lib/Mrt.Docker.ps1')
. (Join-Path $scriptsRoot 'lib/Mrt.Release.ps1')
$projectRoot = Set-MrtRepoRoot -ScriptsDirectory $scriptsRoot

Invoke-MrtReleaseBuild -ProjectRoot $projectRoot -PluginSlug $script:MrtPluginSlug `
    -PluginItems $script:MrtPluginItems -SkipBuild:$SkipBuild -SkipValidate:$SkipValidate `
    -UseDocker:$UseDocker
