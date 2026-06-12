# Run PHPStan and PHPCS. Default: Docker. Use -Local for host vendor/.
param(
    [switch]$Local,
    [switch]$Timings
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot '_runner.ps1')
Initialize-MrtGateEnvironment -Timings:$Timings -EnsureVendor -PreferHostVendor:$Local

Invoke-MrtWithDockerDefault -Local:$Local `
    -DockerHint 'Running composer lint in Docker...' `
    -DockerUnavailableMessage 'Docker is not running. Pass -Local to use host vendor/.' `
    -DockerAction {
        Assert-MrtDockerAvailable
        Invoke-MrtTimedStep -Title 'composer lint (Docker)' -Action {
            Invoke-MrtDockerComposer -ComposerArgs @('lint') -ExitOnError
        }
        Write-Host 'Lint OK.' -ForegroundColor Green
        Complete-MrtGateEnvironment
    } `
    -LocalAction {
        $vendor = Join-Path (Get-MrtRepoRoot) 'vendor'
        if (-not (Test-Path $vendor)) {
            Write-Host "Run 'composer install' first." -ForegroundColor Red
            exit 1
        }
        Write-Host 'Using existing vendor/.'
        Invoke-MrtTimedStep -Title 'composer lint (local)' -Action {
            & (Join-Path $vendor 'bin/phpstan') analyse --no-progress
            if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
            & (Join-Path $vendor 'bin/phpcs')
        }
        Write-Host 'Lint OK.' -ForegroundColor Green
        Complete-MrtGateEnvironment
    }
