#!/usr/bin/env bash
# Wrapper — implementation in gate/lint.sh
exec "$(dirname "$0")/gate/lint.sh" "$@"
