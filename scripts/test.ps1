# Wrapper — implementation in gate/test.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'gate/test.ps1') @args
exit $LASTEXITCODE
