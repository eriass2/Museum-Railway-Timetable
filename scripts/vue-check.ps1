# Run Vue typecheck, Vitest, build, and verify in Docker by default (node:22-alpine).
# Use -Local when Node/npm is installed on the host.
param(
    [switch]$Local
)

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot\..

function Test-DockerAvailable {
    & docker info 2>$null | Out-Null
    return $LASTEXITCODE -eq 0
}

function Test-NpmAvailable {
    try {
        & npm --version 2>$null | Out-Null
        return $LASTEXITCODE -eq 0
    } catch {
        return $false
    }
}

function Invoke-DockerVueCheck {
    Write-Host "Running Vue check in Docker (node:22-alpine)..." -ForegroundColor Cyan
    & docker compose --profile tools run --rm vue sh -c "npm ci && npm run check"
    exit $LASTEXITCODE
}

if (-not $Local) {
    if (-not (Test-DockerAvailable)) {
        if (Test-NpmAvailable) {
            Write-Host "Docker is not running. Pass -Local to use host npm." -ForegroundColor Yellow
        } else {
            Write-Host "Docker is not running and npm is not in PATH." -ForegroundColor Red
        }
        exit 1
    }
    Invoke-DockerVueCheck
}

if (-not (Test-NpmAvailable)) {
    Write-Host "npm not in PATH. Omit -Local to use Docker." -ForegroundColor Red
    exit 1
}

Write-Host "Running Vue check locally (composer vue:check)..." -ForegroundColor Cyan
& composer vue:check
exit $LASTEXITCODE
