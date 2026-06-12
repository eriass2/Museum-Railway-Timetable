# Run PHPUnit in Docker by default (PHP 8.2, CI parity). Use -Local for host PHP 8.2+.
# Never invoke vendor\bin\phpunit directly on Windows.
param(
    [switch]$Local,
    [switch]$Timings,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = 'Stop'
$scriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
. (Join-Path $scriptsRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot
Initialize-MrtScriptTimings -Timings:$Timings

Ensure-MrtVendor -PreferHost:$Local

Invoke-MrtWithDockerDefault -Local:$Local `
    -DockerHint 'Using Docker (php:8.2-cli). Pass -Local to run on host PHP.' `
    -DockerUnavailableMessage 'PHP not in PATH and Docker is not running.' `
    -DockerAction {
        Invoke-MrtTimedStep -Title 'PHPUnit (Docker)' -SkipStepHeader -Action {
            Invoke-MrtDockerPhpUnit -PhpUnitArgs $Passthrough -ExitOnError
        }
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
    }
