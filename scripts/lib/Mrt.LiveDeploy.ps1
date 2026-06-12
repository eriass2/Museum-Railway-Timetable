# Live deploy helpers: config, sync, and watch mode.

function Get-MrtLiveDeployConfig {
    param([Parameter(Mandatory = $true)] [string] $Path)

    if (-not (Test-Path $Path)) {
        Write-Host "ERROR: Missing config: $Path" -ForegroundColor Red
        Write-Host 'Copy local/live-deploy.config.example.json to local/live-deploy.config.json' -ForegroundColor Yellow
        exit 1
    }

    try {
        return Get-Content $Path -Raw | ConvertFrom-Json
    } catch {
        Write-Host "ERROR: Could not parse $Path - $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

function Sync-MrtPluginItemSsh {
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

    $remoteItem = ($RemoteRoot.TrimEnd('/') + '/' + $Item) -replace '\\', '/'
    $sshPortArg = if ($SshPort -ne 22) { @('-p', $SshPort) } else { @() }
    $scpPortArg = if ($SshPort -ne 22) { @('-P', $SshPort) } else { @() }

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

function Sync-MrtPluginLocal {
    param(
        [string] $SourceRoot,
        [string] $TargetRoot,
        [string[]] $PluginItems
    )

    $parent = Split-Path -Parent $TargetRoot
    if (-not (Test-Path $parent)) {
        throw "Target parent folder not found: $parent"
    }
    if (-not (Test-Path $TargetRoot)) {
        New-Item -ItemType Directory -Path $TargetRoot -Force | Out-Null
    }

    Write-Host "  Target (local): $TargetRoot" -ForegroundColor Gray
    $copied = 0
    foreach ($item in $PluginItems) {
        if (Copy-MrtPluginItem -SourceRoot $SourceRoot -TargetRoot $TargetRoot -Item $item) {
            $copied++
        }
    }
    return $copied
}

function Sync-MrtPluginSsh {
    param(
        [string] $SourceRoot,
        [string] $SshHost,
        [int] $SshPort,
        [string] $RemoteRoot,
        [string[]] $PluginItems
    )

    $portArg = if ($SshPort -ne 22) { @('-p', $SshPort) } else { @() }
    $remoteUnix = ($RemoteRoot -replace '\\', '/')
    & ssh @portArg $SshHost "mkdir -p $remoteUnix"
    if ($LASTEXITCODE -ne 0) {
        throw "ssh mkdir failed (exit $LASTEXITCODE)."
    }

    Write-Host "  Target (ssh): ${SshHost}:$RemoteRoot" -ForegroundColor Gray
    $copied = 0
    foreach ($item in $PluginItems) {
        if (Sync-MrtPluginItemSsh -SourceRoot $SourceRoot -SshHost $SshHost `
                -SshPort $SshPort -RemoteRoot $RemoteRoot -Item $item) {
            $copied++
        }
    }
    return $copied
}

function Invoke-MrtPluginSync {
    param(
        $Config,
        [string] $SourceRoot,
        [string[]] $PluginItems
    )

    switch ($Config.targetType) {
        'local' {
            $target = [string] $Config.localPath
            if (-not $target) {
                throw 'localPath is required when targetType is local.'
            }
            return Sync-MrtPluginLocal -SourceRoot $SourceRoot -TargetRoot $target -PluginItems $PluginItems
        }
        'ssh' {
            $hostName = [string] $Config.sshHost
            $remote = [string] $Config.remotePath
            $port = if ($Config.sshPort) { [int] $Config.sshPort } else { 22 }
            if (-not $hostName -or -not $remote) {
                throw 'sshHost and remotePath are required when targetType is ssh.'
            }
            return Sync-MrtPluginSsh -SourceRoot $SourceRoot -SshHost $hostName -SshPort $port `
                -RemoteRoot $remote -PluginItems $PluginItems
        }
        default {
            throw "Unknown targetType '$($Config.targetType)'. Use 'local' or 'ssh'."
        }
    }
}

function Invoke-MrtLiveDeploy {
    param(
        [string] $ProjectRoot,
        [string] $ConfigPath,
        [string[]] $PluginItems,
        [bool] $BuildVue,
        [bool] $SkipBuild,
        [bool] $UseDocker
    )

    $config = Get-MrtLiveDeployConfig -Path $ConfigPath

    Write-Host "`n=== MRT live deploy (sync plugin, no data reset) ===" -ForegroundColor Cyan
    Write-Host "  Source: $ProjectRoot" -ForegroundColor Gray

    if ($BuildVue) {
        Invoke-MrtVueBuild -UseDocker:$UseDocker
    } elseif (-not $SkipBuild) {
        $distAdmin = Join-Path $ProjectRoot 'assets/dist/vue/assets/admin.js'
        if (-not (Test-Path $distAdmin)) {
            Write-Host 'No Vue build found - building first.' -ForegroundColor Yellow
            Invoke-MrtVueBuild -UseDocker:$UseDocker
        }
    }

    Write-Host "`n--- Sync plugin files ---" -ForegroundColor Cyan
    $copied = Invoke-MrtPluginSync -Config $config -SourceRoot $ProjectRoot -PluginItems $PluginItems

    Write-Host "`nDeploy complete! ($copied items)" -ForegroundColor Green
    if ($config.siteUrl) {
        Write-Host "Site: $($config.siteUrl)" -ForegroundColor Gray
    }
}

function New-MrtLiveDeployWatchRoot {
    param(
        [string] $ProjectRoot,
        [string] $PluginSlug
    )

    return @(
        @{ Path = Join-Path $ProjectRoot 'frontend/vue/src'; Vue = $true },
        @{ Path = Join-Path $ProjectRoot 'frontend/vue/index.html'; Vue = $true },
        @{ Path = Join-Path $ProjectRoot 'frontend/vue/vite.config.ts'; Vue = $true },
        @{ Path = Join-Path $ProjectRoot 'inc'; Vue = $false },
        @{ Path = Join-Path $ProjectRoot 'assets'; Vue = $false },
        @{ Path = Join-Path $ProjectRoot 'languages'; Vue = $false },
        @{ Path = Join-Path $ProjectRoot "$PluginSlug.php"; Vue = $false },
        @{ Path = Join-Path $ProjectRoot 'uninstall.php'; Vue = $false }
    )
}

function Start-MrtLiveDeployWatchLoop {
    param(
        [scriptblock] $DeployAction
    )

    try {
        while ($true) {
            Start-Sleep -Milliseconds 400
            if (-not $script:MrtWatchDueAt) { continue }
            if ((Get-Date) -lt $script:MrtWatchDueAt) { continue }

            $buildVue = $script:MrtWatchNeedsVue
            $script:MrtWatchNeedsVue = $false
            $script:MrtWatchDueAt = $null

            try {
                & $DeployAction $buildVue
            } catch {
                Write-Host "`nWatch deploy failed: $($_.Exception.Message)" -ForegroundColor Red
            }
        }
    } finally {
        Get-EventSubscriber | Unregister-Event -ErrorAction SilentlyContinue
    }
}

function Start-MrtLiveDeployWatch {
    param(
        [string] $ProjectRoot,
        [string] $ConfigPath,
        [string] $PluginSlug,
        [string[]] $PluginItems,
        [bool] $SkipBuild,
        [bool] $UseDocker
    )

    $script:MrtWatchNeedsVue = $false
    $script:MrtWatchDueAt = $null
    $debounceSec = 2.5

    Write-Host "`n=== MRT live deploy - watch mode ===" -ForegroundColor Cyan
    Write-Host "Press Ctrl+C to stop.`n" -ForegroundColor Gray

    Invoke-MrtLiveDeploy -ProjectRoot $ProjectRoot -ConfigPath $ConfigPath `
        -PluginItems $PluginItems -BuildVue (-not $SkipBuild) -SkipBuild:$SkipBuild -UseDocker:$UseDocker

    $watchers = @()
    foreach ($root in (New-MrtLiveDeployWatchRoot -ProjectRoot $ProjectRoot -PluginSlug $PluginSlug)) {
        $path = $root.Path
        if (-not (Test-Path $path)) { continue }

        $item = Get-Item $path
        $w = New-Object System.IO.FileSystemWatcher
        $w.Path = if ($item.PSIsContainer) { $path } else { $item.DirectoryName }
        $w.Filter = if ($item.PSIsContainer) { '*.*' } else { $item.Name }
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

    $deployAction = {
        param([bool] $BuildVue)
        Invoke-MrtLiveDeploy -ProjectRoot $ProjectRoot -ConfigPath $ConfigPath `
            -PluginItems $PluginItems -BuildVue $BuildVue -SkipBuild:$true -UseDocker:$UseDocker
    }.GetNewClosure()

    try {
        Start-MrtLiveDeployWatchLoop -DeployAction $deployAction
    } finally {
        foreach ($w in $watchers) {
            $w.EnableRaisingEvents = $false
            $w.Dispose()
        }
    }
}
