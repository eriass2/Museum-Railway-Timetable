# Utvecklingsläge vs live

## När räknas det som utveckling?

`MRT_is_development_mode()` är **på** om något av följande gäller:

- `WP_DEBUG` är `true` i `wp-config.php` (Docker har det via `WORDPRESS_DEBUG`)
- `define( 'MRT_DEVELOPMENT', true );` i `wp-config.php` (t.ex. staging utan debug-loggar)

Filter: `mrt_is_development_mode` kan tvinga läge vid behov.

## Vue-experiment (branch `experiment/vue-public-ui`)

Docker dev sätter `MRT_VUE_FRONTEND` och bygger Vue automatiskt via `docker-dev-reset.ps1` (service `vue`).

Publik CSS laddas från Vite-bundeln (`frontend/vue/src/styles/mrt-public.css` → `assets/dist/vue/`), inte från `journey-wizard.css` / `frontend-public.css` i WP.

Se [VUE_EXPERIMENT.md](VUE_EXPERIMENT.md). Bygg manuellt: `composer vue:build` eller `docker compose --profile tools run --rm vue`.

Lokal kvalitetskontroll utan WordPress: `composer vue:check` (typecheck, Vitest, build, bundle smoke test). Samma kommando körs i GitHub Actions CI. Manuell regression: [frontend/vue/TESTING.md](../frontend/vue/TESTING.md).

Utan `MRT_VUE_FRONTEND` laddas fortfarande jQuery-modulerna under `assets/journey-wizard/*.js` (legacy). Vue-bundeln används inte då.

## Endast utveckling (döljs i typisk produktion)

| Verktyg | Var |
|---------|-----|
| Rensa plugin-data | Dashboard → Development tools |
| Import Lennakatten (test-PDF-data) | Dashboard + undermeny Import Lennakatten |
| Skapa demosida / component demo | Dashboard + Component demo page |
| **Set up development menu** | Dashboard + Component demo page |
| Admin-undermeny Component demo page | Railway Timetable |

**Set up development menu** skapar/uppdaterar sidor och lägger länkar i sajtens **klassiska** nav-meny (primary om ingen finns, annars befintlig primary – inga dubbletter):

| Sida | Innehåll |
|------|----------|
| **Component demo** | Månad + översikt + wizard (tre block) |
| **Wizard smoke test** | Full wizard utan fixture |
| **Debug: Month** | Endast månadskalender (import-datum) |
| **Debug: Overview** | Endast tidtabellsöversikt (GRÖN) |
| **Debug: Wizard date / outbound / return / summary** | Wizard med `debug="…"` och hårdkodad fixture-data |

Wizard-debug kräver `WP_DEBUG` eller `MRT_DEVELOPMENT`. Presets: `inc/public/journey-wizard/debug-fixtures.php`, appliceras i Vue via `useWizardDebug.ts` och `createWizardStore` (`debug="date|outbound|return|summary"`).

Gamla planner smoke-sidor (om de fanns) tas bort vid setup.

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

Gör samma sak som dashboard **Clear plugin database** → **Import demo data** → **Set up development menu**, och skriver JSON med `pages.component_demo` och `pages.wizard`.

Linux/macOS: `./scripts/docker-dev-reset.sh`

Kräver `WP_DEBUG` eller `MRT_DEVELOPMENT` (Docker har `WORDPRESS_DEBUG=1`).

## Röktest efter setup

1. Kör `docker-dev-reset.ps1` **eller** manuellt: meny-knapp + import  
2. Öppna front-sidan – menyn ska visa två smoke-länkar  
3. **Component demo** – scrolla igenom alla tre block  

Se [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md) och [ACCESSIBILITY_SMOKE.md](ACCESSIBILITY_SMOKE.md).

## JS-debug

I utvecklingsläge sätts `window.mrtDebug` i admin (för `console.log` i admin-skript enligt [REBUILD_RULES.md](REBUILD_RULES.md)).
