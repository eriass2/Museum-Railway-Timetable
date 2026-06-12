# Wrapper — implementation in php/validate.php
$ErrorActionPreference = 'Stop'
& php (Join-Path $PSScriptRoot 'validate.php')
exit $LASTEXITCODE
