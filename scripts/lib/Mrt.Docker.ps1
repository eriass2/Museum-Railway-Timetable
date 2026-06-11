# Shared Docker / npm helpers for MRT PowerShell scripts.
# Usage: . (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1'); Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

. (Join-Path $PSScriptRoot 'Mrt.Plugin.ps1')

function Write-MrtStep {
    param([Parameter(Mandatory = $true)] [string] $Title)

    Write-Host "`n--- $Title ---" -ForegroundColor Cyan
}

function Test-MrtDockerAvailable {
    & docker info 2>$null | Out-Null
    return $LASTEXITCODE -eq 0
}

function Assert-MrtDockerAvailable {
    param([string] $Message = 'Docker is not running. Start Docker Desktop and retry.')

    if (-not (Test-MrtDockerAvailable)) {
        Write-Host $Message -ForegroundColor Red
        exit 1
    }
}

function Test-MrtNpmAvailable {
    try {
        & npm --version 2>$null | Out-Null
        return $LASTEXITCODE -eq 0
    } catch {
        return $false
    }
}

function Assert-MrtNpmAvailable {
    param([string] $Message = 'npm not in PATH. Omit -Local to use Docker.')

    if (-not (Test-MrtNpmAvailable)) {
        Write-Host $Message -ForegroundColor Red
        exit 1
    }
}

function Get-MrtLocalPhpVersion {
    $version = $null
    & php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>$null | ForEach-Object { $version = $_ }
    if ($LASTEXITCODE -ne 0 -or -not $version) {
        return $null
    }
    return $version
}

function Invoke-MrtDockerCompose {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $ComposeArgs,
        [switch] $ExitOnError,
        [switch] $StreamOutput,
        [switch] $ReturnOutput
    )

    $dockerArgs = @('compose') + $ComposeArgs
    $output = $null

    if ($ReturnOutput -or $StreamOutput) {
        $prevEap = $ErrorActionPreference
        $ErrorActionPreference = 'Continue'
        try {
            $output = & docker @dockerArgs 2>&1
        } finally {
            $ErrorActionPreference = $prevEap
        }
        if ($StreamOutput) {
            $output | ForEach-Object { Write-Host $_ }
        }
    } else {
        & docker @dockerArgs
    }

    if ($ExitOnError -and $LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
    if ($ReturnOutput) {
        return $output
    }
    return $LASTEXITCODE
}

function Invoke-MrtDockerComposer {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $ComposerArgs,
        [switch] $ExitOnError,
        [switch] $StreamOutput,
        [switch] $ReturnOutput
    )

    $composeArgs = @('--profile', 'tools', 'run', '--rm', 'composer') + $ComposerArgs
    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -ExitOnError:$ExitOnError `
        -StreamOutput:$StreamOutput -ReturnOutput:$ReturnOutput
}

function Ensure-MrtVendor {
    $vendorPath = Join-Path (Get-MrtRepoRoot) 'vendor'
    if (Test-Path $vendorPath) {
        return
    }

    Write-Host 'vendor/ missing.' -ForegroundColor Yellow
    if (-not (Test-MrtDockerAvailable)) {
        Write-Host "Run 'composer install' or start Docker and retry."
        exit 1
    }

    Write-Host 'Installing dependencies via Docker...'
    Invoke-MrtDockerComposer -ComposerArgs @('install', '--no-interaction') -ExitOnError
}

function Start-MrtDockerStack {
    param([switch] $ExitOnError)

    Invoke-MrtDockerCompose -ComposeArgs @('up', '-d', '--build') -ExitOnError:$ExitOnError
}

function Wait-MrtWordPressReady {
    param(
        [int] $TimeoutSec = 120,
        [int] $IntervalSec = 3
    )

    Write-Host 'Waiting for WordPress...' -ForegroundColor Gray
    $attempts = [math]::Max(1, [math]::Ceiling($TimeoutSec / $IntervalSec))

    for ($i = 1; $i -le $attempts; $i++) {
        & docker compose run --rm --no-TTY wordpress-init wp --allow-root core is-installed 2>$null | Out-Null
        if ($LASTEXITCODE -eq 0) {
            return
        }
        if ($i -lt $attempts) {
            Start-Sleep -Seconds $IntervalSec
        }
    }

    Write-Host "WordPress did not become ready within ${TimeoutSec}s." -ForegroundColor Red
    exit 1
}

function Invoke-MrtWpCli {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $WpArgs,
        [switch] $AsRoot,
        [string] $Entrypoint = 'wp',
        [switch] $StreamOutput,
        [switch] $ReturnOutput,
        [switch] $ExitOnError
    )

    $composeArgs = @('run', '--rm', '--no-TTY')
    if ($AsRoot) {
        $composeArgs += '--user', 'root'
    }
    if ($Entrypoint -ne 'wp') {
        $composeArgs += '--entrypoint', $Entrypoint
    }
    $composeArgs += 'wordpress-init'
    if ($Entrypoint -eq 'wp') {
        $composeArgs += '--allow-root'
    }
    $composeArgs += $WpArgs

    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -StreamOutput:$StreamOutput `
        -ReturnOutput:$ReturnOutput -ExitOnError:$ExitOnError
}

