# Utvecklingsläge vs live

## När räknas det som utveckling?

`MRT_is_development_mode()` är **på** om något av följande gäller:

- `WP_DEBUG` är `true` i `wp-config.php` (Docker har det via `WORDPRESS_DEBUG`)
- `define( 'MRT_DEVELOPMENT', true );` i `wp-config.php` (t.ex. staging utan debug-loggar)

Filter: `mrt_is_development_mode` kan tvinga läge vid behov.

## Publikt Vue-frontend

Docker dev bygger Vue automatiskt via `docker-dev-reset.ps1` (service `vue`).

Publik CSS laddas från Vite-bundeln (`frontend/vue/src/styles/mrt-public.css` → `assets/dist/vue/`), inte från separata WP-handles för `frontend-public.css`.

Se [VUE_FRONTEND.md](VUE_FRONTEND.md). Bygg manuellt: `composer vue:build` eller `docker compose --profile tools run --rm vue`.

Lokal kvalitetskontroll utan WordPress: `composer vue:check` (typecheck, Vitest, build, bundle smoke test). PHPUnit: `composer test` (kräver **PHP 8.2+**; på äldre system: `docker compose --profile tools run --rm composer test`). Samma kedja körs i GitHub Actions CI. Manuell regression: [frontend/vue/TESTING.md](../frontend/vue/TESTING.md).

Publikt UI är **Vue-only** (jQuery-wizard-moduler är borttagna). Månad, översikt och wizard mountar Vue via `assets/dist/vue/`.

## Endast utveckling (döljs i typisk produktion)

| Verktyg | Var |
|---------|-----|
| Rensa plugin-data | Vue **Dev tools** (`#/dev-tools`) |
| Import Lennakatten (test-PDF-data) | Vue **Utvecklingsverktyg** (`#/dev-tools`) |
| Skapa demosida | Dev tools (+ Component demo page för manuell POST) |
| **Set up development menu** | Dev tools |
| Synka tidtabellssidor | Dev tools |
| Admin-undermeny Component demo page | Railway Timetable |

**Set up development menu** skapar/uppdaterar sidor och lägger **två** länkar i sajtens **klassiska** nav-meny (primary om ingen finns, annars befintlig primary – inga dubbletter):

| Sida | I front-meny? | Innehåll |
|------|---------------|----------|
| **Component demo** | Ja | Månad + översikt + wizard (tre block) |
| **Wizard smoke test** | Ja | Full wizard utan fixture |
| **Debug: Month / Overview / Wizard …** | Nej (admin-länkar) | En shortcode per sida för snabb layout-debug |

Per-komponent debug-sidor skapas fortfarande vid setup men listas under **Railway Timetable → Component demo page** (inte i front-menyn). Kör **Set up development menu** igen för att ta bort gamla debug-länkar ur menyn.

Wizard-debug kräver `WP_DEBUG` eller `MRT_DEVELOPMENT`. Presets: `inc/public/journey-wizard/debug-fixtures.php`, appliceras i Vue via `useWizardDebug.ts` och `createWizardStore` (`debug="date|outbound|return|summary"`).

Block-tema med enbart Site Editor-navigation kan kräva manuell länk tills vidare.

## Publika tidtabellssidor

Vid import/dev-reset synkas WordPress-sidor automatiskt:

| Sida | Slug | Shortcode |
|------|------|-----------|
| **Trafikkalender** (index) | `tidtabeller` | `[museum_timetable_month legend="1"]` + tidtabellslista |
| En per publicerad tidtabell | `tidtabell-{kod}` | `[museum_timetable_overview timetable_id="…"]` |

**Trafikkalender** sätts som sajtens **statiska startsida** (`page_on_front`). Klicka på en trafikdag i kalendern för att se avgångar den dagen. Länk med `?mrt_month=YYYY-MM` och valfritt `?mrt_date=YYYY-MM-DD`. I utvecklingsläge tas WordPress standardinnehåll bort (Hello world, Sample Page).

Menyn får en länk till index om dev-meny är uppsatt. Synka manuellt: `MRT_sync_timetable_public_pages()` (kräver admin; använd `MRT_dev_cli_set_admin_user()` i WP-CLI).

## Live / produktion (alltid tillgängligt)

| Funktion | Kommentar |
|----------|-----------|
| Dashboard, statistik, inställningar (inkl. min/max bytestid) | Vue-admin; `WP_DEBUG` av |
| Stationer, rutter, tidtabeller, turer | Vue-admin (CPT-skärmar redirectar) |
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

Gör samma sak som Dev tools **Rensa plugin-databas** → **Importera Lennakatten-demo** → **Sätt upp utvecklingsmeny**, och skriver JSON med `pages.component_demo` och `pages.wizard`.

Linux/macOS: `./scripts/docker-dev-reset.sh`

Kräver `WP_DEBUG` eller `MRT_DEVELOPMENT` (Docker har `WORDPRESS_DEBUG=1`).

WordPress i Docker är på **svenska** (`sv_SE`). Vid `docker compose up` installeras språkpaketet automatiskt; `docker-dev-reset` säkerställer locale även på befintliga volymer.

## Röktest efter setup

1. Kör `docker-dev-reset.ps1` **eller** manuellt: meny-knapp + import  
2. Öppna **http://localhost:8080/** – startsidan ska vara **Tidtabeller** (inte bloggen Hello world)  
3. Front-menyn ska visa **Component demo** och **Wizard smoke test** (inga Debug:-länkar)  
4. **Component demo** – scrolla igenom alla tre block  

Se [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md) och [ACCESSIBILITY_SMOKE.md](ACCESSIBILITY_SMOKE.md).

## JS-debug

I utvecklingsläge sätts `window.mrtDebug` i admin (för `console.log` i admin-skript enligt [REBUILD_RULES.md](REBUILD_RULES.md)).
