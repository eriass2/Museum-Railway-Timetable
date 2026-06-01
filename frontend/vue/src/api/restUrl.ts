/**
 * Build Museum Railway Timetable REST URLs from WP-provided restUrl + relative path.
 * Supports pretty permalinks (/wp-json/...) and plain (?rest_route=...) installs.
 *
 * restUrl always comes from PHP (rest_url()) — never hardcode localhost or production hosts.
 */

/** Must match MRT_REST_NAMESPACE in inc/constants.php */
export const MRT_REST_NAMESPACE = 'museum-railway-timetable/v1';

export type MrtRestUrlConfig = {
  restUrl?: string;
  restNonce?: string;
};

/** Normalize restUrl from PHP config (trailing slash). */
export function normalizeMrtRestBase(restUrl: string): string {
  const trimmed = restUrl.trim();
  return trimmed.endsWith('/') ? trimmed : `${trimmed}/`;
}

/**
 * Resolve REST base URL from mount config.
 * Falls back to same-origin /wp-json/… when restUrl is missing (dev/tests only).
 */
export function resolveMrtRestBase(config: Pick<MrtRestUrlConfig, 'restUrl'>): string {
  const configured = config.restUrl?.trim();
  if (configured) {
    return normalizeMrtRestBase(configured);
  }
  if (typeof window !== 'undefined' && window.location?.origin) {
    return `${window.location.origin}/wp-json/${MRT_REST_NAMESPACE}/`;
  }
  return `/wp-json/${MRT_REST_NAMESPACE}/`;
}

export function resolveMrtRestNonce(
  config: Pick<MrtRestUrlConfig, 'restNonce'>,
): string {
  return config.restNonce || '';
}

export function buildMrtRestUrl(
  base: string,
  path: string,
  query?: Record<string, string | number>,
): string {
  const cleanPath = path.replace(/^\/+/, '').split('?')[0] ?? '';
  let url: URL;

  if (base.includes('rest_route=')) {
    url = new URL(base);
    const restRoute = (url.searchParams.get('rest_route') || '').replace(/\/+$/, '');
    const fullRoute = `${restRoute}/${cleanPath}`.replace(/\/{2,}/g, '/');
    url.searchParams.set('rest_route', fullRoute);
  } else {
    const normalized = normalizeMrtRestBase(base);
    url = new URL(cleanPath, normalized);
  }

  if (query) {
    for (const [key, value] of Object.entries(query)) {
      if (value !== undefined && value !== '') {
        url.searchParams.set(key, String(value));
      }
    }
  }

  return url.toString();
}

/** Join configured restUrl with a relative route path. */
export function buildMrtRestUrlFromConfig(
  config: MrtRestUrlConfig,
  path: string,
  query?: Record<string, string | number>,
): string {
  return buildMrtRestUrl(resolveMrtRestBase(config), path, query);
}
