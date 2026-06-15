# Vue build/check helpers for MRT PowerShell scripts.

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

function Ensure-MrtVueE2eShell {
    if (-not (Test-MrtDockerAvailable)) {
        return
    }
    if (Test-MrtToolsServiceRunning -Service 'vue-e2e') {
        return
    }
    Write-Host 'Starting tools shell: vue-e2e...' -ForegroundColor DarkGray
    Invoke-MrtDockerCompose -ComposeArgs @('--profile', 'tools', 'up', '-d', 'vue-e2e') -ExitOnError
}

function Get-MrtVueE2eShellCommand {
    param([string[]] $PlaywrightArgs = @())

    $npmCi = Get-MrtNpmCiShellSnippet
    if ($PlaywrightArgs.Count -eq 0) {
        return '{0} && npm run e2e' -f $npmCi
    }
    $escaped = ($PlaywrightArgs | ForEach-Object {
        "'" + ($_ -replace "'", "'\\''") + "'"
    }) -join ' '
    return '{0} && npm run e2e -- {1}' -f $npmCi, $escaped
}

function Invoke-MrtDockerVueE2e {
    param(
        [string[]] $PlaywrightArgs = @(),
        [switch] $ExitOnError,
        [switch] $StreamOutput
    )

    Ensure-MrtVueE2eShell
    Write-Host 'Running Vue E2E in Docker (playwright v1.60 jammy)...' -ForegroundColor Cyan
    Invoke-MrtDockerToolsService -Service 'vue-e2e' -RunArgs @(
        'sh', '-c', (Get-MrtVueE2eShellCommand -PlaywrightArgs $PlaywrightArgs)
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
