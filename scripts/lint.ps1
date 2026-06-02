# Run PHPStan and PHPCS in Docker (requires: Docker running).
$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot\..

& docker info 2>$null | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Docker is not running. Start Docker Desktop and retry."
    exit 1
}

if (-not (Test-Path vendor)) {
    Write-Host "vendor/ missing - installing via Docker..."
    & docker compose --profile tools run --rm composer install --no-interaction
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

Write-Host "Running PHPStan in Docker..."
& docker compose --profile tools run --rm composer phpstan -- --no-progress
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "Running PHPCS in Docker..."
& docker compose --profile tools run --rm composer phpcs
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "Lint OK."
