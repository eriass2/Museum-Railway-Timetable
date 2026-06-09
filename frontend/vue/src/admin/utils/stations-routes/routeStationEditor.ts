import type { RouteRow, StationRow } from '../../types';

export function emptyStationDraft(): StationRow {
  return {
    id: 0,
    title: '',
    station_type: '',
    bus_suffix: false,
    lat: '',
    lng: '',
    display_order: 0,
    price_zones: [] as number[],
    train_change_map: {},
  };
}

export function emptyRouteDraft(): RouteRow {
  return {
    id: 0,
    title: '',
    start_station: 0,
    end_station: 0,
    station_ids: [],
    stations: [],
  };
}

export function deriveRouteTermini(
  stationIds: number[],
): Pick<RouteRow, 'start_station' | 'end_station'> {
  if (!stationIds.length) {
    return { start_station: 0, end_station: 0 };
  }
  return {
    start_station: stationIds[0],
    end_station: stationIds[stationIds.length - 1],
  };
}

export function syncRouteTermini(route: RouteRow): RouteRow {
  return { ...route, ...deriveRouteTermini(route.station_ids) };
}

export function routeStationRoleFor(
  route: Pick<RouteRow, 'start_station' | 'end_station'>,
  stationId: number,
): 'start' | 'end' | 'both' | null {
  const isStart = route.start_station > 0 && stationId === route.start_station;
  const isEnd = route.end_station > 0 && stationId === route.end_station;
  if (isStart && isEnd) return 'both';
  if (isStart) return 'start';
  if (isEnd) return 'end';
  return null;
}

export function moveRouteStation(ids: number[], idx: number, dir: -1 | 1): number[] {
  const next = idx + dir;
  if (next < 0 || next >= ids.length) {
    return ids;
  }
  const copy = [...ids];
  const tmp = copy[idx];
  copy[idx] = copy[next];
  copy[next] = tmp;
  return copy;
}

export function appendRouteStation(ids: number[], stationId: number): number[] {
  if (stationId <= 0 || ids.includes(stationId)) {
    return ids;
  }
  return [...ids, stationId];
}

export function removeRouteStation(route: RouteRow, idx: number): RouteRow {
  const station_ids = route.station_ids.filter((_, i) => i !== idx);
  return syncRouteTermini({ ...route, station_ids });
}
