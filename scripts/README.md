# Scripts – Museum Railway Timetable

Entry-point scripts for quality gates, Docker dev, deploy, and fixtures. Shared logic lives in **`lib/`** so wrappers stay thin.

**Layout (Fas A–C):**

| Folder | Purpose |
|--------|---------|
| `lib/` | Shared PowerShell/bash helpers |
| `gate/` | Quality gates (check, test, lint, vue-check, coverage) |
| `php/` | PHP utilities (validate, npm-ci-if-needed, coverage-summary) |
| `csv/` | CSV validate and zip packaging |
| `i18n/` | Translation template and `.po` helpers |
| `dev/` | Docker dev reset, smoke, E2E, diagnostics |
| `release/` | Production zip and live deploy |
| `fixtures/lennakatten/` | PDF → CSV fixture sync and verify |
| `maintenance/` | One-off PHP refactor/split scripts |
| Root `*.ps1` / `*.sh` / `*.php` | Daily entry points (wrappers + gates) |

Root wrappers (`check.ps1`, `test.ps1`, `validate.php`, …) forward to `gate/`, `dev/`, or `php/` for backward compatibility. Prefer `mrt check` / `bash scripts/mrt.sh check` for new usage — see [lib/ARCHITECTURE.md](lib/ARCHITECTURE.md).

**Maintenance and fixtures:**

| Folder | Purpose |
|--------|---------|
| [maintenance/](maintenance/) | One-off PHP refactor/split scripts (archived; not part of daily gates) |
| [fixtures/lennakatten/](fixtures/lennakatten/) | PDF → CSV fixture sync and verify for Lennakatten data |

**Roadmap:** [docs/DOCKER_SCRIPTS_PLAN.md](../docs/DOCKER_SCRIPTS_PLAN.md)

## Shared libraries

| File | Platform | Purpose |
|------|----------|---------|
| `lib/Mrt.Docker.ps1` | PowerShell | Docker Compose, WP-CLI, Vue build/check, vendor install |
| `lib/Mrt.Plugin.ps1` | PowerShell | Plugin slug, file list, version, copy/sync helpers |
| `lib/mrt-docker.sh` | Bash/sh | Loader for `lib/mrt/*.sh` (same building blocks as PS) |
| `lib/mrt/*.sh` | Bash/sh | Modular bash: timings, wpcli, tools, dev, vendor |

PowerShell scripts dot-source the Docker lib (works from root wrappers or subfolders):

```powershell
$scriptsRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path  # when in gate/, dev/, …
. (Join-Path $scriptsRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot
```

Or use the unified CLI: `.\scripts\mrt.ps1 check` (see below).

Bash scripts source the shell lib:

```sh
. "$(dirname "$0")/lib/mrt-docker.sh"
```

### Docker helpers (automatic)

Prefer **`.\scripts\*.ps1`** over raw `docker compose` — wrappers apply:

- `--no-deps` on tools services (`composer`, `php-test`, `vue`)
- Long-running **tools shell** (P6): `compose exec` into `composer` / `php-test` / `vue` when profile is up; auto-starts on first gate run; fallback to `run --rm` with entrypoint override
- Named volumes `mrt_vendor` and `mrt_vue_node_modules` (less bind-mount I/O on Windows)
- WP-CLI via long-running **`wpcli`** sidecar (`compose exec`) when the stack is up; falls back to `run wordpress-init`
- Conditional `npm ci` when `node_modules` matches `package-lock.json` (logs *Skipped npm ci* / *Running npm ci*)
- `-Timings` or `MRT_SCRIPT_TIMINGS=1` for per-step duration on gate scripts
- Single container for `check.ps1` (`composer check:all`) and `lint.ps1` (`composer lint`)
- HTTP poll + one WP-CLI check when waiting for WordPress

---

## Unified CLI (`mrt`)

| Goal | Command |
|------|---------|
| Help | `.\scripts\mrt.ps1 help` or `bash scripts/mrt.sh help` |
| Host bootstrap (CI parity) | `.\scripts\mrt.ps1 setup-dev` or `bash scripts/setup-dev.sh` |
| PHP gate | `.\scripts\mrt.ps1 check` |
| PHP + Vue | `.\scripts\mrt.ps1 check -Vue` |
| PHPUnit | `.\scripts\mrt.ps1 test` |
| Dev reset | `.\scripts\mrt.ps1 dev reset` |
| Plugin file sync (Windows) | `.\scripts\mrt.ps1 dev watch` |
| CSV validate / zip | `.\scripts\mrt.ps1 csv validate -- <path>` / `csv zip` |
| i18n | `.\scripts\mrt.ps1 i18n` |

