#!/usr/bin/env sh
# WordPress / WP-CLI helpers for bash MRT scripts.

mrt_wordpress_running() {
	docker compose ps --status running -q wordpress 2>/dev/null | grep -q .
}

mrt_wpcli_running() {
	docker compose ps --status running -q wpcli 2>/dev/null | grep -q .
}

mrt_compose_run_no_deps_if_up() {
	if mrt_wordpress_running; then
		docker compose run --rm --no-deps "$@"
	else
		docker compose run --rm "$@"
	fi
}

mrt_wp_cli() {
	if mrt_wpcli_running; then
		docker compose exec -T wpcli wp --allow-root "$@"
	else
		mrt_compose_run_no_deps_if_up --no-TTY wordpress-init wp --allow-root "$@"
	fi
}

mrt_wp_sh() {
	if mrt_wpcli_running; then
		docker compose exec -T wpcli sh "$@"
	else
		mrt_compose_run_no_deps_if_up wordpress-init sh "$@"
	fi
}

mrt_wait_until() {
	deadline="$1"
	interval="$2"
	shift 2

	while [ "$(date +%s)" -lt "$deadline" ]; do
		if "$@"; then
			return 0
		fi
		sleep "$interval"
	done
	return 1
}

mrt_http_ready() {
	curl -sf -o /dev/null --max-time 5 "$1" 2>/dev/null
}

mrt_wp_installed() {
	mrt_wp_cli core is-installed >/dev/null 2>&1
}

mrt_wait_wordpress() {
	timeout="${1:-120}"
	interval="${2:-2}"
	login_url="${MRT_DEV_SITE_URL}/wp-login.php"
	deadline=$(( $(date +%s) + timeout ))

	echo "Waiting for WordPress..."
	if ! mrt_wait_until "$deadline" "$interval" mrt_http_ready "$login_url"; then
		echo "WordPress did not respond at ${login_url} within ${timeout}s." >&2
		return 1
	fi
	if ! mrt_wait_until "$deadline" "$interval" mrt_wp_installed; then
		echo "WordPress did not become ready within ${timeout}s." >&2
		return 1
	fi
}

mrt_wp_eval() {
	mrt_wp_cli eval "$1"
}

mrt_docker_up() {
	docker compose up -d "$@"
}

mrt_docker_available() {
	docker info >/dev/null 2>&1
}
