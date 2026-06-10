# PHPUnit line coverage for inc/ via Docker + PCOV (exploratory; not a CI gate).
param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Passthrough
)

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot\..

if (-not (Test-Path vendor)) {
    Write-Host "vendor/ missing. Run composer install first." -ForegroundColor Red
    exit 1
}

& docker info 2>$null | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Docker is not running. Coverage requires Docker (php:8.2-cli + PCOV)." -ForegroundColor Red
    exit 1
}

New-Item -ItemType Directory -Force -Path coverage | Out-Null

$phpUnitArgs = @('--coverage-clover', 'coverage/clover.xml', '--colors=never')
if ($Passthrough.Count -gt 0) {
    $phpUnitArgs += $Passthrough
}

Write-Host "Running PHPUnit with PCOV in Docker..." -ForegroundColor Cyan
$shellCmd = @'
apt-get update -qq && apt-get install -y -qq $PHPIZE_DEPS >/dev/null &&
pecl install pcov >/dev/null && docker-php-ext-enable pcov >/dev/null &&
vendor/bin/phpunit
'@ -replace "`r`n", ' '

$dockerArgs = @(
    'compose', '--profile', 'tools', 'run', '--rm', '--entrypoint', 'sh', 'php-test',
    '-c', "$shellCmd $($phpUnitArgs -join ' ')"
)
$prevEap = $ErrorActionPreference
$ErrorActionPreference = 'Continue'
& docker @dockerArgs 2>&1 | ForEach-Object { Write-Host $_ }
$dockerExit = $LASTEXITCODE
$ErrorActionPreference = $prevEap
if ($dockerExit -ne 0) {
    exit $dockerExit
}

Write-Host ""
& php scripts/coverage-summary.php coverage/clover.xml
exit $LASTEXITCODE
