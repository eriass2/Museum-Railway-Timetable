# Wrapper — implementation in dev/docker-dev-reset.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'dev/docker-dev-reset.ps1') @args
exit $LASTEXITCODE
