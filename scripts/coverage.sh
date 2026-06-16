#!/usr/bin/env bash
# Wrapper — implementation in gate/coverage.sh
exec bash "$(dirname "$0")/gate/coverage.sh" "$@"
