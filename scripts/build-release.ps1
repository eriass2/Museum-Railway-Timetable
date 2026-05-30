# Build Vue bundles and pack a production-ready plugin zip for live WordPress.
#
# Usage (from repo root):
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\build-release.ps1
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\build-release.ps1 -SkipBuild
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\build-release.ps1 -UseDocker
#
# Output: release/museum-railway-timetable-<version>.zip
# Upload: unzip into wp-content/plugins/ (or upload zip via host panel).

param(
    [switch] $SkipBuild = $false,
    [switch] $SkipValidate = $false,
    [switch] $UseDocker = $false
)

$ErrorActionPreference = "Stop"
$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

$pluginSlug = "museum-railway-timetable"
$pluginItems = @(
    "$pluginSlug.php",
    "uninstall.php",
    "inc",
    "assets",
    "languages"
)

function Test-DockerAvailable {
    & docker info 2>$null | Out-Null
    return $LASTEXITCODE -eq 0
}

function Test-NpmAvailable {
    try {
        & npm --version 2>$null | Out-Null
        return $LASTEXITCODE -eq 0
    } catch {
        return $false
    }
}

function Get-PluginVersion {
    $main = Join-Path $projectRoot "$pluginSlug.php"
    if (-not (Test-Path $main)) {
        throw "Missing $pluginSlug.php"
    }
    $content = Get-Content $main -Raw
    if ($content -match "define\s*\(\s*'MRT_VERSION'\s*,\s*'([^']+)'\s*\)") {
        return $Matches[1]
    }
    if ($content -match '\* Version:\s*([0-9.]+)') {
        return $Matches[1]
    }
    return "0.0.0"
}

function Invoke-VueBuild {
    if ($UseDocker -or -not (Test-NpmAvailable)) {
        if (-not (Test-DockerAvailable)) {
            throw "npm not in PATH and Docker is not running. Install Node or start Docker."
        }
        Write-Host "`n--- Vue build (Docker) ---" -ForegroundColor Cyan
        & docker compose --profile tools run --rm vue sh -c "npm ci && npm run build && npm run verify"
        if ($LASTEXITCODE -ne 0) {
            throw "Vue build failed in Docker (exit $LASTEXITCODE)."
        }
        return
    }

    Write-Host "`n--- Vue build (local npm) ---" -ForegroundColor Cyan
    & composer vue:build
    if ($LASTEXITCODE -ne 0) {
        throw "composer vue:build failed (exit $LASTEXITCODE)."
    }

    Push-Location (Join-Path $projectRoot "frontend/vue")
    try {
        & npm run verify
        if ($LASTEXITCODE -ne 0) {
            throw "npm run verify failed (exit $LASTEXITCODE)."
        }
    } finally {
        Pop-Location
    }
}

function Test-VueArtifacts {
    $adminJs = Join-Path $projectRoot "assets/dist/vue/assets/admin.js"
    $manifest = Join-Path $projectRoot "assets/dist/vue/.vite/manifest.json"
    if (-not (Test-Path $adminJs)) {
        throw "Missing $adminJs - run without -SkipBuild or build Vue manually."
    }
    if (-not (Test-Path $manifest)) {
        throw "Missing Vue manifest at assets/dist/vue/.vite/manifest.json"
    }
    $manifestJson = Get-Content $manifest -Raw | ConvertFrom-Json
    $mainKey = ($manifestJson.PSObject.Properties | Where-Object { $_.Name -like "src/main.*" } | Select-Object -First 1).Name
    if (-not $mainKey) {
        throw "Vue manifest has no main entry (src/main.ts)."
    }
    $mainFile = $manifestJson.$mainKey.file
    $mainPath = Join-Path $projectRoot "assets/dist/vue/$mainFile"
    if (-not (Test-Path $mainPath)) {
        throw "Missing public bundle: assets/dist/vue/$mainFile"
    }
    Write-Host "  Vue OK: admin.js + $mainFile" -ForegroundColor Green
}

function Invoke-PluginValidate {
    Write-Host "`n--- Plugin validate ---" -ForegroundColor Cyan
    & php (Join-Path $projectRoot "scripts/validate.php")
    if ($LASTEXITCODE -ne 0) {
        throw "scripts/validate.php failed (exit $LASTEXITCODE)."
    }
}

function New-ReleaseZip {
    param(
        [string] $Version,
        [string] $ReleaseDir,
        [string] $StagingRoot
    )

    $pluginDir = Join-Path $StagingRoot $pluginSlug
    if (Test-Path $StagingRoot) {
        Remove-Item $StagingRoot -Recurse -Force
    }
    New-Item -ItemType Directory -Path $pluginDir -Force | Out-Null

    foreach ($item in $pluginItems) {
        $src = Join-Path $projectRoot $item
        if (-not (Test-Path $src)) {
            throw "Required release file missing: $item"
        }
        Copy-Item -Path $src -Destination (Join-Path $pluginDir $item) -Recurse -Force
        Write-Host "  Packed: $item" -ForegroundColor Green
    }

    if (-not (Test-Path $ReleaseDir)) {
        New-Item -ItemType Directory -Path $ReleaseDir -Force | Out-Null
    }

    $zipName = "$pluginSlug-$Version.zip"
    $zipPath = Join-Path $ReleaseDir $zipName
    if (Test-Path $zipPath) {
        Remove-Item $zipPath -Force
    }

    Compress-Archive -Path $pluginDir -DestinationPath $zipPath -CompressionLevel Optimal
    Remove-Item $StagingRoot -Recurse -Force

    return $zipPath
}

Write-Host "`n=== Museum Railway Timetable - release build ===" -ForegroundColor Cyan

$version = Get-PluginVersion
Write-Host "Version: $version" -ForegroundColor Gray

if (-not $SkipBuild) {
    Invoke-VueBuild
} else {
    Write-Host "`n--- Vue build skipped (-SkipBuild) ---" -ForegroundColor Yellow
}

Test-VueArtifacts

if (-not $SkipValidate) {
    Invoke-PluginValidate
} else {
    Write-Host "`n--- Validate skipped (-SkipValidate) ---" -ForegroundColor Yellow
}

$releaseDir = Join-Path $projectRoot "release"
$stagingRoot = Join-Path $releaseDir "staging"
Write-Host "`n--- Pack zip ---" -ForegroundColor Cyan
$zipPath = New-ReleaseZip -Version $version -ReleaseDir $releaseDir -StagingRoot $stagingRoot

$sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host "`nRelease ready:" -ForegroundColor Green
Write-Host "  $zipPath ($sizeMb MB)" -ForegroundColor White
Write-Host "`nLive: upload zip, extract to wp-content/plugins/, activate in WP admin." -ForegroundColor Gray
Write-Host "Ensure pretty permalinks and WP_DEBUG off on production.`n" -ForegroundColor Gray
