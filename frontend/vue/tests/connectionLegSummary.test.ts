import { describe, expect, it } from 'vitest';
import {
  buildConnectionLegSummary,
  stationTitleLookup,
  transferLabelBetween,
  waitMinutesBetween,
} from '../src/wizard/utils/connectionLegSummary';
import type { JourneyConnection } from '../src/wizard/types';

const stations = [
  { id: 1, title: 'Uppsala Östra' },
  { id: 2, title: 'Selknä' },
  { id: 3, title: 'Fjällnora' },
];

const stationTitle = stationTitleLookup(stations);

describe('connectionLegSummary', () => {
  it('builds leg cards with transfer between segments', () => {
    const connection: JourneyConnection = {
      service_id: 0,
      connection_type: 'transfer',
      transfer_station_id: 2,
      transfer_wait_minutes: 8,
      from_departure: '13:55',
      to_arrival: '17:30',
      legs: [
        {
          service_id: 77,
          service_number: '77',
          train_type: 'Ångtåg',
          from_station_id: 1,
          to_station_id: 2,
          from_departure: '13:55',
          to_arrival: '17:14',
          destination: 'Faringe',
        },
        {
          service_id: 701,
          service_number: 'B1',
          train_type: 'Buss',
          train_type_slug: 'buss',
          from_station_id: 2,
          to_station_id: 3,
          from_departure: '17:22',
          to_arrival: '17:30',
          destination: 'Uppsala Östra',
        },
      ],
    };

    const items = buildConnectionLegSummary(connection, stationTitle, {
      changeAt: 'Byte vid %s',
      transferTrip: 'Byte',
    });

    expect(items).toHaveLength(3);
    expect(items[0]).toMatchObject({
      type: 'leg',
      leg: {
        vehicleLabel: 'Ångtåg 77 mot Faringe',
        timeRange: '13.55 – 17.14',
        route: 'Uppsala Östra → Selknä',
      },
    });
    expect(items[1]).toEqual({
      type: 'transfer',
      label: 'Byte vid Selknä · 8 min',
    });
    expect(items[2]).toMatchObject({
      type: 'leg',
      leg: {
        vehicleLabel: 'Buss B1 mot Uppsala Östra',
        timeRange: '17.22 – 17.30',
        route: 'Selknä → Fjällnora',
      },
    });
  });

  it('computes wait minutes between arrival and departure', () => {
    expect(waitMinutesBetween('17:14', '17:22')).toBe(8);
    expect(waitMinutesBetween('17:22', '17:14')).toBeNull();
  });

  it('falls back to computed wait when transfer_wait_minutes is missing', () => {
    const label = transferLabelBetween(
      { to_station_id: 2, to_arrival: '17:14' },
      { from_station_id: 2, from_departure: '17:22' },
      {},
      stationTitle,
      { changeAt: 'Byte vid %s' },
    );
    expect(label).toBe('Byte vid Selknä · 8 min');
  });
});
