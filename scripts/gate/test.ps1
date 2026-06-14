# Run PHPUnit in Docker by default (PHP 8.2, CI parity). Use -Local for host PHP 8.2+.
param(
    [switch]$Local,
    [switch]$Timings,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot '_runner.ps1')
$Passthrough = @($Passthrough | Where-Object { -not [string]::IsNullOrWhiteSpace($_) })
Initialize-MrtGateEnvironment -Timings:$Timings -EnsureVendor -PreferHostVendor:$Local

Invoke-MrtWithDockerDefault -Local:$Local `
    -DockerHint 'Using Docker (php:8.2-cli). Pass -Local to run on host PHP.' `
    -DockerUnavailableMessage 'PHP not in PATH and Docker is not running.' `
    -DockerAction {
        Assert-MrtDockerAvailable
        Invoke-MrtTimedStep -Title 'PHPUnit (Docker)' -SkipStepHeader -Action {
            Invoke-MrtDockerPhpUnit -PhpUnitArgs $Passthrough -ExitOnError
        }
        Complete-MrtGateEnvironment
    } `
    -LocalAction {
        Invoke-MrtTimedStep -Title 'PHPUnit (local)' -Action {
            Assert-MrtLocalPhpMin -MinVersion '8.2' | Out-Null
            Write-Host 'Running PHPUnit locally (composer test)...'
            if ($Passthrough.Count -gt 0) {
                & composer test -- @Passthrough
            } else {
                & composer test
            }
        }
        Complete-MrtGateEnvironment
    }
