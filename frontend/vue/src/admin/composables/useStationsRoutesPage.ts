import { computed, ref, watch } from 'vue';
import {
  createRoute,
  createStation,
  deleteRoute,
  deleteStation,
  getPrices,
  listRoutes,
  listStations,
  updateRoute,
  updateStation,
} from '../api/adminRest';
import { adminConfirm } from './adminConfirm';
import { proceedIfDiscardAllowed } from './adminDiscardGuard';
import { useAdminResource } from './useAdminResource';
import { useAdminRowFlash } from './useAdminRowFlash';
import { useAdminSaveNotice } from './useAdminSaveNotice';
import { adminErrorMessage, adminFmt, adminStr } from '../utils/adminLabels';
import { emptyRouteDraft, emptyStationDraft } from '../utils/routeStationEditor';
import { resolveStationPriceZoneOptions, stationMissingPriceZone } from '../utils/stationPriceZones';
import type { RoutesPanelView } from '../components/AdminRoutesPanel.vue';
import type { StationsPanelView } from '../components/AdminStationsPanel.vue';
import { adminConfig } from '../types';
import type { RouteRow, StationRow } from '../types';

function routeDraftSnapshot(route: RouteRow): string {
  return JSON.stringify(route);
}

function stationDraftSnapshot(station: StationRow): string {
  return JSON.stringify(station);
}

function cloneStationRow(station: StationRow): StationRow {
  return {
    ...station,
    price_zones: [...(station.price_zones ?? [])],
    train_change_map: { ...(station.train_change_map ?? {}) },
  };
}

