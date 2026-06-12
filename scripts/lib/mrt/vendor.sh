#!/usr/bin/env sh
# Vendor readiness for bash MRT scripts.

mrt_docker_vendor_ready() {
	mrt_tools_run php-test -r \
		'exit(is_file("vendor/autoload.php") && is_file("vendor/bin/phpstan") ? 0 : 1);'
}

mrt_ensure_vendor() {
	if ! mrt_docker_available; then
		echo "Docker is not running." >&2
		return 1
	fi
	if mrt_docker_vendor_ready; then
		echo "Using existing vendor/ (Docker volume)."
		return 0
	fi
	echo "vendor/ missing in Docker tools volume."
	echo "Installing dependencies via Docker..."
	mrt_tools_run composer install --no-interaction
}
