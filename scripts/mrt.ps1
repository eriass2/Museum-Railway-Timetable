# Unified MRT developer CLI — Windows entry (Fas 3 S2).
# Bash scripts/mrt.sh is canonical on Linux/macOS/WSL; this forwards to existing .ps1 gates.
param(
    [Parameter(Position = 0)]
    [string] $Command = 'help',
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
        [string[]] $Args = @()
    )

    & (Join-Path $scriptsRoot $Name) @Args
    exit $LASTEXITCODE
}

$cmd = $Command.ToLowerInvariant()
$sub = ''
$Passthrough = @($Remaining)

if ($Passthrough.Count -gt 0 -and $cmd -in @('dev', 'release', 'csv', 'vue')) {
    $sub = $Passthrough[0].ToLowerInvariant()
    $Passthrough = @($Passthrough | Select-Object -Skip 1)
}

switch ($cmd) {
    { $_ -in 'help', '-h', '--help' } { Show-MrtHelp; exit 0 }
    'check' { Invoke-MrtScript 'check.ps1' $Passthrough }
    'test' { Invoke-MrtScript 'test.ps1' $Passthrough }
    'lint' { Invoke-MrtScript 'lint.ps1' $Passthrough }
    'vue-check' { Invoke-MrtScript 'vue-check.ps1' $Passthrough }
    'vue' {
        if ($sub -eq 'check') {
            Invoke-MrtScript 'vue-check.ps1' $Passthrough
        }
        Write-Host "Unknown vue subcommand: $sub (try: check)" -ForegroundColor Red
        exit 1
    }
    'coverage' { Invoke-MrtScript 'coverage.ps1' $Passthrough }
    'setup-dev' { Invoke-MrtScript 'setup-dev.ps1' $Passthrough }
    'dev' {
        switch ($sub) {
            'reset' { Invoke-MrtScript 'docker-dev-reset.ps1' $Passthrough }
            'smoke' { Invoke-MrtScript 'docker-smoke.ps1' $Passthrough }
            'watch' { Invoke-MrtScript 'docker-watch.ps1' $Passthrough }
            default {
                Write-Host "Unknown dev subcommand: $sub (try: reset, smoke, watch)" -ForegroundColor Red
                exit 1
            }
        }
    }
    'release' {
        switch ($sub) {
            'build' { Invoke-MrtScript 'build-release.ps1' $Passthrough }
            'deploy' { Invoke-MrtScript 'live-deploy.ps1' $Passthrough }
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
            'zip' { Invoke-MrtScript 'csv-package-zip.ps1' $Passthrough }
            default {
                Write-Host "Unknown csv subcommand: $sub (try: validate, zip)" -ForegroundColor Red
                exit 1
            }
        }
    }
    'i18n' { Invoke-MrtScript 'make-i18n.ps1' $Passthrough }
    default {
        Write-Host "Unknown command: $Command (run: mrt help)" -ForegroundColor Red
        exit 1
    }
}
