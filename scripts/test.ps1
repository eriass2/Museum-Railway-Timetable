# Run PHPUnit in Docker by default (PHP 8.2, CI parity). Use -Local for host PHP 8.2+.
# Never invoke vendor\bin\phpunit directly on Windows.
param(
    [switch]$Local,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

Ensure-MrtVendor

$phpVersion = $null
if ($Local) {
    $phpVersion = Get-MrtLocalPhpVersion
    if (-not $phpVersion) {
        Write-Host 'Local PHP not in PATH. Omit -Local to use Docker.' -ForegroundColor Red
        exit 1
    }
    if ([version]$phpVersion -lt [version]'8.2') {
        Write-Host "Local PHP $phpVersion < 8.2. Omit -Local to use Docker." -ForegroundColor Red
        exit 1
    }
}

if (-not $Local) {
    if (-not (Test-MrtDockerAvailable)) {
        if ($phpVersion) {
            Write-Host "Local PHP is $phpVersion; PHPUnit 11 needs PHP 8.2+. Docker is not running." -ForegroundColor Red
        } else {
            Write-Host 'PHP not in PATH and Docker is not running.' -ForegroundColor Red
        }
        exit 1
    }
    Write-Host 'Using Docker (php:8.2-cli). Pass -Local to run on host PHP.' -ForegroundColor Cyan
    Invoke-MrtDockerPhpUnit -PhpUnitArgs $Passthrough -ExitOnError
}

Write-Host 'Running PHPUnit locally (composer test)...'
if ($Passthrough.Count -gt 0) {
    & composer test -- @Passthrough
} else {
    & composer test
}
exit $LASTEXITCODE
