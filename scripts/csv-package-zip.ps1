# Pack Lennakatten CSV fixture into an import-ready zip (validate + Linux zip).
#
# Usage:
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\csv-package-zip.ps1
#   powershell -File .\scripts\csv-package-zip.ps1 -Source testdata/fixtures/lennakatten -Output testdata/fixtures/lennakatten.zip

param(
    [string] $Source = "testdata/fixtures/lennakatten",
    [string] $Output = "testdata/fixtures/lennakatten.zip"
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

Write-Host "`n=== csv-package-zip: validate ===" -ForegroundColor Cyan
docker compose --profile tools run --rm php-test scripts/csv-validate.php $Source
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "`n=== csv-package-zip: pack ===" -ForegroundColor Cyan
$sourcePosix = ($Source -replace '\\', '/')
$outputPosix = ($Output -replace '\\', '/')
$zipCmd = "apk add --no-cache zip >/dev/null && rm -f '$outputPosix' && cd '$sourcePosix' && zip -qr '/app/$outputPosix' manifest.json *.csv"

docker run --rm -v "${root}:/app" -w /app alpine sh -c $zipCmd
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

$outPath = Join-Path $root $Output
$sizeKb = [math]::Round((Get-Item $outPath).Length / 1KB, 1)
Write-Host "OK: $outPath ($sizeKb KiB)" -ForegroundColor Green
