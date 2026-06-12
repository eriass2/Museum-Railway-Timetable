# Shared Docker / npm helpers for MRT PowerShell scripts.
# Usage: . (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1'); Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

$libRoot = Split-Path -Parent $PSCommandPath
. (Join-Path $libRoot 'Mrt.Plugin.ps1')
. (Join-Path $libRoot 'Mrt.Timings.ps1')
. (Join-Path $libRoot 'Mrt.Host.ps1')
. (Join-Path $libRoot 'Mrt.Compose.ps1')
. (Join-Path $libRoot 'Mrt.ToolsShell.ps1')
. (Join-Path $libRoot 'Mrt.WpCli.ps1')
. (Join-Path $libRoot 'Mrt.Vendor.ps1')
. (Join-Path $libRoot 'Mrt.Vue.ps1')
. (Join-Path $libRoot 'Mrt.Dev.ps1')
