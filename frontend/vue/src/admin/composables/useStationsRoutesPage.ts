import { computed, ref, watch } from 'vue';
import {
  createRoute,
  createStation,
  deleteRoute,
  deleteStation,
  listRoutes,
  listStations,
  updateRoute,
  updateStation,
} from '../api/adminRest';
import { adminConfirm } from './adminConfirm';
import { useAdminResource } from './useAdminResource';
import { useAdminRowFlash } from './useAdminRowFlash';
import { useAdminSaveNotice } from './useAdminSaveNotice';
import { adminErrorMessage, adminFmt, adminStr } from '../utils/adminLabels';
import { emptyRouteDraft, emptyStationDraft } from '../utils/routeStationEditor';
import { adminConfig } from '../types';
import type { RouteRow, StationRow } from '../types';

export function useStationsRoutesPage() {
  const cfg = adminConfig();
  const stations = ref<StationRow[]>([]);
  const routes = ref<RouteRow[]>([]);
  const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
  const { flashRow, isFlashed } = useAdminRowFlash();
  const newStation = ref(emptyStationDraft());
  const newRoute = ref(emptyRouteDraft());
  const sectionTab = ref<'stations' | 'routes'>('stations');
  const editingRoute = ref<RouteRow | null>(null);

  const { loading, error, data, load, reload } = useAdminResource({
    fetch: async () => {
      const [s, r] = await Promise.all([listStations(), listRoutes()]);
      return { stations: s.items, routes: r.items };
    },
    errorMessage: (e) => adminErrorMessage(cfg, e, 'loadFailed'),
  });

  watch(
    data,
    (payload) => {
      if (!payload) {
        return;
      }
      stations.value = payload.stations.map((row) => ({
        ...row,
        price_zones: row.price_zones ?? [],
      }));
      routes.value = payload.routes.map((row) => ({ ...row }));
    },
    { immediate: true },
  );

  const stationsById = computed(
    () =>
      new Map(
        stations.value.map((st) => [
          st.id,
          { title: st.title, station_type: st.station_type },
        ]),
      ),
  );

  function stationTitle(stationId: number): string {
    return stations.value.find((s) => s.id === stationId)?.title || String(stationId);
  }

  async function addStation() {
    const draft = newStation.value;
    if (!cfg.canManage || !draft.title.trim()) return;
    await createStation({
      title: draft.title.trim(),
      station_type: draft.station_type || undefined,
      lat: draft.lat || undefined,
      lng: draft.lng || undefined,
      bus_suffix: draft.bus_suffix,
      display_order: draft.display_order || undefined,
      price_zones: draft.price_zones,
    });
    newStation.value = emptyStationDraft();
    await reload();
  }

  async function addRoute() {
    const draft = newRoute.value;
    if (!cfg.canManage || !draft.title.trim()) return;
    await createRoute({
      title: draft.title.trim(),
      station_ids: draft.station_ids,
      start_station: draft.start_station || undefined,
      end_station: draft.end_station || undefined,
    });
    newRoute.value = emptyRouteDraft();
    await reload();
  }

  function editRoute(route: RouteRow) {
    sectionTab.value = 'routes';
    editingRoute.value = { ...route, station_ids: [...route.station_ids] };
  }

  async function saveRoute() {
    if (!editingRoute.value || !cfg.canManage) return;
    const title = editingRoute.value.title;
    const routeId = editingRoute.value.id;
    await updateRoute(routeId, {
      title: editingRoute.value.title,
      start_station: editingRoute.value.start_station,
      end_station: editingRoute.value.end_station,
      station_ids: editingRoute.value.station_ids,
    });
    editingRoute.value = null;
    showSaveNotice(adminFmt(cfg, 'stationsRouteSaved', title));
    flashRow(routeId);
    await reload();
  }

  async function saveStationMeta(st: StationRow) {
    if (!cfg.canManage) return;
    await updateStation(st.id, st);
    showSaveNotice(adminFmt(cfg, 'stationsStationSaved', st.title));
    flashRow(st.id);
  }

  async function removeStation(st: StationRow) {
    if (!cfg.canManage) return;
    const ok = await adminConfirm({
      title: adminStr(cfg, 'stationsDeleteStationTitle'),
      message: adminFmt(cfg, 'stationsDeleteStationMsg', st.title),
      confirmLabel: adminStr(cfg, 'delete'),
      danger: true,
    });
    if (!ok) return;
    error.value = '';
    try {
      await deleteStation(st.id);
      await reload();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'stationsDeleteStationFailed');
    }
  }

  async function removeRoute(route: RouteRow) {
    if (!cfg.canManage) return;
    const ok = await adminConfirm({
      title: adminStr(cfg, 'stationsDeleteRouteTitle'),
      message: adminFmt(cfg, 'stationsDeleteRouteMsg', route.title),
      confirmLabel: adminStr(cfg, 'delete'),
      danger: true,
    });
    if (!ok) return;
    error.value = '';
    try {
      await deleteRoute(route.id);
      if (editingRoute.value?.id === route.id) {
        editingRoute.value = null;
      }
      await reload();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'stationsDeleteRouteFailed');
    }
  }

  return {
    cfg,
    stations,
    routes,
    saveMsg,
    newStation,
    newRoute,
    sectionTab,
    editingRoute,
    loading,
    error,
    load,
    stationsById,
    isFlashed,
    stationTitle,
    addStation,
    addRoute,
    editRoute,
    saveRoute,
    saveStationMeta,
    removeStation,
    removeRoute,
  };
}
