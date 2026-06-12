#!/usr/bin/env bash
# Wrapper — implementation in gate/check.sh
exec "$(dirname "$0")/gate/check.sh" "$@"
