# Docker dev and diagnostics

| Script | Purpose |
|--------|---------|
| `docker-dev-reset.ps1` / `.sh` | Reset stack, import Lennakatten, dev menu |
| `docker-smoke.ps1` | Import + HTTP smoke (no DB clear) |
| `docker-watch.ps1` / `.sh` | Plugin sync via `compose watch` + volume (Windows I/O) |
| `ci-e2e-wp.sh` | WordPress + Playwright E2E (prepare → run → restore; CI + `mrt dev e2e-wp`) |
| `e2e-wp.sh` / `e2e-wp.ps1` | Entry for `mrt dev e2e-wp` |
| `bench-calendar.php` | Journey calendar benchmark (WP-CLI/Docker) |
| `warm-journey-cache.php` | Pre-warm wizard PHP transients |

Entry points at repo root stay **`scripts/docker-dev-reset.ps1`** etc. — thin wrappers forward here.
