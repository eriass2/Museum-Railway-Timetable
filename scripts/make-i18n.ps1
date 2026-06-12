# Wrapper — implementation in i18n/make-i18n.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'i18n/make-i18n.ps1') @args
exit $LASTEXITCODE
