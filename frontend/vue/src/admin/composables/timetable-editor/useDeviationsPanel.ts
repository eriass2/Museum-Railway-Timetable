import { computed, ref, type Ref } from 'vue';
import type { TimetableServiceRow } from '../../types';
import {
  createDeviationRow,
  formatDeviationTripLabel,
  hasDeviationRow,
  isCancelledDeviationNotice,
  toggleCancelledDeviationNotice,
  type DeviationRow,
} from '../../utils/timetable-editor/deviationsPayload';
import { proceedIfDiscardAllowed } from '../adminDiscardGuard';

export type DeviationsPanelView = 'list' | 'edit' | 'create';

function rowSnapshot(row: DeviationRow): string {
  return JSON.stringify(row);
}

export function useDeviationsPanel(
  rows: Ref<DeviationRow[]>,
  services: Ref<TimetableServiceRow[]>,
  dates: Ref<string[]>,
  cancelledNotice: Ref<string>,
) {
  const viewMode = ref<DeviationsPanelView>('list');
  const draft = ref<DeviationRow | null>(null);
  const draftSnapshot = ref('');
  const editIndex = ref(-1);

  const isDraftDirty = computed(() => {
    if (!draft.value) {
      return false;
    }
    return rowSnapshot(draft.value) !== draftSnapshot.value;
  });

  function resetDetail(): void {
    draft.value = null;
    draftSnapshot.value = '';
    editIndex.value = -1;
    viewMode.value = 'list';
  }

  function openDraft(next: DeviationRow, mode: 'edit' | 'create', index = -1): void {
    draft.value = { ...next };
    draftSnapshot.value = rowSnapshot(draft.value);
    editIndex.value = index;
    viewMode.value = mode;
  }

  function startCreate(defaultDate: string, defaultServiceId: number): void {
    const service = services.value.find((s) => s.id === defaultServiceId);
    if (!service || !defaultDate) {
      return;
    }
    openDraft(createDeviationRow(service, defaultDate), 'create');
  }

  function startEdit(index: number): void {
    const row = rows.value[index];
    if (!row) {
      return;
    }
    openDraft(row, 'edit', index);
  }

  async function requestBackToList(): Promise<boolean> {
    if (viewMode.value !== 'list' && isDraftDirty.value) {
      if (!(await proceedIfDiscardAllowed(true))) {
        return false;
      }
    }
    resetDetail();
    return true;
  }

  function applyDraftToRows(): boolean {
    if (!draft.value) {
      return false;
    }
    if (viewMode.value === 'edit' && editIndex.value >= 0) {
      rows.value = rows.value.map((row, idx) =>
        idx === editIndex.value ? { ...draft.value! } : row,
      );
      return true;
    }
    if (
      viewMode.value === 'create' &&
      !hasDeviationRow(rows.value, draft.value.service_id, draft.value.date)
    ) {
      rows.value = [...rows.value, { ...draft.value }];
      return true;
    }
    return false;
  }

  function removeRow(index: number): void {
    rows.value = rows.value.filter((_, i) => i !== index);
    if (editIndex.value === index) {
      resetDetail();
    }
  }

  function draftIsCancelled(): boolean {
    if (!draft.value) {
      return false;
    }
    return isCancelledDeviationNotice(draft.value.notice, cancelledNotice.value);
  }

  function setDraftCancelled(cancelled: boolean): void {
    if (!draft.value) {
      return;
    }
    draft.value.notice = toggleCancelledDeviationNotice(
      draft.value.notice,
      cancelled,
      cancelledNotice.value,
    );
  }

  function updateDraftTrip(serviceId: number): void {
    if (!draft.value) {
      return;
    }
    const service = services.value.find((s) => s.id === serviceId);
    if (!service) {
      return;
    }
    draft.value.service_id = service.id;
    draft.value.trip_label = formatDeviationTripLabel(service);
    if (!draft.value.train_type_id) {
      draft.value.train_type_id = service.train_type_id || 0;
    }
  }

  function updateDraftDate(date: string): void {
    if (draft.value) {
      draft.value.date = date;
    }
  }

  const canApplyCreate = computed(() => {
    if (!draft.value || viewMode.value !== 'create') {
      return false;
    }
    return (
      draft.value.date !== '' &&
      draft.value.service_id > 0 &&
      !hasDeviationRow(rows.value, draft.value.service_id, draft.value.date)
    );
  });

  return {
    viewMode,
    draft,
    editIndex,
    isDraftDirty,
    startCreate,
    startEdit,
    requestBackToList,
    applyDraftToRows,
    removeRow,
    resetDetail,
    draftIsCancelled,
    setDraftCancelled,
    updateDraftTrip,
    updateDraftDate,
    canApplyCreate,
  };
}
