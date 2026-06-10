import { describe, expect, it } from 'vitest';
import type { RouteRow } from '../src/admin/types';
import {
  appendRouteStation,
  deriveRouteTermini,
  emptyRouteDraft,
  moveRouteStation,
  removeRouteStation,
  routeStationRoleFor,
  syncRouteTermini,
} from '../src/admin/utils/stations-routes/routeStationEditor';

describe('routeStationEditor', () => {
  const route = (): RouteRow => ({
    id: 1,
    title: 'Test',
    start_station: 10,
    end_station: 30,
    station_ids: [10, 20, 30],
    stations: [],
  });

  it('detects start, end, and both roles', () => {
    expect(routeStationRoleFor(route(), 10)).toBe('start');
    expect(routeStationRoleFor(route(), 30)).toBe('end');
    expect(routeStationRoleFor({ ...route(), end_station: 10 }, 10)).toBe('both');
    expect(routeStationRoleFor(route(), 20)).toBeNull();
  });

  it('reorders and appends stations', () => {
    expect(moveRouteStation([1, 2, 3], 1, -1)).toEqual([2, 1, 3]);
    expect(appendRouteStation([1, 2], 3)).toEqual([1, 2, 3]);
    expect(appendRouteStation([1, 2], 2)).toEqual([1, 2]);
  });

  it('derives termini from first and last station', () => {
    expect(deriveRouteTermini([10, 20, 30])).toEqual({
      start_station: 10,
      end_station: 30,
    });
    expect(deriveRouteTermini([])).toEqual({ start_station: 0, end_station: 0 });
  });

  it('syncRouteTermini overwrites manual endpoints', () => {
    expect(
      syncRouteTermini({
        ...route(),
        start_station: 30,
        end_station: 10,
      }),
    ).toEqual(route());
  });

  it('removes station and re-derives termini', () => {
    const updated = removeRouteStation(route(), 0);
    expect(updated.station_ids).toEqual([20, 30]);
    expect(updated.start_station).toBe(20);
    expect(updated.end_station).toBe(30);
  });

  it('emptyRouteDraft starts with no stations', () => {
    expect(emptyRouteDraft().station_ids).toEqual([]);
  });
});
