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

function Assert-MrtLocalPhpMin {
    param(
        [Parameter(Mandatory = $true)]
        [string] $MinVersion
    )

    $version = Get-MrtLocalPhpVersion
    if (-not $version) {
        Write-Host 'Local PHP not in PATH. Omit -Local to use Docker.' -ForegroundColor Red
        exit 1
    }
    if ([version]$version -lt [version]$MinVersion) {
        Write-Host "Local PHP $version < $MinVersion. Omit -Local to use Docker." -ForegroundColor Red
        exit 1
    }
    return $version
}

function Get-MrtNpmCiShellSnippet {
    return @'
if [ ! -d node_modules ] || [ ! -f node_modules/.package-lock.json ] || ! cmp -s package-lock.json node_modules/.package-lock.json 2>/dev/null; then npm ci; fi
'@.Trim()
}

function Get-MrtVueShellCommand {
    param(
        [ValidateSet('Check', 'Build', 'BuildVerify')]
        [string] $Mode = 'Check'
    )

    $npmCi = Get-MrtNpmCiShellSnippet
    switch ($Mode) {
        'Check' { return '{0} && npm run check' -f $npmCi }
        'Build' { return '{0} && npm run build' -f $npmCi }
        'BuildVerify' { return '{0} && npm run build && npm run verify' -f $npmCi }
    }
}

function Test-MrtWordPressContainerRunning {
    $running = Invoke-MrtDockerCompose -ComposeArgs @(
        'ps', '--status', 'running', '-q', 'wordpress'
    ) -ReturnOutput
    if ($null -eq $running) {
        return $false
    }
    foreach ($line in @($running)) {
        if ($line -match '\S') {
            return $true
        }
    }
    return $false
}

function Get-MrtToolsServiceRunArgs {
    param([Parameter(Mandatory = $true)] [string] $Service)

    return @('--profile', 'tools', 'run', '--rm', '--no-deps', $Service)
}

function Get-MrtWordPressInitRunArgs {
    param(
        [switch] $NoTty,
        [switch] $AsRoot,
        [string] $Entrypoint = 'wp'
    )

    $composeArgs = @('run', '--rm')
    if ($NoTty) {
        $composeArgs += '--no-TTY'
    }
    if (Test-MrtWordPressContainerRunning) {
        $composeArgs += '--no-deps'
    }
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
    return $composeArgs
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

function Invoke-MrtDockerToolsService {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Service,
        [Parameter(Mandatory = $true)]
        [string[]] $RunArgs,
        [switch] $ExitOnError,
        [switch] $StreamOutput,
        [switch] $ReturnOutput
    )

    $composeArgs = Get-MrtToolsServiceRunArgs -Service $Service
    if ($RunArgs.Count -gt 0) {
        $composeArgs += $RunArgs
    }
    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -ExitOnError:$ExitOnError `
        -StreamOutput:$StreamOutput -ReturnOutput:$ReturnOutput
}

function Invoke-MrtDockerComposer {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $ComposerArgs,
        [switch] $ExitOnError,
        [switch] $StreamOutput,
        [switch] $ReturnOutput
    )

    Invoke-MrtDockerToolsService -Service 'composer' -RunArgs $ComposerArgs `
        -ExitOnError:$ExitOnError -StreamOutput:$StreamOutput -ReturnOutput:$ReturnOutput
}

function Invoke-MrtWithDockerDefault {
    param(
        [switch] $Local,
        [Parameter(Mandatory = $true)]
        [scriptblock] $DockerAction,
        [Parameter(Mandatory = $true)]
        [scriptblock] $LocalAction,
        [string] $DockerHint,
        [string] $DockerUnavailableMessage = 'Docker is not running.',
        [switch] $DockerUnavailableWarning
    )

    if (-not $Local) {
        if (-not (Test-MrtDockerAvailable)) {
            $color = if ($DockerUnavailableWarning) { 'Yellow' } else { 'Red' }
            Write-Host $DockerUnavailableMessage -ForegroundColor $color
            exit 1
        }
        if ($DockerHint) {
            Write-Host $DockerHint -ForegroundColor Cyan
        }
        & $DockerAction | Out-Null
        exit $LASTEXITCODE
    }

    & $LocalAction | Out-Null
    exit $LASTEXITCODE
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
    param(
        [switch] $ExitOnError,
        [switch] $Build
    )

    $composeArgs = @('up', '-d')
    if ($Build) {
        $composeArgs += '--build'
    }
    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -ExitOnError:$ExitOnError
}

