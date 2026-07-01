# Developer Guide – Museum Railway Timetable

En ingång för utvecklare. Läs detta först.

---

## Snabbstart

```sh
git clone <repo>
cd Museum-Railway-Timetable
composer install

composer check          # host PHP (CI-paritet)
# eller Docker gates:
bash scripts/mrt.sh check --skip-phpcs   # Linux/WSL
.\scripts\mrt.ps1 check -SkipPhpcs      # Windows
```

**En ingång:** `mrt help` — se [scripts/README.md](../scripts/README.md).

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
| `.\scripts\mrt.ps1 test` / `bash scripts/mrt.sh test` | PHPUnit i Docker (standard) eller `-Local` / `--local` på host PHP 8.3+ |
| `.\scripts\test.ps1` | Samma som `mrt test` (root-wrapper) |
| `composer csv:validate -- <path>` | Validera CSV-paket utan WordPress |
| `composer phpstan` / `phpcs` / `lint` | Statisk analys + WPCS |

GitHub Actions (`.github/workflows/ci.yml`) kör `composer check` vid push/PR. Dependabot uppdaterar Composer månadsvis.

**PHPStan:** `phpstan-wordpress`, config `phpstan.neon`. **PHPCS:** WPCS; prefix `MRT_` kan flaggas – bedöm mot [STYLE_GUIDE.md](STYLE_GUIDE.md) och [REBUILD_RULES.md](REBUILD_RULES.md). `composer phpcbf` fixar formatering där det går.

**Pre-commit:** kräver bash (WSL/Git Bash på Windows) – `pre-commit install`.

### Script-bibliotek (`scripts/lib/`)

PowerShell- och bash-script delar helpers i `scripts/lib/` så Docker-, Vue- och WP-CLI-kommandon inte dupliceras i varje entry-script.

| Modul | Användning |
|-------|------------|
| `Mrt.Docker.ps1` + `Mrt.*.ps1` | Dot-source från `*.ps1` — Compose, tools-shell, WP-CLI, Vue, vendor |
| `mrt-docker.sh` + `lib/mrt/*.sh` | Source från `*.sh` — samma byggblock för bash/CI |

**Full kommandotabell:** [scripts/README.md](../scripts/README.md)  
**Modullayout:** [scripts/lib/ARCHITECTURE.md](../scripts/lib/ARCHITECTURE.md)  
**Roadmap (Fas 0–3):** [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md)  
**CI vs Docker dev:** [CI_AND_DEV_MODEL.md](CI_AND_DEV_MODEL.md)  
**Dev Container:** [.devcontainer/README.md](../.devcontainer/README.md)

**Föredra `mrt`** — `.\scripts\mrt.ps1` (Windows) eller `bash scripts/mrt.sh` (Linux/WSL). Root-wrappers (`check.ps1`, `test.ps1`, …) fungerar fortfarande. Kör **skripten** — undvik rå `docker compose …` (skripten hanterar tools-shell exec, `--no-deps`, villkorlig `npm ci`, m.m.).

---

## Lokala testlägen

### Snabbtest utan WordPress

Kör detta för ren PHP/JS-logik och statisk kontroll. Det kräver bara PHP 8.3+, Composer och Node:

```sh
composer install
composer check
```

### Full WordPress-testning

Använd Docker när du behöver klicka i admin, prova shortcodes eller testa dataflöden i en riktig WordPress-installation:

```powershell
.\scripts\mrt.ps1 dev reset
# efter ändring i Dockerfile:
.\scripts\mrt.ps1 dev reset -Build
```

Linux/WSL:

```sh
bash scripts/mrt.sh dev reset
bash scripts/mrt.sh dev reset --build
```

- Webbplats: <http://localhost:8080> (eller din port — se nedan)
- Admin: <http://localhost:8080/wp-admin>
- Login: `admin` / `admin`

**Portkonflikt?** Kopiera `.env.example` till `.env` och sätt t.ex. `MRT_WP_PORT=8081`. Starta om stacken (`mrt dev reset`). Skript och Docker Compose läser `.env` automatiskt; WordPress `siteurl`/`home` synkas vid dev reset.

**Quality gates (Docker)** — samma beteende via `mrt` eller root-wrappers:

