# Production release zip helpers (Vue build, validate, pack).

function Invoke-MrtPluginValidate {
    param([Parameter(Mandatory = $true)] [string] $ProjectRoot)

    Write-Host "`n--- Plugin validate ---" -ForegroundColor Cyan
    & php (Join-Path $ProjectRoot 'scripts/validate.php')
    if ($LASTEXITCODE -ne 0) {
        throw "scripts/validate.php failed (exit $LASTEXITCODE)."
    }
}

function Write-MrtReleaseInstallTxt {
    param(
        [Parameter(Mandatory = $true)] [string] $PluginDir,
        [Parameter(Mandatory = $true)] [string] $PluginSlug,
        [Parameter(Mandatory = $true)] [string] $Version
    )

    $mainFile = "$PluginSlug.php"
    $lines = @(
        "Installation och publicering - Museum Railway Timetable v$Version",
        '',
        '1. Installera plugin',
        '   Rekommenderat: wp-admin -> Plugins -> Add New -> Upload Plugin',
        '   Valj zip-filen -> Install Now -> Activate',
        '',
        '   Manuellt (FTP): packa upp zip direkt i wp-content/plugins/',
        '   sa att denna fil ligger har:',
        "     wp-content/plugins/$PluginSlug/$mainFile",
        '',
        '2. Permalankar (kravs for tidtabell i frontend)',
        '   wp-admin -> Inställningar -> Permalankar -> valj t.ex. Inläggsnamn -> Spara',
        '   Utan snygga permalankar kan Vue-frontenden inte hamta tidtabellsdata via REST.',
        '',
        '3. Importera tidtabellsdata (vid tom databas eller ny sajt)',
        '   wp-admin -> Railway Timetable -> Import/export',
        '   Ladda upp CSV-zip (t.ex. lennakatten.zip fran projektets testdata/fixtures/)',
        '   Valj merge eller override. Publicera tidtabellssidor skapas/uppdateras automatiskt.',
        '',
        '4. Kontrollera frontend',
        '   Startsida (Trafikkalender) ska visa månadskalender; klick på trafikdag visar avgångar.',
        '   Varje tidtabellssida anvander [museum_timetable_overview] och laddar rutnat via REST.',
        '',
        'Vanligt fel - filnamn med backslash (Linux):',
        "    $PluginSlug\inc\admin.php  (FEL - ska vara mappen inc/)",
        '  Orsak: Windows-zip med backslash. Anvand build-release.ps1 fran senaste repo.',
        '',
        'Vanligt fel - extra mapp (aktivering misslyckas):',
        "    wp-content/plugins/$PluginSlug/$PluginSlug/$mainFile",
        '  Orsak: gammal installation fanns kvar vid ny uppladdning.',
        '',
        'Innan du laddar upp igen:',
        "  1. Plugins -> ta bort ALLA Museum Railway Timetable (Delete, inte bara avaktivera)",
        "  2. Kontrollera i filhaneraren att mappen plugins/$PluginSlug ar borta",
        '  3. Ladda upp zip pa nytt via Upload Plugin',
        '  4. Aktivera - ska da bara finnas EN rad i pluginlistan',
        '',
        "Version $Version finns aven i plugin-huvudet ($mainFile)."
    )
    Set-Content -Path (Join-Path $PluginDir 'INSTALL.txt') -Value $lines -Encoding UTF8
}

