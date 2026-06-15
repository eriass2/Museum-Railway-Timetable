# WordPress + Playwright E2E (prepare, run, restore). Requires Git Bash.
# Usage: .\scripts\mrt.ps1 dev e2ewp

param(
    [switch] $Timings
)

$ErrorActionPreference = 'Stop'
$scriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
. (Join-Path $scriptsRoot 'lib/Mrt.Timings.ps1')
Initialize-MrtScriptTimings -Timings:$Timings

function Resolve-MrtGitBash {
    foreach ($path in @(
            'C:\Program Files\Git\bin\bash.exe'
            'C:\Program Files (x86)\Git\bin\bash.exe'
        )) {
        if (Test-Path $path) {
            return $path
        }
    }
    $cmd = Get-Command bash -ErrorAction SilentlyContinue
    if ($cmd -and $cmd.Source -notmatch 'WindowsApps\\bash\.exe$') {
        return $cmd.Source
    }
    Write-Host 'Git Bash is required for WordPress E2E (scripts/dev/ci-e2e-wp.sh).' -ForegroundColor Red
    exit 1
}

$repoRoot = (Resolve-Path (Join-Path $scriptsRoot '..')).Path
$bash = Resolve-MrtGitBash
$ciScript = (Join-Path $scriptsRoot 'dev/ci-e2e-wp.sh') -replace '\\', '/'
$unixRoot = $repoRoot -replace '\\', '/'

Write-Host "`n=== MRT WordPress E2E (prepare, Playwright, restore) ===" -ForegroundColor Cyan
& $bash -lc "cd '$unixRoot' && bash '$ciScript'"
exit $LASTEXITCODE
