# Docker Compose core helpers for MRT PowerShell scripts.

function Test-MrtWordPressContainerRunning {
    $running = Invoke-MrtDockerCompose -ComposeArgs @(
        'ps', '--status', 'running', '-q', 'wordpress'
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

function Invoke-MrtDockerCompose {
    param(
        [Parameter(Mandatory = $true)]
        [string[]] $ComposeArgs,
        [switch] $ExitOnError,
        [switch] $StreamOutput,
        [switch] $ReturnOutput
    )

    $dockerArgs = @('compose') + $ComposeArgs
    $output = $null

    if ($ReturnOutput -or $StreamOutput) {
        $prevEap = $ErrorActionPreference
        $ErrorActionPreference = 'Continue'
        try {
            $output = & docker @dockerArgs 2>&1
        } finally {
            $ErrorActionPreference = $prevEap
        }
        if ($StreamOutput) {
            $output | ForEach-Object { Write-Host $_ }
        }
    } else {
        # PHPUnit and other tools log to stderr; do not treat as terminating errors on Windows.
        $prevEap = $ErrorActionPreference
        $ErrorActionPreference = 'Continue'
        try {
            & docker @dockerArgs 2>&1 | ForEach-Object { Write-Host $_ }
        } finally {
            $ErrorActionPreference = $prevEap
        }
    }

    if ($ExitOnError -and $LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
    if ($ReturnOutput) {
        return $output
    }
    return $LASTEXITCODE
}

function Start-MrtDockerStack {
    param(
        [switch] $ExitOnError,
        [switch] $Build
    )

    $composeArgs = @('up', '-d')
    if ($Build) {
        $composeArgs += '--build'
    }
    Invoke-MrtDockerCompose -ComposeArgs $composeArgs -ExitOnError:$ExitOnError
}
