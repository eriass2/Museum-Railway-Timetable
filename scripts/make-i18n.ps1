# Regenerate translation template and merge Swedish catalog.
# Requires Docker (wordpress-init + debian for gettext).
# Usage: powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\make-i18n.ps1

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

$Plugin = "/var/www/html/wp-content/plugins/museum-railway-timetable"
$Lang   = "$Plugin/languages"

Write-Host "Generating .pot..."
docker compose run --rm --entrypoint wp wordpress-init --allow-root i18n make-pot `
    $Plugin `
    "$Lang/museum-railway-timetable.pot" `
    --domain=museum-railway-timetable `
    --exclude=node_modules,vendor,tests,.git

Write-Host "Merging .po (preserve translations, drop obsolete)..."
$LangMount = Join-Path $Root "languages"
docker run --rm -v "${LangMount}:/work" -w /work debian:bookworm-slim bash -lc "apt-get update -qq && apt-get install -y -qq gettext php-cli >/dev/null && msgmerge --no-fuzzy -U museum-railway-timetable-sv_SE.po museum-railway-timetable.pot && msgattrib --no-obsolete --no-fuzzy -o museum-railway-timetable-sv_SE.po museum-railway-timetable-sv_SE.po"

Write-Host "Filling empty Swedish msgstr from msgid..."
docker run --rm -v "${Root}:/work" -w /work php:8.2-cli php scripts/fill-sv-po.php languages/museum-railway-timetable-sv_SE.po

Write-Host "Compiling .mo..."
docker run --rm -v "${LangMount}:/work" -w /work debian:bookworm-slim bash -lc "apt-get update -qq && apt-get install -y -qq gettext >/dev/null && msgfmt -o museum-railway-timetable-sv_SE.mo museum-railway-timetable-sv_SE.po && msgfmt --statistics -o /dev/null museum-railway-timetable-sv_SE.po"

Write-Host "Done."
