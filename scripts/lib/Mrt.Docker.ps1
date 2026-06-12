# Shared Docker / npm helpers for MRT PowerShell scripts.
# Usage: . (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1'); Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

. (Join-Path $PSScriptRoot 'Mrt.Plugin.ps1')

$script:MrtScriptTimings = $env:MRT_SCRIPT_TIMINGS -match '^(1|true|yes)$'
$script:MrtTimingStepTitle = $null
$script:MrtTimingStepStarted = $null

function Initialize-MrtScriptTimings {
    param([switch] $Timings)

    if ($Timings) {
        $script:MrtScriptTimings = $true
    }
}

function Test-MrtScriptTimingsEnabled {
    return [bool] $script:MrtScriptTimings
}

function Write-MrtTiming {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Step,
        [Parameter(Mandatory = $true)]
        [TimeSpan] $Elapsed
    )

    $ms = [math]::Round($Elapsed.TotalMilliseconds)
    $label = if ($ms -lt 1000) { "${ms}ms" } else { '{0:N1}s' -f $Elapsed.TotalSeconds }
    Write-Host "  [timing] $Step - $label" -ForegroundColor DarkGray
}

function Complete-MrtScriptTimings {
    if (-not (Test-MrtScriptTimingsEnabled)) {
        return
    }
    if (-not $script:MrtTimingStepStarted) {
        return
    }

    $elapsed = (Get-Date) - $script:MrtTimingStepStarted
    Write-MrtTiming -Step $script:MrtTimingStepTitle -Elapsed $elapsed
    $script:MrtTimingStepTitle = $null
    $script:MrtTimingStepStarted = $null
}

function Invoke-MrtTimedStep {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Title,
        [Parameter(Mandatory = $true)]
        [scriptblock] $Action,
        [switch] $SkipStepHeader
    )

    if (-not $SkipStepHeader) {
        Write-Host "`n--- $Title ---" -ForegroundColor Cyan
    }

    if (-not (Test-MrtScriptTimingsEnabled)) {
        & $Action
        return
    }

    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    try {
        & $Action
    } finally {
        $sw.Stop()
        Write-MrtTiming -Step $Title -Elapsed $sw.Elapsed
    }
}

function Write-MrtStep {
    param([Parameter(Mandatory = $true)] [string] $Title)

    Complete-MrtScriptTimings
    Write-Host "`n--- $Title ---" -ForegroundColor Cyan
    if (Test-MrtScriptTimingsEnabled) {
        $script:MrtTimingStepTitle = $Title
        $script:MrtTimingStepStarted = Get-Date
    }
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
    & php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>$null | ForEach-Object { $version = $_ }
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
        Write-Host "Local PHP $version is below $MinVersion. Omit -Local to use Docker." -ForegroundColor Red
        exit 1
    }
    return $version
}

