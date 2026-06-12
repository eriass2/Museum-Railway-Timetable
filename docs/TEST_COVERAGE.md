# PHP kodtäckning (utforskande)

**Datum:** 2026-06-09  
**Scope:** `inc/` via PHPUnit + PCOV (456 tester).

Kodtäckning är **inte** CI-gate i v1 — använd för att hitta otäckta domän- och REST-ytor.

**Relaterat:** [TEST_IMPLEMENTATION_PLAN.md](TEST_IMPLEMENTATION_PLAN.md) (Tier C1), [DEVELOPER.md](DEVELOPER.md).

---

## Kör

```powershell
.\scripts\mrt.ps1 coverage -Timings
```

Linux/WSL:

```sh
bash scripts/mrt.sh coverage --timings
```

Alternativ (root-wrapper): `.\scripts\coverage.ps1` / `bash scripts/gate/coverage.sh`.

- Installerar PCOV via **`docker/Dockerfile.tools`** (första gången: `docker compose --profile tools build php-test`).
- Skriver Clover till `coverage/clover.xml` (gitignored).
- Sammanfattning via `scripts/php/coverage-summary.php` **i samma container** (ingen host-PHP krävs).

---

## Baseline (2026-06-09)

| Mått | Värde |
|------|-------|
| Rader/statements i `inc/` | 11 818 |
| Täckta | 5 933 |
| **Total** | **50,2 %** |

### Tolkning

- **0 % — förväntat otäckt:** bootstrap, shortcode-registrering, asset/loaders, admin-meny, l10n-filer, WP hook-entrypoints. Dessa körs i WordPress, inte i PHPUnit-stubs.
- **Låg täckning — prioritera vid behov:** `timetables-handlers.php`, `import-export.php`, `train-types.php`, `traffic-notices-public.php` (REST-handlers med mycket grenar).
- **Stark täckning:** journey-engine, priser, CSV-import/export-domän, dashboard-REST (delvis), trafikmeddelanden-domän.

---

## Filer med 0 % täckning (70 st)

Admin & bootstrap: `inc/admin.php`, `inc/admin/app.php`, `inc/admin/menu.php`, `inc/bootstrap.php`, `inc/bootstrap/domain.php`, `inc/constants.php`, `inc/shortcodes.php`

Admin tools: `inc/admin/tools/*` (demo, dev-nav, import-lennakatten, timetable-pages, component-debug-pages)

Assets & l10n: `inc/assets.php`, `inc/assets/*.php`, `inc/assets/l10n/**`, `inc/assets/data/admin-help/**`, `inc/assets/vue-frontend.php`, `inc/assets/frontend.php`

Infrastructure loaders: `inc/infrastructure/post-types.php`, `inc/infrastructure/rest/loader.php`, `inc/infrastructure/rest/admin/timetables*.php`, `inc/infrastructure/rest/public/journey-public.php`, `inc/infrastructure/wordpress/environment.php`

Domän (loader/thin): `inc/domain/journey/journey-normalize.php`, `inc/domain/pricing/price-rules.php`, `inc/domain/route/routes.php`, `inc/domain/service/services.php`, `inc/domain/timetable/view/overview/overview-bus-rows.php`

Import: `inc/import/csv/loader.php`, `inc/import/csv/slugify.php`

Övrigt: `inc/public/journey-wizard/debug-fixtures.php`

Kör `.\scripts\mrt.ps1 coverage` för full lista och filer under 25 %.

---

## Under 25 % (top 11)

| Täckning | Fil |
|----------|-----|
| 4,4 % | `inc/infrastructure/rest/admin/timetables-handlers.php` |
| 4,9 % | `inc/assets/brand-tokens.php` |
| 6,7 % | `inc/import/lennakatten/importer.php` |
| 7,2 % | `inc/admin/tools/dev/dev-cli.php` |
| 7,4 % | `inc/infrastructure/rest/admin/import-export.php` |
| 12,1 % | `inc/infrastructure/rest/admin/train-types.php` |
| 14,3 % | `inc/infrastructure/rest/admin/dashboard.php` |
| 18,2 % | `inc/domain/service/connections.php` |
| 22,5 % | `inc/import/csv/export/exporter-entities.php` |
| 23,3 % | `inc/infrastructure/rest/public/traffic-notices-public.php` |
| 24,2 % | `inc/infrastructure/rest/dev/dev-tools.php` |
