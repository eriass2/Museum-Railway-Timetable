# DEPRECATED: use `composer plugin-check` or `php scripts/validate.php` instead.
# Thin wrapper kept for backwards compatibility.
$ErrorActionPreference = 'Stop'
Write-Warning 'validate.ps1 is deprecated. Use: composer plugin-check  (or: php scripts/validate.php)'
& php (Join-Path (Join-Path $PSScriptRoot '..') 'validate.php')
exit $LASTEXITCODE
