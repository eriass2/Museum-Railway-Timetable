import type { StopTimeRow } from '../../types';

/** Map editor rows to REST stop-times payload. */
export function stopTimesToApiPayload(rows: StopTimeRow[]): Record<string, unknown>[] {
  return rows.map((s) => ({
    station_id: s.id,
    stops_here: s.stops_here ? '1' : '0',
    arrival: s.arrival_time || '',
    departure: s.departure_time || '',
    pickup: s.pickup_allowed ? '1' : '',
    dropoff: s.dropoff_allowed ? '1' : '',
  }));
}
