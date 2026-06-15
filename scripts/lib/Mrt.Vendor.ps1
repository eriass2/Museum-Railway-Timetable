# Vendor directory readiness for MRT PowerShell scripts.

function Test-MrtDockerVendorReady {
    Invoke-MrtDockerPhpTest -PhpArgs @(
        '-r',
        "exit(is_file('vendor/autoload.php') && is_file('vendor/bin/phpstan') ? 0 : 1);"
    )
    return ($LASTEXITCODE -eq 0)
}

function Ensure-MrtVendor {
    param(
        [switch] $PreferHost
    )

    if (-not $PreferHost -and (Test-MrtDockerAvailable)) {
        if (Test-MrtDockerVendorReady) {
            Write-Host 'Using existing vendor/ (Docker volume).' -ForegroundColor DarkGray
            return
        }

        Write-Host 'vendor/ missing in Docker tools volume.' -ForegroundColor Yellow
        Write-Host 'Installing dependencies via Docker...'
        Invoke-MrtTimedStep -Title 'composer install (Docker)' -SkipStepHeader -Action {
            Invoke-MrtDockerComposer -ComposerArgs @('install', '--no-interaction') -ExitOnError
        }
        return
    }

    $vendorPath = Join-Path (Get-MrtRepoRoot) 'vendor'
    if (Test-Path $vendorPath) {
        Write-Host 'Using existing vendor/.' -ForegroundColor DarkGray
        return
    }

    Write-Host 'vendor/ missing.' -ForegroundColor Yellow
    if (-not (Test-MrtDockerAvailable)) {
        Write-Host "Run 'composer install' or start Docker and retry."
        exit 1
    }

    Write-Host 'Installing dependencies via Docker...'
    Invoke-MrtTimedStep -Title 'composer install (Docker)' -SkipStepHeader -Action {
        Invoke-MrtDockerComposer -ComposerArgs @('install', '--no-interaction') -ExitOnError
    }
}