function Get-MrtDemoPageUrl {
    Write-MrtStep -Title 'Demo page'
    $eval = @(
        '$r = MRT_ensure_components_demo_page_cli();',
        'if (is_wp_error($r)) { echo $r->get_error_message(); }',
        "else { wp_update_post(array('ID' => (int) `$r, 'post_status' => 'publish')); echo get_permalink((int) `$r); }"
    ) -join ' '

    $demoOut = Invoke-MrtWpCli -WpArgs @('eval', $eval) -ReturnOutput
    $demoOut | ForEach-Object { Write-Host $_ }
    if ($LASTEXITCODE -ne 0) {
        return $null
    }

    $match = ($demoOut | Out-String) | Select-String -Pattern 'https?://\S+' | Select-Object -Last 1
    if (-not $match) {
        return $null
    }
    return $match.Matches.Value
}

function Invoke-MrtEnsureSvLocale {
    Write-MrtStep -Title 'Swedish locale (sv_SE)'
    Invoke-MrtDockerCompose -ComposeArgs @(
        'run', '--rm', 'wordpress-init',
        'sh', '/usr/local/bin/mrt-ensure-sv-locale.sh'
    ) -StreamOutput
}

function Set-MrtWpDebug {
    param([bool] $Enabled = $true)

    Write-MrtStep -Title 'Enable WP_DEBUG (development)'
    $value = if ($Enabled) { 'true' } else { 'false' }
    Invoke-MrtWpCli -WpArgs @('config', 'set', 'WP_DEBUG', $value, '--raw') -AsRoot -StreamOutput
    Invoke-MrtWpCli -WpArgs @('config', 'set', 'WP_DEBUG_LOG', $value, '--raw') -AsRoot -StreamOutput
}

function Invoke-MrtDevResetImport {
    Write-MrtStep -Title 'Reset and import'
    $eval = @(
        "if (!function_exists('MRT_dev_reset_and_import_cli')) {",
        "fwrite(STDERR, 'Plugin not active or dev-cli not loaded'.PHP_EOL); exit(1);",
        '} MRT_dev_reset_and_import_cli();'
    ) -join ' '
    Invoke-MrtWpCli -WpArgs @('eval', $eval) -StreamOutput -ExitOnError
}

function Invoke-MrtDockerPhpUnit {
    param(
        [string[]] $PhpUnitArgs,
        [switch] $ExitOnError
    )

    Write-Host 'Running PHPUnit in Docker (php:8.2-cli)...' -ForegroundColor Cyan
    $composeArgs = @(
        '--profile', 'tools', 'run', '--rm', 'php-test',
        'vendor/bin/phpunit'
    )
    if ($PhpUnitArgs.Count -gt 0) {
        $composeArgs += $PhpUnitArgs
    }

    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -ExitOnError:$ExitOnError
}

function Invoke-MrtDockerPhpUnitWithPcov {
    param(
        [string[]] $PhpUnitArgs,
        [switch] $ExitOnError
    )

    Write-Host 'Running PHPUnit with PCOV in Docker...' -ForegroundColor Cyan
    $shellCmd = @'
apt-get update -qq && apt-get install -y -qq $PHPIZE_DEPS >/dev/null &&
pecl install pcov >/dev/null && docker-php-ext-enable pcov >/dev/null &&
vendor/bin/phpunit
'@ -replace "`r`n", ' '

    Invoke-MrtDockerCompose -ComposeArgs @(
        '--profile', 'tools', 'run', '--rm', '--entrypoint', 'sh', 'php-test',
        '-c', "$shellCmd $($PhpUnitArgs -join ' ')"
    ) -StreamOutput -ExitOnError:$ExitOnError
}

function Invoke-MrtDockerVue {
    param(
        [ValidateSet('Check', 'Build', 'BuildVerify')]
        [string] $Mode = 'Check',
        [switch] $ExitOnError,
        [switch] $StreamOutput
    )

    $shellCmd = switch ($Mode) {
        'Check' { 'npm ci && npm run check' }
        'Build' { 'npm ci && npm run build' }
        'BuildVerify' { 'npm ci && npm run build && npm run verify' }
    }

    $label = switch ($Mode) {
        'Check' { 'Vue check' }
        'Build' { 'Vue build' }
        'BuildVerify' { 'Vue build + verify' }
    }

    Write-Host "Running $label in Docker (node:22-alpine)..." -ForegroundColor Cyan
    Invoke-MrtDockerCompose -ComposeArgs @(
        '--profile', 'tools', 'run', '--rm', 'vue',
        'sh', '-c', $shellCmd
    ) -ExitOnError:$ExitOnError -StreamOutput:$StreamOutput
}

function Invoke-MrtVueBuild {
    param(
        [switch] $UseDocker
    )

    $root = Get-MrtRepoRoot
    $preferDocker = $UseDocker -or -not (Test-MrtNpmAvailable)

    if ($preferDocker) {
        if (-not (Test-MrtDockerAvailable)) {
            throw 'npm not in PATH and Docker is not running. Install Node or start Docker.'
        }
        Write-MrtStep -Title 'Vue build (Docker)'
        Invoke-MrtDockerVue -Mode BuildVerify
        if ($LASTEXITCODE -ne 0) {
            throw "Vue build failed in Docker (exit $LASTEXITCODE)."
        }
        return
    }

    Write-MrtStep -Title 'Vue build (local npm)'
    & composer vue:build
    if ($LASTEXITCODE -ne 0) {
        throw "composer vue:build failed (exit $LASTEXITCODE)."
    }

    Push-Location (Join-Path $root 'frontend/vue')
    try {
        & npm run verify
        if ($LASTEXITCODE -ne 0) {
            throw "npm run verify failed (exit $LASTEXITCODE)."
        }
    } finally {
        Pop-Location
    }
}
