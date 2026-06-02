# Run PHP quality gates in Docker (validate + PHPStan + PHPUnit; optional PHPCS).
# CI parity on Windows without local PHP 8.2.
param(
    [switch]$SkipPhpcs,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot\..

function Test-DockerAvailable {
    & docker info 2>$null | Out-Null
    return $LASTEXITCODE -eq 0
}

function Invoke-DockerComposer {
    param(
        [string[]]$ComposerArgs
    )

    $dockerArgs = @(
        'compose', '--profile', 'tools', 'run', '--rm', 'composer'
    ) + $ComposerArgs
    & docker @dockerArgs
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}

if (-not (Test-DockerAvailable)) {
    Write-Host "Docker is not running. Start Docker Desktop and retry." -ForegroundColor Red
    exit 1
}

if (-not (Test-Path vendor)) {
    Write-Host "vendor/ missing - installing via Docker..." -ForegroundColor Yellow
    Invoke-DockerComposer -ComposerArgs @('install', '--no-interaction')
}

Write-Host "Running composer check in Docker..." -ForegroundColor Cyan
$checkArgs = @('check')
if ($Passthrough.Count -gt 0) {
    $checkArgs += $Passthrough
}
Invoke-DockerComposer -ComposerArgs $checkArgs

if (-not $SkipPhpcs) {
    Write-Host "Running PHPCS in Docker..." -ForegroundColor Cyan
    Invoke-DockerComposer -ComposerArgs @('phpcs')
}

Write-Host "PHP check OK." -ForegroundColor Green
