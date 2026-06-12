# Regenerate translation template and merge Swedish catalog.
# Requires Docker (wordpress-init + debian for gettext).
# Usage: powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\make-i18n.ps1

$ErrorActionPreference = 'Stop'
$scriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
. (Join-Path $scriptsRoot 'lib/Mrt.Docker.ps1')
$Root = Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot

Assert-MrtDockerAvailable

$Plugin = $script:MrtWpPluginContainerPath
$Lang = "$Plugin/languages"
$LangMount = Join-Path $Root 'languages'

Write-Host 'Generating .pot...'
Invoke-MrtWpCli -WpArgs @(
    'i18n', 'make-pot',
    $Plugin,
    "$Lang/museum-railway-timetable.pot",
    '--domain=museum-railway-timetable',
    '--exclude=node_modules,vendor,tests,.git'
) -ExitOnError

Write-Host 'Merging .po (preserve translations, drop obsolete)...'
docker run --rm -v "${LangMount}:/work" -w /work debian:bookworm-slim bash -lc `
    "apt-get update -qq && apt-get install -y -qq gettext php-cli >/dev/null && msgmerge --no-fuzzy -U museum-railway-timetable-sv_SE.po museum-railway-timetable.pot && msgattrib --no-obsolete --no-fuzzy -o museum-railway-timetable-sv_SE.po museum-railway-timetable-sv_SE.po"
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host 'Filling empty Swedish msgstr from msgid...'
docker run --rm -v "${Root}:/work" -w /work php:8.2-cli php scripts/fill-sv-po.php languages/museum-railway-timetable-sv_SE.po
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host 'Compiling .mo...'
docker run --rm -v "${LangMount}:/work" -w /work debian:bookworm-slim bash -lc `
    "apt-get update -qq && apt-get install -y -qq gettext >/dev/null && msgfmt -o museum-railway-timetable-sv_SE.mo museum-railway-timetable-sv_SE.po && msgfmt --statistics -o /dev/null museum-railway-timetable-sv_SE.po"
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host 'Done.'
