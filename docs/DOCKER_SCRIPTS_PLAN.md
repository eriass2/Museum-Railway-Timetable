# Docker- och skriptlager — plan

Plan för utvecklarverktyg under `scripts/` och `docker-compose.yml`.  
**Status:** Fas 0–1 genomförda. Fas 2 pågår (P1–P4 klara 2026-06-12).

**Relaterat:** [scripts/README.md](../scripts/README.md), [DEVELOPER.md](DEVELOPER.md), `.cursor/rules/testing-commands.mdc`.

---

## Nuvarande modell (Fas 0 — klar)

Entry-script (`check.ps1`, `test.ps1`, `vue-check.ps1`, …) är tunna. Gemensam logik ligger i:

| Modul | Plattform |
|-------|-----------|
| `scripts/lib/Mrt.Docker.ps1` | PowerShell (Windows) |
| `scripts/lib/mrt-docker.sh` | Bash (CI, Linux, WSL) |

### Optimeringar (commit `b3934a4`)

| Område | Beteende |
|--------|----------|
| Vue i Docker | `npm ci` **endast** om `node_modules` saknas eller `package-lock.json` ändrats |
| Tools-containers | `--no-deps` på `composer`, `php-test`, `vue` |
| `check.ps1` / `lint.ps1` | **En** container per körning (`composer check:all` / `composer lint`) |
| WordPress-väntan | HTTP-poll mot `/wp-login.php`, sedan `wp core is-installed` |
| Dev reset | `up -d` utan `--build` som standard; `-Build` vid Dockerfile-ändring |
| Wrapper-buggar | `test.ps1` / `vue-check.ps1` avslutar efter lyckad Docker-körning |

### Rekommenderade kommandon (kör skript — inte rå `docker compose`)

| Mål | Windows | CI / Linux |
|-----|---------|------------|
| Full PHP-gate + PHPCS | `.\scripts\check.ps1` | `composer check` (+ ev. `composer phpcs`) |
| PHP + Vue | `.\scripts\check.ps1 -Vue` | `composer check` + `composer vue:check` |
| PHPUnit | `.\scripts\test.ps1` | `composer test` |
| Vue | `.\scripts\vue-check.ps1` | `bash scripts/vue-check.sh` |
| Dev reset | `.\scripts\docker-dev-reset.ps1` | `./scripts/docker-dev-reset.sh` |
| Dev reset + rebuild image | `.\scripts\docker-dev-reset.ps1 -Build` | `./scripts/docker-dev-reset.sh --build` |

### Medveten hybrid: Docker vs host

| Miljö | PHP/Vue-körning |
|-------|-----------------|
| Windows (rekommenderat) | Docker via `scripts/*.ps1` |
| GitHub Actions | Host PHP 8.2 + Node 22 (`composer check`, `composer vue:check`) |
| Linux/WSL | Host eller `scripts/*.sh` |

