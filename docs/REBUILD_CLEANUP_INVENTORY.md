# Rebuild cleanup inventory

Syfte: klassificera nuvarande filer inför nästa rebuild-steg. Detta dokument är en plan för rensning och flytt, inte en radering i sig.

Statusnycklar:

- `keep` – ska behållas i nuvarande form tills vidare
- `move` – ska behållas men flyttas till ny struktur
- `rewrite` – funktionen behövs, men implementationen bör byggas om
- `delete` – kan tas bort när ersättning/ny struktur finns

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
| `docs/README.md` | `rewrite` | Ska bli kort index för rebuild-dokument och aktiv dokumentation. |
| `docs/DEVELOPER.md` | `rewrite` | Behåll setup/testkommandon, ta bort historisk brus. |
| `docs/ARCHITECTURE.md` | `rewrite` | Synka med ny struktur (`domain`, `admin`, `public`, `infrastructure`). |
| `docs/STYLE_GUIDE.md` | `rewrite` | Ersätt med/peka mot `REBUILD_RULES.md`; undvik dubbla kodregler. |
| `docs/DATA_MODEL.md` | `move/rewrite` | Behåll datamodellidéer, skriv om efter faktisk rebuild-modell. |
| `docs/SHORTCODES.md` | `rewrite` | Behåll bara shortcodes som ingår i MVP. |
| `docs/ADMIN_WORKFLOW.md` | `rewrite` | Behåll arbetsflöde efter ny adminstruktur. |
| `docs/ACCESSIBILITY.md` | `keep/rewrite` | Behåll WCAG-krav, uppdatera efter ny frontend. |
| `docs/PHP_INSTALL_WINDOWS.md` | `keep` | Praktisk setupinfo, låg risk. |

Första dokument-cleanup bör ta bort duplicerade regler efter att `REBUILD_RULES.md` är etablerad som källa.

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
| `inc/functions/helpers-datetime.php` | `move` | `inc/domain/datetime/datetime.php` | Flyttad; legacyfilen är loader tills resten av rebuilden använder ny väg direkt. |
| `inc/functions/journey-*.php` | `move/rewrite` | `inc/domain/journey/` | Flyttade till domain; legacyfiler är loaders där de behövs. Skriv om gränssnitt senare. |
| `inc/functions/journey-prices.php` | `move` | `inc/domain/pricing/prices.php` | Flyttad; legacyfilen är loader tills resten av rebuilden använder ny väg direkt. |
| `inc/data/price-matrix-builtin.php` | `move` | `inc/domain/pricing/price-matrix-builtin.php` | Flyttad seed/reference data. |
| `inc/functions/timetable-view/*` | `move/rewrite` | `inc/domain/timetable/view/` | Flyttad. Dela dataförberedelse från rendering i senare rewrite. |
| `inc/functions/services.php` | `move/rewrite` | `inc/domain/service/services.php` | Flyttad; separera queries, mapping, connection search senare. |
| `inc/functions/helpers-services.php` | `move/rewrite` | `inc/domain/service/stop-times.php` | Flyttad; behåll stopptidshelpers, rensa WP-adapterdelar senare. |
| `inc/functions/helpers-connections.php` | `move/rewrite` | `inc/domain/service/connections.php` | Flyttad; connection helper för service/transfer-info. |
| `inc/functions/helpers-routes.php` | `move/rewrite` | `inc/domain/route/routes.php` | Flyttad; legacyfilen är loader. Separera WP queries senare. |
| `inc/functions/helpers-stations.php` | `move` | `inc/domain/station/stations.php` | Flyttad; legacyfilen är loader. |
| `inc/functions/helpers-utils.php` | `split` | `inc/domain/shared/`, `inc/domain/train-type/`, `inc/infrastructure/` | Train-type icon helpers är flyttade; resten är blandat. |

### Behåll/flytta som import

