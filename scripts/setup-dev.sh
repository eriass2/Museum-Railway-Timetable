#!/usr/bin/env bash
# Bootstrap host PHP + Node deps for CI-parity local gates (Fas 3 S3).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "=== MRT setup-dev (host) ==="

if ! command -v php >/dev/null 2>&1; then
	echo "PHP not in PATH. Install PHP 8.2+ or use Docker gates (scripts/mrt.sh check)." >&2
	exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
	echo "Composer not in PATH." >&2
	exit 1
fi

echo "Installing Composer dependencies..."
composer install --no-interaction

if command -v npm >/dev/null 2>&1; then
	php scripts/npm-ci-if-needed.php frontend/vue
else
	echo "npm not in PATH — skip frontend/vue (use Docker: scripts/mrt.sh vue-check)."
fi

echo ""
echo "Host dev ready."
echo "  composer check && composer vue:check   # same as GitHub Actions validate job"
echo "  bash scripts/mrt.sh check              # Docker gates (Windows/Linux)"
echo "  bash scripts/mrt.sh dev reset          # full WordPress dev stack"