Samma **composer-skript**, inte nödvändigtvis samma container — avsiktligt för snabb CI. Se [Fas 3](#fas-3--strategi-och-enhetligt-cli) om paritet blir problem.

---

## Fas 1 — låg risk, hög nytta

**Mål:** Dokumentation, paritet och bättre feedback utan ny infrastruktur.

| ID | Punkt | Insats | Status |
|----|---------|--------|--------|
| D1 | Docs pekar på skript, inte rå `docker compose … npm ci` | Liten | **Klar** (2026-06-12) |
| D2 | `-Build` i `docker-dev-reset.sh` (paritet med `.ps1`) | Liten | **Klar** (2026-06-12) |
| D3 | `-Timings` / `MRT_SCRIPT_TIMINGS=1` — logga stegtid | Liten | **Klar** (2026-06-12) |
| D4 | Tydlig logg: *Skipped npm ci*, *Using existing vendor* | Liten | **Klar** (2026-06-12) |
| D5 | `csv-package-zip.sh` via `mrt_tools_run` | Liten | **Klar** (2026-06-12) |
| D6 | Villkorlig `npm ci` i `composer vue:build` / `vue:check` (host) | Liten | **Klar** (2026-06-12) |
| D7 | `docker-smoke.ps1`: hämta demo-URL via WP-CLI (inte hårdkodade `?p=39`) | Medel | **Klar** (2026-06-12) |

**Exit-kriterium:** Nya utveckare och agenter hittar rätt kommando utan att kopiera inaktuella docker-rader.

---

## Fas 2 — prestanda

**Mål:** Mindre I/O-straff (Windows) och färre cold starts.

| ID | Uppgift | Insats | Förväntad vinst | Status |
|----|---------|--------|-----------------|--------|
| P1 | Named volumes för `vendor/` och `frontend/vue/node_modules` | Medel | Snabbare upprepade check/test/vue på Windows | **Klar** (2026-06-12) |
| P2 | Dokumentera WSL2: repo under `\\wsl$\…` inte `C:\Projects\…` | Liten | 2–10× fil-I/O i Docker | **Klar** (2026-06-12) |
| P3 | Eget `docker/Dockerfile.tools` — PHP 8.2 + mbstring, xml, **PCOV** | Medel | `coverage.ps1` utan `apt-get`/`pecl` varje gång | **Klar** (2026-06-12) |
| P4 | Kör `coverage-summary.php` i samma container som PHPUnit | Liten | Coverage utan host-PHP | **Klar** (2026-06-12) |
| P5 | WP-CLI via `docker compose exec` (sidecar eller CLI i WP-image) | Stor | Färre `run wordpress-init`-containers | **Klar** (2026-06-12) |
| P6 | Long-running **tools-shell** (`compose exec` istället för `run`) | Medel | ~2–5 s sparat per gate på Windows | |
| P7 | `compose watch` för plugin-bind mount | Medel | Snabbare iterering mot WordPress | |

**Exit-kriterium:** `check.ps1 -Vue` och `docker-dev-reset.ps1` märkbart snabbare vid upprepade körningar samma dag.

---

## Fas 3 — strategi och enhetligt CLI

**Mål:** En implementation, tydlig dev/CI-modell.

| ID | Uppgift | Insats | Beslut som krävs |
|----|---------|--------|------------------|
| S1 | Bash som canonical + tunna PS-wrappers **eller** gemensamt `mrt`-CLI | Stor | Windows utan WSL? |
| S2 | Samlat CLI: `mrt check`, `mrt test`, `mrt dev reset` | Medel | Bakåtkompatibilitet för befintliga `.ps1` |
| S3 | CI: allt i Docker **eller** dokumenterad host-only med `setup-dev` | Stor | CI-tid vs paritet |
| S4 | Dev Container (`.devcontainer/`) | Stor | Cursor/VS Code standard för teamet? |
| S5 | Init-container: HTTP-poll istället för `sleep 3` i `wordpress-init` | Liten | — |

**Exit-kriterium:** En plats att lära sig (`mrt help`), ingen duplicerad PS/bash-logik att hålla synkad manuellt.

---

## Prioritering (rekommendation)

```text
Nu     → Fas 1 (docs + paritet + timings)
Sedan  → P1 + P3 (volumes + tools-image) — störst praktisk vinst
Senare → P5/P6 (WP-CLI + tools-shell)
Sist   → S1/S2 (CLI-konsolidering) när smärta från dubbel kodbas märks
```

---

## Kända begränsningar (medvetet ej fixat)

| Område | Nuvarande beteende |
|--------|-------------------|
| Bind mount | Plugin-kod mountas fortfarande från host (WordPress + tools) — WSL2-volym eller P7 `watch` hjälper |
| Docs i root README | Kan fortfarande nämna `docker compose up -d --build` som snabbstart |

---

## Genomfört (arkiv)

| Datum | Vad |
|-------|-----|
| 2026-06-11 | Fas 0: optimering + refaktor `Mrt.Docker.ps1` / `mrt-docker.sh`, `composer check:all`, `-Build` på `docker-dev-reset.ps1` — commit `b3934a4` |
| 2026-06-12 | Fas 1 D1–D2: docs → skript, `docker-dev-reset.sh --build` / `--skip-compose` |
| 2026-06-12 | Fas 1 D3–D4: `-Timings` / `MRT_SCRIPT_TIMINGS=1`, npm ci + vendor-logg |
| 2026-06-12 | Fas 1 D5–D7: csv-package via tools, host npm ci, smoke-URL:er via WP-CLI |
| 2026-06-12 | Fas 2 P1–P4: named volumes, Dockerfile.tools + PCOV, coverage i Docker, WSL2-docs |
| 2026-06-12 | Fas 2 P5: `wpcli` sidecar + `compose exec` (fallback `run wordpress-init`) |
