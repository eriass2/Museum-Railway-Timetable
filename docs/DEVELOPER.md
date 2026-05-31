# Developer Guide – Museum Railway Timetable

En ingång för utvecklare. Läs detta först.

---

## Snabbstart

```sh
git clone <repo>
cd Museum-Railway-Timetable
composer install

composer check
```

---

## Dokumentation

Fullständigt index: **[README.md](README.md)**.

| Dokument | Innehåll |
|----------|----------|
| [REBUILD_RULES.md](REBUILD_RULES.md) | Arkitektur, design, kvalitet (primär vid ny kod) |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Lager, bootstrap, `inc/`-struktur |
| [STYLE_GUIDE.md](STYLE_GUIDE.md) | PHP/CSS/JS, `.mrt-*`, säkerhet |
| [DATA_MODEL.md](DATA_MODEL.md) | Post types, meta, tabeller |
| [SHORTCODES.md](SHORTCODES.md) | Månad, översikt, wizard |
| [REBUILD_PRODUCT_DECISIONS.md](REBUILD_PRODUCT_DECISIONS.md) | MVP-beslut (wizard-only m.m.) |
| [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md) | Dev-meny, component demo |
| [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md) | Manuell rökning |
| [ACCESSIBILITY.md](ACCESSIBILITY.md) | WCAG per modul |
| [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md) | Skapa tidtabell i admin |
| [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md) | PHP/Composer på Windows |

---

## Kommandon och CI

| Kommando | Vad det gör |
|----------|-------------|
| `composer check` | Kör lokal snabbkontroll: plugin-check, PHPStan, PHPUnit och JS-tester |
| `composer plugin-check` | `php scripts/validate.php` – filer, syntax, ABSPATH, text domain |
| `composer test` | PHPUnit (`php vendor/bin/phpunit` — kör i terminal, öppna inte `vendor\bin\phpunit` direkt på Windows) |
| `.\scripts\test.ps1` | PHPUnit lokalt (PHP 8.2+) eller auto i Docker `php-test` (PHP 8.2) om lokal PHP saknas/är för gammal |
| `composer csv:validate -- <path>` | Validera CSV-paket utan WordPress |
| `composer test:js` | Node-baserade JS-tester för delade assets |
| `composer phpstan` / `phpcs` / `lint` | Statisk analys + WPCS |

GitHub Actions (`.github/workflows/ci.yml`) kör `composer check` vid push/PR. Dependabot uppdaterar Composer månadsvis.

**PHPStan:** `phpstan-wordpress`, config `phpstan.neon`. **PHPCS:** WPCS; prefix `MRT_` kan flaggas – bedöm mot [STYLE_GUIDE.md](STYLE_GUIDE.md) och [REBUILD_RULES.md](REBUILD_RULES.md). `composer phpcbf` fixar formatering där det går.

**Pre-commit:** kräver bash (WSL/Git Bash på Windows) – `pre-commit install`.

---

## Lokala testlägen

### Snabbtest utan WordPress

Kör detta för ren PHP/JS-logik och statisk kontroll. Det kräver bara PHP 8.2+, Composer och Node:

```sh
composer install
composer check
```

### Full WordPress-testning

Använd Docker när du behöver klicka i admin, prova shortcodes eller testa dataflöden i en riktig WordPress-installation:

```sh
docker compose up -d --build
```

- Webbplats: <http://localhost:8080>
- Admin: <http://localhost:8080/wp-admin>
- Login: `admin` / `admin`

Kör Composer-kommandon i Docker om datorn saknar lokal PHP/Composer:

```sh
docker compose run --rm composer install
docker compose run --rm composer check
```

Local by Flywheel kan fortfarande användas via `local/deploy.ps1`, men Docker är den portabla standarden för manuell WordPress-testning.

### Dev reset (clear + import + smoke menu)

Efter ändringar i import, rutter eller demosidor – ett kommando för agent/utvecklare:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-dev-reset.ps1
```

`-SkipCompose` om Docker redan kör. Output: JSON med `pages.component_demo` och `pages.wizard`. Reset bygger Vue (`docker compose --profile tools run --rm vue`) och laddar publik CSS via Vite-bundeln — se [VUE_FRONTEND.md](VUE_FRONTEND.md).

### Automatiserad Docker-smoke

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-smoke.ps1
```

(`docker-smoke` kör import + demo men **rensar inte**; använd `docker-dev-reset` för full omstart.)

Se även [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md) och [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md).

### Playwright E2E (Vue)

Lokal körning (kräver Node/npm):

```powershell
cd frontend/vue
npm ci
npm run e2e
```

**WordPress-integration** (Docker + riktig WP, valfritt lokalt):

```powershell
# Sätter demo-URL för month-wp.spec.ts m.m.
$env:MRT_E2E_WP_DEMO_URL = 'http://localhost:8080/museum-railway-timetable-component-demo/'
bash scripts/ci-e2e-wp.sh
```

CI kör `composer vue:check`, isolerade Vue-E2E och `scripts/ci-e2e-wp.sh` på pull requests — se [.github/workflows/ci.yml](../.github/workflows/ci.yml).

### Manuell smoke-checklista i WordPress

Kör detta efter `docker compose up -d --build` när ändringen påverkar admin, shortcodes eller dataflöden:

- Logga in på <http://localhost:8080/wp-admin> med `admin` / `admin`.
- Kontrollera att menyn **Railway Timetable** syns och att pluginet är aktivt.
- Skapa minst två stationer, en rutt, en tidtabell och en trip/service.
- Lägg in stopptider för trippen och spara utan felmeddelanden.
- Skapa eller öppna en sida med `[museum_journey_wizard]` (reseflöde).
- Vid behov: **Import demo data** via Vue **Dev tools** eller **Component demo page** (månad, översikt, wizard).
- Kontrollera frontend: formulär/tabell visas, sökning går att köra och inga PHP-fel syns.
- Kontrollera loggar vid fel: `docker compose logs wordpress`.

---

## Checklista före deploy

- [ ] `composer check` grönt
- [ ] Manuell smoke-checklista i WordPress är genomförd vid UI/dataflödesändringar
- [ ] [ACCESSIBILITY.md](ACCESSIBILITY.md) – kort rökning vid UI-ändringar
- [ ] Översättningar: kör `powershell -File .\scripts\make-i18n.ps1` efter nya `__()`-strängar; fyll i tomma `msgstr` i `languages/museum-railway-timetable-sv_SE.po` vid behov
- [ ] `docker compose up -d --build` eller `.\local\deploy.ps1` om du testar i full WordPress
- [ ] **Live:** `powershell -File .\scripts\build-release.ps1` → `release/museum-railway-timetable.zip` (Vue-bygg + validate + pack; ladda upp via WP Plugins → Upload). Se `INSTALL.txt` i zip:en (permalänkar, CSV-import, felsökning).

---

## Krav

- PHP 8.2+ (plugin), Composer, WordPress 6.0+
- Windows utan PHP: [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md)

---

## Konstanter (`inc/constants.php`)

`MRT_TEXT_DOMAIN`, `MRT_POST_TYPE_*`, `MRT_TAXONOMY_TRAIN_TYPE`, `MRT_POST_TYPES`.

PHP: `declare(strict_types=1)` där det passar.
