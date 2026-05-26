# Utvecklingsläge vs live

## När räknas det som utveckling?

`MRT_is_development_mode()` är **på** om något av följande gäller:

- `WP_DEBUG` är `true` i `wp-config.php` (Docker har det via `WORDPRESS_DEBUG`)
- `define( 'MRT_DEVELOPMENT', true );` i `wp-config.php` (t.ex. staging utan debug-loggar)

Filter: `mrt_is_development_mode` kan tvinga läge vid behov.

## Endast utveckling (döljs i typisk produktion)

| Verktyg | Var |
|---------|-----|
| Rensa plugin-data | Dashboard → Development tools |
| Import Lennakatten (test-PDF-data) | Dashboard + undermeny Import Lennakatten |
| Skapa demosida / component demo | Dashboard + Component demo page |
| **Set up development menu** | Dashboard + Component demo page |
| Admin-undermeny Component demo page | Railway Timetable |

**Set up development menu** skapar/uppdaterar tre sidor och lägger länkar i sajtens **klassiska** nav-meny (primary om ingen finns, annars befintlig primary – inga dubbletter):

1. **Component demo** – alla shortcodes  
2. **Wizard smoke test** – `[museum_journey_wizard]`  
3. **Planner smoke test** – `[museum_journey_planner]`

Block-tema med enbart Site Editor-navigation kan kräva manuell länk tills vidare.

## Live / produktion (alltid tillgängligt)

| Funktion | Kommentar |
|----------|-----------|
| Dashboard, statistik, inställningar (inkl. min/max bytestid) | `WP_DEBUG` av |
| Stationer, rutter, tidtabeller, turer, meta boxes | Manuell redigering |
| Prismatris (inställningar) | Publik resa |
| Alla shortcodes på valfria sidor | Redaktör lägger in shortcode |
| CPT-listor under Railway Timetable | Daglig drift |

## Ett kommando: rensa + importera + meny (Docker)

Från repo-roten (PowerShell):

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-dev-reset.ps1
```

Snabbare om containrarna redan kör:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-dev-reset.ps1 -SkipCompose
```

Gör samma sak som dashboard **Clear plugin database** → **Import demo data** → **Set up development menu**, och skriver JSON med sid-URL:er.

Linux/macOS: `./scripts/docker-dev-reset.sh`

Kräver `WP_DEBUG` eller `MRT_DEVELOPMENT` (Docker har `WORDPRESS_DEBUG=1`).

## Röktest efter setup

1. Kör `docker-dev-reset.ps1` **eller** manuellt: meny-knapp + import  
2. Öppna front-sidan – menyn ska visa tre smoke-länkar  
3. **Component demo** – scrolla igenom alla fyra block  

Se [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md) och [ACCESSIBILITY_SMOKE.md](ACCESSIBILITY_SMOKE.md).

## JS-debug

I utvecklingsläge sätts `window.mrtDebug` i admin (för `console.log` i admin-skript enligt [REBUILD_RULES.md](REBUILD_RULES.md)).
