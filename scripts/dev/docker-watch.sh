#!/usr/bin/env bash
# Start WordPress stack with plugin volume + compose watch (P7).
set -euo pipefail

SCRIPTS="$(cd "$(dirname "$0")/.." && pwd)"
ROOT="$(cd "$SCRIPTS/.." && pwd)"
cd "$ROOT"
. "$SCRIPTS/lib/mrt-docker.sh"

no_up=0
for arg in "$@"; do
	case "$arg" in
	--no-up) no_up=1 ;;
	esac
done

if [ "$no_up" -eq 0 ]; then
	echo "Starting stack with plugin volume (watch overlay)..."
	docker compose -f docker-compose.yml -f docker-compose.watch.yml up -d
fi

echo "Watching plugin files (sync to mrt_plugin volume)..."
echo "Tip: run dev reset once after first watch start if the plugin volume is empty."
exec docker compose -f docker-compose.yml -f docker-compose.watch.yml watch
