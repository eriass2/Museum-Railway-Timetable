# Wrapper — implementation in gate/lint.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'gate/lint.ps1') @args
exit $LASTEXITCODE