| Nuvarande område | Status | Ny plats | Kommentar |
|------------------|--------|----------|-----------|
| `inc/import-lennakatten/import-data.php` | `move` | `inc/import/lennakatten/reference-data.php` | Flyttad referensdata från PDF. |
| `inc/import-lennakatten/import-run.php` | `rewrite` | `inc/import/lennakatten/importer.php` | Flyttad; dela upp runner, repository, mapper senare. |
| `inc/import-lennakatten/loader.php` | `rewrite` | `inc/admin/tools/import-page.php` | UI-adapter för import. |

### Behåll/flytta som admin

| Nuvarande område | Status | Ny plats | Kommentar |
|------------------|--------|----------|-----------|
| `inc/admin-page/*` | `rewrite` | `inc/admin/dashboard/` | Behåll verktyg, gör WordPress-native och mindre. |
| `inc/admin-meta-boxes/*` | `rewrite` | `inc/admin/meta-boxes/` | Behåll dataflöden, dela större service/timetable-filer. |
| `inc/admin-ajax/*` | `rewrite` | `inc/infrastructure/ajax/` | Tunna endpoints som delegerar till domain. |
| `inc/cpt/*` | `move/rewrite` | `inc/infrastructure/post-types/` | Registrering av CPT/taxonomies. |

### Behåll/flytta som public

| Nuvarande område | Status | Ny plats | Kommentar |
|------------------|--------|----------|-----------|
| `inc/shortcodes/shortcode-month.php` | `rewrite` | `inc/public/month-calendar/shortcode.php` | Flyttad. Behåll om MVP behöver månadsvy. |
| `inc/shortcodes/shortcode-overview.php` | `rewrite` | `inc/public/timetable-overview/shortcode.php` | Flyttad. Behåll tidtabellsöversikt. |
| `inc/shortcodes/shortcode-journey-wizard.php` + `inc/shortcodes/journey-wizard/` | `rewrite` | `inc/public/journey-wizard/` | Flyttad. Primär frontend enligt mockup. |
| `inc/shortcodes/shortcode-journey.php` | `delete/rewrite` | `inc/public/journey-planner/shortcode.php` | Flyttad. Legacy one-page planner; behåll bara om MVP kräver den. |
| `inc/demo-page.php` | `rewrite` | `inc/admin/tools/demo-page.php` | Behövs som demo/testverktyg men ska vara admin tool. |

### Loaders

| Fil | Status | Kommentar |
|-----|--------|-----------|
| `inc/assets.php` | `move` | Loader för `inc/assets/`. |
| `inc/admin-page.php`, `inc/admin-meta-boxes.php`, `inc/admin-ajax.php`, `inc/cpt.php`, `inc/shortcodes.php` | `delete/rewrite` | Ersätt med `inc/bootstrap.php` och modul-loaders i ny struktur. |

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

Rekommenderat första cleanup-scope:

1. Skapa `inc/bootstrap.php`.
2. Skapa tom ny målstruktur (`domain`, `import`, `admin`, `public`, `infrastructure`).
3. Purga nuvarande utseendeimplementation enligt avsnittet `Utseendepurge`.
4. Flytta endast loaders/enkla rena helpers först.
5. Låt gamla icke-utseende-filer samexistera tills motsvarande tester pekar på ny modul.
6. Radera bara dokumentationsdubbletter som helt ersätts av `REBUILD_*`.

Undvik i första cleanup-PR:

- att radera fungerande import
- att radera testdata
- att radera journey-domänlogik
- att byta datamodell och UI samtidigt

---

## 9. Beslutsfrågor före radering

- Ska `[museum_journey_planner]` överleva som legacy/simple mode, eller ersättas helt av wizard?
- Ska månadsvyn vara fristående shortcode eller bara en del av wizard/tidtabellsöversikt?
- Ska admin fortsatt stödja manuell datainmatning, eller primärt import + korrigering?
- Ska ny struktur införas stegvis i nuvarande plugin eller byggas i parallell `inc-next/` först?
