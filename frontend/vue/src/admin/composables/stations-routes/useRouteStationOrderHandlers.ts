import type { Ref } from 'vue';
import type { RouteRow } from '../../types';
import {
  applyRouteStationMove,
  removeRouteStation,
} from '../../utils/stations-routes/routeStationEditor';

export function useRouteStationOrderHandlers(routeRef: Ref<RouteRow | null>) {
  function onMove(idx: number, dir: -1 | 1): void {
    const route = routeRef.value;
    if (!route) {
      return;
    }
    routeRef.value = applyRouteStationMove(route, idx, dir);
  }

  function onRemove(idx: number): void {
    const route = routeRef.value;
    if (!route) {
      return;
    }
    Object.assign(route, removeRouteStation(route, idx));
  }

  return { onMove, onRemove };
}
