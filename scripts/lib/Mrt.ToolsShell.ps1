# Docker tools-shell helpers (composer, php-test, vue).

function Get-MrtToolsServiceRunArgs {
    param([Parameter(Mandatory = $true)] [string] $Service)

    return @('--profile', 'tools', 'run', '--rm', '--no-deps', $Service)
}

function Test-MrtToolsServiceRunning {
    param([Parameter(Mandatory = $true)] [string] $Service)

    $running = Invoke-MrtDockerCompose -ComposeArgs @(
        '--profile', 'tools', 'ps', '--status', 'running', '-q', $Service
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

function Ensure-MrtToolsShell {
    if (-not (Test-MrtDockerAvailable)) {
        return
    }

    $services = @('composer', 'php-test', 'vue')
    $missing = @($services | Where-Object { -not (Test-MrtToolsServiceRunning $_) })
    if ($missing.Count -eq 0) {
        return
    }

    Write-Host "Starting tools shell: $($missing -join ', ')..." -ForegroundColor DarkGray
    $composeArgs = @('--profile', 'tools', 'up', '-d') + $missing
    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -ExitOnError
}

function Get-MrtToolsExecArgs {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Service,
        [Parameter(Mandatory = $true)]
        [string[]] $RunArgs
    )

    switch ($Service) {
        'composer' { return @('composer') + $RunArgs }
        'php-test' { return @('php') + $RunArgs }
        default { return $RunArgs }
    }
}

function Get-MrtToolsServiceRunFallbackArgs {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Service,
        [Parameter(Mandatory = $true)]
        [string[]] $RunArgs
    )

    $composeArgs = Get-MrtToolsServiceRunArgs -Service $Service
    switch ($Service) {
        'composer' { $composeArgs += @('--entrypoint', 'composer') }
        'php-test' { $composeArgs += @('--entrypoint', 'php') }
    }
    if ($RunArgs.Count -gt 0) {
        $composeArgs += $RunArgs
    }
    return $composeArgs
}

function Invoke-MrtDockerToolsService {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Service,
        [Parameter(Mandatory = $true)]
        [string[]] $RunArgs,
        [switch] $ExitOnError,
        [switch] $StreamOutput,
        [switch] $ReturnOutput
    )

    Ensure-MrtToolsShell
    if (Test-MrtToolsServiceRunning -Service $Service) {
        $composeArgs = @('--profile', 'tools', 'exec', '--no-TTY', $Service)
        $composeArgs += Get-MrtToolsExecArgs -Service $Service -RunArgs $RunArgs
    } else {
        $composeArgs = Get-MrtToolsServiceRunFallbackArgs -Service $Service -RunArgs $RunArgs
    }
    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -ExitOnError:$ExitOnError `
        -StreamOutput:$StreamOutput -ReturnOutput:$ReturnOutput
}

function Invoke-MrtDockerComposer {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $ComposerArgs,
        [switch] $ExitOnError,
        [switch] $StreamOutput,
        [switch] $ReturnOutput
    )

    Invoke-MrtDockerToolsService -Service 'composer' -RunArgs $ComposerArgs `
        -ExitOnError:$ExitOnError -StreamOutput:$StreamOutput -ReturnOutput:$ReturnOutput
}

function Invoke-MrtDockerPhpTest {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $PhpArgs,
        [switch] $ExitOnError,
        [switch] $StreamOutput
    )

    Invoke-MrtDockerToolsService -Service 'php-test' -RunArgs $PhpArgs `
        -ExitOnError:$ExitOnError -StreamOutput:$StreamOutput
}

function Invoke-MrtDockerPhpUnit {
    param(
        [string[]] $PhpUnitArgs,
        [switch] $ExitOnError
    )

    Write-Host 'Running PHPUnit in Docker (php-test)...' -ForegroundColor Cyan
    $runArgs = @('vendor/bin/phpunit')
    $extra = @($PhpUnitArgs | Where-Object { -not [string]::IsNullOrWhiteSpace($_) })
    if ($extra.Count -gt 0) {
        $runArgs += $extra
    }
    Invoke-MrtDockerToolsService -Service 'php-test' -RunArgs $runArgs -ExitOnError:$ExitOnError
}

function Invoke-MrtDockerPhpUnitWithPcov {
    param(
        [string[]] $PhpUnitArgs,
        [switch] $ExitOnError
    )

    Write-Host 'Running PHPUnit with PCOV in Docker (php-test)...' -ForegroundColor Cyan
    $runArgs = @('vendor/bin/phpunit')
    if ($PhpUnitArgs.Count -gt 0) {
        $runArgs += $PhpUnitArgs
    }
    Invoke-MrtDockerToolsService -Service 'php-test' -RunArgs $runArgs `
        -ExitOnError:$ExitOnError -StreamOutput
}
