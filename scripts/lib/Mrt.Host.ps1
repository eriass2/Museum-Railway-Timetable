# Host toolchain checks (Docker, npm, PHP) for MRT PowerShell scripts.

function Test-MrtDockerAvailable {
    & docker info 2>$null | Out-Null
    return $LASTEXITCODE -eq 0
}

function Assert-MrtDockerAvailable {
    param([string] $Message = 'Docker is not running. Start Docker Desktop and retry.')

    if (-not (Test-MrtDockerAvailable)) {
        Write-Host $Message -ForegroundColor Red
        exit 1
    }
}

function Test-MrtNpmAvailable {
    try {
        & npm --version 2>$null | Out-Null
        return $LASTEXITCODE -eq 0
    } catch {
        return $false
    }
}

function Assert-MrtNpmAvailable {
    param([string] $Message = 'npm not in PATH. Omit -Local to use Docker.')

    if (-not (Test-MrtNpmAvailable)) {
        Write-Host $Message -ForegroundColor Red
        exit 1
    }
}

function Get-MrtLocalPhpVersion {
    $version = $null
    & php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>$null | ForEach-Object { $version = $_ }
    if ($LASTEXITCODE -ne 0 -or -not $version) {
        return $null
    }
    return $version
}

function Assert-MrtLocalPhpMin {
    param(
        [Parameter(Mandatory = $true)]
        [string] $MinVersion
    )

    $version = Get-MrtLocalPhpVersion
    if (-not $version) {
        Write-Host 'Local PHP not in PATH. Omit -Local to use Docker.' -ForegroundColor Red
        exit 1
    }
    if ([version]$version -lt [version]$MinVersion) {
        Write-Host "Local PHP $version is below $MinVersion. Omit -Local to use Docker." -ForegroundColor Red
        exit 1
    }
    return $version
}

function Invoke-MrtWithDockerDefault {
    param(
        [switch] $Local,
        [Parameter(Mandatory = $true)]
        [scriptblock] $DockerAction,
        [Parameter(Mandatory = $true)]
        [scriptblock] $LocalAction,
        [string] $DockerHint,
        [string] $DockerUnavailableMessage = 'Docker is not running.',
        [switch] $DockerUnavailableWarning
    )

    if (-not $Local) {
        if (-not (Test-MrtDockerAvailable)) {
            $color = if ($DockerUnavailableWarning) { 'Yellow' } else { 'Red' }
            Write-Host $DockerUnavailableMessage -ForegroundColor $color
            exit 1
        }
        if ($DockerHint) {
            Write-Host $DockerHint -ForegroundColor Cyan
        }
        & $DockerAction | Out-Null
        exit $LASTEXITCODE
    }

    & $LocalAction | Out-Null
    exit $LASTEXITCODE
}

function Test-MrtHttpUrlReady {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Url,
        [int] $TimeoutSec = 5
    )

    try {
        $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec $TimeoutSec -ErrorAction Stop
        return ($response.StatusCode -ge 200 -and $response.StatusCode -lt 500)
    } catch {
        return $false
    }
}

function Wait-MrtUntilDeadline {
    param(
        [Parameter(Mandatory = $true)]
        [datetime] $Deadline,
        [Parameter(Mandatory = $true)]
        [scriptblock] $Test,
        [int] $IntervalSec = 2
    )

    while ((Get-Date) -lt $Deadline) {
        if (& $Test) {
            return $true
        }
        Start-Sleep -Seconds $IntervalSec
    }
    return $false
}
