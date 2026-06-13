import { computed, ref, watch } from 'vue';
import { getPrices, listLines, listRoutes, listStations } from '../../api/adminRest';
import { useAdminResource } from '../useAdminResource';
import { adminErrorMessage } from '../../utils/adminLabels';
import { resolveStationPriceZoneOptions, stationMissingPriceZone } from '../../utils/stations-routes/stationPriceZones';
import { syncRouteTermini } from '../../utils/stations-routes/routeStationEditor';
import { adminConfig } from '../../types';
import type { LineRow, RouteRow, StationRow } from '../../types';

export type StationsRoutesSectionTab = 'stations' | 'lines' | 'routes';

async function fetchLineRows(): Promise<LineRow[]> {
  try {
    const payload = await listLines();
    return payload?.items ?? [];
  } catch {
    return [];
  }
}

export function useStationsRoutesData() {
  const cfg = adminConfig();
  const stations = ref<StationRow[]>([]);
  const routes = ref<RouteRow[]>([]);
  const lines = ref<LineRow[]>([]);
  const priceZoneOptions = ref<number[]>([...resolveStationPriceZoneOptions(undefined)]);
  const showMissingZonesOnly = ref(false);

  const { loading, error, data, load, reload } = useAdminResource({
    fetch: async () => {
      const [s, lineRows, prices] = await Promise.all([
        listStations(),
        fetchLineRows(),
        getPrices().catch(() => null),
      ]);
      const routesPayload =
        lineRows.length > 0 ? null : await listRoutes().catch(() => null);
      return {
        stations: s?.items ?? [],
        routes: routesPayload?.items ?? [],
        lines: lineRows,
        priceZones: prices?.zones,
      };
    },
    errorMessage: (e) => adminErrorMessage(cfg, e, 'loadFailed'),
  });

  watch(
    data,
    (payload) => {
      if (!payload) {
        return;
      }
      priceZoneOptions.value = resolveStationPriceZoneOptions(payload.priceZones);
      stations.value = payload.stations.map((row) => ({
        ...row,
        price_zones: row.price_zones ?? [],
        train_change_map: row.train_change_map ?? {},
      }));
      routes.value = payload.routes.map((row) => syncRouteTermini({ ...row }));
      lines.value = payload.lines ?? [];
    },
    { immediate: true },
  );

  const hasLineRegistry = computed(() => lines.value.length > 0);

  const stationsById = computed(
    () =>
      new Map(
        stations.value.map((st) => [
          st.id,
          { title: st.title, station_type: st.station_type },
        ]),
      ),
  );

  const visibleStations = computed(() => {
    if (!showMissingZonesOnly.value) {
      return stations.value;
    }
    return stations.value.filter((st) => stationMissingPriceZone(st));
  });

  function stationTitle(stationId: number): string {
    return stations.value.find((s) => s.id === stationId)?.title || String(stationId);
  }

  return {
    cfg,
    stations,
    routes,
    lines,
    priceZoneOptions,
    showMissingZonesOnly,
    hasLineRegistry,
    stationsById,
    visibleStations,
    loading,
    error,
    load,
    reload,
    stationTitle,
  };
}
