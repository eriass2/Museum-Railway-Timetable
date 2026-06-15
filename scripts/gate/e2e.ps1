# Static Playwright E2E in Docker (playwright:v1.60-jammy). WP integration: mrt dev e2e-wp (prepare + restore).
param(
    [switch]$Timings,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot '_runner.ps1')
Initialize-MrtGateEnvironment -Timings:$Timings

Invoke-MrtWithDockerDefault -Local:$false `
    -DockerUnavailableMessage 'Docker is not running.' `
    -DockerAction {
        Assert-MrtDockerAvailable
        Invoke-MrtTimedStep -Title 'Vue E2E (Docker)' -SkipStepHeader -Action {
            Invoke-MrtDockerVueE2e -PlaywrightArgs $Passthrough -ExitOnError -StreamOutput
        }
        Complete-MrtGateEnvironment
    } `
    -LocalAction {
        Write-Host 'Vue E2E requires Docker (Playwright browsers). Use: .\scripts\mrt.ps1 e2e' -ForegroundColor Red
        exit 1
    }
