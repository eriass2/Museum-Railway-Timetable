import type { JourneyConnection } from '../../shared/journey';

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
      service_number: conn.service_number,
      train_type: conn.train_type,
      train_type_slug: conn.train_type_slug,
      train_type_icon: conn.train_type_icon,
      destination: conn.destination,
      from_departure: departureFromOrigin(conn),
      to_arrival: arrivalAtDestination(conn),
    },
  ];
}

export function isTransfer(conn: JourneyConnection): boolean {
  return conn.connection_type === 'transfer' || (conn.legs?.length ?? 0) > 1;
}
