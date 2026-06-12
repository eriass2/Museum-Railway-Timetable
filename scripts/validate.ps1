# Wrapper — implementation in gate/validate.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'gate/validate.ps1') @args
exit $LASTEXITCODE
