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
| `lib/mrt-docker.sh` | Bash: Compose, tools-shell, WP-CLI, vendor, dev helpers |
| `lib/Mrt.Docker.ps1` | PowerShell: same responsibilities for Windows |
| `lib/Mrt.Plugin.ps1` | Plugin metadata, repo root resolution |

Docker Compose invocation and tools-shell (P6) logic live in these two libs. Gate scripts stay thin.

## Layout

```
scripts/
  mrt.sh / mrt.ps1     # unified CLI (S2)
  gate/                # quality gates (check, test, lint, vue-check, coverage)
  dev/                 # docker dev reset, smoke, watch
  release/             # build zip, live deploy
  setup-dev.sh         # host bootstrap for CI parity (S3)
  lib/                 # shared helpers + mrt-help.txt
```

Root `*.ps1`, `*.sh`, `*.php` wrappers remain for backward compatibility.

## Duplication policy

- **New Docker behaviour:** implement in `mrt-docker.sh` first, mirror in `Mrt.Docker.ps1` for Windows.
- **New commands:** add to `mrt.sh` + `mrt.ps1` and shared help.
- **Long term (optional):** PS could delegate more to bash via Git Bash; not required today.

See [docs/CI_AND_DEV_MODEL.md](../../docs/CI_AND_DEV_MODEL.md) for CI vs local Docker.
