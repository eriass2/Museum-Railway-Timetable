import { describe, expect, it } from 'vitest';
import type { JourneyConnection } from '../src/shared/journey';
import {
  connectionToPriceLegs,
  encodeTripPriceLegs,
} from '../src/shared/connectionPriceLegs';

describe('connectionPriceLegs', () => {
  it('builds a single leg from direct connection', () => {
    const conn: JourneyConnection = { service_id: 42 };
    expect(connectionToPriceLegs(conn, 1, 9)).toEqual([
      { service_id: 42, from_station_id: 1, to_station_id: 9 },
    ]);
  });

  it('maps multi-leg connections', () => {
    const conn: JourneyConnection = {
      service_id: 0,
      legs: [
        {
          service_id: 10,
          from_station_id: 1,
          to_station_id: 5,
        },
        {
          service_id: 11,
          from_station_id: 5,
          to_station_id: 9,
        },
      ],
    };
    expect(connectionToPriceLegs(conn, 1, 9)).toHaveLength(2);
  });

  it('encodes legs as JSON for REST', () => {
    const legs = connectionToPriceLegs({ service_id: 3 }, 1, 2);
    expect(encodeTripPriceLegs(legs)).toBe(
      JSON.stringify([{ service_id: 3, from_station_id: 1, to_station_id: 2 }]),
    );
    expect(encodeTripPriceLegs([])).toBe('');
  });
});
