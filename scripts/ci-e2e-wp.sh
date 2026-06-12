#!/usr/bin/env bash
# Wrapper — implementation in dev/ci-e2e-wp.sh
exec "$(dirname "$0")/dev/ci-e2e-wp.sh" "$@"
