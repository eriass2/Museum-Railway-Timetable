# Wrapper — implementation in gate/check.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'gate/check.ps1') @args
exit $LASTEXITCODE
