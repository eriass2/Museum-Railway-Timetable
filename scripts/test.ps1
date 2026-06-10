# Run PHPUnit in Docker by default (PHP 8.2, CI parity). Use -Local for host PHP 8.2+.
# Never invoke vendor\bin\phpunit directly on Windows.
param(
    [switch]$Local,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot\..

function Test-DockerAvailable {
    & docker info 2>$null | Out-Null
    return $LASTEXITCODE -eq 0
}

function Invoke-DockerPhpUnit {
    param([string[]]$PhpUnitArgs)

    Write-Host "Running PHPUnit in Docker (php:8.2-cli)..." -ForegroundColor Cyan
    $dockerArgs = @(
        'compose', '--profile', 'tools', 'run', '--rm', 'php-test',
        'vendor/bin/phpunit'
    )
    if ($PhpUnitArgs.Count -gt 0) {
        $dockerArgs += $PhpUnitArgs
    }
    & docker @dockerArgs
    exit $LASTEXITCODE
}

function Ensure-Vendor {
    if (Test-Path vendor) {
        return
    }
    Write-Host "vendor/ missing." -ForegroundColor Yellow
    if (-not (Test-DockerAvailable)) {
        Write-Host "Run 'composer install' or start Docker and retry."
        exit 1
    }
    Write-Host "Installing dependencies via Docker..."
    & docker compose --profile tools run --rm composer install --no-interaction
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}

Ensure-Vendor

$useDocker = -not $Local
$phpVersion = $null

if ($Local) {
    & php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>$null | ForEach-Object { $phpVersion = $_ }
    if ($LASTEXITCODE -ne 0 -or -not $phpVersion) {
        Write-Host "Local PHP not in PATH. Omit -Local to use Docker." -ForegroundColor Red
        exit 1
    }
    if ([version]$phpVersion -lt [version]"8.2") {
        Write-Host "Local PHP $phpVersion < 8.2. Omit -Local to use Docker." -ForegroundColor Red
        exit 1
    }
}

if ($useDocker) {
    if (-not (Test-DockerAvailable)) {
        if ($phpVersion) {
            Write-Host "Local PHP is $phpVersion; PHPUnit 11 needs PHP 8.2+. Docker is not running." -ForegroundColor Red
        } else {
            Write-Host "PHP not in PATH and Docker is not running." -ForegroundColor Red
        }
        exit 1
    }
    Write-Host "Using Docker (php:8.2-cli). Pass -Local to run on host PHP." -ForegroundColor Cyan
    Invoke-DockerPhpUnit -PhpUnitArgs $Passthrough
}

Write-Host "Running PHPUnit locally (composer test)..."
if ($Passthrough.Count -gt 0) {
    & composer test -- @Passthrough
} else {
    & composer test
}
exit $LASTEXITCODE
