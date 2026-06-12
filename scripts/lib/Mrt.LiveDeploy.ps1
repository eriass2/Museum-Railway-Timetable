# Live deploy helpers: config, sync, and watch mode.

$libRoot = Split-Path -Parent $PSCommandPath
. (Join-Path $libRoot 'Mrt.LiveDeploy.Sync.ps1')
. (Join-Path $libRoot 'Mrt.LiveDeploy.Watch.ps1')
