# Shared plugin metadata for MRT scripts.
# Dot-source via Mrt.Docker.ps1 or directly from release/deploy scripts.

$script:MrtPluginSlug = 'museum-railway-timetable'
$script:MrtPluginItems = @(
    "$script:MrtPluginSlug.php",
    'uninstall.php',
    'inc',
    'assets',
    'languages'
)

$script:MrtDevSiteUrl = 'http://localhost:8080'
$script:MrtWpPluginContainerPath = "/var/www/html/wp-content/plugins/$script:MrtPluginSlug"

function Set-MrtRepoRoot {
    param(
        [Parameter(Mandatory = $true)]
        [string] $ScriptsDirectory
    )

    $root = (Resolve-Path (Join-Path $ScriptsDirectory '..')).Path
    $script:MrtRepoRoot = $root
    Set-Location $root
    return $root
}

function Get-MrtRepoRoot {
    if (-not $script:MrtRepoRoot) {
        throw 'Call Set-MrtRepoRoot first.'
    }
    return $script:MrtRepoRoot
}

function Get-MrtPluginVersion {
    $root = Get-MrtRepoRoot
    $main = Join-Path $root "$script:MrtPluginSlug.php"
    if (-not (Test-Path $main)) {
        throw "Missing $script:MrtPluginSlug.php"
    }

    $content = Get-Content $main -Raw
    if ($content -match "define\s*\(\s*'MRT_VERSION'\s*,\s*'([^']+)'\s*\)") {
        return $Matches[1]
    }
    if ($content -match '\* Version:\s*([0-9.]+)') {
        return $Matches[1]
    }
    return '0.0.0'
}

function Copy-MrtPluginItem {
    param(
        [Parameter(Mandatory = $true)]
        [string] $SourceRoot,
        [Parameter(Mandatory = $true)]
        [string] $TargetRoot,
        [Parameter(Mandatory = $true)]
        [string] $Item
    )

    $src = Join-Path $SourceRoot $Item
    $dst = Join-Path $TargetRoot $Item

    if (-not (Test-Path $src)) {
        Write-Host "  Skip (missing): $Item" -ForegroundColor Yellow
        return $false
    }

    if (Test-Path $dst) {
        Remove-Item $dst -Recurse -Force -ErrorAction SilentlyContinue
    }

    Copy-Item -Path $src -Destination $dst -Recurse -Force
    Write-Host "  Copied: $Item" -ForegroundColor Green
    return $true
}

function Copy-MrtPluginTree {
    param(
        [Parameter(Mandatory = $true)]
        [string] $SourceRoot,
        [Parameter(Mandatory = $true)]
        [string] $TargetRoot,
        [string[]] $Items = $script:MrtPluginItems
    )

    $copied = 0
    foreach ($item in $Items) {
        if (Copy-MrtPluginItem -SourceRoot $SourceRoot -TargetRoot $TargetRoot -Item $item) {
            $copied++
        }
    }
    return $copied
}

function Test-MrtVueArtifacts {
    $root = Get-MrtRepoRoot
    $adminJs = Join-Path $root 'assets/dist/vue/assets/admin.js'
    $manifest = Join-Path $root 'assets/dist/vue/.vite/manifest.json'

    if (-not (Test-Path $adminJs)) {
        throw "Missing $adminJs - run without -SkipBuild or build Vue manually."
    }
    if (-not (Test-Path $manifest)) {
        throw 'Missing Vue manifest at assets/dist/vue/.vite/manifest.json'
    }

    $manifestJson = Get-Content $manifest -Raw | ConvertFrom-Json
    $mainKey = ($manifestJson.PSObject.Properties | Where-Object { $_.Name -like 'src/main.*' } | Select-Object -First 1).Name
    if (-not $mainKey) {
        throw 'Vue manifest has no main entry (src/main.ts).'
    }

    $mainFile = $manifestJson.$mainKey.file
    $mainPath = Join-Path $root "assets/dist/vue/$mainFile"
    if (-not (Test-Path $mainPath)) {
        throw "Missing public bundle: assets/dist/vue/$mainFile"
    }

    Write-Host "  Vue OK: admin.js + $mainFile" -ForegroundColor Green
}
