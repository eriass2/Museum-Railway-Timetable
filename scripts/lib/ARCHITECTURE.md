# Scripts architecture (Fas 3 S1)

## Canonical entry points

| Platform | CLI | Gates |
|----------|-----|-------|
| Linux / macOS / WSL / CI | `bash scripts/mrt.sh` | `scripts/gate/*.sh` |
| Windows (Docker) | `.\scripts\mrt.ps1` | `scripts/gate/*.ps1` |

Both CLIs forward to the same behaviour; help text is shared in `lib/mrt-help.txt`.

## Shared libraries

| File | Role |
|------|------|
| `lib/mrt-docker.sh` | Bash loader for modular Docker libs (below) |
| `lib/mrt/constants.sh` | Dev site URL, npm-ci snippet |
| `lib/mrt/timings.sh` | Step headers and optional timings |
| `lib/mrt/wpcli.sh` | Compose, WP-CLI sidecar, WordPress readiness |
| `lib/mrt/tools.sh` | Tools-shell, composer/php-test/vue exec |
| `lib/mrt/dev.sh` | Vue check/build, locale, WP_DEBUG, smoke pages |
| `lib/mrt/vendor.sh` | Conditional `composer install` in Docker volume |
| `lib/Mrt.Docker.ps1` | PowerShell loader for modular Docker libs (below) |
| `lib/Mrt.Timings.ps1` | Step headers and optional timings |
| `lib/Mrt.Host.ps1` | Docker/npm/PHP availability, local vs Docker routing |
| `lib/Mrt.Compose.ps1` | Core `docker compose` invoke and stack up |
| `lib/Mrt.ToolsShell.ps1` | Tools-shell, composer/php-test/vue exec, PHPUnit |
| `lib/Mrt.WpCli.ps1` | WP-CLI sidecar and WordPress readiness |
| `lib/Mrt.Vendor.ps1` | Conditional `composer install` in Docker volume |
| `lib/Mrt.Vue.ps1` | Vue check/build in Docker or local npm |
| `lib/Mrt.Dev.ps1` | Dev reset, locale, WP_DEBUG, smoke pages |
| `lib/Mrt.LiveDeploy.ps1` | Loader for live deploy sync + watch |
| `lib/Mrt.LiveDeploy.Sync.ps1` | Config, local/SSH plugin sync |
| `lib/Mrt.LiveDeploy.Watch.ps1` | File watchers and debounced re-deploy |
| `lib/Mrt.Release.ps1` | Production zip build (validate, pack) |
| `lib/Mrt.Plugin.ps1` | Plugin metadata, repo root resolution |

## Layout

```
scripts/
  mrt.sh / mrt.ps1     # unified CLI (S2)
  gate/                # quality gates + _runner.ps1 / _init.sh
  dev/                 # docker dev reset, smoke, watch
  release/             # build zip, live deploy
  php/                 # validate.php + validate/ sections
  csv/, i18n/          # CSV zip, i18n helpers
  maintenance/         # one-off refactor scripts (archived)
  fixtures/            # Lennakatten PDF/fixture tooling
  setup-dev.sh         # host bootstrap for CI parity (S3)
  lib/                 # shared helpers + mrt-help.txt
```

## Root wrappers (backward compatibility)

Root `*.ps1`, `*.sh`, and thin `*.php` files (e.g. `validate.php`, `coverage-summary.php`) remain as stable entry points. They forward to `gate/`, `dev/`, `release/`, or `php/` — prefer `mrt check` / `bash scripts/mrt.sh check` for new usage. Wrappers are not deprecated on a fixed timeline; remove only when docs and muscle memory no longer reference them.

## Duplication policy

- **New Docker behaviour:** implement in `lib/mrt/*.sh` first, mirror in matching `Mrt.*.ps1` for Windows.
- **New commands:** add to `mrt.sh` + `mrt.ps1` and shared help.
- **Long term (optional):** PS could delegate more to bash via Git Bash; not required today.

See [docs/CI_AND_DEV_MODEL.md](../../docs/CI_AND_DEV_MODEL.md) for CI vs local Docker.
