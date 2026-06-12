# Development reset and smoke-page helpers for MRT PowerShell scripts.

function Get-MrtDemoPageUrl {
    Write-MrtStep -Title 'Demo page'
    $eval = @(
        '$r = MRT_ensure_components_demo_page_cli();',
        'if (is_wp_error($r)) { echo $r->get_error_message(); }',
        'else { wp_update_post(array(''ID'' => (int) $r, ''post_status'' => ''publish'')); echo get_permalink((int) $r); }'
    ) -join ' '

    $demoOut = Invoke-MrtWpCli -WpArgs @('eval', $eval) -ReturnOutput
    $demoOut | ForEach-Object { Write-Host $_ }
    if ($LASTEXITCODE -ne 0) {
        return $null
    }

    $match = ($demoOut | Out-String) | Select-String -Pattern 'https?://\S+' | Select-Object -Last 1
    if (-not $match) {
        return $null
    }
    return $match.Matches.Value
}

function Get-MrtSmokePageUrlEntries {
    $eval = @(
        "if (!function_exists('MRT_dev_smoke_page_permalinks')) {",
        "fwrite(STDERR, 'dev-cli not loaded'.PHP_EOL); exit(1);",
        '}',
        "if (function_exists('MRT_dev_cli_set_admin_user')) { MRT_dev_cli_set_admin_user(); }",
        "if (function_exists('MRT_ensure_dev_smoke_pages')) { MRT_ensure_dev_smoke_pages(); }",
        'echo wp_json_encode(MRT_dev_smoke_page_permalinks());'
    ) -join ' '

    $raw = Invoke-MrtWpCli -WpArgs @('eval', $eval) -ReturnOutput -NoTty
    if ($LASTEXITCODE -ne 0) {
        return @()
    }

    $jsonLine = ($raw | Where-Object { $_ -match '^\{' } | Select-Object -Last 1)
    if (-not $jsonLine) {
        return @()
    }

    $labels = @{
        wizard         = 'Wizard smoke test'
        component_demo = 'Component demo'
        planner        = 'Planner smoke test'
    }

    $map = $jsonLine | ConvertFrom-Json
    $pages = @()
    foreach ($prop in $map.PSObject.Properties) {
        $label = $labels[$prop.Name]
        if (-not $label) {
            $label = $prop.Name
        }
        $pages += @{ Name = $label; Url = [string] $prop.Value }
    }
    return $pages
}

function Invoke-MrtEnsureSvLocale {
    Write-MrtStep -Title 'Swedish locale (sv_SE)'
    Invoke-MrtWpCli -Entrypoint 'sh' -WpArgs @('/usr/local/bin/mrt-ensure-sv-locale.sh') -StreamOutput
}

function Set-MrtWpDebug {
    param([bool] $Enabled = $true)

    Write-MrtStep -Title 'Enable WP_DEBUG (development)'
    $value = if ($Enabled) { 'true' } else { 'false' }
    Invoke-MrtWpCli -WpArgs @('config', 'set', 'WP_DEBUG', $value, '--raw') -AsRoot -StreamOutput
    Invoke-MrtWpCli -WpArgs @('config', 'set', 'WP_DEBUG_LOG', $value, '--raw') -AsRoot -StreamOutput
}

function Invoke-MrtDevResetImport {
    Write-MrtStep -Title 'Reset and import'
    $eval = @(
        "if (!function_exists('MRT_dev_reset_and_import_cli')) {",
        "fwrite(STDERR, 'Plugin not active or dev-cli not loaded'.PHP_EOL); exit(1);",
        '} MRT_dev_reset_and_import_cli();'
    ) -join ' '
    Invoke-MrtWpCli -WpArgs @('eval', $eval) -NoTty -StreamOutput -ExitOnError
}
