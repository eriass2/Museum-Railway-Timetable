import { adminFetch } from './adminRestCore';

export function listTimetables() {
  return adminFetch<{ items: import('../types').TimetableListItem[] }>(
    '/timetables',
  );
}

export function getTimetable(id: number) {
  return adminFetch<import('../types').TimetableDetail>(`/timetables/${id}`);
}

export function createTimetable(body: { title: string; type?: string }) {
  return adminFetch<import('../types').TimetableDetail>('/timetables', {
    method: 'POST',
    body: JSON.stringify(body),
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

export function updateTimetableService(
  timetableId: number,
  serviceId: number,
  body: {
    route_id: number;
    train_type_id?: number;
    end_station_id?: number;
    service_number?: string;
    highlight_label?: string;
    highlight_color?: string;
    highlight_note?: string;
  },
) {
  return adminFetch<import('../types').TimetableServiceRow>(
    `/timetables/${timetableId}/services/${serviceId}`,
    { method: 'PATCH', body: JSON.stringify(body) },
  );
}

export function removeTimetableService(timetableId: number, serviceId: number) {
  return adminFetch<{ removed: boolean }>(
    `/timetables/${timetableId}/services/${serviceId}`,
    { method: 'DELETE' },
  );
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

export function getRouteDestinations(routeId: number) {
  return adminFetch<{ destinations: { id: number; name: string }[] }>(
    `/routes/${routeId}/destinations`,
  );
}
