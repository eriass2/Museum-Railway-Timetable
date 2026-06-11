import { ref, type ComponentPublicInstance, type Ref } from 'vue';
import { proceedIfDiscardAllowed } from '../adminDiscardGuard';

type StopTimesEditorRef = ComponentPublicInstance<{ getIsDirty: () => boolean }> | null;
type StoptimesPanelView = 'list' | 'detail';

export function useStoptimesPanelBack(
  viewMode: Ref<StoptimesPanelView>,
  onBack: () => void,
) {
  const stopTimesRef = ref<StopTimesEditorRef>(null);

  async function tryLeaveDetail(): Promise<boolean> {
    if (viewMode.value === 'list') {
      return true;
    }
    const dirty = stopTimesRef.value?.getIsDirty() ?? false;
    if (dirty && !(await proceedIfDiscardAllowed(true))) {
      return false;
    }
    onBack();
    return true;
  }

  async function onBackClick(): Promise<void> {
    await tryLeaveDetail();
  }

  return { stopTimesRef, tryLeaveDetail, onBackClick };
}
