# Shared gate bootstrap for PowerShell quality scripts.
# Usage: . (Join-Path $PSScriptRoot '_runner.ps1'); Initialize-MrtGateEnvironment -Timings:$Timings

$script:MrtGateScriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
. (Join-Path $script:MrtGateScriptsRoot 'lib/Mrt.Docker.ps1')

function Initialize-MrtGateEnvironment {
    param(
        [switch] $Timings,
        [switch] $RequireDocker,
        [switch] $EnsureVendor,
        [switch] $PreferHostVendor
    )

    Set-MrtRepoRoot -ScriptsDirectory $script:MrtGateScriptsRoot
    Initialize-MrtScriptTimings -Timings:$Timings

    if ($RequireDocker) {
        Assert-MrtDockerAvailable
    }
    if ($EnsureVendor) {
        Ensure-MrtVendor -PreferHost:$PreferHostVendor
    }
}

function Complete-MrtGateEnvironment {
    Complete-MrtScriptTimings
}
