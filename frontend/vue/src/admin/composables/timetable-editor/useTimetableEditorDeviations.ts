import { ref, type Ref } from 'vue';
import { getDeviations, saveDeviations } from '../../api/adminRest';
import type { AdminClientConfig } from '../../types';
import { adminErrorMessage, adminStr } from '../../utils/adminLabels';
import {
  deviationsToSavePayload,
  type DeviationRow,
} from '../../utils/timetable-editor/deviationsPayload';

type RunMutation = <T>(fn: () => Promise<T>, fallbackKey: string) => Promise<boolean>;

export function useTimetableEditorDeviations(options: {
  timetableId: () => number;
  error: Ref<string>;
  cfg: AdminClientConfig;
  syncSnapshots: () => void;
  runMutation: RunMutation;
  showSaveNotice: (message: string) => void;
}) {
  const { timetableId, error, cfg, syncSnapshots, runMutation, showSaveNotice } = options;
  const deviationRows = ref<DeviationRow[]>([]);

  async function loadDeviations(): Promise<void> {
    const res = await getDeviations(timetableId());
    deviationRows.value = res.rows;
    syncSnapshots();
  }

  async function loadDeviationsIfNeeded(): Promise<void> {
    if (deviationRows.value.length > 0) {
      return;
    }
    try {
      await loadDeviations();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'editorDeviationsLoadFailed');
    }
  }

  async function saveDeviationChanges(): Promise<void> {
    const ok = await runMutation(async () => {
      await saveDeviations(timetableId(), deviationsToSavePayload(deviationRows.value));
    }, 'saveFailed');
    if (!ok) {
      return;
    }
    syncSnapshots();
    showSaveNotice(adminStr(cfg, 'editorSavedDeviations'));
  }

  return {
    deviationRows,
    loadDeviations,
    loadDeviationsIfNeeded,
    saveDeviationChanges,
  };
}
