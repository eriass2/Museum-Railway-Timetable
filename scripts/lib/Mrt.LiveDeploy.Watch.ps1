# Live deploy: file watch and debounced re-sync.

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

function Register-MrtLiveDeployFileWatcher {
    param(
        [string] $Path,
        [bool] $NeedsVue,
        [double] $DebounceSec
    )

    if (-not (Test-Path $Path)) {
        return $null
    }

    $item = Get-Item $Path
    $w = New-Object System.IO.FileSystemWatcher
    $w.Path = if ($item.PSIsContainer) { $Path } else { $item.DirectoryName }
    $w.Filter = if ($item.PSIsContainer) { '*.*' } else { $item.Name }
    $w.IncludeSubdirectories = $item.PSIsContainer
    $w.EnableRaisingEvents = $true

    Register-ObjectEvent -InputObject $w -EventName Changed -Action {
        if ($Event.SourceEventArgs.Name -match '\.(tmp|swp|~)$') { return }
        if ($using:NeedsVue) { $script:MrtWatchNeedsVue = $true }
        $script:MrtWatchDueAt = (Get-Date).AddSeconds($using:DebounceSec)
    } | Out-Null
    Register-ObjectEvent -InputObject $w -EventName Created -Action {
        if ($using:NeedsVue) { $script:MrtWatchNeedsVue = $true }
        $script:MrtWatchDueAt = (Get-Date).AddSeconds($using:DebounceSec)
    } | Out-Null
    Register-ObjectEvent -InputObject $w -EventName Renamed -Action {
        if ($using:NeedsVue) { $script:MrtWatchNeedsVue = $true }
        $script:MrtWatchDueAt = (Get-Date).AddSeconds($using:DebounceSec)
    } | Out-Null
    Register-ObjectEvent -InputObject $w -EventName Deleted -Action {
        if ($using:NeedsVue) { $script:MrtWatchNeedsVue = $true }
        $script:MrtWatchDueAt = (Get-Date).AddSeconds($using:DebounceSec)
    } | Out-Null

    Write-Host "Watching: $Path" -ForegroundColor Gray
    return $w
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

function Stop-MrtLiveDeployWatchers {
    param(
        [System.IO.FileSystemWatcher[]] $Watchers
    )

    foreach ($w in $Watchers) {
        $w.EnableRaisingEvents = $false
        $w.Dispose()
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
        $w = Register-MrtLiveDeployFileWatcher -Path $root.Path -NeedsVue ([bool] $root.Vue) `
            -DebounceSec $debounceSec
        if ($null -ne $w) {
            $watchers += $w
        }
    }

    $deployAction = {
        param([bool] $BuildVue)
        Invoke-MrtLiveDeploy -ProjectRoot $ProjectRoot -ConfigPath $ConfigPath `
            -PluginItems $PluginItems -BuildVue $BuildVue -SkipBuild:$true -UseDocker:$UseDocker
    }.GetNewClosure()

    try {
        Start-MrtLiveDeployWatchLoop -DeployAction $deployAction
    } finally {
        Stop-MrtLiveDeployWatchers -Watchers $watchers
    }
}