function Test-MrtReleaseZipStructure {
    param(
        [Parameter(Mandatory = $true)] [string] $ZipPath,
        [Parameter(Mandatory = $true)] [string] $PluginSlug
    )

    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $zip = [System.IO.Compression.ZipFile]::OpenRead($ZipPath)
    try {
        $mainEntry = "$PluginSlug/$PluginSlug.php"
        $found = $false
        foreach ($entry in $zip.Entries) {
            if ($entry.FullName -match '\\') {
                throw "Zip entry uses backslash (breaks Linux hosts): $($entry.FullName)"
            }
            if ($entry.FullName -eq $mainEntry) {
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

function New-MrtForwardSlashZip {
    param(
        [Parameter(Mandatory = $true)] [string] $PluginDir,
        [Parameter(Mandatory = $true)] [string] $ZipPath
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

function New-MrtLinuxReleaseZipViaDocker {
    param(
        [Parameter(Mandatory = $true)] [string] $ReleaseDir,
        [Parameter(Mandatory = $true)] [string] $PluginSlug
    )

    if (-not (Test-MrtDockerAvailable)) {
        return $false
    }

    Write-Host '  Zip via Linux (Docker alpine + zip)' -ForegroundColor Gray
    & docker run --rm `
        -v "${ReleaseDir}:/release" `
        alpine sh -c "apk add --no-cache zip >/dev/null && cd /release/staging && zip -qr /release/${PluginSlug}.zip ${PluginSlug}"
    return $LASTEXITCODE -eq 0
}

function New-MrtReleaseZip {
    param(
        [Parameter(Mandatory = $true)] [string] $ProjectRoot,
        [Parameter(Mandatory = $true)] [string] $PluginSlug,
        [Parameter(Mandatory = $true)] [string[]] $PluginItems,
        [Parameter(Mandatory = $true)] [string] $Version,
        [Parameter(Mandatory = $true)] [string] $ReleaseDir,
        [Parameter(Mandatory = $true)] [string] $StagingRoot
    )

    $pluginDir = Join-Path $StagingRoot $PluginSlug
    if (Test-Path $StagingRoot) {
        Remove-Item $StagingRoot -Recurse -Force
    }
    New-Item -ItemType Directory -Path $pluginDir -Force | Out-Null

    foreach ($item in $PluginItems) {
        $src = Join-Path $ProjectRoot $item
        if (-not (Test-Path $src)) {
            throw "Required release file missing: $item"
        }
        Copy-Item -Path $src -Destination (Join-Path $pluginDir $item) -Recurse -Force
        Write-Host "  Packed: $item" -ForegroundColor Green
    }

    Write-MrtReleaseInstallTxt -PluginDir $pluginDir -PluginSlug $PluginSlug -Version $Version
    Write-Host '  Packed: INSTALL.txt' -ForegroundColor Green

    if (-not (Test-Path $ReleaseDir)) {
        New-Item -ItemType Directory -Path $ReleaseDir -Force | Out-Null
    }

    $zipPath = Join-Path $ReleaseDir "$PluginSlug.zip"
    if (Test-Path $zipPath) {
        Remove-Item $zipPath -Force
    }

    Get-ChildItem -Path $ReleaseDir -Filter "$PluginSlug-*.zip" -ErrorAction SilentlyContinue |
        Remove-Item -Force

    $usedDocker = New-MrtLinuxReleaseZipViaDocker -ReleaseDir $ReleaseDir -PluginSlug $PluginSlug
    if (-not $usedDocker) {
        Write-Host '  Zip via .NET (forward-slash entries)' -ForegroundColor Gray
        New-MrtForwardSlashZip -PluginDir $pluginDir -ZipPath $zipPath
    }
    Test-MrtReleaseZipStructure -ZipPath $zipPath -PluginSlug $PluginSlug
    Remove-Item $StagingRoot -Recurse -Force

    return $zipPath
}

function Invoke-MrtReleaseBuild {
    param(
        [Parameter(Mandatory = $true)] [string] $ProjectRoot,
        [Parameter(Mandatory = $true)] [string] $PluginSlug,
        [Parameter(Mandatory = $true)] [string[]] $PluginItems,
        [switch] $SkipBuild,
        [switch] $SkipValidate,
        [switch] $UseDocker
    )

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
        Invoke-MrtPluginValidate -ProjectRoot $ProjectRoot
    } else {
        Write-Host "`n--- Validate skipped (-SkipValidate) ---" -ForegroundColor Yellow
    }

    $releaseDir = Join-Path $ProjectRoot 'release'
    $stagingRoot = Join-Path $releaseDir 'staging'
    Write-Host "`n--- Pack zip ---" -ForegroundColor Cyan
    $zipPath = New-MrtReleaseZip -ProjectRoot $ProjectRoot -PluginSlug $PluginSlug `
        -PluginItems $PluginItems -Version $version -ReleaseDir $releaseDir -StagingRoot $stagingRoot

    $sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
    Write-Host "`nRelease ready:" -ForegroundColor Green
    Write-Host "  $zipPath ($sizeMb MB)" -ForegroundColor White
    Write-Host "`nLive: wp-admin -> Plugins -> Add New -> Upload Plugin -> choose this zip." -ForegroundColor Gray
    Write-Host 'Manual FTP: extract into wp-content/plugins/ (see INSTALL.txt in zip).' -ForegroundColor Gray
    Write-Host "Ensure pretty permalinks and WP_DEBUG off on production.`n" -ForegroundColor Gray
}
