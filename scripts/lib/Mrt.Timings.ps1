# Script step timing helpers for MRT PowerShell scripts.

$script:MrtScriptTimings = $env:MRT_SCRIPT_TIMINGS -match '^(1|true|yes)$'
$script:MrtTimingStepTitle = $null
$script:MrtTimingStepStarted = $null

function Initialize-MrtScriptTimings {
    param([switch] $Timings)

    if ($Timings -or ($env:MRT_SCRIPT_TIMINGS -match '^(1|true|yes)$')) {
        $script:MrtScriptTimings = $true
    }
}

function Test-MrtScriptTimingsEnabled {
    return [bool] $script:MrtScriptTimings
}

function Write-MrtTiming {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Step,
        [Parameter(Mandatory = $true)]
        [TimeSpan] $Elapsed
    )

    $ms = [math]::Round($Elapsed.TotalMilliseconds)
    $label = if ($ms -lt 1000) { "${ms}ms" } else { '{0:N1}s' -f $Elapsed.TotalSeconds }
    Write-Host "  [timing] $Step - $label" -ForegroundColor DarkGray
}

function Complete-MrtScriptTimings {
    if (-not (Test-MrtScriptTimingsEnabled)) {
        return
    }
    if (-not $script:MrtTimingStepStarted) {
        return
    }

    $elapsed = (Get-Date) - $script:MrtTimingStepStarted
    Write-MrtTiming -Step $script:MrtTimingStepTitle -Elapsed $elapsed
    $script:MrtTimingStepTitle = $null
    $script:MrtTimingStepStarted = $null
}

function Invoke-MrtTimedStep {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Title,
        [Parameter(Mandatory = $true)]
        [scriptblock] $Action,
        [switch] $SkipStepHeader
    )

    if (-not $SkipStepHeader) {
        Write-Host "`n--- $Title ---" -ForegroundColor Cyan
    }

    if (-not (Test-MrtScriptTimingsEnabled)) {
        & $Action
        return
    }

    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    try {
        & $Action
    } finally {
        $sw.Stop()
        Write-MrtTiming -Step $Title -Elapsed $sw.Elapsed
    }
}

function Write-MrtStep {
    param([Parameter(Mandatory = $true)] [string] $Title)

    Complete-MrtScriptTimings
    Write-Host "`n--- $Title ---" -ForegroundColor Cyan
    if (Test-MrtScriptTimingsEnabled) {
        $script:MrtTimingStepTitle = $Title
        $script:MrtTimingStepStarted = Get-Date
    }
}
