#!/usr/bin/env bash
# Wrapper — implementation in dev/docker-watch.sh
exec bash "$(dirname "$0")/dev/docker-watch.sh" "$@"
