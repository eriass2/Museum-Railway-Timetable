import type { MrtRestConfig } from '../config/types';
import { configureMrtLog, mrtLog, resolveMrtLogSource } from '../utils/mrtLog';
import { resolveMrtString } from '../utils/mrtStrings';
import { buildMrtRestUrlFromConfig, resolveMrtRestNonce } from './restUrl';
import { logMrtRestTiming } from './mrtRestTiming';

export type MrtRestResponse<T> = {
  success: boolean;
  data?: T;
  message?: string;
};

export type MrtRestRequestInit = {
  method: 'GET' | 'POST';
  path: string;
  query?: Record<string, string | number>;
  body?: Record<string, unknown>;
};

function compactQuery(
  query?: Record<string, string | number>,
): Record<string, string | number> | undefined {
  if (!query) {
    return undefined;
  }
  const out: Record<string, string | number> = {};
  for (const [key, value] of Object.entries(query)) {
    if (value !== undefined && value !== '') {
      out[key] = value;
    }
  }
  return Object.keys(out).length ? out : undefined;
}

function errorMessage(json: unknown, status: number, config: MrtRestConfig): string {
  if (json && typeof json === 'object') {
    if ('message' in json && typeof (json as { message: unknown }).message === 'string') {
      return (json as { message: string }).message;
    }
    if ('data' in json) {
      const data = (json as { data: unknown }).data;
      if (data && typeof data === 'object' && 'message' in data) {
        return String((data as { message: string }).message);
      }
    }
  }
  return status === 403
    ? resolveMrtString(config, 'securityCheckFailed', 'Säkerhetskontroll misslyckades.')
    : resolveMrtString(config, 'requestFailed', 'Begäran misslyckades.');
}

function logRestFailure(
  config: MrtRestConfig,
  init: MrtRestRequestInit,
  status: number,
  json: unknown,
  kind: 'http' | 'network',
): void {
  if (!config.isDevMode) {
    return;
  }
  configureMrtLog({
    isDevMode: true,
    defaultSource: resolveMrtLogSource(config),
  });
  mrtLog({
    level: 'error',
    source: resolveMrtLogSource(config),
    message:
      kind === 'network'
        ? `REST ${init.method} ${init.path} network error`
        : `REST ${init.method} ${init.path} → ${status}`,
    context: kind === 'http' ? { response: json } : undefined,
  });
}

function requestHeaders(method: 'GET' | 'POST', config: MrtRestConfig): Record<string, string> {
  const headers: Record<string, string> = {
    'X-WP-Nonce': resolveMrtRestNonce(config),
  };
  if (method === 'POST') {
    headers['Content-Type'] = 'application/json';
  }
  return headers;
}

export async function mrtRestRequest<T>(
  config: MrtRestConfig,
  init: MrtRestRequestInit,
): Promise<MrtRestResponse<T>> {
  const path = init.path.replace(/^\/+/, '');
  const url = buildMrtRestUrlFromConfig(config, path, compactQuery(init.query));
  const requestInit: RequestInit = {
    method: init.method,
    headers: requestHeaders(init.method, config),
    credentials: 'same-origin',
  };
  if (init.method === 'POST') {
    requestInit.body = JSON.stringify(init.body ?? {});
  }

  const started = performance.now();
  try {
    const res = await fetch(url, requestInit);
    const json = await res.json().catch(() => null);
    if (!res.ok) {
      logRestFailure(config, init, res.status, json, 'http');
      logMrtRestTiming(config, init, performance.now() - started, false);
      return { success: false, message: errorMessage(json, res.status, config) };
    }
    logMrtRestTiming(config, init, performance.now() - started, true);
    return { success: true, data: json as T };
  } catch {
    logRestFailure(config, init, 0, null, 'network');
    logMrtRestTiming(config, init, performance.now() - started, false);
    return {
      success: false,
      message: resolveMrtString(config, 'networkError', 'Nätverksfel. Försök igen.'),
    };
  }
}
