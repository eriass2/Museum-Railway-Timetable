# Run PHP quality gates in Docker (validate + PHPStan + PHPUnit; optional PHPCS).
# Pass -Vue to also run frontend/vue via the node:22-alpine container.
param(
    [switch]$SkipPhpcs,
    [switch]$Vue,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

Assert-MrtDockerAvailable
Ensure-MrtVendor

Write-Host 'Running composer check in Docker...' -ForegroundColor Cyan
$checkArgs = @('check')
if ($Passthrough.Count -gt 0) {
    $checkArgs += $Passthrough
}
Invoke-MrtDockerComposer -ComposerArgs $checkArgs -ExitOnError

if (-not $SkipPhpcs) {
    Write-Host 'Running PHPCS in Docker...' -ForegroundColor Cyan
    Invoke-MrtDockerComposer -ComposerArgs @('phpcs') -ExitOnError
}

Write-Host 'PHP check OK.' -ForegroundColor Green

if ($Vue) {
    & (Join-Path $PSScriptRoot 'vue-check.ps1')
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}
