# Unified MRT developer CLI — Windows entry (Fas 3 S2).
# Bash scripts/mrt.sh is canonical on Linux/macOS/WSL; this forwards to existing .ps1 gates.
param(
    [Parameter(Position = 0)]
    [string] $Command = 'help',
    [switch]$Timings,
    [switch]$Local,
    [switch]$Vue,
    [switch]$SkipPhpcs,
    [switch]$Build,
    [switch]$SkipCompose,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]] $Remaining
)

$ErrorActionPreference = 'Stop'
$scriptsRoot = $PSScriptRoot

function Show-MrtHelp {
    Get-Content (Join-Path $scriptsRoot 'lib/mrt-help.txt') | Write-Host
}

function Invoke-MrtScript {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Name,
        [string[]] $ScriptArgs = @()
    )

    $args = @($ScriptArgs | Where-Object { $_ -ne $null -and $_ -ne '' })
    & (Join-Path $scriptsRoot $Name) @args
    exit $LASTEXITCODE
}

function Get-MrtGateSwitchArgs {
    param([string[]] $Extra = @())

    $switchArgs = @()
    # -Timings is forwarded via $env:MRT_SCRIPT_TIMINGS (see below) — not as a string (PS splits -Timings → -T).
    if ($Local) { $switchArgs += '-Local' }
    if ($Vue) { $switchArgs += '-Vue' }
    if ($SkipPhpcs) { $switchArgs += '-SkipPhpcs' }
    if ($Build) { $switchArgs += '-Build' }
    if ($SkipCompose) { $switchArgs += '-SkipCompose' }
    return $switchArgs + $Extra
}

$cmd = $Command.ToLowerInvariant()
$sub = ''
$Passthrough = if ($null -eq $Remaining) { @() } else { @($Remaining) }

if ($Passthrough.Count -gt 0 -and $cmd -in @('dev', 'release', 'csv', 'vue')) {
    $sub = $Passthrough[0].ToLowerInvariant()
    $Passthrough = @($Passthrough | Select-Object -Skip 1)
}

$GateArgs = @(Get-MrtGateSwitchArgs -Extra $Passthrough)
if ($Timings) {
    $env:MRT_SCRIPT_TIMINGS = '1'
}

switch ($cmd) {
    { $_ -in 'help', '-h', '--help' } { Show-MrtHelp; exit 0 }
    'check' { Invoke-MrtScript -Name 'gate/check.ps1' -ScriptArgs $GateArgs }
    'test' { Invoke-MrtScript -Name 'gate/test.ps1' -ScriptArgs $GateArgs }
    'lint' { Invoke-MrtScript -Name 'gate/lint.ps1' -ScriptArgs $GateArgs }
    'vue-check' { Invoke-MrtScript -Name 'gate/vue-check.ps1' -ScriptArgs $GateArgs }
    'e2e' { Invoke-MrtScript -Name 'gate/e2e.ps1' -ScriptArgs $GateArgs }
    'vue' {
        switch ($sub) {
            'check' { Invoke-MrtScript -Name 'gate/vue-check.ps1' -ScriptArgs $GateArgs }
            default {
                Write-Host "Unknown vue subcommand: $sub (try: check)" -ForegroundColor Red
                exit 1
            }
        }
    }
    'coverage' { Invoke-MrtScript -Name 'gate/coverage.ps1' -ScriptArgs $GateArgs }
    'setup-dev' { Invoke-MrtScript -Name 'setup-dev.ps1' -ScriptArgs $GateArgs }
    'dev' {
        switch ($sub) {
            'reset' { Invoke-MrtScript -Name 'dev/docker-dev-reset.ps1' -ScriptArgs $GateArgs }
            'smoke' { Invoke-MrtScript -Name 'dev/docker-smoke.ps1' -ScriptArgs $GateArgs }
            'watch' { Invoke-MrtScript -Name 'dev/docker-watch.ps1' -ScriptArgs $GateArgs }
            default {
                Write-Host "Unknown dev subcommand: $sub (try: reset, smoke, watch)" -ForegroundColor Red
                exit 1
            }
        }
    }
    'release' {
        switch ($sub) {
            'build' { Invoke-MrtScript -Name 'release/build-release.ps1' -ScriptArgs $GateArgs }
            'deploy' { Invoke-MrtScript -Name 'release/live-deploy.ps1' -ScriptArgs $GateArgs }
            default {
                Write-Host "Unknown release subcommand: $sub (try: build, deploy)" -ForegroundColor Red
                exit 1
            }
        }
    }
    'csv' {
        switch ($sub) {
            'validate' {
                & composer csv:validate -- @Passthrough
                exit $LASTEXITCODE
            }
            'zip' { Invoke-MrtScript -Name 'csv/csv-package-zip.ps1' -ScriptArgs $GateArgs }
            default {
                Write-Host "Unknown csv subcommand: $sub (try: validate, zip)" -ForegroundColor Red
                exit 1
            }
        }
    }
    'i18n' { Invoke-MrtScript -Name 'i18n/make-i18n.ps1' -ScriptArgs $GateArgs }
    default {
        Write-Host "Unknown command: $Command (run: mrt help)" -ForegroundColor Red
        exit 1
    }
}
