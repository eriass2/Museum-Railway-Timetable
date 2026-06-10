import { describe, expect, it } from 'vitest';
import {
  connectionLegItems,
  connectionRouteText,
  connectionTimeRange,
} from '../src/wizard/composables/useConnectionLegDisplay';
import type { JourneyConnection } from '../src/wizard/types';

const stations = [
  { id: 1, title: 'Uppsala Östra' },
  { id: 2, title: 'Marielund' },
  { id: 3, title: 'Faringe' },
];

const transferConnection: JourneyConnection = {
  service_id: 1,
  connection_type: 'transfer',
  from_departure: '10:00',
  to_arrival: '11:25',
  legs: [
    {
      service_id: 71,
      service_number: '71',
      train_type: 'Ångtåg',
      from_station_id: 1,
      to_station_id: 2,
      from_departure: '10:00',
      to_arrival: '10:35',
      destination: 'Marielund',
    },
    {
      service_id: 61,
      service_number: '61',
      train_type: 'Dieseltåg',
      from_station_id: 2,
      to_station_id: 3,
      from_departure: '10:45',
      to_arrival: '11:25',
      destination: 'Faringe',
    },
  ],
};

describe('useConnectionLegDisplay helpers', () => {
  it('formats route text by leg context', () => {
    expect(connectionRouteText('outbound', 'Uppsala Östra', 'Faringe')).toBe(
      'Uppsala Östra → Faringe',
    );
    expect(connectionRouteText('return', 'Uppsala Östra', 'Faringe')).toBe(
      'Faringe → Uppsala Östra',
    );
  });

  it('formats door-to-door time range', () => {
    expect(connectionTimeRange(transferConnection)).toBe('10.00 – 11.25');
  });

  it('builds leg items with transfer row', () => {
    const items = connectionLegItems(transferConnection, stations, {});
    expect(items).toHaveLength(3);
    expect(items[0].type).toBe('leg');
    expect(items[1].type).toBe('transfer');
    expect(items[2].type).toBe('leg');
  });
});
