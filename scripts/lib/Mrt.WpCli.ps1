# WP-CLI sidecar helpers for MRT PowerShell scripts.

function Test-MrtWpCliContainerRunning {
    $running = Invoke-MrtDockerCompose -ComposeArgs @(
        'ps', '--status', 'running', '-q', 'wpcli'
    ) -ReturnOutput
    if ($null -eq $running) {
        return $false
    }
    foreach ($line in @($running)) {
        if ($line -match '\S') {
            return $true
        }
    }
    return $false
}

function Get-MrtWordPressInitRunArgs {
    param(
        [switch] $NoTty,
        [switch] $AsRoot,
        [string] $Entrypoint = 'wp'
    )

    $composeArgs = @('run', '--rm')
    if ($NoTty) {
        $composeArgs += '--no-TTY'
    }
    if (Test-MrtWordPressContainerRunning) {
        $composeArgs += '--no-deps'
    }
    if ($AsRoot) {
        $composeArgs += '--user', 'root'
    }
    if ($Entrypoint -ne 'wp') {
        $composeArgs += '--entrypoint', $Entrypoint
    }
    $composeArgs += 'wordpress-init'
    if ($Entrypoint -eq 'wp') {
        $composeArgs += '--allow-root'
    }
    return $composeArgs
}

function Get-MrtWpCliComposeArgs {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $CommandArgs,
        [switch] $NoTty,
        [switch] $AsRoot,
        [string] $Entrypoint = 'wp'
    )

    if (Test-MrtWpCliContainerRunning) {
        $composeArgs = @('exec')
        if ($NoTty) {
            $composeArgs += '-T'
        }
        $composeArgs += 'wpcli'
        if ($Entrypoint -eq 'wp') {
            $composeArgs += 'wp', '--allow-root'
        } else {
            $composeArgs += $Entrypoint
        }
        $composeArgs += $CommandArgs
        return $composeArgs
    }

    $composeArgs = Get-MrtWordPressInitRunArgs -NoTty:$NoTty -AsRoot:$AsRoot -Entrypoint $Entrypoint
    $composeArgs += $CommandArgs
    return $composeArgs
}

function Invoke-MrtWpCli {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $WpArgs,
        [switch] $AsRoot,
        [switch] $NoTty,
        [string] $Entrypoint = 'wp',
        [switch] $StreamOutput,
        [switch] $ReturnOutput,
        [switch] $ExitOnError
    )

    $composeArgs = Get-MrtWpCliComposeArgs -CommandArgs $WpArgs -NoTty:$NoTty `
        -AsRoot:$AsRoot -Entrypoint $Entrypoint

    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -StreamOutput:$StreamOutput `
        -ReturnOutput:$ReturnOutput -ExitOnError:$ExitOnError
}

function Wait-MrtWordPressReady {
    param(
        [int] $TimeoutSec = 120,
        [int] $IntervalSec = 2
    )

    Write-Host 'Waiting for WordPress...' -ForegroundColor Gray
    $loginUrl = "$script:MrtDevSiteUrl/wp-login.php"
    $deadline = (Get-Date).AddSeconds($TimeoutSec)

    if (-not (Wait-MrtUntilDeadline -Deadline $deadline -IntervalSec $IntervalSec -Test {
            Test-MrtHttpUrlReady -Url $loginUrl
        })) {
        Write-Host "WordPress did not respond at $loginUrl within ${TimeoutSec}s." -ForegroundColor Red
        exit 1
    }

    if (-not (Wait-MrtUntilDeadline -Deadline $deadline -IntervalSec $IntervalSec -Test {
            Invoke-MrtWpCli -WpArgs @('core', 'is-installed') -NoTty | Out-Null
            return ($LASTEXITCODE -eq 0)
        })) {
        Write-Host "WordPress did not become ready within ${TimeoutSec}s." -ForegroundColor Red
        exit 1
    }
}
