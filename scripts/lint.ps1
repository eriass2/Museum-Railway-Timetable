# Run PHPStan and PHPCS in Docker (requires: Docker running).
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

Assert-MrtDockerAvailable
Ensure-MrtVendor

Write-Host 'Running PHPStan in Docker...'
Invoke-MrtDockerComposer -ComposerArgs @('phpstan', '--', '--no-progress') -ExitOnError

Write-Host 'Running PHPCS in Docker...'
Invoke-MrtDockerComposer -ComposerArgs @('phpcs') -ExitOnError

Write-Host 'Lint OK.'
