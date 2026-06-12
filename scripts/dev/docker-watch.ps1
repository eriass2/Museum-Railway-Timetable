# Start WordPress stack with plugin volume + compose watch (P7).
# Press Ctrl+C to stop watch; containers keep running.
param(
    [switch] $NoUp
)

$ErrorActionPreference = 'Stop'
$scriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
. (Join-Path $scriptsRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $scriptsRoot

Assert-MrtDockerAvailable

$composeFiles = @('-f', 'docker-compose.yml', '-f', 'docker-compose.watch.yml')

if (-not $NoUp) {
    Write-Host 'Starting stack with plugin volume (watch overlay)...' -ForegroundColor Cyan
    Invoke-MrtDockerCompose -ComposeArgs ($composeFiles + @('up', '-d')) -ExitOnError
}

Write-Host 'Watching plugin files (sync to mrt_plugin volume)...' -ForegroundColor Cyan
Write-Host 'Tip: run dev reset once after first watch start if the plugin volume is empty.' -ForegroundColor DarkGray
Invoke-MrtDockerCompose -ComposeArgs ($composeFiles + @('watch')) -ExitOnError
