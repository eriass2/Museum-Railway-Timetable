import type { LineRow, RouteRow } from '../../types';
import {
  applyRouteStationMove,
  removeRouteStation,
  syncRouteTermini,
} from './routeStationEditor';

export function lineRowToRouteDraft(line: LineRow): RouteRow {
  return {
    id: 0,
    title: line.title,
    start_station: line.start_station,
    end_station: line.end_station,
    station_ids: [...line.station_ids],
    stations: [],
  };
}

export function applyRouteDraftToLine(line: LineRow, route: RouteRow): LineRow {
  const synced = syncRouteTermini(route);
  return {
    ...line,
    station_ids: synced.station_ids,
    start_station: synced.start_station,
    end_station: synced.end_station,
  };
}

export function applyLineStationMove(line: LineRow, idx: number, dir: -1 | 1): LineRow {
  return applyRouteDraftToLine(line, applyRouteStationMove(lineRowToRouteDraft(line), idx, dir));
}

export function removeLineStation(line: LineRow, idx: number): LineRow {
  return applyRouteDraftToLine(line, removeRouteStation(lineRowToRouteDraft(line), idx));
}
