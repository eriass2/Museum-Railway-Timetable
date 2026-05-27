import type { JourneyConnection } from '../types';

export function departureFromOrigin(conn: JourneyConnection): string {
  return conn.from_departure || conn.from_arrival || conn.departure || '';
}

export function arrivalAtDestination(conn: JourneyConnection): string {
  return conn.to_arrival || conn.to_departure || conn.arrival || '';
}

export function connectionLegs(conn: JourneyConnection) {
  if (conn.legs?.length) {
    return conn.legs;
  }
  return [
    {
      service_id: conn.service_id,
      service_name: conn.service_name,
      train_type: conn.train_type,
      from_departure: departureFromOrigin(conn),
      to_arrival: arrivalAtDestination(conn),
    },
  ];
}

export function isTransfer(conn: JourneyConnection): boolean {
  return conn.connection_type === 'transfer' || (conn.legs?.length ?? 0) > 1;
}
