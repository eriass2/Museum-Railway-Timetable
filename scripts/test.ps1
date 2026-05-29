# Run PHPUnit via Composer (never open vendor/bin/phpunit directly on Windows).
# Requires: composer install, PHP >= 8.2 locally OR Docker (see below).
$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot\..

if (-not (Test-Path vendor)) {
    Write-Host "Run 'composer install' first (or use Docker below)."
    exit 1
}

$phpVersion = & php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "PHP not found in PATH. Use Docker:" -ForegroundColor Yellow
    Write-Host "  docker compose --profile tools run --rm composer test"
    exit 1
}

if ([version]$phpVersion -lt [version]"8.2") {
    Write-Host "Local PHP is $phpVersion; PHPUnit 11 needs PHP 8.2+. Use Docker:" -ForegroundColor Yellow
    Write-Host "  docker compose --profile tools run --rm composer test"
    exit 1
}

Write-Host "Running PHPUnit (composer test)..."
& composer test
exit $LASTEXITCODE
