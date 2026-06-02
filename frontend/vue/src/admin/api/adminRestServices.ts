import { adminFetch } from './adminRestCore';

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