function Get-MrtNpmCiShellSnippet {
    return @'
if [ ! -d node_modules ] || [ ! -f node_modules/.package-lock.json ] || ! cmp -s package-lock.json node_modules/.package-lock.json 2>/dev/null; then echo 'Running npm ci...'; npm ci; cp package-lock.json node_modules/.package-lock.json; else echo 'Skipped npm ci (node_modules matches package-lock.json)'; fi
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

function Test-MrtToolsServiceRunning {
    param([Parameter(Mandatory = $true)] [string] $Service)

    $running = Invoke-MrtDockerCompose -ComposeArgs @(
        '--profile', 'tools', 'ps', '--status', 'running', '-q', $Service
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

function Ensure-MrtToolsShell {
    if (-not (Test-MrtDockerAvailable)) {
        return
    }

    $services = @('composer', 'php-test', 'vue')
    $missing = @($services | Where-Object { -not (Test-MrtToolsServiceRunning $_) })
    if ($missing.Count -eq 0) {
        return
    }

    Write-Host "Starting tools shell: $($missing -join ', ')..." -ForegroundColor DarkGray
    $composeArgs = @('--profile', 'tools', 'up', '-d') + $missing
    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -ExitOnError
}

function Get-MrtToolsExecArgs {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Service,
        [Parameter(Mandatory = $true)]
        [string[]] $RunArgs
    )

    switch ($Service) {
        'composer' { return @('composer') + $RunArgs }
        'php-test' { return @('php') + $RunArgs }
        default { return $RunArgs }
    }
}

function Get-MrtToolsServiceRunFallbackArgs {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Service,
        [Parameter(Mandatory = $true)]
        [string[]] $RunArgs
    )

    $composeArgs = Get-MrtToolsServiceRunArgs -Service $Service
    switch ($Service) {
        'composer' { $composeArgs += @('--entrypoint', 'composer') }
        'php-test' { $composeArgs += @('--entrypoint', 'php') }
    }
    if ($RunArgs.Count -gt 0) {
        $composeArgs += $RunArgs
    }
    return $composeArgs
}

function Test-MrtWpCliContainerRunning {
    $running = Invoke-MrtDockerCompose -ComposeArgs @(
        'ps', '--status', 'running', '-q', 'wpcli'
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

function Get-MrtWpCliComposeArgs {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $CommandArgs,
        [switch] $NoTty,
        [switch] $AsRoot,
        [string] $Entrypoint = 'wp'
    )

    if (Test-MrtWpCliContainerRunning) {
        $composeArgs = @('exec')
        if ($NoTty) {
            $composeArgs += '-T'
        }
        $composeArgs += 'wpcli'
        if ($Entrypoint -eq 'wp') {
            $composeArgs += 'wp', '--allow-root'
        } else {
            $composeArgs += $Entrypoint
        }
        $composeArgs += $CommandArgs
        return $composeArgs
    }

    $composeArgs = Get-MrtWordPressInitRunArgs -NoTty:$NoTty -AsRoot:$AsRoot -Entrypoint $Entrypoint
    $composeArgs += $CommandArgs
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

    Ensure-MrtToolsShell
    if (Test-MrtToolsServiceRunning -Service $Service) {
        $composeArgs = @('--profile', 'tools', 'exec', '-T', $Service)
        $composeArgs += Get-MrtToolsExecArgs -Service $Service -RunArgs $RunArgs
    } else {
        $composeArgs = Get-MrtToolsServiceRunFallbackArgs -Service $Service -RunArgs $RunArgs
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

function Test-MrtDockerVendorReady {
    Invoke-MrtDockerPhpTest -PhpArgs @(
        '-r',
        'exit(is_file("vendor/autoload.php") ? 0 : 1);'
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

    $composeArgs = Get-MrtWpCliComposeArgs -CommandArgs $WpArgs -NoTty:$NoTty `
        -AsRoot:$AsRoot -Entrypoint $Entrypoint

    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -StreamOutput:$StreamOutput `
        -ReturnOutput:$ReturnOutput -ExitOnError:$ExitOnError
}

function Get-MrtDemoPageUrl {
    Write-MrtStep -Title 'Demo page'
    $eval = @(
        '$r = MRT_ensure_components_demo_page_cli();',
        'if (is_wp_error($r)) { echo $r->get_error_message(); }',
        'else { wp_update_post(array(''ID'' => (int) $r, ''post_status'' => ''publish'')); echo get_permalink((int) $r); }'
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

function Get-MrtSmokePageUrlEntries {
    $eval = @(
        "if (!function_exists('MRT_dev_smoke_page_permalinks')) {",
        "fwrite(STDERR, 'dev-cli not loaded'.PHP_EOL); exit(1);",
        '}',
        "if (function_exists('MRT_dev_cli_set_admin_user')) { MRT_dev_cli_set_admin_user(); }",
        "if (function_exists('MRT_ensure_dev_smoke_pages')) { MRT_ensure_dev_smoke_pages(); }",
        'echo wp_json_encode(MRT_dev_smoke_page_permalinks());'
    ) -join ' '

    $raw = Invoke-MrtWpCli -WpArgs @('eval', $eval) -ReturnOutput -NoTty
    if ($LASTEXITCODE -ne 0) {
        return @()
    }

    $jsonLine = ($raw | Where-Object { $_ -match '^\{' } | Select-Object -Last 1)
    if (-not $jsonLine) {
        return @()
    }

    $labels = @{
        wizard         = 'Wizard smoke test'
        component_demo = 'Component demo'
        planner        = 'Planner smoke test'
    }

    $map = $jsonLine | ConvertFrom-Json
    $pages = @()
    foreach ($prop in $map.PSObject.Properties) {
        $label = $labels[$prop.Name]
        if (-not $label) {
            $label = $prop.Name
        }
        $pages += @{ Name = $label; Url = [string] $prop.Value }
    }
    return $pages
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

    Write-Host 'Running PHPUnit in Docker (php-test)...' -ForegroundColor Cyan
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

    Write-Host 'Running PHPUnit with PCOV in Docker (php-test)...' -ForegroundColor Cyan
    $runArgs = @('vendor/bin/phpunit')
    if ($PhpUnitArgs.Count -gt 0) {
        $runArgs += $PhpUnitArgs
    }
    Invoke-MrtDockerToolsService -Service 'php-test' -RunArgs $runArgs `
        -ExitOnError:$ExitOnError -StreamOutput
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
