import { ref } from 'vue';
import { proceedIfDiscardAllowed } from './adminDiscardGuard';

export type AdminListEditorView = 'list' | 'create' | 'edit';

export function useAdminListEditor<TSnapshot>(snapshotFn: (value: TSnapshot) => string) {
  const viewMode = ref<AdminListEditorView>('list');
  const formSnapshot = ref('');

  function captureSnapshot(value: TSnapshot): void {
    formSnapshot.value = snapshotFn(value);
  }

  function isDirty(current: TSnapshot): boolean {
    if (viewMode.value === 'list') {
      return false;
    }
    return snapshotFn(current) !== formSnapshot.value;
  }

  async function guardBackToList(isDirtyFn: () => boolean, reset: () => void): Promise<boolean> {
    if (viewMode.value !== 'list' && !(await proceedIfDiscardAllowed(isDirtyFn()))) {
      return false;
    }
    reset();
    return true;
  }

  return {
    viewMode,
    formSnapshot,
    captureSnapshot,
    isDirty,
    guardBackToList,
  };
}
