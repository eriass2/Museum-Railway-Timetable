#!/usr/bin/env bash
# WordPress Playwright E2E — prepare, run, restore. Prefer: mrt dev e2ewp
set -euo pipefail
exec bash "$(dirname "$0")/ci-e2e-wp.sh" "$@"
