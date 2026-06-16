#!/usr/bin/env sh
# Wrapper — implementation in gate/vue-check.sh
exec bash "$(dirname "$0")/gate/vue-check.sh" "$@"