function Test-MrtHttpUrlReady {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Url,
        [int] $TimeoutSec = 5
    )

    try {
        $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec $TimeoutSec -ErrorAction Stop
        return ($response.StatusCode -ge 200 -and $response.StatusCode -lt 500)
    } catch {
        return $false
    }
}

function Wait-MrtUntilDeadline {
    param(
        [Parameter(Mandatory = $true)]
        [datetime] $Deadline,
        [Parameter(Mandatory = $true)]
        [scriptblock] $Test,
        [int] $IntervalSec = 2
    )

    while ((Get-Date) -lt $Deadline) {
        if (& $Test) {
            return $true
        }
        Start-Sleep -Seconds $IntervalSec
    }
    return $false
}

function Wait-MrtWordPressReady {
    param(
        [int] $TimeoutSec = 120,
        [int] $IntervalSec = 2
    )

    Write-Host 'Waiting for WordPress...' -ForegroundColor Gray
    $loginUrl = "$script:MrtDevSiteUrl/wp-login.php"
    $deadline = (Get-Date).AddSeconds($TimeoutSec)

    if (-not (Wait-MrtUntilDeadline -Deadline $deadline -IntervalSec $IntervalSec -Test {
            Test-MrtHttpUrlReady -Url $loginUrl
        })) {
        Write-Host "WordPress did not respond at $loginUrl within ${TimeoutSec}s." -ForegroundColor Red
        exit 1
    }

    if (-not (Wait-MrtUntilDeadline -Deadline $deadline -IntervalSec $IntervalSec -Test {
            Invoke-MrtWpCli -WpArgs @('core', 'is-installed') -NoTty | Out-Null
            return ($LASTEXITCODE -eq 0)
        })) {
        Write-Host "WordPress did not become ready within ${TimeoutSec}s." -ForegroundColor Red
        exit 1
    }
}

function Invoke-MrtWpCli {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $WpArgs,
        [switch] $AsRoot,
        [switch] $NoTty,
        [string] $Entrypoint = 'wp',
        [switch] $StreamOutput,
        [switch] $ReturnOutput,
        [switch] $ExitOnError
    )

    $composeArgs = Get-MrtWordPressInitRunArgs -NoTty:$NoTty -AsRoot:$AsRoot -Entrypoint $Entrypoint
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
    Invoke-MrtWpCli -Entrypoint 'sh' -WpArgs @('/usr/local/bin/mrt-ensure-sv-locale.sh') -StreamOutput
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
    Invoke-MrtWpCli -WpArgs @('eval', $eval) -NoTty -StreamOutput -ExitOnError
}

function Invoke-MrtDockerPhpUnit {
    param(
        [string[]] $PhpUnitArgs,
        [switch] $ExitOnError
    )

    Write-Host 'Running PHPUnit in Docker (php:8.2-cli)...' -ForegroundColor Cyan
    $runArgs = @('vendor/bin/phpunit')
    if ($PhpUnitArgs.Count -gt 0) {
        $runArgs += $PhpUnitArgs
    }
    Invoke-MrtDockerToolsService -Service 'php-test' -RunArgs $runArgs -ExitOnError:$ExitOnError
}

function Invoke-MrtDockerPhpTest {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $PhpArgs,
        [switch] $ExitOnError,
        [switch] $StreamOutput
    )

    Invoke-MrtDockerToolsService -Service 'php-test' -RunArgs $PhpArgs `
        -ExitOnError:$ExitOnError -StreamOutput:$StreamOutput
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

    $composeArgs = @(
        '--profile', 'tools', 'run', '--rm', '--no-deps',
        '--entrypoint', 'sh', 'php-test', '-c', "$shellCmd $($PhpUnitArgs -join ' ')"
    )
    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -StreamOutput -ExitOnError:$ExitOnError
}

function Invoke-MrtDockerVue {
    param(
        [ValidateSet('Check', 'Build', 'BuildVerify')]
        [string] $Mode = 'Check',
        [switch] $ExitOnError,
        [switch] $StreamOutput
    )

    $label = switch ($Mode) {
        'Check' { 'Vue check' }
        'Build' { 'Vue build' }
        'BuildVerify' { 'Vue build + verify' }
    }

    Write-Host "Running $label in Docker (node:22-alpine)..." -ForegroundColor Cyan
    Invoke-MrtDockerToolsService -Service 'vue' -RunArgs @(
        'sh', '-c', (Get-MrtVueShellCommand -Mode $Mode)
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
