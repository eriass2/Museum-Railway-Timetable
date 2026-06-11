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
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
$projectRoot = Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

$pluginSlug = $script:MrtPluginSlug
$pluginItems = $script:MrtPluginItems

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
        "Installation och publicering - Museum Railway Timetable v$Version",
        "",
        "1. Installera plugin",
        "   Rekommenderat: wp-admin -> Plugins -> Add New -> Upload Plugin",
        "   Valj zip-filen -> Install Now -> Activate",
        "",
        "   Manuellt (FTP): packa upp zip direkt i wp-content/plugins/",
        "   sa att denna fil ligger har:",
        "     wp-content/plugins/$pluginSlug/$mainFile",
        "",
        "2. Permalankar (kravs for tidtabell i frontend)",
        "   wp-admin -> Inställningar -> Permalankar -> valj t.ex. Inläggsnamn -> Spara",
        "   Utan snygga permalankar kan Vue-frontenden inte hamta tidtabellsdata via REST.",
        "",
        "3. Importera tidtabellsdata (vid tom databas eller ny sajt)",
        "   wp-admin -> Railway Timetable -> Import/export",
        "   Ladda upp CSV-zip (t.ex. lennakatten.zip fran projektets testdata/fixtures/)",
        "   Valj merge eller override. Publica tidtabellssidor skapas/uppdateras automatiskt.",
        "",
        "4. Kontrollera frontend",
        "   Startsida (Trafikkalender) ska visa månadskalender; klick på trafikdag visar avgångar.",
        "   Varje tidtabellssida anvander [museum_timetable_overview] och laddar rutnat via REST.",
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

    if (-not (Test-MrtDockerAvailable)) {
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

$version = Get-MrtPluginVersion
Write-Host "Version: $version" -ForegroundColor Gray

if (-not $SkipBuild) {
    Invoke-MrtVueBuild -UseDocker:$UseDocker
} else {
    Write-Host "`n--- Vue build skipped (-SkipBuild) ---" -ForegroundColor Yellow
}

Test-MrtVueArtifacts

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
