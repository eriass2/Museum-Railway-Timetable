import { computed, onMounted, ref, watch, type ComponentPublicInstance } from 'vue';
import { getTimetable } from '../../api/adminRest';
import { useAdminMutation } from '../useAdminMutation';
import { useAdminSaveNotice } from '../useAdminSaveNotice';
import { useTimetableEditorDatesMeta } from './useTimetableEditorDatesMeta';
import { useTimetableEditorDeviations } from './useTimetableEditorDeviations';
import { useTimetableEditorDirty } from './useTimetableEditorDirty';
import { useTimetableEditorOverview } from './useTimetableEditorOverview';
import { useTimetableEditorTabNavigation } from './useTimetableEditorTabNavigation';
import { useTimetableEditorTrips } from './useTimetableEditorTrips';
import type { StoptimesPanelView, TimetableEditorTab } from './timetableEditorTypes';
import { adminConfig } from '../../types';
import type { TimetableDetail } from '../../types';
import { adminErrorMessage } from '../../utils/adminLabels';
import { buildTimetableTypeOptions } from '../../utils/timetableTypeOptions';

export type {
  StoptimesPanelView,
  TimetableEditorTab,
  TimetableServiceEditRow,
} from './timetableEditorTypes';

export function useTimetableEditorPage(timetableId: () => number) {
  const cfg = adminConfig();
  const detail = ref<TimetableDetail | null>(null);
  const loading = ref(true);
  const error = ref('');
  const stoptimesView = ref<StoptimesPanelView>('list');
  const selectedServiceId = ref(0);
  const stoptimesPanelRef = ref<ComponentPublicInstance<{ requestBackToList: () => Promise<boolean> }> | null>(
    null,
  );
  const deviationsTabRef = ref<ComponentPublicInstance<{ requestBackToList: () => Promise<boolean> }> | null>(
    null,
  );
  const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
  const { runMutation } = useAdminMutation(error);

  let syncSnapshots = (): void => {};

  const datesMeta = useTimetableEditorDatesMeta({
    timetableId,
    detail,
    cfg,
    error,
    runMutation,
    syncSnapshots: () => syncSnapshots(),
    showSaveNotice,
  });

  const deviations = useTimetableEditorDeviations({
    timetableId,
    error,
    cfg,
    syncSnapshots: () => syncSnapshots(),
    runMutation,
    showSaveNotice,
  });

  const dirty = useTimetableEditorDirty(
    detail,
    datesMeta.editTitle,
    datesMeta.editType,
    deviations.deviationRows,
  );
  syncSnapshots = dirty.syncSnapshots;

  const overviewState = useTimetableEditorOverview(timetableId, error, cfg);

  async function loadDetail(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
      detail.value = await getTimetable(timetableId());
      datesMeta.syncMetaFromDetail(detail.value);
      syncSnapshots();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'genericError');
    } finally {
      loading.value = false;
    }
  }

  const trips = useTimetableEditorTrips({
    timetableId,
    detail,
    cfg,
    runMutation,
    loadDetail,
    showSaveNotice,
  });

  function backToStoptimesList(): void {
    selectedServiceId.value = 0;
    stoptimesView.value = 'list';
  }

  async function requestBackToStoptimesList(): Promise<boolean> {
    return (await stoptimesPanelRef.value?.requestBackToList()) ?? true;
  }

  async function handleTabActivated(nextTab: TimetableEditorTab): Promise<void> {
    if (nextTab === 'grid') {
      await overviewState.loadOverviewForTab(true);
      return;
    }
    if (nextTab === 'preview') {
      await overviewState.loadOverviewForTab(false);
      return;
    }
    if (nextTab === 'deviations') {
      await deviations.loadDeviationsIfNeeded();
    }
  }

  const navigation = useTimetableEditorTabNavigation({
    tripsView: trips.tripsView,
    requestBackToTripsList: trips.requestBackToTripsList,
    stoptimesView,
    requestBackToStoptimesList,
    deviationsTabRef,
    onTabActivated: handleTabActivated,
  });

  const timetableTypes = computed(() => buildTimetableTypeOptions(cfg));

  const trafficToday = computed(() => {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  });

  function trainTypeIconKey(typeId: number): string {
    if (typeId <= 0) {
      return '';
    }
    return detail.value?.train_types.find((t) => t.id === typeId)?.icon_key ?? '';
  }

  function openStoptimes(serviceId: number): void {
    void (async () => {
      if (navigation.tab.value === 'trips' && trips.tripsView.value !== 'list') {
        if (!(await trips.requestBackToTripsList())) {
          return;
        }
      }
      selectedServiceId.value = serviceId;
      stoptimesView.value = 'detail';
      navigation.tab.value = 'stoptimes';
    })();
  }

  function onMobileSaved(message: string): void {
    showSaveNotice(message);
  }

  onMounted(() => {
    navigation.initRouteTabSync();
    void loadDetail();
  });

  watch(timetableId, () => {
    selectedServiceId.value = 0;
    stoptimesView.value = 'list';
    trips.resetTripsPanel();
    overviewState.clearOverview();
    void loadDetail();
  });

  return {
    cfg,
    tab: navigation.tab,
    detail,
    overview: overviewState.overview,
    loading,
    error,
    dateInput: datesMeta.dateInput,
    newTrip: trips.newTrip,
    editTrip: trips.editTrip,
    tripsView: trips.tripsView,
    stoptimesView,
    stoptimesPanelRef,
    deviationsTabRef,
    selectedServiceId,
    gridOverviewLoading: overviewState.gridOverviewLoading,
    deviationRows: deviations.deviationRows,
    saveMsg,
    editTitle: datesMeta.editTitle,
    editType: datesMeta.editType,
    metaDirty: dirty.metaDirty,
    datesDirty: dirty.datesDirty,
    deviationsDirty: dirty.deviationsDirty,
    tabLabel: dirty.tabLabel,
    timetableTypes,
    trafficToday,
    trainTypeIconKey,
    loadDetail,
    loadOverview: overviewState.loadOverview,
    loadOverviewForTab: overviewState.loadOverviewForTab,
    startCreateTrip: trips.startCreateTrip,
    requestBackToTripsList: trips.requestBackToTripsList,
    requestBackToStoptimesList,
    switchTab: navigation.switchTab,
    leaveActiveSubView: navigation.leaveActiveSubView,
    backToTripsList: trips.backToTripsList,
    backToStoptimesList,
    startEditTrip: trips.startEditTrip,
    cancelEditTrip: trips.cancelEditTrip,
    saveEditTrip: trips.saveEditTrip,
    saveDates: datesMeta.saveDates,
    saveMeta: datesMeta.saveMeta,
    removeTimetable: datesMeta.removeTimetable,
    addDate: datesMeta.addDate,
    removeDate: datesMeta.removeDate,
    addTrip: trips.addTrip,
    removeTrip: trips.removeTrip,
    saveDeviationChanges: deviations.saveDeviationChanges,
    openStoptimes,
    onMobileSaved,
  };
}
