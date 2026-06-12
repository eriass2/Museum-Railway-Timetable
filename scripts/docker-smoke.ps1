# Wrapper — implementation in dev/docker-smoke.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'dev/docker-smoke.ps1') @args
exit $LASTEXITCODE
