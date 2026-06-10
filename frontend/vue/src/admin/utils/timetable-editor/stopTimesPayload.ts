import type { StopTimeRow } from '../../types';

/** Map editor rows to REST stop-times payload. */
export function stopTimesToApiPayload(rows: StopTimeRow[]): Record<string, unknown>[] {
  return rows.map((s) => ({
    station_id: s.id,
    stops_here: s.stops_here ? '1' : '0',
    arrival: s.arrival_time || '',
    departure: s.departure_time || '',
    pickup_mode: s.pickup_mode,
    dropoff_mode: s.dropoff_mode,
    approximate: s.approximate_time ? '1' : '',
    in_service_timetable: s.in_service_timetable === false ? 0 : 1,
  }));
}
