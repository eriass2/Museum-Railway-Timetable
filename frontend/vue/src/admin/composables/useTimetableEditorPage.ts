import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import {
  addTimetableService,
  deleteTimetable,
  getDeviations,
  getRouteDestinations,
  getTimetable,
  getTimetableOverview,
  removeTimetableService,
  saveDeviations,
  updateTimetable,
  updateTimetableService,
} from '../api/adminRest';
import type { TripEditDraft } from '../components/timetable-editor/TimetableEditorTripEditForm.vue';
import type { TripsPanelView } from '../components/timetable-editor/TimetableEditorTripsTab.vue';
import {
  emptyTripDraft,
  tripDraftToApiBody,
} from '../components/timetable-editor/tripFormTypes';
import { adminConfirm } from './adminConfirm';
import { useAdminSaveNotice } from './useAdminSaveNotice';
import { useTimetableEditorDirty } from './useTimetableEditorDirty';
import { deviationsToSavePayload, type DeviationRow } from '../utils/deviationsPayload';
import { adminConfig } from '../types';
import type { TimetableDetail, TimetableServiceRow } from '../types';
import { adminErrorMessage, adminFmt, adminStr } from '../utils/adminLabels';
import type { TimetableOverviewPayload } from '../../types/timetableOverview';

export type TimetableServiceEditRow = TimetableServiceRow & {
  end_station_id?: number;
  highlight_label?: string;
  highlight_color?: string;
  highlight_note?: string;
};

export type StoptimesPanelView = 'list' | 'detail';

