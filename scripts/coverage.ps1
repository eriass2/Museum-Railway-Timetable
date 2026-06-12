# Wrapper — implementation in gate/coverage.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'gate/coverage.ps1') @args
exit $LASTEXITCODE
