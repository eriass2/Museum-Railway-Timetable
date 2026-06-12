# Wrapper — implementation in release/live-deploy.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'release/live-deploy.ps1') @args
exit $LASTEXITCODE
