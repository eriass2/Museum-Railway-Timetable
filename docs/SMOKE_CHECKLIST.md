# Smoke-checklista

Snabb genomgång efter ändringar i frontend, shortcodes eller import. Kör automatiskt:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-smoke.ps1
```

## Manuellt i webbläsaren

| Vad | URL | Förväntat |
|-----|-----|-----------|
| Admin dashboard | http://localhost:8080/wp-admin/admin.php?page=mrt_settings | Kort, rutnät, sektioner med `admin.css` |
| Wizard | Sida med `[museum_journey_wizard]` eller demo | Grön hero, steg 1–4, kalenderfärger, ikoner |
| Månad | `[museum_timetable_month]` | Kalender, klickbar trafikdag |
| Översikt | `[museum_timetable_overview]` | Rutnät per rutt |

Login: `admin` / `admin`

**Meny (utveckling):** Railway Timetable → dashboard eller Component demo page → **Set up development menu** (kräver `WP_DEBUG` eller `MRT_DEVELOPMENT`). Lägger till Component demo och Wizard smoke test i sajtmenyn. Se [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md).

## Kommandon

```powershell
docker compose up -d
docker compose run --rm composer check
npm run test:js
php scripts/validate.php
```

## Kända begränsningar

- `composer check` i Docker saknar Node (`test:js` körs lokalt med Node 22).
- Lokal PHP 7.4 kör inte full `composer install`; använd Docker för PHPUnit/PHPStan.
- Mockup-PNG finns inte i repot; wizard-stil är återställd från pre-purge CSS.

## Nästa steg (main, utan PR-krav)

- ~~Legacy `inc/functions/*`-loaders~~ — borttagna; `inc/bootstrap/domain.php`.
- ~~`inc/admin-page/`~~ — `inc/admin/` (`dashboard/`, `tools/`, `meta-boxes/`).
- ~~`inc/admin-meta-boxes/`~~, ~~`inc/admin-ajax/`~~, ~~`inc/cpt/`~~ — `inc/admin/meta-boxes/`, `inc/infrastructure/ajax/`, `inc/infrastructure/post-types/`.
- Wizard utresa/retur: `steps-outbound-return.css`; designreferens i `docs/mockups/DESIGN_TOKENS.md`.
- [ACCESSIBILITY_SMOKE.md](ACCESSIBILITY_SMOKE.md) – manuell WCAG-logg; statiska markörer i `validate.php`.
- [REBUILD_PRODUCT_DECISIONS.md](REBUILD_PRODUCT_DECISIONS.md) – MVP-beslut (wizard, månad, import).