| Mål | Windows | Linux/WSL |
|-----|---------|-----------|
| Full PHP-gate | `.\scripts\mrt.ps1 check` | `bash scripts/mrt.sh check` |
| PHP utan PHPCS | `.\scripts\mrt.ps1 check -SkipPhpcs` | `bash scripts/mrt.sh check --skip-phpcs` |
| PHP + Vue | `.\scripts\mrt.ps1 check -Vue` | `bash scripts/mrt.sh check --vue` |
| PHPUnit | `.\scripts\mrt.ps1 test` | `bash scripts/mrt.sh test` |
| Vue | `.\scripts\mrt.ps1 vue-check` | `bash scripts/mrt.sh vue-check` |
| Lint | `.\scripts\mrt.ps1 lint` | `bash scripts/mrt.sh lint` |
| Coverage (utforskande) | `.\scripts\mrt.ps1 coverage -Timings` | `bash scripts/mrt.sh coverage --timings` |

Alternativ: `.\scripts\check.ps1`, `.\scripts\test.ps1`, `.\scripts\vue-check.ps1` (root-wrappers).

Om datorn saknar lokal PHP/Composer: använd **`mrt check`** / **`mrt test`** — inte rå `docker compose … run composer …`.

Vue körs i **`vue`-containern** — använd **`mrt vue-check`**, inte `docker compose … run composer vue:check` (`composer`-imaget saknar npm).

**Docker tools-volymer (Fas 2):** `composer` och `php-test` delar named volume `mrt_vendor`; `vue` använder `mrt_vue_node_modules`. Det minskar bind-mount-I/O på Windows. Volymerna är **separata** från eventuell host-`vendor/` — gate-skript installerar via Docker om volymen saknar `vendor/autoload.php`. Rensa vid behov: `docker volume rm $(docker volume ls -q --filter name=mrt_vendor)` (projektnamn varierar).

**WSL2 (Windows):** Klona och kör Docker från Linux-filsystemet (`\\wsl$\Ubuntu\home\…`) i stället för `C:\Projects\…` — bind mounts blir då 2–10× snabbare. Se [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md) Fas 2 P2.

Local by Flywheel kan fortfarande användas via `local/deploy.ps1`, men Docker är den portabla standarden för manuell WordPress-testning.

### Live test / staging (test3)

Synka plugin till en riktig WordPress utan zip — som Docker volume mount (ingen data-reset):

1. Kopiera `local/live-deploy.config.example.json` → `local/live-deploy.config.json`
2. Sätt `targetType`: `local` (sökväg/UNC) eller `ssh` (`sshHost` + `remotePath`)
3. Kör:

```powershell
.\scripts\mrt.ps1 release deploy
.\scripts\mrt.ps1 release deploy -SkipBuild   # bara PHP/assets
.\scripts\live-deploy.ps1 -Watch              # auto vid filändring
```

Bygger Vue vid behov (samma som dev reset), kopierar `inc`, `assets`, `languages` m.m. För full omstart med import: `mrt dev reset` lokalt eller WP-CLI på servern.

### Dev reset (clear + import + smoke menu)

Efter ändringar i import, rutter eller demosidor – ett kommando:

```powershell
.\scripts\mrt.ps1 dev reset
```

`-SkipCompose` / `--skip-compose` om Docker redan kör. `-Build` / `--build` om `Dockerfile` ändrats. Bygger Vue och laddar publik CSS via Vite-bundeln — se [VUE_FRONTEND.md](VUE_FRONTEND.md).

### Automatiserad Docker-smoke

```powershell
.\scripts\mrt.ps1 dev smoke
```

(`dev smoke` kör import + demo men **rensar inte**; använd `dev reset` för full omstart.)

Se även [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md) och [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md).

### Playwright E2E (Vue)

Lokal körning (kräver Node/npm):

```powershell
cd frontend/vue
npm ci
npm run e2e
```

**WordPress-integration** (Docker + riktig WP; prepare → Playwright → **restore** så dev-db inte lämnas smutsig):

```powershell
.\scripts\mrt.ps1 dev e2ewp
# eller: bash scripts/mrt.sh dev e2ewp
# CI anropar samma flöde via bash scripts/ci-e2e-wp.sh
```

Restore kör samma Lennakatten override + demo-trafik som i början (tar bort E2E-tidtabeller utan fixture-kod och återställer `mrt_public_notices`). **Kör enstaka `*-wp.spec.ts` utan hela skriptet** → avsluta med `.\scripts\mrt.ps1 dev reset` eller `dev e2ewp`.

