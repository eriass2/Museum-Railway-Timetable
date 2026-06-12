# PHPUnit line coverage for inc/ via Docker + PCOV (exploratory; not a CI gate).
param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = 'Stop'
$scriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
. (Join-Path $scriptsRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

Assert-MrtDockerAvailable -Message 'Docker is not running. Coverage requires Docker (php-test + PCOV).'
Ensure-MrtVendor

New-Item -ItemType Directory -Force -Path coverage | Out-Null

$phpUnitArgs = @('--coverage-clover', 'coverage/clover.xml', '--colors=never')
if ($Passthrough.Count -gt 0) {
    $phpUnitArgs += $Passthrough
}

Invoke-MrtDockerPhpUnitWithPcov -PhpUnitArgs $phpUnitArgs -ExitOnError

Write-Host ''
Invoke-MrtDockerPhpTest -PhpArgs @('scripts/coverage-summary.php', 'coverage/clover.xml') `
    -StreamOutput -ExitOnError
exit $LASTEXITCODE
