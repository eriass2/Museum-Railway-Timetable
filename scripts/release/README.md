# Release and deploy

| Script | Purpose |
|--------|---------|
| `build-release.ps1` | Vue build + validate + production zip |
| `live-deploy.ps1` | Sync plugin to staging/live (no zip) |

Entry points at repo root stay **`scripts/build-release.ps1`** and **`scripts/live-deploy.ps1`** — thin wrappers forward here.

Config for live deploy: `local/live-deploy.config.json`.
