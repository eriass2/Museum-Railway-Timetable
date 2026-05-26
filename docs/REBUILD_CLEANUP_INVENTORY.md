# Rebuild cleanup inventory

Syfte: klassificera nuvarande filer inför nästa rebuild-steg. Detta dokument är en plan för rensning och flytt, inte en radering i sig.

Statusnycklar:

- `keep` – ska behållas i nuvarande form tills vidare
- `move` – ska behållas men flyttas till ny struktur
- `rewrite` – funktionen behövs, men implementationen bör byggas om
- `delete` – kan tas bort när ersättning/ny struktur finns
- `done` – genomfört i `main` (se avsnitt 0)

---

## 0. Genomfört i main

| Område | Resultat |
|--------|----------|
| `inc/functions/*`, `inc/shortcodes/*` | Borttagna; laddning via `inc/bootstrap/domain.php`. |
| `inc/import-lennakatten/import-data.php`, `import-run.php`, `loader.php` | Borttagna/flyttade; domain i `inc/import/lennakatten/`, UI i `inc/admin/tools/import-lennakatten.php`. |
| `inc/admin-page/*` | → `inc/admin/dashboard/`, `inc/admin/tools/`, `inc/admin/admin-list.php`. |
| `inc/admin-page.php` | → `inc/admin.php` (bootstrap anropar). |
| `inc/demo-page.php` | → `inc/admin/tools/demo-page.php`. |
| `inc/admin-meta-boxes/*` | → `inc/admin/meta-boxes/` + `inc/admin/meta-boxes.php`. |
| `inc/admin-ajax/*` | → `inc/infrastructure/ajax/` + `inc/infrastructure/ajax.php`. |
| `inc/cpt/*` | → `inc/infrastructure/post-types/`. |
| Publik tidtabellsöversikt | CSS grid-fix + bredare demosida (`public-layout.css`, `overview-special-rows.css`). |
| Import UI | `inc/admin/tools/import-lennakatten.php`. |
| Assets loader | `inc/assets/loader.php`. |
| Mockups | `docs/mockups/README.md`, `DESIGN_TOKENS.md`. |
| Produktbeslut | `docs/REBUILD_PRODUCT_DECISIONS.md`. |
| A11y rökning | `docs/ACCESSIBILITY_SMOKE.md` + statiska kontroller i `validate.php`. |
| Fysisk rensning §7 (2026-05-25) | Sista kvarvarande dubbletter borttagna: `inc/admin-meta-boxes/`, `inc/admin-ajax/`, `inc/import-lennakatten/`. `php scripts/validate.php` grönt. |
| Översättningar (2026-05-26) | `.pot` regenererad från `inc/`; `.po` mergad (orphan journey-planner-strängar borta); `scripts/make-i18n.ps1`. |

---

## 1. Källor som alltid ska sparas

| Område | Status | Kommentar |
|--------|--------|-----------|
| `testdata/reference-pdfs/` | `keep` | Referenskälla för trafikdagar, tågtyper, tider och specialnoteringar. |
| `docs/mockups/` | `keep` | Mockups styr frontendflöde och designprioritering. |
| `docs/REBUILD_SKETCH.md` | `keep` | Produkt- och cleanup-målbild. |
| `docs/REBUILD_RULES.md` | `keep` | Nya regler för kod, design och kvalitet. |
| `tests/` | `keep/move` | Testerna ska styra rebuild; flyttas/uppdateras när moduler flyttas. |

Viktigt: `docs/mockups/` är referensmaterial och ska sparas. Nuvarande implementation av utseende/styling ska däremot rensas i purge-steget.

---

## 2. Dokumentation

| Fil/område | Status | Kommentar |
|------------|--------|-----------|
| `docs/REBUILD_SKETCH.md` | `keep` | Primär målbild. |
| `docs/REBUILD_RULES.md` | `keep` | Primär regelbok. |
| `docs/REBUILD_CLEANUP_INVENTORY.md` | `keep` | Detta dokument. |
| `docs/README.md` | `done` | Index: rebuild + daglig utveckling; pekar på `REBUILD_RULES`. |
| `docs/DEVELOPER.md` | `done` | Setup/test; länkar rebuild och smoke. |
| `docs/ARCHITECTURE.md` | `done` | Bootstrap, `inc/`-karta, tre shortcodes. |
| `docs/STYLE_GUIDE.md` | `done` | Konventioner; design/arkitektur → `REBUILD_RULES`. |
| `docs/DATA_MODEL.md` | `done` | Datamodell + kodkarta till `inc/domain/`. |
| `docs/SHORTCODES.md` | `done` | Tre MVP-shortcodes (wizard-only för resa). |
| `docs/ADMIN_WORKFLOW.md` | `done` | Arbetsflöde + `inc/admin/`-referens. |
| `docs/ACCESSIBILITY.md` | `done` | WCAG per wizard/månad/översikt. |
| `docs/PHP_INSTALL_WINDOWS.md` | `keep` | Praktisk setupinfo, låg risk. |