**Windows utan host-`npm`** — kör Playwright i Docker mot stacken på `localhost:8080`:

```powershell
# 1. Stack up (t.ex. .\scripts\mrt.ps1 dev reset -SkipCompose om redan uppe)
# 2. Tillfälligt: WP siteurl/home måste nås från containern som host.docker.internal:8080
docker compose exec -T wordpress wp option update siteurl 'http://host.docker.internal:8080' --allow-root
docker compose exec -T wordpress wp option update home 'http://host.docker.internal:8080' --allow-root

# 3. Playwright (version ska matcha package-lock; image v1.61.0-jammy)
docker run --rm -it `
  -v "${PWD}:/work" -w /work/frontend/vue `
  -e MRT_E2E_WP_DEMO_URL=http://host.docker.internal:8080/museum-railway-timetable-component-demo/ `
  -e MRT_E2E_WP_ADMIN_URL=http://host.docker.internal:8080/wp-admin/admin.php?page=mrt_app `
  --add-host=host.docker.internal:host-gateway `
  mcr.microsoft.com/playwright:v1.61.0-jammy `
  bash -lc "npm ci && npm run e2e"

# 4. Återställ siteurl/home till localhost:8080 för lokal webbläsare
docker compose exec -T wordpress wp option update siteurl 'http://localhost:8080' --allow-root
docker compose exec -T wordpress wp option update home 'http://localhost:8080' --allow-root
```

`package-lock.json` kan peka på nyare `@playwright/test` än CI-Docker-imagen — håll image-tag i synk med [ci.yml](../.github/workflows/ci.yml) eller kör `bash scripts/ci-e2e-wp.sh` i WSL/Linux CI.

CI kör `composer vue:check`, isolerade Vue-E2E och `scripts/ci-e2e-wp.sh` på pull requests — se [.github/workflows/ci.yml](../.github/workflows/ci.yml).

**Responsivitet (F0 + T1–T8):** statiska specs i Docker — `.\scripts\mrt.ps1 e2e -- --grep "responsive"`. Manuell viewport-rökning: [RESPONSIVE_MANUAL_TEST_PLAN.md](RESPONSIVE_MANUAL_TEST_PLAN.md).

### Manuell smoke-checklista i WordPress

Kör detta efter `.\scripts\mrt.ps1 dev reset` (eller `docker compose up -d`) när ändringen påverkar admin, shortcodes eller dataflöden:

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

- [ ] `composer check` eller `.\scripts\mrt.ps1 check -Vue` grönt
- [ ] Manuell smoke-checklista i WordPress är genomförd vid UI/dataflödesändringar
- [ ] [ACCESSIBILITY.md](ACCESSIBILITY.md) – kort rökning vid UI-ändringar
- [ ] Översättningar: `.\scripts\mrt.ps1 i18n` efter nya `__()`-strängar; fyll i tomma `msgstr` i `languages/museum-railway-timetable-sv_SE.po` vid behov
- [ ] `.\scripts\mrt.ps1 dev reset` (eller `.\local\deploy.ps1`) om du testar i full WordPress
- [ ] **Live:** `.\scripts\mrt.ps1 release build` → `release/museum-railway-timetable.zip` (Vue-bygg + validate + pack; ladda upp via WP Plugins → Upload). Se `INSTALL.txt` i zip:en (permalänkar, CSV-import, felsökning).

---

## Krav

- PHP 8.3+ (plugin), Composer, WordPress 6.0+
- Windows utan PHP: [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md)

---

## Konstanter (`inc/constants.php`)

`MRT_TEXT_DOMAIN`, `MRT_POST_TYPE_*`, `MRT_TAXONOMY_TRAIN_TYPE`, `MRT_POST_TYPES`.

PHP: `declare(strict_types=1)` där det passar.

---

## Bidra

Följ [REBUILD_RULES.md](REBUILD_RULES.md), [STYLE_GUIDE.md](STYLE_GUIDE.md) och [ARCHITECTURE.md](ARCHITECTURE.md). Kör `composer check` eller `.\scripts\mrt.ps1 check -Vue` innan pull request.

Vid PR: använd checklistan i [`.github/pull_request_template.md`](../.github/pull_request_template.md) och säkerställ att `composer test` samt `php scripts/validate.php` är gröna.
