#!/usr/bin/env sh
# Load repo .env and resolve dev site URL (MRT_WP_PORT / MRT_DEV_SITE_URL).

mrt_load_dotenv() {
	root="$1"
	env_file="$root/.env"
	if [ ! -f "$env_file" ]; then
		return 0
	fi

	while IFS= read -r line || [ -n "$line" ]; do
		case "$line" in
			''|\#*) continue ;;
		esac
		key=${line%%=*}
		val=${line#*=}
		key=$(printf '%s' "$key" | tr -d ' ')
		val=$(printf '%s' "$val" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' -e 's/^["'"'"']//' -e 's/["'"'"']$//')
		eval "cur=\${$key-__unset__}"
		if [ "$cur" = __unset__ ]; then
			export "$key=$val"
		fi
	done < "$env_file"
}

mrt_resolve_dev_site_url() {
	if [ -n "${MRT_DEV_SITE_URL:-}" ]; then
		printf '%s' "$MRT_DEV_SITE_URL"
		return 0
	fi
	port="${MRT_WP_PORT:-8080}"
	printf 'http://localhost:%s' "$port"
}
