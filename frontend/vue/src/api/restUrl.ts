/**
 * Build a Museum Railway Timetable REST URL from WP restUrl + relative path.
 * Supports pretty permalinks (/wp-json/...) and plain (?rest_route=...) installs.
 */
export function buildMrtRestUrl(
  base: string,
  path: string,
  query?: Record<string, string | number>,
): string {
  const cleanPath = path.replace(/^\/+/, '');
  let url: URL;

  if (base.includes('rest_route=')) {
    url = new URL(base);
    const restRoute = (url.searchParams.get('rest_route') || '').replace(/\/+$/, '');
    const fullRoute = `${restRoute}/${cleanPath}`.replace(/\/{2,}/g, '/');
    url.searchParams.set('rest_route', fullRoute);
  } else {
    const normalized = base.endsWith('/') ? base : `${base}/`;
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
