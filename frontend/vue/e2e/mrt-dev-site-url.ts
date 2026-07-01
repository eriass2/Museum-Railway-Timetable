import { existsSync, readFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { resolveMrtDevSiteUrl, type MrtDevSiteEnv } from '../src/utils/mrtDevSiteUrl';

function readRepoDotEnv(): Partial<MrtDevSiteEnv> {
  const root = resolve(dirname(fileURLToPath(import.meta.url)), '../../..');
  const envPath = resolve(root, '.env');
  if (!existsSync(envPath)) {
    return {};
  }

  const out: Partial<MrtDevSiteEnv> = {};
  for (const line of readFileSync(envPath, 'utf8').split('\n')) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith('#')) {
      continue;
    }
    const eq = trimmed.indexOf('=');
    if (eq <= 0) {
      continue;
    }
    const key = trimmed.slice(0, eq).trim();
    if (key !== 'MRT_WP_PORT' && key !== 'MRT_DEV_SITE_URL') {
      continue;
    }
    const value = trimmed.slice(eq + 1).trim().replace(/^["']|["']$/g, '');
    out[key as keyof MrtDevSiteEnv] = value;
  }
  return out;
}

/** Base URL for local WordPress E2E (env vars, then repo .env, then port 8080). */
export function mrtDevSiteUrl(): string {
  const fromProcess: MrtDevSiteEnv = {
    MRT_E2E_WP_SITE_URL: process.env.MRT_E2E_WP_SITE_URL,
    MRT_DEV_SITE_URL: process.env.MRT_DEV_SITE_URL,
    MRT_WP_PORT: process.env.MRT_WP_PORT,
  };

  if (
    fromProcess.MRT_E2E_WP_SITE_URL ||
    fromProcess.MRT_DEV_SITE_URL ||
    fromProcess.MRT_WP_PORT
  ) {
    return resolveMrtDevSiteUrl(fromProcess);
  }

  return resolveMrtDevSiteUrl(fromProcess, readRepoDotEnv());
}

export { resolveMrtDevSiteUrl };
