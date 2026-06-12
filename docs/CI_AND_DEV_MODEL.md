# CI and local development model (Fas 3 S3)

## Decision summary

| Context | PHP / Vue gates | WordPress E2E |
|---------|-----------------|---------------|
| **GitHub Actions** (`ci.yml`) | Host PHP 8.2 + Node 22 | Docker via `scripts/ci-e2e-wp.sh` |
| **Windows (recommended)** | Docker via `.\scripts\mrt.ps1` | `mrt dev reset`, `mrt dev smoke` |
| **Linux / WSL** | Docker via `bash scripts/mrt.sh check` or host after `setup-dev` | `mrt dev reset`, `mrt dev smoke` |

**Why hybrid CI?** Host `composer check` + `composer vue:check` is faster than spinning up tools containers on every push. Windows dev uses Docker for PHP/Node parity without local installs.

**Parity rule:** Same `composer.json` scripts everywhere — not necessarily the same runtime.

## Bootstrap

### Host (matches CI validate job)

```bash
bash scripts/setup-dev.sh
# or: composer install && composer check && composer vue:check
```

Windows with Git Bash: `.\scripts\setup-dev.ps1`

### Docker (matches Windows daily flow)

```bash
bash scripts/mrt.sh check
bash scripts/mrt.sh dev reset
```

## Optional Docker CI verification

Run locally before release if you need container parity with Windows:

```bash
bash scripts/mrt.sh check --skip-phpcs
bash scripts/mrt.sh vue-check
```

A dedicated CI job for full Docker gates is intentionally omitted to keep PR checks fast; add later if drift becomes a problem.

## Related

- [DOCKER_SCRIPTS_PLAN.md](DOCKER_SCRIPTS_PLAN.md) — roadmap
- [scripts/lib/ARCHITECTURE.md](../scripts/lib/ARCHITECTURE.md) — S1 layout
- [.github/workflows/ci.yml](../.github/workflows/ci.yml) — current CI
