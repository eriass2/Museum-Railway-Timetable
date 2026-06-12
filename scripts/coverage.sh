#!/usr/bin/env bash
# Wrapper — implementation in gate/coverage.sh
exec "$(dirname "$0")/gate/coverage.sh" "$@"
