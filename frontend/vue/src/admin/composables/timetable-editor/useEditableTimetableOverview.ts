import { overviewUiLabels } from '../../../shared/overviewUiLabels';
import { useOverviewGridEdit } from './useOverviewGridEdit';

export function useEditableTimetableOverview(onRefreshNeeded: () => void) {
  const editor = useOverviewGridEdit();
  const labels = overviewUiLabels({});

  function onGridCellSaved(): void {
    editor.clearCache();
    onRefreshNeeded();
  }

  return { editor, labels, onGridCellSaved };
}
