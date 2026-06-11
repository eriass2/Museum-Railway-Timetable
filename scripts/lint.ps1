# Run PHPStan and PHPCS in Docker (requires: Docker running).
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

Assert-MrtDockerAvailable
Ensure-MrtVendor

Write-Host 'Running composer lint in Docker...' -ForegroundColor Cyan
Invoke-MrtDockerComposer -ComposerArgs @('lint') -ExitOnError

Write-Host 'Lint OK.' -ForegroundColor Green
