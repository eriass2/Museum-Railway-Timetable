#!/usr/bin/env sh
# Shared Docker helpers for bash scripts.
# Usage: . "$(dirname "$0")/lib/mrt-docker.sh"

mrt_docker_up() {
	docker compose up -d --build
}

mrt_wait_wordpress() {
	timeout="${1:-120}"
	interval="${2:-5}"
	echo "Waiting for WordPress..."
	i=1
	max=$((timeout / interval))
	while [ "$i" -le "$max" ]; do
		if docker compose run --rm --no-TTY wordpress-init wp --allow-root core is-installed >/dev/null 2>&1; then
			return 0
		fi
		if [ "$i" -eq "$max" ]; then
			echo "WordPress did not become ready within ${timeout}s." >&2
			return 1
		fi
		sleep "$interval"
		i=$((i + 1))
	done
}

mrt_vue_build_verify() {
	docker compose --profile tools run --rm vue sh -c "npm ci && npm run build && npm run verify"
}

mrt_ensure_sv_locale() {
	docker compose run --rm wordpress-init sh /usr/local/bin/mrt-ensure-sv-locale.sh
}

mrt_dev_reset_import() {
	docker compose run --rm wordpress-init wp --allow-root eval \
		"if (!function_exists('MRT_dev_reset_and_import_cli')) { fwrite(STDERR, 'Plugin not active or dev-cli not loaded'.PHP_EOL); exit(1); } MRT_dev_reset_and_import_cli();"
}
