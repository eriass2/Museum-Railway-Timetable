# Run PHP quality gates in Docker (validate + PHPStan + PHPUnit; optional PHPCS).
# Pass -Vue to also run frontend/vue via the node:22-alpine container.
param(
    [switch]$SkipPhpcs,
    [switch]$Vue,
    [switch]$Timings,
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot '_runner.ps1')
Initialize-MrtGateEnvironment -Timings:$Timings -RequireDocker -EnsureVendor

$composerScript = if ($SkipPhpcs) { 'check' } else { 'check:all' }
$checkArgs = @($composerScript)
if ($Passthrough.Count -gt 0) {
    $checkArgs += $Passthrough
}
Invoke-MrtTimedStep -Title "composer $composerScript (Docker)" -Action {
    Invoke-MrtDockerComposer -ComposerArgs $checkArgs -ExitOnError
}

Write-Host 'PHP check OK.' -ForegroundColor Green

if ($Vue) {
    $vueArgs = @()
    if ($Timings) {
        $vueArgs += '-Timings'
    }
    & (Join-Path $script:MrtGateScriptsRoot 'gate/vue-check.ps1') @vueArgs
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}

Complete-MrtGateEnvironment
