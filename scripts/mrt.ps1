# Unified MRT developer CLI (Fas 3 S2). Forwards to existing scripts.
param(
    [Parameter(Position = 0)]
    [string] $Command = 'help',
    [Parameter(Position = 1)]
    [string] $SubCommand = '',
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]] $Passthrough
)

$ErrorActionPreference = 'Stop'
$scriptsRoot = $PSScriptRoot

function Show-MrtHelp {
    Write-Host @'
MRT developer CLI — forwards to scripts/*.ps1

  mrt check [-SkipPhpcs] [-Vue] [-Timings]
  mrt test [-Local] [-Timings] [phpunit args...]
  mrt lint [-Timings]
  mrt vue-check [-Local] [-Timings]
  mrt coverage [phpunit args...]
  mrt dev reset [-Build] [-SkipCompose] [-Timings]
  mrt dev smoke
  mrt dev watch [--no-up]
  mrt release build [-SkipBuild] [-SkipValidate]
  mrt help

Examples:
  .\scripts\mrt.ps1 check -SkipPhpcs
  .\scripts\mrt.ps1 dev reset -Build
  .\scripts\mrt.ps1 test tests/Unit/CsvManifestTest.php
'@
}

function Invoke-MrtScript {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Name,
        [string[]] $Args = @()
    )

    & (Join-Path $scriptsRoot $Name) @Args
    exit $LASTEXITCODE
}

$cmd = $Command.ToLowerInvariant()
$sub = $SubCommand.ToLowerInvariant()

switch ($cmd) {
    'help' { Show-MrtHelp; exit 0 }
    'check' { Invoke-MrtScript 'check.ps1' $Passthrough }
    'test' { Invoke-MrtScript 'test.ps1' $Passthrough }
    'lint' { Invoke-MrtScript 'lint.ps1' $Passthrough }
    'vue-check' { Invoke-MrtScript 'vue-check.ps1' $Passthrough }
    'vue' {
        if ($sub -eq 'check') {
            Invoke-MrtScript 'vue-check.ps1' $Passthrough
        }
        Write-Host "Unknown vue subcommand: $SubCommand (try: check)" -ForegroundColor Red
        exit 1
    }
    'coverage' { Invoke-MrtScript 'coverage.ps1' $Passthrough }
    'dev' {
        switch ($sub) {
            'reset' { Invoke-MrtScript 'docker-dev-reset.ps1' $Passthrough }
            'smoke' { Invoke-MrtScript 'docker-smoke.ps1' $Passthrough }
            'watch' { Invoke-MrtScript 'docker-watch.ps1' $Passthrough }
            default {
                Write-Host "Unknown dev subcommand: $SubCommand (try: reset, smoke, watch)" -ForegroundColor Red
                exit 1
            }
        }
    }
    'release' {
        switch ($sub) {
            'build' { Invoke-MrtScript 'build-release.ps1' $Passthrough }
            default {
                Write-Host "Unknown release subcommand: $SubCommand (try: build)" -ForegroundColor Red
                exit 1
            }
        }
    }
    default {
        Write-Host "Unknown command: $Command (run: mrt help)" -ForegroundColor Red
        exit 1
    }
}
