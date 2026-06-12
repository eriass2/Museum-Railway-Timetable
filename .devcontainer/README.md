# Dev Container (Fas 3 S4)

Open this repo in VS Code / Cursor with **Dev Containers: Reopen in Container**.

## What starts

- Full stack from `docker-compose.yml`: MariaDB, WordPress, `wpcli`, and tools profile (`composer`, `php-test`, `vue`)
- Workspace folder: `/app` in the `php-test` service (PHP 8.2 + PCOV)

## After open

1. WordPress: <http://localhost:8080> (may need `docker compose up` if not auto-started)
2. Gates inside container: `bash scripts/mrt.sh check`
3. Host Windows gates still work via `.\scripts\mrt.ps1` when not using dev container

See [docs/CI_AND_DEV_MODEL.md](../docs/CI_AND_DEV_MODEL.md).
