import { describe, expect, it } from 'vitest';
import {
  buildTransferLabel,
  stationTitleLookup,
} from '../src/shared/connectionLegDisplay';
import { waitMinutesBetween } from '../src/shared/tripClock';
import { buildConnectionLegSummary } from '../src/wizard/utils/buildConnectionLegSummary';
import { transferLabelStringsFromCfg } from '../src/wizard/utils/wizardLabels';
import type { JourneyConnection } from '../src/shared/journey';

const stations = [
  { id: 1, title: 'Uppsala Östra' },
  { id: 2, title: 'Selknä' },
  { id: 3, title: 'Fjällnora' },
];

const stationTitle = stationTitleLookup(stations);
const transferStrings = {
  changeAt: 'Byte vid %s',
  transferTrip: 'Byte',
};

const transferConnection: JourneyConnection = {
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
      destination: 'Fjällnora',
    },
  ],
};

describe('buildTransferLabel', () => {
  it('includes station and wait time', () => {
    const [leg1, leg2] = transferConnection.legs!;
    const label = buildTransferLabel(leg1, leg2, transferConnection, stationTitle, transferStrings);
    expect(label).toBe('Byte vid Selknä · 8 min');
  });

  it('uses per-leg times when connection transfer_wait_minutes is first-transfer only', () => {
    const marielundSelknaStations = stationTitleLookup([
      { id: 1, title: 'Uppsala Östra' },
      { id: 2, title: 'Marielund' },
      { id: 3, title: 'Selknä' },
      { id: 4, title: 'Fjällnora' },
    ]);
    const threeLegConnection: JourneyConnection = {
      service_id: 0,
      connection_type: 'transfer',
      transfer_station_id: 2,
      transfer_wait_minutes: 10,
      legs: [
        {
          service_id: 71,
          to_station_id: 2,
          to_arrival: '10:35',
        },
        {
          service_id: 61,
          from_station_id: 2,
          from_departure: '10:45',
          to_station_id: 3,
          to_arrival: '10:50',
        },
        {
          service_id: 701,
          from_station_id: 3,
          from_departure: '10:53',
          to_station_id: 4,
        },
      ],
    };
    const [leg1, leg2, leg3] = threeLegConnection.legs!;
    expect(
      buildTransferLabel(leg1, leg2, threeLegConnection, marielundSelknaStations, transferStrings),
    ).toBe('Byte vid Marielund · 10 min');
    expect(
      buildTransferLabel(leg2, leg3, threeLegConnection, marielundSelknaStations, transferStrings),
    ).toBe('Byte vid Selknä · 3 min');
  });

  it('falls back to computed wait when transfer_wait_minutes is missing', () => {
    const label = buildTransferLabel(
      { service_id: 0, to_station_id: 2, to_arrival: '17:14' },
      { service_id: 0, from_station_id: 2, from_departure: '17:22' },
      { service_id: 0 },
      stationTitle,
      transferStrings,
    );
    expect(label).toBe('Byte vid Selknä · 8 min');
  });

  it('matches summary and detail expand wording for the same connection', () => {
    const [leg1, leg2] = transferConnection.legs!;
    const detailLabel = buildTransferLabel(
      leg1,
      leg2,
      transferConnection,
      stationTitle,
      transferStrings,
    );
    const summaryItems = buildConnectionLegSummary(transferConnection, stationTitle, {
      changeAt: 'Byte vid %s',
      transferTrip: 'Byte',
    });
    const summaryTransfer = summaryItems.find((item) => item.type === 'transfer');
    expect(summaryTransfer?.type).toBe('transfer');
    if (summaryTransfer?.type === 'transfer') {
      expect(detailLabel).toBe(summaryTransfer.label);
    }
  });
});

describe('buildConnectionLegSummary', () => {
  it('builds leg cards with transfer between segments', () => {
    const items = buildConnectionLegSummary(transferConnection, stationTitle, {
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
        vehicleLabel: 'Veteranbuss mot Fjällnora',
        timeRange: '17.22 – 17.30',
        route: 'Selknä → Fjällnora',
      },
    });
  });
});

describe('transferLabelStringsFromCfg', () => {
  it('reads changeAt and transferTrip from wizard cfg', () => {
    expect(
      transferLabelStringsFromCfg({
        changeAt: 'Byte vid %s',
        transferTrip: 'Byte',
      }),
    ).toEqual({
      changeAt: 'Byte vid %s',
      transferTrip: 'Byte',
    });
  });
});

describe('waitMinutesBetween', () => {
  it('computes minutes between arrival and departure', () => {
    expect(waitMinutesBetween('17:14', '17:22')).toBe(8);
    expect(waitMinutesBetween('17:22', '17:14')).toBeNull();
  });
});
