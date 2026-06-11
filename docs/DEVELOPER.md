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

Fullständigt index: **[README.md](README.md)** (produkt, design, data, Vue, test, åtgärdsplaner).

Snabbstart vid ny kod:

| Dokument | Innehåll |
|----------|----------|
| [REBUILD_RULES.md](REBUILD_RULES.md) | Arkitektur, design, kvalitet |
| [STYLE_GUIDE.md](STYLE_GUIDE.md) | PHP/CSS/JS, säkerhet, i18n |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Lager, bootstrap, `inc/`-struktur |

---

## Kommandon och CI

| Kommando | Vad det gör |
|----------|-------------|
| `composer check` | Kör lokal snabbkontroll: plugin-check, PHPStan och PHPUnit |
| `composer plugin-check` | `php scripts/validate.php` – filer, syntax, ABSPATH, text domain |
| `composer test` | PHPUnit (`php vendor/bin/phpunit` — kör i terminal, öppna inte `vendor\bin\phpunit` direkt på Windows) |
| `.\scripts\test.ps1` | PHPUnit lokalt (PHP 8.2+) eller auto i Docker `php-test` (PHP 8.2) om lokal PHP saknas/är för gammal |
| `composer csv:validate -- <path>` | Validera CSV-paket utan WordPress |
| `composer phpstan` / `phpcs` / `lint` | Statisk analys + WPCS |

GitHub Actions (`.github/workflows/ci.yml`) kör `composer check` vid push/PR. Dependabot uppdaterar Composer månadsvis.

**PHPStan:** `phpstan-wordpress`, config `phpstan.neon`. **PHPCS:** WPCS; prefix `MRT_` kan flaggas – bedöm mot [STYLE_GUIDE.md](STYLE_GUIDE.md) och [REBUILD_RULES.md](REBUILD_RULES.md). `composer phpcbf` fixar formatering där det går.

**Pre-commit:** kräver bash (WSL/Git Bash på Windows) – `pre-commit install`.

### Script-bibliotek (`scripts/lib/`)

PowerShell- och bash-script delar helpers i `scripts/lib/` så Docker-, Vue- och WP-CLI-kommandon inte dupliceras i varje entry-script.

| Modul | Användning |
|-------|------------|
| `Mrt.Docker.ps1` | Dot-source från `*.ps1` — Compose, WP-CLI, Vue, vendor |
| `Mrt.Plugin.ps1` | Plugin-slug, fil-lista, kopiera/synka |
| `mrt-docker.sh` | Source från `*.sh` — samma byggblock för bash/CI |

**Full kommandotabell:** [scripts/README.md](../scripts/README.md)

På Windows: föredra `.\scripts\*.ps1` (Docker by default). Bash-varianter (`vue-check.sh`, `lint.sh`, `docker-dev-reset.sh`) använder samma lib.

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
docker compose --profile tools run --rm composer install
docker compose --profile tools run --rm composer check
```

Vue (typecheck, Vitest, build) körs i **`vue`-containern**:

```powershell
.\scripts\vue-check.ps1
# Linux/WSL: bash scripts/vue-check.sh
```

PHP + Vue: `.\scripts\check.ps1 -Vue`

Local by Flywheel kan fortfarande användas via `local/deploy.ps1`, men Docker är den portabla standarden för manuell WordPress-testning.

### Live test / staging (test3)

Synka plugin till en riktig WordPress utan zip — som Docker volume mount (ingen data-reset):

1. Kopiera `local/live-deploy.config.example.json` → `local/live-deploy.config.json`
2. Sätt `targetType`: `local` (sökväg/UNC) eller `ssh` (`sshHost` + `remotePath`)
3. Kör:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\live-deploy.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\live-deploy.ps1 -SkipBuild   # bara PHP/assets
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\live-deploy.ps1 -Watch       # auto vid filändring
```

Bygger Vue vid behov (samma som `docker-dev-reset`), kopierar `inc`, `assets`, `languages` m.m. För full omstart med import: `docker-dev-reset.ps1` lokalt eller WP-CLI på servern.

### Dev reset (clear + import + smoke menu)

Efter ändringar i import, rutter eller demosidor – ett kommando för agent/utvecklare:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\docker-dev-reset.ps1
```

`-SkipCompose` om Docker redan kör. Bygger Vue och laddar publik CSS via Vite-bundeln — se [VUE_FRONTEND.md](VUE_FRONTEND.md). Linux: `./scripts/docker-dev-reset.sh`.

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

---

## Bidra

Följ [REBUILD_RULES.md](REBUILD_RULES.md), [STYLE_GUIDE.md](STYLE_GUIDE.md) och [ARCHITECTURE.md](ARCHITECTURE.md). Kör `composer check` (eller `.\scripts\check.ps1 -Vue`) innan pull request.

Vid PR: använd checklistan i [`.github/pull_request_template.md`](../.github/pull_request_template.md) och säkerställ att `composer test` samt `php scripts/validate.php` är gröna.
