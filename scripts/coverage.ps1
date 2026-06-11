# PHPUnit line coverage for inc/ via Docker + PCOV (exploratory; not a CI gate).
param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

if (-not (Test-Path (Join-Path (Get-MrtRepoRoot) 'vendor'))) {
    Write-Host 'vendor/ missing. Run composer install first.' -ForegroundColor Red
    exit 1
}

Assert-MrtDockerAvailable -Message 'Docker is not running. Coverage requires Docker (php:8.2-cli + PCOV).'

New-Item -ItemType Directory -Force -Path coverage | Out-Null

$phpUnitArgs = @('--coverage-clover', 'coverage/clover.xml', '--colors=never')
if ($Passthrough.Count -gt 0) {
    $phpUnitArgs += $Passthrough
}

Invoke-MrtDockerPhpUnitWithPcov -PhpUnitArgs $phpUnitArgs -ExitOnError

Write-Host ''
& php scripts/coverage-summary.php coverage/clover.xml
exit $LASTEXITCODE
