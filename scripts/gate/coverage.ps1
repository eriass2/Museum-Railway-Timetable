# PHPUnit line coverage for inc/ via Docker + PCOV (exploratory; not a CI gate).
param(
    [switch]$Timings,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot '_runner.ps1')
Initialize-MrtGateEnvironment -Timings:$Timings -RequireDocker -EnsureVendor

New-Item -ItemType Directory -Force -Path coverage | Out-Null

$phpUnitArgs = @('--coverage-clover', 'coverage/clover.xml', '--colors=never')
if ($Passthrough.Count -gt 0) {
    $phpUnitArgs += $Passthrough
}

Invoke-MrtTimedStep -Title 'PHPUnit with PCOV (Docker)' -SkipStepHeader -Action {
    Invoke-MrtDockerPhpUnitWithPcov -PhpUnitArgs $phpUnitArgs -ExitOnError
}

Write-Host ''
Invoke-MrtTimedStep -Title 'Coverage summary' -SkipStepHeader -Action {
    Invoke-MrtDockerPhpTest -PhpArgs @('scripts/php/coverage-summary.php', 'coverage/clover.xml') `
        -StreamOutput -ExitOnError
}
Complete-MrtGateEnvironment
exit $LASTEXITCODE
