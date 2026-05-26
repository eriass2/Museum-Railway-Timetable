# Smoke-checklista (imorgon)

Snabb genomgång efter frontend-rebuild PR:erna. Kör automatiskt:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-smoke.ps1
```

## Branches att granska (merge-ordning)

1. `cursor/frontend-wizard-base-99eb` – journey wizard CSS
2. `cursor/frontend-shared-base-99eb` – planner, månad, översikt, knappar
3. `cursor/frontend-admin-minimal-99eb` – admin + bootstrap + smoke-script

## Manuellt i webbläsaren

| Vad | URL | Förväntat |
|-----|-----|-----------|
| Admin dashboard | http://localhost:8080/wp-admin/admin.php?page=mrt_settings | Kort, rutnät, sektioner med `admin.css` |
| Wizard | Sida med `[museum_journey_wizard]` eller demo | Grön hero, steg 1–2, kalenderfärger |
| Planner | `[museum_journey_planner]` | Formulär + sök, resultattabell |
| Månad | `[museum_timetable_month]` | Kalender, klickbar trafikdag |
| Översikt | `[museum_timetable_overview]` | Rutnät per rutt |

Login: `admin` / `admin`

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

## Efter merge (nästa steg)

- ~~Legacy `inc/functions/*`-loaders~~ — borttagna; laddning via `inc/bootstrap/domain.php`.
- Wizard utresa/retur finpolish om mockups läggs i `docs/mockups/`.
- [ACCESSIBILITY.md](ACCESSIBILITY.md) – kort manuell rökning.
