import { computed, ref, watch } from 'vue';
import {
  createRoute,
  createStation,
  deleteRoute,
  deleteStation,
  getPrices,
  listLines,
  listRoutes,
  listStations,
  updateLine,
  updateRoute,
  updateStation,
} from '../../api/adminRest';
import { adminConfirm } from '../adminConfirm';
import { proceedIfDiscardAllowed } from '../adminDiscardGuard';
import { useAdminResource } from '../useAdminResource';
import { useAdminRowFlash } from '../useAdminRowFlash';
import { useAdminSaveNotice } from '../useAdminSaveNotice';
import { adminErrorMessage, adminFmt, adminStr } from '../../utils/adminLabels';
import {
  emptyRouteDraft,
  emptyStationDraft,
  syncRouteTermini,
} from '../../utils/stations-routes/routeStationEditor';
import { resolveStationPriceZoneOptions, stationMissingPriceZone } from '../../utils/stations-routes/stationPriceZones';
import type { LinesPanelView } from '../../components/stations-routes/LinesPanel.vue';
import type { RoutesPanelView } from '../../components/stations-routes/RoutesPanel.vue';
import type { StationsPanelView } from '../../components/stations-routes/StationsPanel.vue';
import { adminConfig } from '../../types';
import type { LineRow, RouteRow, StationRow } from '../../types';

export type StationsRoutesSectionTab = 'stations' | 'lines' | 'routes';

function routeDraftSnapshot(route: RouteRow): string {
  return JSON.stringify(route);
}

function lineDraftSnapshot(line: LineRow): string {
  return JSON.stringify({ code: line.code, title: line.title });
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
  const lines = ref<LineRow[]>([]);
  const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
  const { flashRow, isFlashed } = useAdminRowFlash();
  const newStation = ref(emptyStationDraft());
  const newRoute = ref(emptyRouteDraft());
  const sectionTab = ref<StationsRoutesSectionTab>('stations');
  const editingStation = ref<StationRow | null>(null);
  const editingRoute = ref<RouteRow | null>(null);
  const stationsView = ref<StationsPanelView>('list');
  const routesView = ref<RoutesPanelView>('list');
  const linesView = ref<LinesPanelView>('list');
  const editingLine = ref<LineRow | null>(null);
  const stationFormSnapshot = ref('');
  const routeFormSnapshot = ref('');
  const lineFormSnapshot = ref('');
  const priceZoneOptions = ref<number[]>([...resolveStationPriceZoneOptions(undefined)]);
  const showMissingZonesOnly = ref(false);

  async function fetchLineRows(): Promise<LineRow[]> {
    try {
      const payload = await listLines();
      return payload?.items ?? [];
    } catch {
      return [];
    }
  }

  const { loading, error, data, load, reload } = useAdminResource({
    fetch: async () => {
      const [s, lines, prices] = await Promise.all([
        listStations(),
        fetchLineRows(),
        getPrices().catch(() => null),
      ]);
      const lineRows = lines ?? [];
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
    if (prev === 'lines' && tab !== 'lines') {
      void requestBackToLinesList();
    }
    if (prev === 'routes' && tab !== 'routes') {
      void requestBackToRoutesList();
    }
  });

  function isLineFormDirty(): boolean {
    if (linesView.value === 'list' || !editingLine.value) {
      return false;
    }
    return lineDraftSnapshot(editingLine.value) !== lineFormSnapshot.value;
  }

  function backToLinesList(): void {
    editingLine.value = null;
    linesView.value = 'list';
    lineFormSnapshot.value = '';
  }

  async function requestBackToLinesList(): Promise<boolean> {
    if (linesView.value !== 'list' && !(await proceedIfDiscardAllowed(isLineFormDirty()))) {
      return false;
    }
    backToLinesList();
    return true;
  }

  function editLine(line: LineRow): void {
    sectionTab.value = 'lines';
    editingLine.value = { ...line };
    linesView.value = 'edit';
    lineFormSnapshot.value = lineDraftSnapshot(editingLine.value);
  }

  async function saveLine(): Promise<void> {
    if (!editingLine.value || !cfg.canManage) {
      return;
    }
    const title = editingLine.value.title.trim();
    const code = editingLine.value.code;
    if (title === '') {
      return;
    }
    await updateLine(code, { title });
    backToLinesList();
    showSaveNotice(adminFmt(cfg, 'stationsLineSaved', title));
    await reload();
  }

  async function addRoute() {
    const draft = newRoute.value;
    if (!cfg.canManage || !draft.title.trim()) return;
    if (draft.station_ids.length < 2) {
      error.value = adminStr(cfg, 'stationsRouteMinStations');
      return;
    }
    const route = syncRouteTermini(draft);
    await createRoute({
      title: route.title.trim(),
      station_ids: route.station_ids,
    });
    backToRoutesList();
    await reload();
  }

  function editRoute(route: RouteRow) {
    if (hasLineRegistry.value) {
      return;
    }
    sectionTab.value = 'routes';
    editingRoute.value = syncRouteTermini({
      ...route,
      station_ids: [...route.station_ids],
    });
    routesView.value = 'edit';
    routeFormSnapshot.value = routeDraftSnapshot(editingRoute.value);
  }

  async function saveRoute() {
    if (!editingRoute.value || !cfg.canManage) return;
    if (editingRoute.value.station_ids.length < 2) {
      error.value = adminStr(cfg, 'stationsRouteMinStations');
      return;
    }
    const title = editingRoute.value.title;
    const routeId = editingRoute.value.id;
    const route = syncRouteTermini(editingRoute.value);
    await updateRoute(routeId, {
      title: route.title,
      station_ids: route.station_ids,
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
    lines,
    hasLineRegistry,
    saveMsg,
    newStation,
    newRoute,
    sectionTab,
    editingStation,
    editingRoute,
    stationsView,
    routesView,
    linesView,
    editingLine,
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
    requestBackToLinesList,
    editLine,
    saveLine,
    backToStationsList,
    backToLinesList,
    backToRoutesList,
    removeStation,
    removeRoute,
  };
}
