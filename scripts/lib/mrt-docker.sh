#!/usr/bin/env sh
# Shared Docker helpers for bash scripts (loader).
# Usage: . "$SCRIPTS/lib/mrt-docker.sh"  (preferred, from gate/_init.sh)
#    or: . "$(dirname "$0")/../lib/mrt-docker.sh"  (from dev/, csv/, …)

if [ -n "${SCRIPTS:-}" ]; then
	_MRT_LIB_DIR="$SCRIPTS/lib/mrt"
else
	_MRT_LIB_DIR="$(CDPATH= cd "$(dirname "$0")/../lib/mrt" && pwd)"
fi

. "$_MRT_LIB_DIR/constants.sh"
. "$_MRT_LIB_DIR/timings.sh"
. "$_MRT_LIB_DIR/wpcli.sh"
. "$_MRT_LIB_DIR/tools.sh"
. "$_MRT_LIB_DIR/dev.sh"
. "$_MRT_LIB_DIR/vendor.sh"
