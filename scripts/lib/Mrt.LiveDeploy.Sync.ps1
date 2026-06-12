# Live deploy: config and plugin sync.

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
