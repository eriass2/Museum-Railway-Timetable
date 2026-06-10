import { describe, expect, it } from 'vitest';
import { ref } from 'vue';
import { useTimetableEditorDirty } from '../src/admin/composables/timetable-editor/useTimetableEditorDirty';
import type { TimetableDetail } from '../src/admin/types';

function makeDetail(dates: string[]): TimetableDetail {
  return {
    id: 1,
    title: 'Test',
    type: 'green',
    dates,
    services: [],
    routes: [],
    train_types: [],
  };
}

describe('useTimetableEditorDirty', () => {
  it('detects unsaved date and meta changes', () => {
    const detail = ref<TimetableDetail | null>(makeDetail(['2026-06-01']));
    const editTitle = ref('Test');
    const editType = ref('green');
    const deviationRows = ref([]);
    const { syncSnapshots, datesDirty, metaDirty } = useTimetableEditorDirty(
      detail,
      editTitle,
      editType,
      deviationRows,
    );
    syncSnapshots();
    expect(datesDirty.value).toBe(false);
    detail.value!.dates.push('2026-06-02');
    expect(datesDirty.value).toBe(true);
    editTitle.value = 'Ny titel';
    expect(metaDirty.value).toBe(true);
  });
});
