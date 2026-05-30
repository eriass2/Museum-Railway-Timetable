# Build Vue bundles and pack a production-ready plugin zip for live WordPress.
#
# Usage (from repo root):
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\build-release.ps1
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\build-release.ps1 -SkipBuild
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\build-release.ps1 -UseDocker
#
# Output: release/museum-railway-timetable.zip (folder name = zip name, avoids double-nesting)
# Upload: wp-admin → Plugins → Add New → Upload Plugin (recommended).

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

function Write-InstallTxt {
    param(
        [string] $PluginDir,
        [string] $Version
    )

    $mainFile = "$pluginSlug.php"
    $lines = @(
        "Installation - Museum Railway Timetable v$Version",
        "",
        "Rekommenderat:",
        "  wp-admin -> Plugins -> Add New -> Upload Plugin",
        "  Valj zip-filen -> Install Now -> Activate",
        "",
        "Manuellt (FTP / filhanterare):",
        "  Packa upp zip direkt i wp-content/plugins/",
        "  sa att denna fil hamnar har:",
        "    wp-content/plugins/$pluginSlug/$mainFile",
        "",
        "Vanligt fel - filnamn med backslash (Linux):",
        "    $pluginSlug\inc\admin.php  (FEL - ska vara mappen inc/)",
        "  Orsak: Windows-zip med backslash. Anvand build-release.ps1 fran senaste repo.",
        "",
        "Vanligt fel - extra mapp (aktivering misslyckas):",
        "    wp-content/plugins/$pluginSlug/$pluginSlug/$mainFile",
        "  Orsak: gammal installation fanns kvar vid ny uppladdning.",
        "",
        "Innan du laddar upp igen:",
        "  1. Plugins -> ta bort ALLA Museum Railway Timetable (Delete, inte bara avaktivera)",
        "  2. Kontrollera i filhaneraren att mappen plugins/$pluginSlug ar borta",
        "  3. Ladda upp zip pa nytt via Upload Plugin",
        "  4. Aktivera - ska da bara finnas EN rad i pluginlistan",
        "",
        "Version $Version finns aven i plugin-huvudet ($mainFile)."
    )
    Set-Content -Path (Join-Path $PluginDir "INSTALL.txt") -Value $lines -Encoding UTF8
}

function Test-ReleaseZipStructure {
    param([string] $ZipPath)

    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $zip = [System.IO.Compression.ZipFile]::OpenRead($ZipPath)
    try {
        $mainEntry = "$pluginSlug/$pluginSlug.php"
        $found = $false
        foreach ($entry in $zip.Entries) {
            if ($entry.FullName -match '\\') {
                throw "Zip entry uses backslash (breaks Linux hosts): $($entry.FullName)"
            }
            $name = $entry.FullName
            if ($name -eq $mainEntry) {
                $found = $true
            }
        }
        if (-not $found) {
            throw "Zip missing required entry: $mainEntry"
        }
        Write-Host "  Zip OK: $mainEntry, forward-slash paths only" -ForegroundColor Green
    } finally {
        $zip.Dispose()
    }
}

function New-ForwardSlashZip {
    param(
        [string] $PluginDir,
        [string] $ZipPath
    )

    Add-Type -AssemblyName System.IO.Compression
    Add-Type -AssemblyName System.IO.Compression.FileSystem

    if (Test-Path $ZipPath) {
        Remove-Item $ZipPath -Force
    }

    $root = (Resolve-Path $PluginDir).Path
    $prefix = Split-Path $PluginDir -Leaf
    $archive = [System.IO.Compression.ZipFile]::Open($ZipPath, [System.IO.Compression.ZipArchiveMode]::Create)
    try {
        Get-ChildItem -Path $PluginDir -Recurse -File | ForEach-Object {
            $relative = $_.FullName.Substring($root.Length).TrimStart('\', '/')
            $entryName = "$prefix/" + ($relative -replace '\\', '/')
            [void][System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                $archive,
                $_.FullName,
                $entryName,
                [System.IO.Compression.CompressionLevel]::Optimal
            )
        }
    } finally {
        $archive.Dispose()
    }
}

function New-LinuxZipViaDocker {
    param(
        [string] $ReleaseDir,
        [string] $ZipPath
    )

    if (-not (Test-DockerAvailable)) {
        return $false
    }

    $releaseMount = $ReleaseDir -replace '\\', '/'
    if ($releaseMount -match '^[A-Za-z]:') {
        $drive = $releaseMount.Substring(0, 1).ToLower()
        $releaseMount = "/${drive}/" + $releaseMount.Substring(3)
    }

    Write-Host "  Zip via Linux (Docker alpine + zip)" -ForegroundColor Gray
    & docker run --rm `
        -v "${ReleaseDir}:/release" `
        alpine sh -c "apk add --no-cache zip >/dev/null && cd /release/staging && zip -qr /release/museum-railway-timetable.zip museum-railway-timetable"
    return $LASTEXITCODE -eq 0
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

    Write-InstallTxt -PluginDir $pluginDir -Version $Version
    Write-Host "  Packed: INSTALL.txt" -ForegroundColor Green

    if (-not (Test-Path $ReleaseDir)) {
        New-Item -ItemType Directory -Path $ReleaseDir -Force | Out-Null
    }

    # Zip name matches plugin folder slug — avoids museum-railway-timetable-0.3.0/museum-railway-timetable/…
    $zipName = "$pluginSlug.zip"
    $zipPath = Join-Path $ReleaseDir $zipName
    if (Test-Path $zipPath) {
        Remove-Item $zipPath -Force
    }

    # Remove legacy version-suffixed zips from earlier build scripts.
    Get-ChildItem -Path $ReleaseDir -Filter "$pluginSlug-*.zip" -ErrorAction SilentlyContinue |
        Remove-Item -Force

    $usedDocker = New-LinuxZipViaDocker -ReleaseDir $ReleaseDir -ZipPath $zipPath
    if (-not $usedDocker) {
        Write-Host "  Zip via .NET (forward-slash entries)" -ForegroundColor Gray
        New-ForwardSlashZip -PluginDir $pluginDir -ZipPath $zipPath
    }
    Test-ReleaseZipStructure -ZipPath $zipPath
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
Write-Host "`nLive: wp-admin -> Plugins -> Add New -> Upload Plugin -> choose this zip." -ForegroundColor Gray
Write-Host "Manual FTP: extract into wp-content/plugins/ (see INSTALL.txt in zip)." -ForegroundColor Gray
Write-Host "Ensure pretty permalinks and WP_DEBUG off on production.`n" -ForegroundColor Gray
