import { describe, expect, it } from 'vitest';
import type { StationRow } from '../src/admin/types';
import {
  formatStationPriceZones,
  resolveStationPriceZoneOptions,
  stationHasPriceZone,
  stationMissingPriceZone,
  toggleStationPriceZone,
} from '../src/admin/utils/stations-routes/stationPriceZones';

describe('stationPriceZones', () => {
  const row = (): StationRow => ({
    id: 1,
    title: 'Test',
    station_type: '',
    bus_suffix: false,
    lat: '',
    lng: '',
    display_order: 0,
    price_zones: [],
  });

  it('toggles zones up to two selections', () => {
    const st = row();
    toggleStationPriceZone(st, 1);
    toggleStationPriceZone(st, 3);
    expect(st.price_zones).toEqual([1, 3]);
    toggleStationPriceZone(st, 4);
    expect(st.price_zones).toEqual([1, 3]);
    toggleStationPriceZone(st, 1);
    expect(st.price_zones).toEqual([3]);
  });

  it('formats zone labels', () => {
    expect(formatStationPriceZones([])).toBe('—');
    expect(formatStationPriceZones([1, 2])).toBe('1, 2');
    expect(stationHasPriceZone({ ...row(), price_zones: [2] }, 2)).toBe(true);
    expect(stationMissingPriceZone(row())).toBe(true);
  });

  it('resolves zone options from schema', () => {
    expect(resolveStationPriceZoneOptions([3, 1])).toEqual([1, 3]);
    expect(resolveStationPriceZoneOptions([])).toEqual([1, 2, 3, 4]);
  });
});
