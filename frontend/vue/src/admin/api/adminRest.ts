import { adminConfig } from '../types';

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

export async function adminFetch<T>(
  path: string,
  init: RequestInit = {},
): Promise<T> {
  const cfg = adminConfig();
  const headers = new Headers(init.headers);
  headers.set('X-WP-Nonce', cfg.restNonce);
  if (init.body && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }
  const res = await fetch(`${cfg.restUrl}${path}`, {
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

export function getDashboard() {
  return adminFetch<import('../types').DashboardPayload>('/dashboard');
}

export function listTimetables() {
  return adminFetch<{ items: import('../types').TimetableListItem[] }>(
    '/timetables',
  );
}

export function getTimetable(id: number) {
  return adminFetch<import('../types').TimetableDetail>(`/timetables/${id}`);
}

export function createTimetable(title: string) {
  return adminFetch<import('../types').TimetableDetail>('/timetables', {
    method: 'POST',
    body: JSON.stringify({ title }),
  });
}

export function updateTimetable(
  id: number,
  body: Partial<{ title: string; type: string; dates: string[] }>,
) {
  return adminFetch<import('../types').TimetableDetail>(`/timetables/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(body),
  });
}

export function getTimetableOverview(id: number) {
  return adminFetch<{ overview: import('../../types/timetableOverview').TimetableOverviewPayload }>(
    `/timetables/${id}/overview`,
  ).then((res) => res.overview);
}

export function addTimetableService(
  timetableId: number,
  body: { route_id: number; train_type_id?: number; end_station_id?: number },
) {
  return adminFetch<Record<string, unknown>>(
    `/timetables/${timetableId}/services`,
    { method: 'POST', body: JSON.stringify(body) },
  );
}

export function removeTimetableService(timetableId: number, serviceId: number) {
  return adminFetch<{ removed: boolean }>(
    `/timetables/${timetableId}/services/${serviceId}`,
    { method: 'DELETE' },
  );
}

export function getRouteDestinations(routeId: number) {
  return adminFetch<{ destinations: { id: number; name: string }[] }>(
    `/routes/${routeId}/destinations`,
  );
}

export function getStopTimes(serviceId: number) {
  return adminFetch<{ route_id: number; stations: import('../types').StopTimeRow[] }>(
    `/services/${serviceId}/stop-times`,
  );
}

export function saveStopTimes(
  serviceId: number,
  stops: Record<string, unknown>[],
  quickEdit = false,
) {
  return adminFetch<{ route_id: number; stations: import('../types').StopTimeRow[] }>(
    `/services/${serviceId}/stop-times`,
    {
      method: 'PUT',
      body: JSON.stringify({ stops, quick_edit: quickEdit }),
    },
  );
}

export function listStations() {
  return adminFetch<{ items: import('../types').StationRow[] }>('/stations');
}

export function createStation(body: Partial<import('../types').StationRow>) {
  return adminFetch<import('../types').StationRow>('/stations', {
    method: 'POST',
    body: JSON.stringify(body),
  });
}

export function updateStation(
  id: number,
  body: Partial<import('../types').StationRow>,
) {
  return adminFetch<import('../types').StationRow>(`/stations/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(body),
  });
}

export function listRoutes() {
  return adminFetch<{ items: import('../types').RouteRow[] }>('/routes');
}

export function createRoute(body: Partial<import('../types').RouteRow>) {
  return adminFetch<import('../types').RouteRow>('/routes', {
    method: 'POST',
    body: JSON.stringify(body),
  });
}

export function updateRoute(id: number, body: Partial<import('../types').RouteRow>) {
  return adminFetch<import('../types').RouteRow>(`/routes/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(body),
  });
}

export function getDeviations(timetableId: number) {
  return adminFetch<{
    rows: {
      service_id: number;
      date: string;
      trip_label: string;
      train_type_id: number;
      notice: string;
    }[];
    timetable_dates: string[];
  }>(`/timetables/${timetableId}/deviations`);
}

export function saveDeviations(
  timetableId: number,
  byService: Record<number, Record<string, { train_type?: number; notice?: string }>>,
) {
  return adminFetch<{ saved: boolean }>(`/timetables/${timetableId}/deviations`, {
    method: 'PUT',
    body: JSON.stringify({ by_service: byService }),
  });
}

export type SettingsPayload = {
  enabled: boolean;
  note: string;
  min_transfer_minutes: number;
  max_transfer_minutes: number;
};

export function getSettings() {
  return adminFetch<SettingsPayload>('/settings');
}

export function saveSettings(body: SettingsPayload) {
  return adminFetch<SettingsPayload>('/settings', {
    method: 'PATCH',
    body: JSON.stringify(body),
  });
}

export type PricesPayload = {
  matrix: Record<string, Record<string, Record<number, number | null>>>;
  ticket_types: Record<string, string>;
  categories: Record<string, string>;
  zones: number[];
};

export function getPrices() {
  return adminFetch<PricesPayload>('/settings/prices');
}

export function savePrices(matrix: PricesPayload['matrix']) {
  return adminFetch<PricesPayload>('/settings/prices', {
    method: 'PATCH',
    body: JSON.stringify({ matrix }),
  });
}
