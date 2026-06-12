#!/usr/bin/env sh
# Wrapper — implementation in gate/vue-check.sh
exec "$(dirname "$0")/gate/vue-check.sh" "$@"
