# Wrapper — implementation in dev/docker-watch.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'dev/docker-watch.ps1') @args
exit $LASTEXITCODE
