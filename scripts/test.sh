#!/usr/bin/env bash
# Wrapper — implementation in gate/test.sh
exec bash "$(dirname "$0")/gate/test.sh" "$@"
