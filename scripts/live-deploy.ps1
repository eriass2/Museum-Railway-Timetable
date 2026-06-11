# Sync Museum Railway Timetable plugin to a live/staging WordPress site.
# Like Docker volume mount: copy plugin files (inc, assets, ...) - no zip, no data reset.
#
# Usage (from repo root):
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\live-deploy.ps1
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\live-deploy.ps1 -SkipBuild
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\live-deploy.ps1 -Watch
#   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\live-deploy.ps1 -UseDocker
#
# Config: copy local/live-deploy.config.example.json to local/live-deploy.config.json

param(
    [switch] $SkipBuild = $false,
    [switch] $Watch = $false,
    [switch] $UseDocker = $false,
    [string] $ConfigPath = ""
)

$ErrorActionPreference = "Stop"
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
$projectRoot = Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

$pluginSlug = $script:MrtPluginSlug
$pluginItems = $script:MrtPluginItems

if (-not $ConfigPath) {
    $ConfigPath = Join-Path $projectRoot "local/live-deploy.config.json"
}

function Get-LiveDeployConfig {
    param([string] $Path)

    if (-not (Test-Path $Path)) {
        Write-Host "ERROR: Missing config: $Path" -ForegroundColor Red
        Write-Host "Copy local/live-deploy.config.example.json to local/live-deploy.config.json" -ForegroundColor Yellow
        exit 1
    }

    try {
        return Get-Content $Path -Raw | ConvertFrom-Json
    } catch {
        Write-Host "ERROR: Could not parse $Path - $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

function Sync-PluginItemSsh {
    param(
        [string] $SourceRoot,
        [string] $SshHost,
        [int] $SshPort,
        [string] $RemoteRoot,
        [string] $Item
    )

    $src = Join-Path $SourceRoot $Item
    if (-not (Test-Path $src)) {
        Write-Host "  Skip (missing): $Item" -ForegroundColor Yellow
        return $false
    }

    $remoteItem = ($RemoteRoot.TrimEnd("/") + "/" + $Item) -replace "\\", "/"
    $sshPortArg = if ($SshPort -ne 22) { @("-p", $SshPort) } else { @() }
    $scpPortArg = if ($SshPort -ne 22) { @("-P", $SshPort) } else { @() }

    & ssh @sshPortArg $SshHost "rm -rf '$remoteItem'"
    if ($LASTEXITCODE -ne 0) {
        throw "ssh rm failed for $Item (exit $LASTEXITCODE)."
    }

    & scp @scpPortArg -r $src "${SshHost}:$remoteItem"
    if ($LASTEXITCODE -ne 0) {
        throw "scp failed for $Item (exit $LASTEXITCODE)."
    }

    Write-Host "  Synced: $Item" -ForegroundColor Green
    return $true
}

function Invoke-PluginSync {
    param(
        $Config
    )

    $copied = 0
    switch ($Config.targetType) {
        "local" {
            $target = [string] $Config.localPath
            if (-not $target) {
                throw "localPath is required when targetType is local."
            }

            $parent = Split-Path -Parent $target
            if (-not (Test-Path $parent)) {
                throw "Target parent folder not found: $parent"
            }
            if (-not (Test-Path $target)) {
                New-Item -ItemType Directory -Path $target -Force | Out-Null
            }

            Write-Host "  Target (local): $target" -ForegroundColor Gray
            foreach ($item in $pluginItems) {
                if (Copy-MrtPluginItem -SourceRoot $projectRoot -TargetRoot $target -Item $item) {
                    $copied++
                }
            }
        }
        "ssh" {
            $hostName = [string] $Config.sshHost
            $remote = [string] $Config.remotePath
            $port = if ($Config.sshPort) { [int] $Config.sshPort } else { 22 }

            if (-not $hostName -or -not $remote) {
                throw "sshHost and remotePath are required when targetType is ssh."
            }

            $portArg = if ($port -ne 22) { @("-p", $port) } else { @() }
            $remoteUnix = ($remote -replace '\\', '/')
            & ssh @portArg $hostName "mkdir -p $remoteUnix"
            if ($LASTEXITCODE -ne 0) {
                throw "ssh mkdir failed (exit $LASTEXITCODE)."
            }

            Write-Host "  Target (ssh): ${hostName}:$remote" -ForegroundColor Gray
            foreach ($item in $pluginItems) {
                if (Sync-PluginItemSsh -SourceRoot $projectRoot -SshHost $hostName -SshPort $port -RemoteRoot $remote -Item $item) {
                    $copied++
                }
            }
        }
        default {
            throw "Unknown targetType '$($Config.targetType)'. Use 'local' or 'ssh'."
        }
    }

    return $copied
}

function Invoke-LiveDeploy {
    param(
        [bool] $BuildVue
    )

    $config = Get-LiveDeployConfig -Path $ConfigPath

    Write-Host "`n=== MRT live deploy (sync plugin, no data reset) ===" -ForegroundColor Cyan
    Write-Host "  Source: $projectRoot" -ForegroundColor Gray

    if ($BuildVue) {
        Invoke-MrtVueBuild -UseDocker:$UseDocker
    } elseif (-not $SkipBuild) {
        $distAdmin = Join-Path $projectRoot "assets/dist/vue/assets/admin.js"
        if (-not (Test-Path $distAdmin)) {
            Write-Host "No Vue build found - building first." -ForegroundColor Yellow
            Invoke-MrtVueBuild -UseDocker:$UseDocker
        }
    }

    Write-Host "`n--- Sync plugin files ---" -ForegroundColor Cyan
    $copied = Invoke-PluginSync -Config $config

    Write-Host "`nDeploy complete! ($copied items)" -ForegroundColor Green
    if ($config.siteUrl) {
        Write-Host "Site: $($config.siteUrl)" -ForegroundColor Gray
    }
}

function Start-LiveDeployWatch {
    $watchRoots = @(
        @{ Path = Join-Path $projectRoot "frontend/vue/src"; Vue = $true },
        @{ Path = Join-Path $projectRoot "frontend/vue/index.html"; Vue = $true },
        @{ Path = Join-Path $projectRoot "frontend/vue/vite.config.ts"; Vue = $true },
        @{ Path = Join-Path $projectRoot "inc"; Vue = $false },
        @{ Path = Join-Path $projectRoot "assets"; Vue = $false },
        @{ Path = Join-Path $projectRoot "languages"; Vue = $false },
        @{ Path = Join-Path $projectRoot "$pluginSlug.php"; Vue = $false },
        @{ Path = Join-Path $projectRoot "uninstall.php"; Vue = $false }
    )

    $script:MrtWatchNeedsVue = $false
    $script:MrtWatchDueAt = $null
    $debounceSec = 2.5

    Write-Host "`n=== MRT live deploy - watch mode ===" -ForegroundColor Cyan
    Write-Host "Press Ctrl+C to stop.`n" -ForegroundColor Gray

    Invoke-LiveDeploy -BuildVue (-not $SkipBuild)

    $watchers = @()
    foreach ($root in $watchRoots) {
        $path = $root.Path
        if (-not (Test-Path $path)) { continue }

        $item = Get-Item $path
        $w = New-Object System.IO.FileSystemWatcher
        $w.Path = if ($item.PSIsContainer) { $path } else { $item.DirectoryName }
        $w.Filter = if ($item.PSIsContainer) { "*.*" } else { $item.Name }
        $w.IncludeSubdirectories = $item.PSIsContainer
        $w.EnableRaisingEvents = $true

        $needsVue = [bool] $root.Vue
        Register-ObjectEvent -InputObject $w -EventName Changed -Action {
            if ($Event.SourceEventArgs.Name -match '\.(tmp|swp|~)$') { return }
            if ($using:needsVue) { $script:MrtWatchNeedsVue = $true }
            $script:MrtWatchDueAt = (Get-Date).AddSeconds($using:debounceSec)
        } | Out-Null
        Register-ObjectEvent -InputObject $w -EventName Created -Action {
            if ($using:needsVue) { $script:MrtWatchNeedsVue = $true }
            $script:MrtWatchDueAt = (Get-Date).AddSeconds($using:debounceSec)
        } | Out-Null
        Register-ObjectEvent -InputObject $w -EventName Renamed -Action {
            if ($using:needsVue) { $script:MrtWatchNeedsVue = $true }
            $script:MrtWatchDueAt = (Get-Date).AddSeconds($using:debounceSec)
        } | Out-Null
        Register-ObjectEvent -InputObject $w -EventName Deleted -Action {
            if ($using:needsVue) { $script:MrtWatchNeedsVue = $true }
            $script:MrtWatchDueAt = (Get-Date).AddSeconds($using:debounceSec)
        } | Out-Null

        $watchers += $w
        Write-Host "Watching: $path" -ForegroundColor Gray
    }

    try {
        while ($true) {
            Start-Sleep -Milliseconds 400
            if (-not $script:MrtWatchDueAt) { continue }
            if ((Get-Date) -lt $script:MrtWatchDueAt) { continue }

            $buildVue = $script:MrtWatchNeedsVue
            $script:MrtWatchNeedsVue = $false
            $script:MrtWatchDueAt = $null

            try {
                Invoke-LiveDeploy -BuildVue $buildVue
            } catch {
                Write-Host "`nWatch deploy failed: $($_.Exception.Message)" -ForegroundColor Red
            }
        }
    } finally {
        foreach ($w in $watchers) {
            $w.EnableRaisingEvents = $false
            $w.Dispose()
        }
        Get-EventSubscriber | Unregister-Event -ErrorAction SilentlyContinue
    }
}

if ($Watch) {
    Start-LiveDeployWatch
} else {
    Invoke-LiveDeploy -BuildVue (-not $SkipBuild)
}