**Regel:** `REBUILD_RULES.md` styr arkitektur och design; `STYLE_GUIDE.md` styr kodkonventioner.

---

## 3. Root och infrastruktur

| Fil/område | Status | Kommentar |
|------------|--------|-----------|
| `museum-railway-timetable.php` | `rewrite` | Ska bli minimal bootstrap som laddar ny struktur. |
| `composer.json`, `composer.lock` | `keep` | Test-/analysverktyg behövs. |
| `phpunit.xml.dist`, `phpstan.neon`, `phpstan-bootstrap.php`, `phpcs.xml` | `keep` | Kvalitetsgrindar. |
| `.github/workflows/ci.yml` | `keep` | CI ska fortsätta vara gate. |
| `.github/dependabot.yml` | `keep` | Dependency-underhåll. |
| `.github/pull_request_template.md` | `rewrite` | Synka med rebuild DoD och manual smoke-krav. |
| `README.md` | `rewrite` | Ska beskriva ny produkt, inte historik. |
| `CONTRIBUTING.md` | `rewrite` | Peka mot rebuild-regler. |
| `uninstall.php` | `rewrite` | Säkerställ att ny datamodell städas korrekt. |
| `local/` | `keep` | Utvecklarhjälp. |
| `docker-compose.yml`, `Dockerfile`, `docker/` | `keep` | Lokal WP-testmiljö. |

---

## 4. Ny föreslagen kodstruktur

Målet är att flytta fungerande delar hit stegvis:

```text
inc/
├── domain/
├── import/
├── admin/
├── public/
├── assets/
├── infrastructure/
└── bootstrap.php
```

---

## 5. Nuvarande `inc/` klassning

### Behåll/flytta som domän

| Nuvarande område | Status | Ny plats | Kommentar |
|------------------|--------|----------|-----------|
| `inc/functions/helpers-datetime.php` | `done` | `inc/domain/datetime/datetime.php` | Legacy loaders borttagna. |
| `inc/functions/journey-*.php` | `done` | `inc/domain/journey/` | Legacy loaders borttagna. |
| `inc/functions/journey-prices.php` | `done` | `inc/domain/pricing/prices.php` | Legacy loaders borttagna. |
| `inc/data/price-matrix-builtin.php` | `move` | `inc/domain/pricing/price-matrix-builtin.php` | Flyttad seed/reference data. |
| `inc/functions/timetable-view/*` | `done` | `inc/domain/timetable/view/` | Loaders borttagna; ev. rewrite data/rendering senare. |
| `inc/functions/services.php` | `done` | `inc/domain/service/services.php` | Loaders borttagna. |
| `inc/functions/helpers-services.php` | `done` | `inc/domain/service/stop-times.php` | Loaders borttagna. |
| `inc/functions/helpers-connections.php` | `done` | `inc/domain/service/connections.php` | Loaders borttagna. |
| `inc/functions/helpers-routes.php` | `done` | `inc/domain/route/routes.php` | Loaders borttagna. |
| `inc/functions/helpers-stations.php` | `done` | `inc/domain/station/stations.php` | Loaders borttagna. |
| `inc/functions/helpers-utils.php` | `done` | `inc/infrastructure/wordpress/helpers-utils.php` | WP-meta/helpers; train-type i domain. |

### Behåll/flytta som import

| Nuvarande område | Status | Ny plats | Kommentar |
|------------------|--------|----------|-----------|
| `inc/import-lennakatten/import-data.php` | `done` | `inc/import/lennakatten/reference-data.php` | Loader borttagen. |
| `inc/import-lennakatten/import-run.php` | `done` | `inc/import/lennakatten/importer.php` | Loader borttagen. |
| `inc/import-lennakatten/loader.php` | `done` | `inc/admin/tools/import-lennakatten.php` | Flyttad. |

### Behåll/flytta som admin

| Nuvarande område | Status | Ny plats | Kommentar |
|------------------|--------|----------|-----------|
| `inc/admin-page/*` | `done` | `inc/admin/dashboard/`, `inc/admin/tools/` | Flyttat; vidare förenkling valfritt. |
| `inc/admin-meta-boxes/*` | `done` | `inc/admin/meta-boxes/` | Flyttat. |
| `inc/admin-ajax/*` | `done` | `inc/infrastructure/ajax/` | Flyttat. |
| `inc/cpt/*` | `done` | `inc/infrastructure/post-types/` | Flyttat. |

### Behåll/flytta som public

| Nuvarande område | Status | Ny plats | Kommentar |
|------------------|--------|----------|-----------|
| `inc/shortcodes/shortcode-month.php` | `done` | `inc/public/month-calendar/shortcode.php` | Legacy shortcode-träd borttaget. |
| `inc/shortcodes/shortcode-overview.php` | `done` | `inc/public/timetable-overview/shortcode.php` | Legacy shortcode-träd borttaget. |
| `inc/shortcodes/shortcode-journey-wizard.php` + `inc/shortcodes/journey-wizard/` | `done` | `inc/public/journey-wizard/` | Legacy shortcode-träd borttaget. |
| `inc/shortcodes/shortcode-journey.php` | `done` | — | Legacy journey-planner shortcode borttagen; wizard kvar. |
| `inc/demo-page.php` | `done` | `inc/admin/tools/demo-page.php` | Flyttad. |

