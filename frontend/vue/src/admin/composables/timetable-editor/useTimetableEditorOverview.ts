import { ref, type Ref } from 'vue';
import { getTimetableOverview } from '../../api/adminRest';
import type { AdminClientConfig } from '../../types';
import { adminErrorMessage } from '../../utils/adminLabels';
import type { TimetableOverviewPayload } from '../../../types/timetableOverview';

export function useTimetableEditorOverview(
  timetableId: () => number,
  error: Ref<string>,
  cfg: AdminClientConfig,
) {
  const overview = ref<TimetableOverviewPayload | null>(null);
  const gridOverviewLoading = ref(false);

  async function loadOverview(): Promise<void> {
    overview.value = await getTimetableOverview(timetableId());
  }

  async function loadOverviewForTab(refresh = false): Promise<void> {
    if (!refresh && overview.value) {
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

  function clearOverview(): void {
    overview.value = null;
  }

  return {
    overview,
    gridOverviewLoading,
    loadOverview,
    loadOverviewForTab,
    clearOverview,
  };
}