export function useTimetableEditorPage(timetableId: () => number) {
  const router = useRouter();
  const cfg = adminConfig();
  const tab = ref<'dates' | 'trips' | 'stoptimes' | 'deviations' | 'preview'>('dates');
  const detail = ref<TimetableDetail | null>(null);
  const overview = ref<TimetableOverviewPayload | null>(null);
  const loading = ref(true);
  const error = ref('');
  const dateInput = ref('');
  const newTrip = ref(emptyTripDraft());
  const destinations = ref<{ id: number; name: string }[]>([]);
  const editTrip = ref<TripEditDraft | null>(null);
  const editDestinations = ref<{ id: number; name: string }[]>([]);
  const tripsView = ref<TripsPanelView>('list');
  const stoptimesView = ref<StoptimesPanelView>('list');
  const selectedServiceId = ref(0);
  const gridOverviewLoading = ref(false);
  const deviationRows = ref<DeviationRow[]>([]);
  const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
  const editTitle = ref('');
  const editType = ref('');

  const {
    syncSnapshots,
    metaDirty,
    datesDirty,
    deviationsDirty,
    tabLabel,
  } = useTimetableEditorDirty(detail, editTitle, editType, deviationRows);

  const timetableTypes = computed(() => [
    { value: '', label: adminStr(cfg, 'editorTypeNone') },
    { value: 'green', label: adminStr(cfg, 'editorTypeGreen') },
    { value: 'yellow', label: adminStr(cfg, 'editorTypeYellow') },
    { value: 'red', label: adminStr(cfg, 'editorTypeRed') },
    { value: 'orange', label: adminStr(cfg, 'editorTypeOrange') },
  ] as const);

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

  async function loadDetail() {
    loading.value = true;
    error.value = '';
    try {
      detail.value = await getTimetable(timetableId());
      editTitle.value = detail.value.title;
      editType.value = detail.value.type || '';
      syncSnapshots();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'genericError');
    } finally {
      loading.value = false;
    }
  }

  async function loadOverview() {
    overview.value = await getTimetableOverview(timetableId());
  }

  async function loadDeviations() {
    const res = await getDeviations(timetableId());
    deviationRows.value = res.rows;
    syncSnapshots();
  }

  onMounted(() => {
    void loadDetail();
  });

  watch(timetableId, () => {
    selectedServiceId.value = 0;
    stoptimesView.value = 'list';
    tripsView.value = 'list';
    editTrip.value = null;
    overview.value = null;
    void loadDetail();
  });

  function backToTripsList(): void {
    editTrip.value = null;
    editDestinations.value = [];
    newTrip.value = emptyTripDraft();
    destinations.value = [];
    tripsView.value = 'list';
  }

  function backToStoptimesList(): void {
    selectedServiceId.value = 0;
    stoptimesView.value = 'list';
  }

  function startCreateTrip(): void {
    if (!cfg.canManage) {
      return;
    }
    editTrip.value = null;
    editDestinations.value = [];
    newTrip.value = emptyTripDraft();
    destinations.value = [];
    tripsView.value = 'create';
  }

  async function onStoptimesGridToggle(event: Event): Promise<void> {
    const el = event.target as HTMLDetailsElement;
    if (!el.open || overview.value) {
      return;
    }
    gridOverviewLoading.value = true;
    try {
      await loadOverview();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'editorOverviewLoadFailed');
    } finally {
      gridOverviewLoading.value = false;
    }
  }

  watch(tab, async (t, prev) => {
    if (prev === 'trips' && t !== 'trips') {
      backToTripsList();
    }
    if (prev === 'stoptimes' && t !== 'stoptimes') {
      backToStoptimesList();
    }
    if (t === 'preview' && !overview.value) {
      try {
        await loadOverview();
      } catch (e) {
        error.value = adminErrorMessage(cfg, e, 'editorOverviewLoadFailed');
      }
    }
    if (t === 'deviations' && deviationRows.value.length === 0) {
      try {
        await loadDeviations();
      } catch (e) {
        error.value = adminErrorMessage(cfg, e, 'editorDeviationsLoadFailed');
      }
    }
  });

  watch(
    () => newTrip.value.route_id,
    async (routeId) => {
      newTrip.value.end_station_id = 0;
      destinations.value = routeId ? (await getRouteDestinations(routeId)).destinations : [];
    },
  );

  function serviceNumberForEdit(service: TimetableServiceEditRow): string {
    return service.service_number === String(service.id) ? '' : service.service_number;
  }

  async function loadEditDestinations(routeId: number, resetEnd = false): Promise<void> {
    if (editTrip.value && resetEnd) {
      editTrip.value.end_station_id = 0;
    }
    editDestinations.value = routeId ? (await getRouteDestinations(routeId)).destinations : [];
  }

  async function startEditTrip(serviceId: number): Promise<void> {
    const service = detail.value?.services.find((s) => s.id === serviceId) as
      | TimetableServiceEditRow
      | undefined;
    if (!service || !cfg.canManage) {
      return;
    }
    editTrip.value = {
      service_id: service.id,
      service_number: serviceNumberForEdit(service),
      route_id: service.route_id,
      train_type_id: service.train_type_id,
      end_station_id: service.end_station_id ?? 0,
      highlight_label: service.highlight_label ?? '',
      highlight_color: service.highlight_color || '#fff9c4',
      highlight_note: service.highlight_note ?? '',
    };
    tripsView.value = 'edit';
    await loadEditDestinations(service.route_id);
  }

  function cancelEditTrip(): void {
    backToTripsList();
  }

  async function saveEditTrip(): Promise<void> {
    if (!editTrip.value || !cfg.canManage || editTrip.value.route_id <= 0) {
      return;
    }
    await updateTimetableService(
      timetableId(),
      editTrip.value.service_id,
      tripDraftToApiBody(editTrip.value),
    );
    backToTripsList();
    await loadDetail();
    showSaveNotice(adminStr(cfg, 'editorSavedTrip'));
  }

  async function saveDates() {
    if (!detail.value || !cfg.canManage) return;
    detail.value = await updateTimetable(timetableId(), { dates: detail.value.dates });
    syncSnapshots();
    showSaveNotice(adminStr(cfg, 'editorSavedDates'));
  }

  async function saveMeta() {
    if (!detail.value || !cfg.canManage) return;
    detail.value = await updateTimetable(timetableId(), {
      title: editTitle.value.trim(),
      type: editType.value,
    });
    editTitle.value = detail.value.title;
    editType.value = detail.value.type || '';
    syncSnapshots();
    showSaveNotice(adminStr(cfg, 'editorSavedMeta'));
  }

  async function removeTimetable() {
    if (!detail.value || !cfg.canManage) return;
    const ok = await adminConfirm({
      title: adminStr(cfg, 'timetablesDeleteTitle'),
      message: adminFmt(cfg, 'timetablesDeleteMessage', detail.value.title),
      confirmLabel: adminStr(cfg, 'delete'),
      danger: true,
    });
    if (!ok) {
      return;
    }
    try {
      await deleteTimetable(timetableId());
      await router.push('/timetables');
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'timetablesDeleteFailed');
    }
  }

  function addDate() {
    if (!detail.value || !dateInput.value) return;
    if (!detail.value.dates.includes(dateInput.value)) {
      detail.value.dates = [...detail.value.dates, dateInput.value].sort();
    }
    dateInput.value = '';
  }

  function removeDate(d: string) {
    if (!detail.value) return;
    detail.value.dates = detail.value.dates.filter((x) => x !== d);
  }

  async function addTrip() {
    if (!cfg.canManage || newTrip.value.route_id <= 0) return;
    await addTimetableService(timetableId(), tripDraftToApiBody(newTrip.value));
    backToTripsList();
    await loadDetail();
  }

  async function removeTrip(serviceId: number) {
    if (!cfg.canManage) return;
    await removeTimetableService(timetableId(), serviceId);
    await loadDetail();
  }

  async function saveDeviationChanges() {
    await saveDeviations(timetableId(), deviationsToSavePayload(deviationRows.value));
    syncSnapshots();
    showSaveNotice(adminStr(cfg, 'editorSavedDeviations'));
  }

  function openStoptimes(serviceId: number) {
    selectedServiceId.value = serviceId;
    stoptimesView.value = 'detail';
    tab.value = 'stoptimes';
  }

  function onMobileSaved(message: string) {
    showSaveNotice(message);
  }

  return {
    cfg,
    tab,
    detail,
    overview,
    loading,
    error,
    dateInput,
    newTrip,
    destinations,
    editTrip,
    editDestinations,
    tripsView,
    stoptimesView,
    selectedServiceId,
    gridOverviewLoading,
    deviationRows,
    saveMsg,
    editTitle,
    editType,
    metaDirty,
    datesDirty,
    deviationsDirty,
    tabLabel,
    timetableTypes,
    trafficToday,
    trainTypeIconKey,
    loadDetail,
    loadOverview,
    onStoptimesGridToggle,
    loadEditDestinations,
    startCreateTrip,
    backToTripsList,
    backToStoptimesList,
    startEditTrip,
    cancelEditTrip,
    saveEditTrip,
    saveDates,
    saveMeta,
    removeTimetable,
    addDate,
    removeDate,
    addTrip,
    removeTrip,
    saveDeviationChanges,
    openStoptimes,
    onMobileSaved,
  };
}
