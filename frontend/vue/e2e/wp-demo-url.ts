/** Demo page URL for WordPress E2E (docker CI or local compose). */
export const wpDemoUrl = process.env.MRT_E2E_WP_DEMO_URL || '';

function wpSiteBase(url: string): string {
  const match = url.match(/^(https?:\/\/[^/]+)/);
  return match?.[1] ?? '';
}

/**
 * Timetable index page ([museum_timetable_index] on the public index/front page).
 * Not on the component demo page — use explicit env or site root from demo URL.
 */
export const wpIndexUrl =
  process.env.MRT_E2E_WP_INDEX_URL ||
  (wpDemoUrl ? `${wpSiteBase(wpDemoUrl)}/` : '');
