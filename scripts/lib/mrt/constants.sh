#!/usr/bin/env sh
# Shared constants for bash MRT scripts.

_mrt_constants_root="$(CDPATH= cd "$(dirname "$0")/../../.." && pwd)"
. "$(dirname "$0")/env.sh"
mrt_load_dotenv "$_mrt_constants_root"
MRT_DEV_SITE_URL="$(mrt_resolve_dev_site_url)"
export MRT_DEV_SITE_URL

MRT_NPM_CI_SNIPPET='if [ ! -d node_modules ] || [ ! -f node_modules/.package-lock.json ] || ! cmp -s package-lock.json node_modules/.package-lock.json 2>/dev/null; then echo "Running npm ci..."; npm ci; cp package-lock.json node_modules/.package-lock.json; else echo "Skipped npm ci (node_modules matches package-lock.json)"; fi'

mrt_npm_ci_snippet="$MRT_NPM_CI_SNIPPET"
