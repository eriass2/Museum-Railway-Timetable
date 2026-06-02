import { adminConfig } from '../types';
import {
  buildMrtRestUrl,
  buildMrtRestUrlFromConfig,
  resolveMrtRestNonce,
} from '../../api/restUrl';

export class AdminRestError extends Error {
  constructor(message: string) {
    super(message);
    this.name = 'AdminRestError';
  }
}

async function parseJson(res: Response): Promise<unknown> {
  try {
    return await res.json();
  } catch {
    return null;
  }
}

/** Join WP restUrl with path (/timetables). Handles plain-permalink rest_route URLs. */
export function buildAdminRestUrl(
  restUrl: string,
  path: string,
  query?: Record<string, string | number>,
): string {
  return buildMrtRestUrl(restUrl, path, query);
}

export async function adminFetch<T>(
  path: string,
  init: RequestInit = {},
  query?: Record<string, string | number>,
): Promise<T> {
  const cfg = adminConfig();
  const headers = new Headers(init.headers);
  headers.set('X-WP-Nonce', resolveMrtRestNonce(cfg));
  if (init.body && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }
  const res = await fetch(buildMrtRestUrlFromConfig(cfg, path, query), {
    ...init,
    headers,
    credentials: 'same-origin',
  });
  const json = await parseJson(res);
  if (!res.ok) {
    const msg =
      json && typeof json === 'object' && 'message' in json
        ? String((json as { message: string }).message)
        : `HTTP ${res.status}`;
    throw new AdminRestError(msg);
  }
  return json as T;
}

export async function adminUpload<T>(path: string, body: FormData): Promise<T> {
  const cfg = adminConfig();
  const res = await fetch(buildMrtRestUrlFromConfig(cfg, path), {
    method: 'POST',
    headers: { 'X-WP-Nonce': resolveMrtRestNonce(cfg) },
    credentials: 'same-origin',
    body,
  });
  const json = await parseJson(res);
  if (!res.ok) {
    const msg =
      json && typeof json === 'object' && 'message' in json
        ? String((json as { message: string }).message)
        : `HTTP ${res.status}`;
    throw new AdminRestError(msg);
  }
  return json as T;
}
