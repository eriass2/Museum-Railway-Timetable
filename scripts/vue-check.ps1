# Wrapper — implementation in gate/vue-check.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'gate/vue-check.ps1') @args
exit $LASTEXITCODE
