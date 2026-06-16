import { computed, ref, type Ref } from 'vue';
import type { TimetableDetail } from '../../types';
import type { DeviationRow } from '../../utils/timetable-editor/deviationsPayload';
import type { TimetableEditorTab } from './timetableEditorTypes';

export type { TimetableEditorTab };
export type { DeviationRow };

function datesKey(dates: string[]): string {
  return [...dates].sort().join(',');
}

export function useTimetableEditorDirty(
  detail: Ref<TimetableDetail | null>,
  editTitle: Ref<string>,
  editType: Ref<string>,
  deviationRows: Ref<DeviationRow[]>,
) {
  const savedDatesKey = ref('');
  const savedMeta = ref({ title: '', type: '' });
  const savedDeviationsKey = ref('');

  function syncSnapshots() {
    if (!detail.value) {
      return;
    }
    savedDatesKey.value = datesKey(detail.value.dates);
    savedMeta.value = {
      title: editTitle.value.trim(),
      type: editType.value,
    };
    savedDeviationsKey.value = JSON.stringify(deviationRows.value);
  }

  const metaDirty = computed(
    () =>
      editTitle.value.trim() !== savedMeta.value.title ||
      editType.value !== savedMeta.value.type,
  );

  const datesDirty = computed(() => {
    if (!detail.value) {
      return false;
    }
    return datesKey(detail.value.dates) !== savedDatesKey.value;
  });

  const deviationsDirty = computed(
    () => JSON.stringify(deviationRows.value) !== savedDeviationsKey.value,
  );

  const dirtyByTab = computed(
    (): Record<TimetableEditorTab, boolean> => ({
      dates: datesDirty.value,
      grid: false,
      trips: false,
      stoptimes: false,
      deviations: deviationsDirty.value,
      preview: metaDirty.value,
    }),
  );

  function tabDirty(tab: TimetableEditorTab): boolean {
    if (tab === 'preview') {
      return metaDirty.value;
    }
    return dirtyByTab.value[tab];
  }

  function tabLabel(base: string, tab: TimetableEditorTab): string {
    return tabDirty(tab) ? `${base} *` : base;
  }

  return {
    syncSnapshots,
    metaDirty,
    datesDirty,
    deviationsDirty,
    dirtyByTab,
    tabDirty,
    tabLabel,
  };
}
