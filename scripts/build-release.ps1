# Wrapper — implementation in release/build-release.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'release/build-release.ps1') @args
exit $LASTEXITCODE
