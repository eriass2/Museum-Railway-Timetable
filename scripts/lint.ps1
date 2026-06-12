# Run PHPStan and PHPCS in Docker (requires: Docker running).
param(
    [switch]$Timings
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot
Initialize-MrtScriptTimings -Timings:$Timings

Assert-MrtDockerAvailable
Ensure-MrtVendor

Invoke-MrtTimedStep -Title 'composer lint (Docker)' -Action {
    Invoke-MrtDockerComposer -ComposerArgs @('lint') -ExitOnError
}

Write-Host 'Lint OK.' -ForegroundColor Green
Complete-MrtScriptTimings
