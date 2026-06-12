# Run Vue typecheck, Vitest, build, and verify in Docker by default (node:22-alpine).
# Use -Local when Node/npm is installed on the host.
param(
    [switch]$Local,
    [switch]$Timings
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot
Initialize-MrtScriptTimings -Timings:$Timings

Invoke-MrtWithDockerDefault -Local:$Local `
    -DockerUnavailableWarning:(Test-MrtNpmAvailable) `
    -DockerUnavailableMessage $(if (Test-MrtNpmAvailable) {
        'Docker is not running. Pass -Local to use host npm.'
    } else {
        'Docker is not running and npm is not in PATH.'
    }) `
    -DockerAction {
        Invoke-MrtTimedStep -Title 'Vue check (Docker)' -SkipStepHeader -Action {
            Invoke-MrtDockerVue -Mode Check -ExitOnError
        }
    } `
    -LocalAction {
        Invoke-MrtTimedStep -Title 'Vue check (local)' -Action {
            Assert-MrtNpmAvailable
            Write-Host 'Running Vue check locally (composer vue:check)...' -ForegroundColor Cyan
            & composer vue:check
        }
    }
