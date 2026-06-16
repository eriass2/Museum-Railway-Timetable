import { onMounted, ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { DisruptionFeedItem, DisruptionFeedPayload } from '@/api/disruptionFeed';
import type { DisruptionFeedEditHint } from '@/utils/disruptionFeedDisplay';
import { fetchTrafficNoticesFeedPreview } from '../../api/adminRestTrafficNotices';
import { adminErrorMessage } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

function editForItem(item: DisruptionFeedItem): DisruptionFeedEditHint | null {
  if (!item.edit?.path) {
    return null;
  }
  return {
    path: item.edit.path,
    label: item.edit.label,
    query: item.edit.query,
  };
}

export function useTrafficNoticesFeedPreview(refreshKey: Ref<number | undefined>) {
  const cfg = adminConfig();
  const loading = ref(true);
  const error = ref('');
  const payload = ref<DisruptionFeedPayload | null>(null);

  async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
      payload.value = await fetchTrafficNoticesFeedPreview(90);
    } catch (e) {
      payload.value = null;
      error.value = adminErrorMessage(cfg, e, 'loadFailed');
    } finally {
      loading.value = false;
    }
  }

  onMounted(() => {
    void load();
  });

  watch(refreshKey, () => {
    void load();
  });

  return {
    cfg,
    loading,
    error,
    payload,
    editForItem,
  };
}
