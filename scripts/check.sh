#!/usr/bin/env bash
# Wrapper — implementation in gate/check.sh
exec bash "$(dirname "$0")/gate/check.sh" "$@"
