# Wrapper — implementation in csv/csv-package-zip.ps1
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'csv/csv-package-zip.ps1') @args
exit $LASTEXITCODE
