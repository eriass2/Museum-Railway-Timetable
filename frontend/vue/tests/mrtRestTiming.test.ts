import { describe, expect, it } from 'vitest';
import { buildMrtRestTimingKey } from '../src/api/mrtRestTiming';

describe('buildMrtRestTimingKey', () => {
  it('includes sorted query params and summarizes leg JSON', () => {
    const legs = JSON.stringify([{ service_id: 10, from_station_id: 1, to_station_id: 5 }]);
    const key = buildMrtRestTimingKey({
      method: 'GET',
      path: 'prices/trip',
      query: {
        to_id: 5,
        from_id: 1,
        trip_type: 'return',
        outbound_legs: legs,
        inbound_legs: '',
      },
    });
    expect(key).toContain('GET prices/trip');
    expect(key).toContain('from_id=1');
    expect(key).toContain('outbound_legs=legs:1');
    expect(key).not.toContain('service_id');
  });

  it('includes POST body station and date fields', () => {
    const key = buildMrtRestTimingKey({
      method: 'POST',
      path: 'journey/search',
      body: {
        from_station: 1,
        to_station: 2,
        date: '2026-05-15',
        notice: 'Inställd tur',
      },
    });
    expect(key).toBe('POST journey/search | from=1&to=2&date=2026-05-15');
  });
});
