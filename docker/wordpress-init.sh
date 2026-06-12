#!/bin/sh
# First-boot WordPress setup for docker-compose (Fas 3 S5).
# Waits via HTTP poll + DB check instead of fixed sleep.
set -eu

WP_INTERNAL="${WORDPRESS_INTERNAL_URL:-http://wordpress}"
LOGIN_URL="${WP_INTERNAL}/wp-login.php"
TIMEOUT="${WORDPRESS_INIT_TIMEOUT:-120}"
INTERVAL="${WORDPRESS_INIT_INTERVAL:-2}"

deadline=$(( $(date +%s) + TIMEOUT ))

mrt_init_ready() {
	[ -f wp-config.php ] \
		&& wp --allow-root db check >/dev/null 2>&1 \
		&& curl -sf -o /dev/null --max-time 5 "$LOGIN_URL" 2>/dev/null
}

echo "Waiting for WordPress (HTTP + DB)..."
while [ "$(date +%s)" -lt "$deadline" ]; do
	if mrt_init_ready; then
		break
	fi
	sleep "$INTERVAL"
done

if ! mrt_init_ready; then
	echo "WordPress not ready within ${TIMEOUT}s (expected ${LOGIN_URL})." >&2
	exit 1
fi

if ! wp --allow-root core is-installed >/dev/null 2>&1; then
	wp --allow-root core install \
		--url="${WORDPRESS_SITE_URL}" \
		--title="${WORDPRESS_SITE_TITLE}" \
		--admin_user="${WORDPRESS_ADMIN_USER}" \
		--admin_password="${WORDPRESS_ADMIN_PASSWORD}" \
		--admin_email="${WORDPRESS_ADMIN_EMAIL}" \
		--locale=sv_SE \
		--skip-email
fi

sh /usr/local/bin/mrt-ensure-sv-locale.sh
wp --allow-root plugin activate museum-railway-timetable
wp --allow-root rewrite structure '/%postname%/' --hard

echo "wordpress-init complete."
