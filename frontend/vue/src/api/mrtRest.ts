import type { MrtRestConfig } from '../config/types';
import { buildMrtRestUrlFromConfig, resolveMrtRestNonce } from './restUrl';

export type MrtAjaxResponse<T> = {
  success: boolean;
  data?: T;
  message?: string;
};

type RouteSpec = {
  method: 'GET' | 'POST';
  path: (data: Record<string, string | number>) => string;
  queryKeys?: string[];
};

const ROUTES: Record<string, RouteSpec> = {
  mrt_search_journey: { method: 'POST', path: () => 'journey/search' },
  mrt_journey_calendar_month: { method: 'POST', path: () => 'journey/calendar' },
  mrt_journey_connection_detail: {
    method: 'POST',
    path: () => 'journey/connection-detail',
  },
  mrt_timetable_overview_data: {
    method: 'GET',
    path: (d) => `timetables/${d.timetable_id}/overview`,
  },
  mrt_get_timetable_for_date: {
    method: 'GET',
    path: () => 'timetables/day',
    queryKeys: ['date', 'train_type'],
  },
};

function buildUrl(
  config: MrtRestConfig,
  path: string,
  data: Record<string, string | number>,
  queryKeys?: string[],
): string {
  const query: Record<string, string | number> = {};
  if (queryKeys) {
    for (const key of queryKeys) {
      if (data[key] !== undefined && data[key] !== '') {
        query[key] = data[key];
      }
    }
  }
  return buildMrtRestUrlFromConfig(
    config,
    path,
    Object.keys(query).length ? query : undefined,
  );
}

function errorMessage(json: unknown, status: number): string {
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
  return status === 403 ? 'Säkerhetskontroll misslyckades.' : 'Begäran misslyckades';
}

export async function mrtRestRequest<T>(
  config: MrtRestConfig,
  action: string,
  data: Record<string, string | number> = {},
): Promise<MrtAjaxResponse<T>> {
  const route = ROUTES[action];
  if (!route) {
    return { success: false, message: `Okänd åtgärd: ${action}` };
  }

  const url = buildUrl(config, route.path(data), data, route.queryKeys);
  const headers: Record<string, string> = {
    'X-WP-Nonce': resolveMrtRestNonce(config),
  };
  const init: RequestInit = {
    method: route.method,
    headers,
    credentials: 'same-origin',
  };
  if (route.method === 'POST') {
    headers['Content-Type'] = 'application/json';
    init.body = JSON.stringify(data);
  }

  try {
    const res = await fetch(url, init);
    const json = await res.json().catch(() => null);
    if (!res.ok) {
      return { success: false, message: errorMessage(json, res.status) };
    }
    return { success: true, data: json as T };
  } catch {
    return { success: false, message: 'Nätverksfel' };
  }
}

/** @deprecated Use mrtRestRequest — kept for gradual migration. */
export const mrtPost = mrtRestRequest;

/** @deprecated Use resolveMrtRestBase from restUrl.ts */
export { resolveMrtRestBase as restBase } from './restUrl';
