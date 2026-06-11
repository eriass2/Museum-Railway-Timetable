# Run Vue typecheck, Vitest, build, and verify in Docker by default (node:22-alpine).
# Use -Local when Node/npm is installed on the host.
param(
    [switch]$Local
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

if (-not $Local) {
    if (-not (Test-MrtDockerAvailable)) {
        if (Test-MrtNpmAvailable) {
            Write-Host 'Docker is not running. Pass -Local to use host npm.' -ForegroundColor Yellow
        } else {
            Write-Host 'Docker is not running and npm is not in PATH.' -ForegroundColor Red
        }
        exit 1
    }
    Invoke-MrtDockerVue -Mode Check -ExitOnError
}

Assert-MrtNpmAvailable

Write-Host 'Running Vue check locally (composer vue:check)...' -ForegroundColor Cyan
& composer vue:check
exit $LASTEXITCODE
