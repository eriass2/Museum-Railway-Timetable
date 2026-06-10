#!/usr/bin/env sh
# Run Vue check in Docker (node:22-alpine). Pass --local to use host npm.
set -e
cd "$(dirname "$0")/.."

use_local=0
if [ "${1:-}" = "--local" ]; then
  use_local=1
  shift
fi

if [ "$use_local" -eq 1 ]; then
  exec composer vue:check "$@"
fi

echo "Running Vue check in Docker (node:22-alpine)..."
exec docker compose --profile tools run --rm vue sh -c "npm ci && npm run check"
