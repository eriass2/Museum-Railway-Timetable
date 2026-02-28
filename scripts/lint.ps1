# Run PHPStan and PHPCS (requires: composer install)
$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot\..

if (-not (Test-Path vendor)) {
    Write-Host "Run 'composer install' first."
    exit 1
}

Write-Host "Running PHPStan..."
& .\vendor\bin\phpstan analyse --no-progress

Write-Host "Running PHPCS..."
& .\vendor\bin\phpcs

Write-Host "Lint OK."
