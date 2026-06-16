import { adminFetch } from './adminRestCore';

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

export function listLines() {
  return adminFetch<{ items: import('../types').LineRow[] }>('/lines');
}

export function updateLine(
  code: string,
  body: Pick<import('../types').LineRow, 'title'> & { station_ids?: number[] },
) {
  return adminFetch<import('../types').LineRow>(`/lines/${encodeURIComponent(code)}`, {
    method: 'PATCH',
    body: JSON.stringify(body),
  });
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
