import { ref, type Ref } from 'vue';
import {
  addTimetableService,
  removeTimetableService,
  updateTimetableService,
} from '../../api/adminRest';
import type { TripEditDraft } from '../../components/timetable-editor/TimetableEditorTripEditForm.vue';
import type { TripsPanelView } from '../../components/timetable-editor/TimetableEditorTripsTab.vue';
import {
  emptyTripDraft,
  tripDraftIsComplete,
  tripDraftSnapshot,
  tripDraftToApiBody,
} from '../../components/timetable-editor/tripFormTypes';
import type { AdminClientConfig, TimetableDetail } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import { useAdminListEditor } from '../useAdminListEditor';
import type { TimetableServiceEditRow } from './timetableEditorTypes';

type RunMutation = <T>(fn: () => Promise<T>, fallbackKey: string) => Promise<boolean>;

export function useTimetableEditorTrips(options: {
  timetableId: () => number;
  detail: Ref<TimetableDetail | null>;
  cfg: AdminClientConfig;
  runMutation: RunMutation;
  loadDetail: () => Promise<void>;
  showSaveNotice: (message: string) => void;
}) {
  const { timetableId, detail, cfg, runMutation, loadDetail, showSaveNotice } = options;
  const newTrip = ref(emptyTripDraft());
  const editTrip = ref<TripEditDraft | null>(null);
  const {
    viewMode: tripsView,
    captureSnapshot: captureTripSnapshot,
    isDirty: isTripSnapshotDirty,
    guardBackToList: guardTripsBackToList,
  } = useAdminListEditor(tripDraftSnapshot);

  function tripFormCurrent(): TripEditDraft | ReturnType<typeof emptyTripDraft> {
    return tripsView.value === 'edit' && editTrip.value ? editTrip.value : newTrip.value;
  }

  function isTripFormDirty(): boolean {
    return isTripSnapshotDirty(tripFormCurrent());
  }

  function backToTripsList(): void {
    editTrip.value = null;
    newTrip.value = emptyTripDraft();
    tripsView.value = 'list';
  }

  async function requestBackToTripsList(): Promise<boolean> {
    return guardTripsBackToList(isTripFormDirty, backToTripsList);
  }

  function startCreateTrip(): void {
    if (!cfg.canManage) {
      return;
    }
    editTrip.value = null;
    newTrip.value = emptyTripDraft();
    tripsView.value = 'create';
    captureTripSnapshot(newTrip.value);
  }

  function serviceNumberForEdit(service: TimetableServiceEditRow): string {
    return service.service_number === String(service.id) ? '' : service.service_number;
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
      line_code: service.line_code ?? '',
      toward_station_id: service.toward_station_id ?? 0,
      train_type_id: service.train_type_id,
      highlight_label: service.highlight_label ?? '',
      highlight_color: service.highlight_color || '#fff9c4',
      highlight_note: service.highlight_note ?? '',
    };
    tripsView.value = 'edit';
    captureTripSnapshot(editTrip.value);
  }

  async function cancelEditTrip(): Promise<void> {
    await requestBackToTripsList();
  }

  async function saveEditTrip(): Promise<void> {
    if (!editTrip.value || !cfg.canManage || !tripDraftIsComplete(editTrip.value)) {
      return;
    }
    const ok = await runMutation(async () => {
      await updateTimetableService(
        timetableId(),
        editTrip.value!.service_id,
        tripDraftToApiBody(editTrip.value!),
      );
    }, 'saveFailed');
    if (!ok) {
      return;
    }
    backToTripsList();
    await loadDetail();
    showSaveNotice(adminStr(cfg, 'editorSavedTrip'));
  }

  async function addTrip(): Promise<void> {
    if (!cfg.canManage || !tripDraftIsComplete(newTrip.value)) {
      return;
    }
    const ok = await runMutation(async () => {
      await addTimetableService(timetableId(), tripDraftToApiBody(newTrip.value));
    }, 'saveFailed');
    if (!ok) {
      return;
    }
    backToTripsList();
    await loadDetail();
  }

  async function removeTrip(serviceId: number): Promise<void> {
    if (!cfg.canManage) {
      return;
    }
    await runMutation(async () => {
      await removeTimetableService(timetableId(), serviceId);
      await loadDetail();
    }, 'saveFailed');
  }

  function resetTripsPanel(): void {
    backToTripsList();
  }

  return {
    newTrip,
    editTrip,
    tripsView: tripsView as typeof tripsView & { value: TripsPanelView },
    startCreateTrip,
    requestBackToTripsList,
    backToTripsList,
    startEditTrip,
    cancelEditTrip,
    saveEditTrip,
    addTrip,
    removeTrip,
    resetTripsPanel,
  };
}
