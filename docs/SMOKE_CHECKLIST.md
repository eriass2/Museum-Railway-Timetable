# Smoke-checklista

Snabb genomgång efter ändringar i frontend, shortcodes eller import.

## Automatiskt (före manuell rökning)

| Kommando | Täcker |
|----------|--------|
| `.\scripts\mrt.ps1 check` / `bash scripts/mrt.sh check` | validate.php, PHPStan, PHPUnit, PHPCS (Docker) |
| `.\scripts\mrt.ps1 check -Vue` / `bash scripts/mrt.sh check --vue` | PHP + Vue (Docker) |
| `.\scripts\mrt.ps1 vue-check` / `bash scripts/mrt.sh vue-check` | Vue typecheck, Vitest, build (Docker) |
| `.\scripts\mrt.ps1 e2e -- --grep "responsive"` | Responsivitet T1–T8 (statisk Playwright, Docker) |
| `.\scripts\mrt.ps1 dev smoke` | Docker: Vue build + import + demo + PHP check (rensar inte DB) |

Root-wrappers (`check.ps1`, `vue-check.ps1`, `docker-smoke.ps1`) fungerar som alternativ.

## Docker-smoke

```powershell
.\scripts\mrt.ps1 dev smoke
```

## Manuellt i webbläsaren

Se **[RESPONSIVE_MANUAL_TEST_PLAN.md](RESPONSIVE_MANUAL_TEST_PLAN.md)** för viewport-matris (390px / 1920px) och admin mobil T6–T8.

| Vad | URL | Förväntat |
|-----|-----|-----------|
| Admin dashboard | http://localhost:8080/wp-admin/admin.php?page=mrt_app | Vue-admin: statistik, varningar, navigation |
| Wizard | Sida med `[museum_journey_wizard]` eller demo | Grön hero, steg 1–4, kalenderfärger, ikoner |
| Månad | `[museum_timetable_month]` | Kalender, klickbar trafikdag |
| Översikt | `[museum_timetable_overview]` | Rutnät per rutt; grid scroll inuti container på mobil |
| Trafikinfo | `[museum_traffic_notices]` eller komponentdemo | Kategorier, feed utan sid-overflow mobil |
| Tidtabellsindex | `[museum_timetable_index]` | Kortlista, cap på bred skärm |

Login: `admin` / `admin`

**Meny (utveckling):** Tidtabell → **Utvecklingsverktyg** (`#/dev-tools`) — kräver `WP_DEBUG` eller `MRT_DEVELOPMENT`. Importera demo, sätt upp utvecklingsmeny och synka tidtabellssidor. Komponentdemo finns under **Komponentdemo** (PHP-admin). Legacy `?page=mrt_import_lennakatten` redirectar till Utvecklingsverktyg.

## Kommandon (typisk ordning)

```powershell
.\scripts\mrt.ps1 dev reset
.\scripts\mrt.ps1 check -SkipPhpcs
.\scripts\mrt.ps1 vue-check
.\scripts\mrt.ps1 e2e -- --grep "responsive"
```

## Kända begränsningar

- Lokal PHP 7.4 kör inte full `composer install`; använd Docker för PHPUnit/PHPStan.
- Mockup-PNG finns inte i repot; wizard-stil är återställd från pre-purge CSS.

## Vue – detaljerad checklista och E2E

Per-app-manuell rökning (månad, översikt, wizard, index): **[frontend/vue/TESTING.md](../frontend/vue/TESTING.md)**.

Snabb E2E utan WordPress (Docker, rekommenderat):

```powershell
.\scripts\mrt.ps1 vue-check
.\scripts\mrt.ps1 e2e
.\scripts\mrt.ps1 e2e -- --grep "responsive"   # T1–T8 responsivitet
```

Responsiv manuell rökning: **[RESPONSIVE_MANUAL_TEST_PLAN.md](RESPONSIVE_MANUAL_TEST_PLAN.md)**.

Med host-Node (valfritt):

```bash
cd frontend/vue
npm run e2e:install   # första gången
npm run e2e
```

Mot Docker-demo: `MRT_E2E_WP_DEMO_URL=http://127.0.0.1:8080/… npm run e2e -- e2e/*-wp.spec.ts` eller `bash scripts/ci-e2e-wp.sh` från reporoten.

**Windows utan host-`npm`:** Playwright Docker-image + `host.docker.internal:8080` — se [DEVELOPER.md](DEVELOPER.md) § Playwright E2E (tillfällig `siteurl`/`home`, image `mcr.microsoft.com/playwright:v1.61.0-jammy`).

## Nästa steg

- Manuell rökning: wizard hela flödet (steg 3–4, byte t.ex. Uppsala Östra → Fjällnora via Selknä).
- Frontend-polish mot mockup; designreferens i [mockups/DESIGN_TOKENS.md](mockups/DESIGN_TOKENS.md).
- [ACCESSIBILITY.md](ACCESSIBILITY.md) – manuell WCAG-checklista och release-logg.
