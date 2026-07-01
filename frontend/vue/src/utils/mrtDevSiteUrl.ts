export type MrtDevSiteEnv = {
  MRT_E2E_WP_SITE_URL?: string;
  MRT_DEV_SITE_URL?: string;
  MRT_WP_PORT?: string;
};

/** Resolve dev WordPress base URL (same rules as scripts/lib/mrt/env.sh). */
export function resolveMrtDevSiteUrl(
  env: MrtDevSiteEnv,
  dotenv: Partial<MrtDevSiteEnv> = {},
): string {
  const explicit = String(
    env.MRT_E2E_WP_SITE_URL || env.MRT_DEV_SITE_URL || dotenv.MRT_DEV_SITE_URL || '',
  ).trim();
  if (explicit) {
    return explicit.replace(/\/$/, '');
  }

  const port = String(env.MRT_WP_PORT || dotenv.MRT_WP_PORT || '8080').trim() || '8080';
  return `http://localhost:${port}`;
}
