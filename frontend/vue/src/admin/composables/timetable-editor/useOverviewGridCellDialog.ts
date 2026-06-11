import { reactive, ref, type Ref } from 'vue';
import type { TimetableTimeCellEdit } from '../../../types/timetableOverview';
import type { OverviewGridEdit } from './useOverviewGridEdit';
import { finalizeGridCellEdit } from '../../utils/timetable-editor/gridCellEdit';

type GridCellDialogProps = {
  serviceId: number;
  stationId: number;
  rowKind: string;
  edit: TimetableTimeCellEdit | undefined;
  editor: OverviewGridEdit;
};

function emptyGridCellDraft(): TimetableTimeCellEdit {
  return {
    arrival: '',
    departure: '',
    stopsHere: false,
    pickupMode: 'scheduled',
    dropoffMode: 'scheduled',
    approximateTime: false,
  };
}

export function useOverviewGridCellDialog(
  props: GridCellDialogProps,
  dialogRef: Ref<HTMLDialogElement | null>,
  onSaved: () => void,
) {
  const saving = ref(false);
  const draft = reactive<TimetableTimeCellEdit>(emptyGridCellDraft());

  function resetDraft(): void {
    const base = props.editor.mergeEdit(props.serviceId, props.stationId, props.edit, {});
    draft.arrival = props.editor.hhmmToInput(base.arrival);
    draft.departure = props.editor.hhmmToInput(base.departure);
    draft.stopsHere = base.stopsHere;
    draft.pickupMode = base.pickupMode;
    draft.dropoffMode = base.dropoffMode;
    draft.approximateTime = base.approximateTime;
  }

  function openDialog(): void {
    resetDraft();
    dialogRef.value?.showModal();
  }

  function closeDialog(): void {
    dialogRef.value?.close();
  }

  async function save(): Promise<void> {
    if (saving.value) {
      return;
    }
    saving.value = true;
    try {
      const payload = finalizeGridCellEdit(
        {
          stopsHere: draft.stopsHere,
          arrival: props.editor.inputToHhmm(draft.arrival),
          departure: props.editor.inputToHhmm(draft.departure),
          pickupMode: draft.pickupMode,
          dropoffMode: draft.dropoffMode,
          approximateTime: draft.approximateTime,
        },
        props.rowKind,
      );
      await props.editor.applyCellEdit(props.serviceId, props.stationId, payload);
      onSaved();
      closeDialog();
    } finally {
      saving.value = false;
    }
  }

  return { draft, saving, openDialog, closeDialog, save };
}
