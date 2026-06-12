#!/usr/bin/env sh
# Shared constants for bash MRT scripts.

MRT_DEV_SITE_URL="${MRT_DEV_SITE_URL:-http://localhost:8080}"

MRT_NPM_CI_SNIPPET='if [ ! -d node_modules ] || [ ! -f node_modules/.package-lock.json ] || ! cmp -s package-lock.json node_modules/.package-lock.json 2>/dev/null; then echo "Running npm ci..."; npm ci; cp package-lock.json node_modules/.package-lock.json; else echo "Skipped npm ci (node_modules matches package-lock.json)"; fi'

mrt_npm_ci_snippet="$MRT_NPM_CI_SNIPPET"
