# Scripts – Museum Railway Timetable

Entry-point scripts for quality gates, Docker dev, deploy, and fixtures. Shared logic lives in **`lib/`** so wrappers stay thin.

**Roadmap:** [docs/DOCKER_SCRIPTS_PLAN.md](../docs/DOCKER_SCRIPTS_PLAN.md)

## Shared libraries

| File | Platform | Purpose |
|------|----------|---------|
| `lib/Mrt.Docker.ps1` | PowerShell | Docker Compose, WP-CLI, Vue build/check, vendor install |
| `lib/Mrt.Plugin.ps1` | PowerShell | Plugin slug, file list, version, copy/sync helpers |
| `lib/mrt-docker.sh` | Bash/sh | Same building blocks for `.sh` scripts and CI |

PowerShell scripts dot-source the Docker lib:

```powershell
. (Join-Path $PSScriptRoot 'lib/Mrt.Docker.ps1')
Set-MrtRepoRoot -ScriptsDirectory $PSScriptRoot
```

Bash scripts source the shell lib:

```sh
. "$(dirname "$0")/lib/mrt-docker.sh"
```

### Docker helpers (automatic)

Prefer **`.\scripts\*.ps1`** over raw `docker compose` — wrappers apply:

- `--no-deps` on tools services (`composer`, `php-test`, `vue`)
- Conditional `npm ci` when `node_modules` matches `package-lock.json` (logs *Skipped npm ci* / *Running npm ci*)
- `-Timings` or `MRT_SCRIPT_TIMINGS=1` for per-step duration on gate scripts
- Single container for `check.ps1` (`composer check:all`) and `lint.ps1` (`composer lint`)
- HTTP poll + one WP-CLI check when waiting for WordPress

---

## Quality gates

| Goal | Windows (recommended) | Linux/macOS / WSL |
|------|----------------------|-------------------|
| PHP validate + PHPStan + PHPUnit + PHPCS | `.\scripts\check.ps1` | `composer check` + `composer phpcs` |
| PHP without PHPCS | `.\scripts\check.ps1 -SkipPhpcs` | `composer check` |
| PHPStan + PHPCS only | `.\scripts\lint.ps1` | `bash scripts/lint.sh` (Docker default) |
| PHPUnit (Docker default) | `.\scripts\test.ps1` | `composer test` |
| PHPUnit (host PHP 8.2+) | `.\scripts\test.ps1 -Local` | `composer test` |
| Vue typecheck + Vitest + build | `.\scripts\vue-check.ps1` | `bash scripts/vue-check.sh` |
| Vue locally | `.\scripts\vue-check.ps1 -Local` | `bash scripts/vue-check.sh --local` |
| PHP + Vue | `.\scripts\check.ps1 -Vue` | — |
| Line coverage (exploratory) | `.\scripts\coverage.ps1` | — |
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
| Smoke test (import + HTTP checks, no clear) | `.\scripts\docker-smoke.ps1` |
| WordPress + Playwright E2E | `bash scripts/ci-e2e-wp.sh` |

Dev site: <http://localhost:8080> — admin / admin.

---

## Deploy and release

| Goal | Command |
|------|---------|
| Production zip | `.\scripts\build-release.ps1` |
| Sync to staging/live (no zip) | `.\scripts\live-deploy.ps1` |
| Sync with file watch | `.\scripts\live-deploy.ps1 -Watch` |
| Local by Flywheel | `.\local\deploy.ps1` |

Config for live deploy: `local/live-deploy.config.json` (see `local/live-deploy.config.example.json`).

---

## Data and i18n

| Goal | Command |
|------|---------|
| Regenerate `.pot` / merge Swedish `.po` | `.\scripts\make-i18n.ps1` |
| Pack CSV fixture zip | `.\scripts\csv-package-zip.ps1` or `bash scripts/csv-package-zip.sh` |
| Validate CSV package | `composer csv:validate -- <path>` |

---

## See also

- [docs/DEVELOPER.md](../docs/DEVELOPER.md) — full developer guide
- [docs/DOCKER_SCRIPTS_PLAN.md](../docs/DOCKER_SCRIPTS_PLAN.md) — optimization roadmap
- [docs/SMOKE_CHECKLIST.md](../docs/SMOKE_CHECKLIST.md) — manual WordPress smoke
- `.cursor/rules/testing-commands.mdc` — agent test commands (Windows/Docker)