### Loaders

| Fil | Status | Kommentar |
|-----|--------|-----------|
| `inc/assets.php` | `done` | Tunn entry; logik i `inc/assets/loader.php`. |
| `inc/admin-page.php` | `done` | `inc/admin.php` + `inc/bootstrap.php` | Ersatt. |
| `inc/admin-meta-boxes.php`, `inc/admin-ajax.php`, `inc/cpt.php` | `done` | Ersatta av `inc/admin/meta-boxes.php`, `inc/infrastructure/ajax.php`, `inc/infrastructure/post-types.php`. |
| `inc/shortcodes.php` | `rewrite` | Registrerar `inc/public/*`; kan förenklas senare. |

---

## 6. Nuvarande `assets/` klassning

| Område | Status | Kommentar |
|--------|--------|-----------|
| `assets/icons/train-types/` | `keep/move` | Tågikonerna ska behållas som produktassets och flyttas till ny assetstruktur. |
| `assets/train-type-icons.css` | `rewrite` | Behåll ikonmappningen som krav, men skriv om CSS efter ny designstruktur. |
| `assets/journey-wizard/` + `assets/journey-wizard.css` | `delete/rewrite` | Ta bort nuvarande styling. Ny frontend-CSS byggs senare från mockups. |
| `assets/frontend.js` | `rewrite` | Behåll bara beteende som behövs; separera från nuvarande styling/legacy planner. |
| `assets/mrt-string-utils.js`, `assets/mrt-date-utils.js`, `assets/mrt-frontend-api.js` | `keep/move` | Beteende-/API-helpers, inte utseende. |
| `assets/admin-*.js` | `rewrite` | Behåll där adminflöden kvarstår, men flytta per adminmodul. |
| `assets/admin-*.css` | `delete/rewrite` | Ta bort nuvarande admin-utseendeimplementation. Ny admin ska luta på WordPress-native CSS och endast ha minimal egen CSS. |
| `assets/CSS_STRUCTURE.md` | `delete` | Nuvarande CSS-struktur försvinner när utseendeimplementationen purgas. |

### Utseendepurge

När purge-steget körs ska följande tas bort eller tömmas till minimal ny grund:

- nuvarande CSS-filer och CSS-modulstruktur
- nuvarande ikon-CSS som stylingimplementation
- frontend-styling som inte kommer direkt från ny mockupbaserad implementation
- admin-styling som ersätts av WordPress-native UI
- dokumentation som beskriver gammalt utseende eller gammal CSS-struktur

Det som ska sparas är referenserna: mockups, tidtabells-PDF:er, tågikonerna i `assets/icons/train-types/` och reglerna i `REBUILD_RULES.md`.

---

## 7. Tester

| Område | Status | Kommentar |
|--------|--------|-----------|
| `tests/Unit/ImportDataTest.php` | `keep` | Styr referensdataimport. |
| `tests/Unit/Journey*` | `keep/move` | Styr journey-domänlogik. |
| `tests/Unit/JourneyMultiLegTest.php` | `keep` | Viktig för byte/gemensam station. |
| `tests/Unit/JourneyPricesTest.php` | `keep` | Prislogik. |
| `tests/Unit/TrainTypeIconTest.php` | `keep` | Tågtypsikon-regler. |
| `tests/Unit/RefactoredHelpersTest.php` | `keep/rewrite` | Behåll relevanta assertions, flytta när helpers byter namn. |
| `tests/JourneyTestFixtures.php`, `tests/wp-stubs.php` | `move/rewrite` | Ny testinfrastruktur bör vara tydligare moduliserad. |
| `tests/js/*` | `keep` | Behåll delade JS-utiltester. |

---

## 8. Första faktiska cleanup-PR efter denna inventering

**Status:** Genomfört i `main` (bootstrap, målstruktur, legacy-träd borta, journey planner borta, dokumentationssynk avsnitt 2). Kvar: §8.6 frontend-polish mot mockups.

Tidigare rekommenderat scope (referens):

1. ~~Skapa `inc/bootstrap.php`.~~
2. ~~Skapa målstruktur (`domain`, `import`, `admin`, `public`, `infrastructure`).~~
3. Utseendepurge – delvis; wizard/CSS återställd, mockup-PNG saknas i repo.
4. ~~Flytta loaders/helpers.~~
5. ~~Legacy samexisterar inte längre.~~
6. Dokumentationsdubbletter – pågående (`docs/README.md` som index).

---

## 9. Beslutsfrågor före radering

**Besvarade** i [REBUILD_PRODUCT_DECISIONS.md](REBUILD_PRODUCT_DECISIONS.md) (2026-05):

- Journey planner shortcode **borttagen**; wizard är enda publika rese-UI (2026-05).
- Månadsvy **fristående** shortcode kvar.
- Admin: **import + manuell** korrigering.
- Struktur **stegvis i nuvarande plugin** (ej `inc-next/`).
