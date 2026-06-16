import { computed, ref, watch, type Ref } from 'vue';
import {
  createRoute,
  createStation,
  deleteRoute,
  deleteStation,
  updateLine,
  updateRoute,
  updateStation,
} from '../../api/adminRest';
import { adminConfirm } from '../adminConfirm';
import { useAdminListEditor } from '../useAdminListEditor';
import { useAdminRowFlash } from '../useAdminRowFlash';
import { useAdminSaveNotice } from '../useAdminSaveNotice';
import { useAdminMutation } from '../useAdminMutation';
import { adminFmt, adminStr } from '../../utils/adminLabels';
import {
  emptyRouteDraft,
  emptyStationDraft,
  syncRouteTermini,
} from '../../utils/stations-routes/routeStationEditor';
import type { LinesPanelView } from '../../components/stations-routes/LinesPanel.vue';
import type { LineRow, RouteRow, StationRow } from '../../types';
import { useStationsRoutesData, type StationsRoutesSectionTab } from './useStationsRoutesData';

export type { StationsRoutesSectionTab };

function routeDraftSnapshot(route: RouteRow): string {
  return JSON.stringify(route);
}

function lineDraftSnapshot(line: LineRow): string {
  return JSON.stringify({
    code: line.code,
    title: line.title,
    station_ids: line.station_ids,
  });
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
  const {
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
  } = useStationsRoutesData();
  const { runMutation } = useAdminMutation(error);
  const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
  const { flashRow, isFlashed } = useAdminRowFlash();
  const newStation = ref(emptyStationDraft());
  const newRoute = ref(emptyRouteDraft());
  const sectionTab = ref<StationsRoutesSectionTab>('stations');
  const editingStation = ref<StationRow | null>(null);
  const editingRoute = ref<RouteRow | null>(null);
  const editingLine = ref<LineRow | null>(null);

  const {
    viewMode: stationsView,
    captureSnapshot: captureStationSnapshot,
    isDirty: isStationSnapshotDirty,
    guardBackToList: guardStationsBackToList,
  } = useAdminListEditor(stationDraftSnapshot);
  const {
    viewMode: routesView,
    captureSnapshot: captureRouteSnapshot,
    isDirty: isRouteSnapshotDirty,
    guardBackToList: guardRoutesBackToList,
  } = useAdminListEditor(routeDraftSnapshot);
  const {
    viewMode: linesView,
    captureSnapshot: captureLineSnapshot,
    isDirty: isLineSnapshotDirty,
    guardBackToList: guardLinesBackToList,
  } = useAdminListEditor(lineDraftSnapshot);

  function stationFormCurrent(): StationRow {
    return stationsView.value === 'edit' && editingStation.value
      ? editingStation.value
      : newStation.value;
  }

  function isStationFormDirty(): boolean {
    return isStationSnapshotDirty(stationFormCurrent());
  }

  function backToStationsList(): void {
    editingStation.value = null;
    newStation.value = emptyStationDraft();
    stationsView.value = 'list';
  }

  async function requestBackToStationsList(): Promise<boolean> {
    return guardStationsBackToList(isStationFormDirty, backToStationsList);
  }

  function startCreateStation(): void {
    if (!cfg.canManage) {
      return;
    }
    editingStation.value = null;
    newStation.value = emptyStationDraft();
    stationsView.value = 'create';
    captureStationSnapshot(newStation.value);
  }

  async function addStation() {
    const draft = newStation.value;
    if (!cfg.canManage || !draft.title.trim()) return;
    const ok = await runMutation(
      () =>
        createStation({
          title: draft.title.trim(),
          station_type: draft.station_type || undefined,
          lat: draft.lat || undefined,
          lng: draft.lng || undefined,
          bus_suffix: draft.bus_suffix,
          display_order: draft.display_order || undefined,
          price_zones: draft.price_zones,
          train_change_map: draft.train_change_map,
        }),
      'stationsSaveStationFailed',
    );
    if (!ok) return;
    backToStationsList();
    await reload();
  }

  function editStation(station: StationRow) {
    sectionTab.value = 'stations';
    editingStation.value = cloneStationRow(station);
    stationsView.value = 'edit';
    captureStationSnapshot(editingStation.value);
  }

  async function saveEditingStation() {
    if (!editingStation.value || !cfg.canManage) return;
    const title = editingStation.value.title;
    const stationId = editingStation.value.id;
    const ok = await runMutation(
      () => updateStation(stationId, editingStation.value!),
      'stationsSaveStationFailed',
    );
    if (!ok) return;
    backToStationsList();
    showSaveNotice(adminFmt(cfg, 'stationsStationSaved', title));
    flashRow(stationId);
    await reload();
  }

  function routeFormCurrent(): RouteRow {
    return routesView.value === 'edit' && editingRoute.value
      ? editingRoute.value
      : newRoute.value;
  }

  function isRouteFormDirty(): boolean {
    return isRouteSnapshotDirty(routeFormCurrent());
  }

  function backToRoutesList(): void {
    editingRoute.value = null;
    newRoute.value = emptyRouteDraft();
    routesView.value = 'list';
  }

  async function requestBackToRoutesList(): Promise<boolean> {
    return guardRoutesBackToList(isRouteFormDirty, backToRoutesList);
  }

  function startCreateRoute(): void {
    if (!cfg.canManage) {
      return;
    }
    editingRoute.value = null;
    newRoute.value = emptyRouteDraft();
    routesView.value = 'create';
    captureRouteSnapshot(newRoute.value);
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
    if (!editingLine.value) {
      return false;
    }
    return isLineSnapshotDirty(editingLine.value);
  }

  const linesDirty = computed(() => linesView.value === 'edit' && isLineFormDirty());

  function backToLinesList(): void {
    editingLine.value = null;
    linesView.value = 'list';
  }

  async function requestBackToLinesList(): Promise<boolean> {
    return guardLinesBackToList(isLineFormDirty, backToLinesList);
  }

  function editLine(line: LineRow): void {
    sectionTab.value = 'lines';
    editingLine.value = { ...line, station_ids: [...line.station_ids] };
    linesView.value = 'edit';
    captureLineSnapshot(editingLine.value);
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
    if (editingLine.value.station_ids.length < 2) {
      error.value = adminStr(cfg, 'stationsLineMinStations');
      return;
    }
    const kind = editingLine.value.kind;
    if ((kind === 'branch' || kind === 'pattern') && editingLine.value.station_ids.length !== 2) {
      error.value = adminStr(cfg, 'stationsLineBranchTwoOnly');
      return;
    }
    const ok = await runMutation(
      () =>
        updateLine(code, {
          title,
          station_ids: editingLine.value!.station_ids,
        }),
      'stationsSaveLineFailed',
    );
    if (!ok) return;
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
    const ok = await runMutation(
      () =>
        createRoute({
          title: route.title.trim(),
          station_ids: route.station_ids,
        }),
      'stationsSaveRouteFailed',
    );
    if (!ok) return;
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
    captureRouteSnapshot(editingRoute.value);
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
    const ok = await runMutation(
      () =>
        updateRoute(routeId, {
          title: route.title,
          station_ids: route.station_ids,
        }),
      'stationsSaveRouteFailed',
    );
    if (!ok) return;
    backToRoutesList();
    showSaveNotice(adminFmt(cfg, 'stationsRouteSaved', title));
    flashRow(routeId);
    await reload();
  }

  async function removeStation(st: StationRow) {
    if (!cfg.canManage) return;
    const confirmed = await adminConfirm({
      title: adminStr(cfg, 'stationsDeleteStationTitle'),
      message: adminFmt(cfg, 'stationsDeleteStationMsg', st.title),
      confirmLabel: adminStr(cfg, 'delete'),
      danger: true,
    });
    if (!confirmed) return;
    await runMutation(async () => {
      await deleteStation(st.id);
      if (editingStation.value?.id === st.id) {
        backToStationsList();
      }
      await reload();
    }, 'stationsDeleteStationFailed');
  }

  async function removeRoute(route: RouteRow) {
    if (!cfg.canManage) return;
    const confirmed = await adminConfirm({
      title: adminStr(cfg, 'stationsDeleteRouteTitle'),
      message: adminFmt(cfg, 'stationsDeleteRouteMsg', route.title),
      confirmLabel: adminStr(cfg, 'delete'),
      danger: true,
    });
    if (!confirmed) return;
    await runMutation(async () => {
      await deleteRoute(route.id);
      if (editingRoute.value?.id === route.id) {
        backToRoutesList();
      }
      await reload();
    }, 'stationsDeleteRouteFailed');
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
    linesView: linesView as Ref<LinesPanelView>,
    linesDirty,
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
