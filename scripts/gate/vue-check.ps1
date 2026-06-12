# Run Vue typecheck, Vitest, build, and verify in Docker by default (node:22-alpine).
param(
    [switch]$Local,
    [switch]$Timings
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot '_runner.ps1')
Initialize-MrtGateEnvironment -Timings:$Timings

Invoke-MrtWithDockerDefault -Local:$Local `
    -DockerUnavailableWarning:(Test-MrtNpmAvailable) `
    -DockerUnavailableMessage $(if (Test-MrtNpmAvailable) {
        'Docker is not running. Pass -Local to use host npm.'
    } else {
        'Docker is not running and npm is not in PATH.'
    }) `
    -DockerAction {
        Assert-MrtDockerAvailable
        Invoke-MrtTimedStep -Title 'Vue check (Docker)' -SkipStepHeader -Action {
            Invoke-MrtDockerVue -Mode Check -ExitOnError
        }
        Complete-MrtGateEnvironment
    } `
    -LocalAction {
        Invoke-MrtTimedStep -Title 'Vue check (local)' -Action {
            Assert-MrtNpmAvailable
            Write-Host 'Running Vue check locally (composer vue:check)...' -ForegroundColor Cyan
            & composer vue:check
        }
        Complete-MrtGateEnvironment
    }