export function useStationsRoutesPage() {
  const cfg = adminConfig();
  const stations = ref<StationRow[]>([]);
  const routes = ref<RouteRow[]>([]);
  const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
  const { flashRow, isFlashed } = useAdminRowFlash();
  const newStation = ref(emptyStationDraft());
  const newRoute = ref(emptyRouteDraft());
  const sectionTab = ref<'stations' | 'routes'>('stations');
  const editingStation = ref<StationRow | null>(null);
  const editingRoute = ref<RouteRow | null>(null);
  const stationsView = ref<StationsPanelView>('list');
  const routesView = ref<RoutesPanelView>('list');
  const stationFormSnapshot = ref('');
  const routeFormSnapshot = ref('');
  const priceZoneOptions = ref<number[]>([...resolveStationPriceZoneOptions(undefined)]);
  const showMissingZonesOnly = ref(false);

  const { loading, error, data, load, reload } = useAdminResource({
    fetch: async () => {
      const [s, r, prices] = await Promise.all([
        listStations(),
        listRoutes(),
        getPrices().catch(() => null),
      ]);
      return {
        stations: s.items,
        routes: r.items,
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

  const visibleStations = computed(() => {
    if (!showMissingZonesOnly.value) {
      return stations.value;
    }
    return stations.value.filter((st) => stationMissingPriceZone(st));
  });

  function stationTitle(stationId: number): string {
    return stations.value.find((s) => s.id === stationId)?.title || String(stationId);
  }

  function isStationFormDirty(): boolean {
    if (stationsView.value === 'list') {
      return false;
    }
    const current =
      stationsView.value === 'edit' && editingStation.value
        ? stationDraftSnapshot(editingStation.value)
        : stationDraftSnapshot(newStation.value);
    return current !== stationFormSnapshot.value;
  }

  function backToStationsList(): void {
    editingStation.value = null;
    newStation.value = emptyStationDraft();
    stationsView.value = 'list';
    stationFormSnapshot.value = '';
  }

  async function requestBackToStationsList(): Promise<boolean> {
    if (stationsView.value !== 'list' && !(await proceedIfDiscardAllowed(isStationFormDirty()))) {
      return false;
    }
    backToStationsList();
    return true;
  }

  function startCreateStation(): void {
    if (!cfg.canManage) {
      return;
    }
    editingStation.value = null;
    newStation.value = emptyStationDraft();
    stationsView.value = 'create';
    stationFormSnapshot.value = stationDraftSnapshot(newStation.value);
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
      train_change_map: draft.train_change_map,
    });
    backToStationsList();
    await reload();
  }

  function editStation(station: StationRow) {
    sectionTab.value = 'stations';
    editingStation.value = cloneStationRow(station);
    stationsView.value = 'edit';
    stationFormSnapshot.value = stationDraftSnapshot(editingStation.value);
  }

  async function saveEditingStation() {
    if (!editingStation.value || !cfg.canManage) return;
    const title = editingStation.value.title;
    const stationId = editingStation.value.id;
    await updateStation(stationId, editingStation.value);
    backToStationsList();
    showSaveNotice(adminFmt(cfg, 'stationsStationSaved', title));
    flashRow(stationId);
    await reload();
  }

  function isRouteFormDirty(): boolean {
    if (routesView.value === 'list') {
      return false;
    }
    const current =
      routesView.value === 'edit' && editingRoute.value
        ? routeDraftSnapshot(editingRoute.value)
        : routeDraftSnapshot(newRoute.value);
    return current !== routeFormSnapshot.value;
  }

  function backToRoutesList(): void {
    editingRoute.value = null;
    newRoute.value = emptyRouteDraft();
    routesView.value = 'list';
    routeFormSnapshot.value = '';
  }

  async function requestBackToRoutesList(): Promise<boolean> {
    if (routesView.value !== 'list' && !(await proceedIfDiscardAllowed(isRouteFormDirty()))) {
      return false;
    }
    backToRoutesList();
    return true;
  }

  function startCreateRoute(): void {
    if (!cfg.canManage) {
      return;
    }
    editingRoute.value = null;
    newRoute.value = emptyRouteDraft();
    routesView.value = 'create';
    routeFormSnapshot.value = routeDraftSnapshot(newRoute.value);
  }

  watch(sectionTab, (tab, prev) => {
    if (prev === 'stations' && tab !== 'stations') {
      void requestBackToStationsList();
    }
    if (prev === 'routes' && tab !== 'routes') {
      void requestBackToRoutesList();
    }
  });

  async function addRoute() {
    const draft = newRoute.value;
    if (!cfg.canManage || !draft.title.trim()) return;
    await createRoute({
      title: draft.title.trim(),
      station_ids: draft.station_ids,
      start_station: draft.start_station || undefined,
      end_station: draft.end_station || undefined,
    });
    backToRoutesList();
    await reload();
  }

  function editRoute(route: RouteRow) {
    sectionTab.value = 'routes';
    editingRoute.value = { ...route, station_ids: [...route.station_ids] };
    routesView.value = 'edit';
    routeFormSnapshot.value = routeDraftSnapshot(editingRoute.value);
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
    backToRoutesList();
    showSaveNotice(adminFmt(cfg, 'stationsRouteSaved', title));
    flashRow(routeId);
    await reload();
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
      if (editingStation.value?.id === st.id) {
        backToStationsList();
      }
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
        backToRoutesList();
      }
      await reload();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'stationsDeleteRouteFailed');
    }
  }

  return {
    cfg,
    stations,
    visibleStations,
    priceZoneOptions,
    showMissingZonesOnly,
    routes,
    saveMsg,
    newStation,
    newRoute,
    sectionTab,
    editingStation,
    editingRoute,
    stationsView,
    routesView,
    loading,
    error,
    load,
    stationsById,
    isFlashed,
    stationTitle,
    addStation,
    addRoute,
    editStation,
    editRoute,
    saveEditingStation,
    saveRoute,
    startCreateStation,
    startCreateRoute,
    requestBackToStationsList,
    requestBackToRoutesList,
    backToStationsList,
    backToRoutesList,
    removeStation,
    removeRoute,
  };
}
