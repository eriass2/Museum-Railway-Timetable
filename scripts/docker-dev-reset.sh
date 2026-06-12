#!/usr/bin/env sh
# Wrapper — implementation in dev/docker-dev-reset.sh
exec "$(dirname "$0")/dev/docker-dev-reset.sh" "$@"
