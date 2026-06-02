import type { JourneyConnection } from '../shared/journey';

export type TripPriceLeg = {
  service_id: number;
  from_station_id: number;
  to_station_id: number;
};

export function connectionToPriceLegs(
  conn: JourneyConnection,
  fromId: number,
  toId: number,
): TripPriceLeg[] {
  if (conn.legs && conn.legs.length > 0) {
    return conn.legs
      .filter(
        (leg) =>
          (leg.service_id ?? 0) > 0
          && (leg.from_station_id ?? 0) > 0
          && (leg.to_station_id ?? 0) > 0,
      )
      .map((leg) => ({
        service_id: leg.service_id!,
        from_station_id: leg.from_station_id!,
        to_station_id: leg.to_station_id!,
      }));
  }
  if ((conn.service_id ?? 0) > 0 && fromId > 0 && toId > 0) {
    return [
      {
        service_id: conn.service_id,
        from_station_id: fromId,
        to_station_id: toId,
      },
    ];
  }
  return [];
}

export function encodeTripPriceLegs(legs: TripPriceLeg[]): string {
  return legs.length > 0 ? JSON.stringify(legs) : '';
}
