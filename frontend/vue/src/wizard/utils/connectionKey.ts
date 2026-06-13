import type { JourneyConnection } from '../types';

/** Stable list key for connection cards (order may change). */
export function connectionListKey(conn: JourneyConnection): string {
  const departure = conn.from_departure ?? conn.departure ?? '';
  const arrival = conn.to_arrival ?? conn.arrival ?? '';
  const transfer = conn.transfer_station_id ?? '';
  return `${conn.service_id}:${departure}:${arrival}:${transfer}`;
}