Existing `.\scripts\*.ps1` entry points remain; `mrt` forwards to them.

---

## Quality gates

| Goal | Windows (recommended) | Linux/macOS / WSL |
|------|----------------------|-------------------|
| Full PHP gate (Docker) | `.\scripts\mrt.ps1 check` | `bash scripts/mrt.sh check` |
| PHP validate + PHPStan + PHPUnit + PHPCS | `.\scripts\check.ps1` | `bash scripts/gate/check.sh` |
| PHP without PHPCS | `.\scripts\check.ps1 -SkipPhpcs` | `bash scripts/mrt.sh check --skip-phpcs` |
| PHPStan + PHPCS only | `.\scripts\lint.ps1` | `bash scripts/gate/lint.sh` |
| PHPUnit (Docker default) | `.\scripts\test.ps1` | `bash scripts/gate/test.sh` |
| PHPUnit (host PHP 8.2+) | `.\scripts\test.ps1 -Local` | `bash scripts/gate/test.sh --local` |
| Vue typecheck + Vitest + build | `.\scripts\vue-check.ps1` | `bash scripts/gate/vue-check.sh` |
| Vue locally | `.\scripts\vue-check.ps1 -Local` | `bash scripts/gate/vue-check.sh --local` |
| PHP + Vue | `.\scripts\check.ps1 -Vue` | `bash scripts/mrt.sh check --vue` |
| Line coverage (exploratory) | `.\scripts\coverage.ps1` or `.\scripts\mrt.ps1 coverage -Timings` | `bash scripts/gate/coverage.sh --timings` |
| Plugin file/syntax check | `composer plugin-check` | `php scripts/validate.php` |

**Windows:** never invoke `vendor\bin\phpunit` directly — use `.\scripts\test.ps1`.

---

## Docker development

| Goal | Command |
|------|---------|
| Full dev reset (stack + import + menu) | `.\scripts\docker-dev-reset.ps1` or `./scripts/docker-dev-reset.sh` |
| Dev reset + rebuild WordPress image | `.\scripts\docker-dev-reset.ps1 -Build` or `./scripts/docker-dev-reset.sh --build` |
| Dev reset (stack already running) | `.\scripts\docker-dev-reset.ps1 -SkipCompose` or `./scripts/docker-dev-reset.sh --skip-compose` |
| Start stack only (no import) | `docker compose up -d` — prefer dev reset when you need data + menu |
| Smoke test (import + HTTP checks, no clear) | `.\scripts\mrt.ps1 dev smoke` or `.\scripts\docker-smoke.ps1` |
| Plugin sync via compose watch (Windows I/O) | `.\scripts\docker-watch.ps1` or `.\scripts\mrt.ps1 dev watch` |
| WordPress + Playwright E2E | `bash scripts/ci-e2e-wp.sh` |

Dev site: <http://localhost:8080> — admin / admin.

---

## Deploy and release

| Goal | Command |
|------|---------|
| Production zip | `.\scripts\mrt.ps1 release build` or `.\scripts\build-release.ps1` |
| Sync to staging/live (no zip) | `.\scripts\mrt.ps1 release deploy` or `.\scripts\live-deploy.ps1` |
| Sync with file watch | `.\scripts\live-deploy.ps1 -Watch` |
| Local by Flywheel | `.\local\deploy.ps1` |

Config for live deploy: `local/live-deploy.config.json` (see `local/live-deploy.config.example.json`).

---

## Data and i18n

| Goal | Command |
|------|---------|
| Regenerate `.pot` / merge Swedish `.po` | `.\scripts\mrt.ps1 i18n` or `.\scripts\i18n/make-i18n.ps1` |
| Pack CSV fixture zip | `.\scripts\mrt.ps1 csv zip` or `.\scripts\csv/csv-package-zip.ps1` |
| Validate CSV package | `composer csv:validate -- <path>` |

---

## See also

- [docs/DEVELOPER.md](../docs/DEVELOPER.md) — full developer guide
- [scripts/lib/ARCHITECTURE.md](lib/ARCHITECTURE.md) — module layout and duplication policy
- [docs/DOCKER_SCRIPTS_PLAN.md](../docs/DOCKER_SCRIPTS_PLAN.md) — optimization roadmap
- [docs/SMOKE_CHECKLIST.md](../docs/SMOKE_CHECKLIST.md) — manual WordPress smoke
- `.cursor/rules/testing-commands.mdc` — agent test commands (Windows/Docker)
