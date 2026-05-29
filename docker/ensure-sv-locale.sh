#!/bin/sh
# Idempotent: Swedish WordPress core + admin user locale (Docker dev).
set -e

if ! wp --allow-root core is-installed >/dev/null 2>&1; then
	exit 0
fi

if ! wp --allow-root core language is-installed sv_SE >/dev/null 2>&1; then
	mkdir -p /var/www/html/wp-content/languages
	chown -R www-data:www-data /var/www/html/wp-content/languages 2>/dev/null || true
	wp --allow-root language core install sv_SE
fi

wp --allow-root site switch-language sv_SE

ADMIN_ID="$(wp --allow-root user list --role=administrator --field=ID --number=1 2>/dev/null | head -n1)"
if [ -n "$ADMIN_ID" ]; then
	wp --allow-root user meta update "$ADMIN_ID" locale sv_SE 2>/dev/null || true
fi