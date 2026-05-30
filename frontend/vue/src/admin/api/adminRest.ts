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

/** Join WP restUrl (often trailing slash) with path (/timetables). */
export function buildAdminRestUrl(restUrl: string, path: string): string {
  const base = restUrl.replace(/\/+$/, '');
  const suffix = path.startsWith('/') ? path : `/${path}`;
  return `${base}${suffix}`;
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
  const res = await fetch(buildAdminRestUrl(cfg.restUrl, path), {
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

export function cancelTrafficToday(date?: string, notice = 'Inställd') {
  return adminFetch<{ date: string; notice: string; services_updated: number }>(
    '/operations/cancel-traffic',
    {
      method: 'POST',
      body: JSON.stringify({ date, notice }),
    },
  );
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

export function deleteTimetable(id: number) {
  return adminFetch<{ deleted: boolean }>(`/timetables/${id}`, { method: 'DELETE' });
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

export function quickDeparture(serviceId: number, departure: string) {
  return adminFetch<{ saved: boolean }>(`/services/${serviceId}/departure`, {
    method: 'PUT',
    body: JSON.stringify({ departure }),
  });
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

export function deleteStation(id: number) {
  return adminFetch<{ deleted: boolean }>(`/stations/${id}`, { method: 'DELETE' });
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

export function deleteRoute(id: number) {
  return adminFetch<{ deleted: boolean }>(`/routes/${id}`, { method: 'DELETE' });
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

export function listTrainTypes() {
  return adminFetch<{ items: import('../types').TrainTypeRow[]; icon_keys: string[] }>(
    '/train-types',
  );
}

export function createTrainType(body: { name: string; slug?: string; icon_key?: string }) {
  return adminFetch<import('../types').TrainTypeRow>('/train-types', {
    method: 'POST',
    body: JSON.stringify(body),
  });
}

export function updateTrainType(
  id: number,
  body: Partial<{ name: string; slug: string; icon_key: string }>,
) {
  return adminFetch<import('../types').TrainTypeRow>(`/train-types/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(body),
  });
}

export function deleteTrainType(id: number) {
  return adminFetch<{ deleted: boolean }>(`/train-types/${id}`, { method: 'DELETE' });
}

export function exportCsv(options: { include_prices?: boolean; include_settings?: boolean }) {
  const params = new URLSearchParams();
  if (options.include_prices !== undefined) {
    params.set('include_prices', options.include_prices ? '1' : '0');
  }
  if (options.include_settings !== undefined) {
    params.set('include_settings', options.include_settings ? '1' : '0');
  }
  const qs = params.toString();
  return adminFetch<{ filename: string; content_base64: string }>(
    `/export/csv${qs ? `?${qs}` : ''}`,
  );
}

export function importCsv(file: File, mode: 'merge' | 'override') {
  const cfg = adminConfig();
  const body = new FormData();
  body.append('file', file);
  body.append('mode', mode);
  return fetch(buildAdminRestUrl(cfg.restUrl, '/import/csv'), {
    method: 'POST',
    headers: { 'X-WP-Nonce': cfg.restNonce },
    credentials: 'same-origin',
    body,
  }).then(async (res) => {
    const json = await res.json().catch(() => null);
    if (!res.ok) {
      const msg =
        json && typeof json === 'object' && 'message' in json
          ? String((json as { message: string }).message)
          : `HTTP ${res.status}`;
      throw new AdminRestError(msg);
    }
    return json as { imported: boolean; stats: Record<string, number>; mode: string };
  });
}

export function devClearDatabase() {
  return adminFetch<{ cleared: boolean }>('/dev/clear-db', { method: 'POST' });
}

export function devImportLennakatten() {
  return adminFetch<{ imported: boolean }>('/dev/import-lennakatten', { method: 'POST' });
}

export function devCreateDemoPage() {
  return adminFetch<{ page_id: number }>('/dev/demo-page', { method: 'POST' });
}

export function devSetupNavigation() {
  return adminFetch<{ menu_id: number; added: number }>('/dev/setup-navigation', {
    method: 'POST',
  });
}

export function devSyncTimetablePages() {
  return adminFetch<{ index_page_id: number; timetable_page_ids: Record<string, number> }>(
    '/dev/sync-timetable-pages',
    { method: 'POST' },
  );
}
